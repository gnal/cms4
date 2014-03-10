<?php

namespace Msi\AdminBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Finder\Finder;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

use Msi\BaseBundle\Controller\Controller;
use Msi\AdminBundle\Event\FilterResponseEntityEvent;
use Msi\AdminBundle\Event\GetResponseEntityEvent;

use Msi\AdminBundle\MsiAdminEvents;

class CoreController extends Controller
{
    protected $admin;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->admin = $this->get($this->get('request')->attributes->get('_admin'));
    }

    public function indexAction(Request $request)
    {
        $this->isGranted('read');

        $qb = $this->getIndexQueryBuilder();

        // Filters
        $parameters = [];
        $filterForm = $this->admin->getForm('filter');

        if (count($filterForm->all())) {
            $this->get('msi_admin.filter.form.handler')->process($filterForm, $this->admin->getObject(), $qb);
            $parameters['filterForm'] = $filterForm->createView();
        }

        // Pager
        $pager = $this->get('msi_admin.pager.factory')->create($qb, array('attr' => array('class' => 'pull-left')));
        $pager->paginate($request->query->get('page', 1) ?: 1, 50);

        // Table
        $grid = $this->admin->getGrid();
        if (property_exists($this->admin->getObjectManager()->getClass(), 'position')) {
            $grid->setSortable(true);
        }

        $result = new ArrayCollection($pager->getIterator()->getArrayCopy());
        $this->admin->postLoad($result);
        $grid->setRows($result);

        $parameters['pager'] = $pager;

        return $this->render($this->admin->getOption('index_template'), $parameters);
    }

    public function newAction(Request $request)
    {
        // check acl
        $this->isGranted('create');
        $this->isGranted('ACL_CREATE', $this->admin->getObject());

        $event = new GetResponseEntityEvent($this->admin->getObject(), $this->getRequest());
        $this->get('event_dispatcher')->dispatch(MsiAdminEvents::ENTITY_NEW_INIT, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        // if post
        if ($this->processForm()) {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                $this->addSuccessFlash();
            }

            if ($this->getRequest()->isXmlHttpRequest()) {
                $qb = $this->admin->getObjectManager()->getMasterQueryBuilder(
                    ['a.id' => $this->admin->getForm()->getData()->getId()]
                );

                if ($this->admin->hasTrait('Translatable')) {
                    $qb->leftJoin('a.translations', 't');
                    $qb->addSelect('t');
                    $qb->andWhere($qb->expr()->eq('t.locale', ':dalocale'))->setParameter('dalocale', $this->getRequest()->getLocale());
                }

                $entity = $qb->getQuery()->getArrayResult()[0];
                return new JsonResponse([
                    'entity' => $entity,
                    'status' => 'ok',
                ]);
            }

            if ($request->query->get('alt') === 'quit') {
                return $this->getResponse();
            } elseif ($request->query->get('alt') === 'add') {
                return new RedirectResponse($this->admin->genUrl('new'));
            } else {
                return new RedirectResponse($this->admin->genUrl('edit', ['id' => $this->admin->getObject()->getId()]));
            }
        } else {
            // huh
            if (in_array($request->getMethod(), array('POST', 'PUT'))) {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('error!'));
            }
        }

        $parameters['form'] = $this->admin->getForm()->createView();

        $this->admin->newPreRender($parameters);

        return $this->render($this->admin->getOption('new_template'), $parameters);
    }

    public function editAction(Request $request)
    {
        // check acl
        if ($request->getMethod() === 'GET') {
            $this->isGranted('read');
            $this->isGranted('ACL_READ', $this->admin->getObject());
        } else {
            $this->isGranted('update');
            $this->isGranted('ACL_UPDATE', $this->admin->getObject());
        }

        $event = new GetResponseEntityEvent($this->admin->getObject(), $this->getRequest());
        $this->get('event_dispatcher')->dispatch(MsiAdminEvents::ENTITY_EDIT_INIT, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        // if post
        if ($this->processForm()) {
            if (!$this->getRequest()->isXmlHttpRequest()) {
                $this->addSuccessFlash();
            }

            if ($request->query->get('alt') === 'quit') {
                $response = $this->getResponse();
            } else {
                if ($this->getRequest()->isXmlHttpRequest()) {
                    $qb = $this->admin->getObjectManager()->getMasterQueryBuilder(
                        ['a.id' => $this->admin->getForm()->getData()->getId()]
                    );

                    if ($this->admin->hasTrait('Translatable')) {
                        $qb->leftJoin('a.translations', 't');
                        $qb->addSelect('t');
                        $qb->andWhere($qb->expr()->eq('t.locale', ':dalocale'))->setParameter('dalocale', $this->getRequest()->getLocale());
                    }

                    $entity = $qb->getQuery()->getArrayResult()[0];
                    $response = new JsonResponse([
                        'entity' => $entity,
                        'status' => 'ok',
                    ]);
                } else {
                    $response = $this->render($this->admin->getOption('edit_template'), ['form' => $this->admin->getForm()->createView()]);
                }
            }

            $event = new FilterResponseEntityEvent($this->admin->getObject(), $this->getRequest(), $response);
            $this->get('event_dispatcher')->dispatch(MsiAdminEvents::ENTITY_EDIT_COMPLETED, $event);

            return $response;
        } else {
            // huh
            if (in_array($request->getMethod(), ['POST', 'PUT'])) {
                $this->get('session')->getFlashBag()->add('error', $this->get('translator')->trans('error!'));
            }
        }

        $parameters['form'] = $this->admin->getForm()->createView();

        $this->admin->editPreRender($parameters);

        $response = $this->render($this->admin->getOption('edit_template'), $parameters);

        return $response;
    }

    public function deleteAction()
    {
        $this->isGranted('delete');
        $this->isGranted('ACL_DELETE', $this->admin->getObject());

        $event = new GetResponseEntityEvent($this->admin->getObject(), $this->getRequest());
        $this->get('event_dispatcher')->dispatch(MsiAdminEvents::ENTITY_DELETE_INIT, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $this->admin->getObjectManager()->delete($this->admin->getObject());

        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->addFlash('success', $this->container->get('translator')->trans('delete_success'));
        }

        return $this->getResponse();
    }

    public function deleteUploadAction()
    {
        $this->isGranted('update');
        $this->isGranted('ACL_UPDATE', $this->admin->getObject());

        if ($this->getRequest()->query->get('locale')) {
            $entity = $this->admin->getObject()->getTranslation($this->getRequest()->query->get('locale'));
        } else {
            $entity = $this->admin->getObject();
        }

        $this->get('msi_admin.uploader')->removeUpload($this->getRequest()->query->get('field'), $entity);
        $setter = 'set'.ucfirst($this->getRequest()->query->get('field'));
        $entity->$setter(null);
        $this->admin->getObjectManager()->update($this->admin->getObject());

        return $this->redirect($this->admin->genUrl('edit', ['id' => $this->admin->getObject()->getId()]));
    }

    public function toggleAction()
    {
        $this->isGranted('update');
        $this->isGranted('ACL_UPDATE', $this->admin->getObject());

        $event = new GetResponseEntityEvent($this->admin->getObject(), $this->getRequest());
        $this->get('event_dispatcher')->dispatch(MsiAdminEvents::ENTITY_TOGGLE_INIT, $event);

        $this->admin->getObjectManager()->toggle($this->admin->getObject(), $this->getRequest()->query->get('field'), $this->get('msi_admin.provider')->getWorkingLocale());

        $response = new Response();

        $event = new FilterResponseEntityEvent($this->admin->getObject(), $this->getRequest(), $response);
        $this->get('event_dispatcher')->dispatch(MsiAdminEvents::ENTITY_TOGGLE_COMPLETED, $event);

        return $response;
    }

    public function sortAction(Request $request)
    {
        $this->isGranted('update');
        $this->isGranted('ACL_UPDATE', $this->admin->getObject());

        $itemId = $request->query->get('current');
        $nextItemId = $request->query->get('next');
        $prevItemId = $request->query->get('prev');

        $rows = $this->admin->getObjectManager()->getFindByQueryBuilder([], [], ['a.position' => 'ASC'])->getQuery()->execute();
        $item = $this->admin->getObjectManager()->find(['a.id' => $itemId]);

        $i = 1;
        foreach ($rows as $row) {
            if ($row->getId() == $itemId) continue;

            if (!$nextItemId && $row->getId() == $prevItemId) {
                $item->setPosition($i+1);
                $this->admin->getObjectManager()->update($item);
            } elseif ($row->getId() == $nextItemId) {
                $item->setPosition($i);
                $this->admin->getObjectManager()->update($item);
                $i++;
            }

            $row->setPosition($i);
            $this->admin->getObjectManager()->update($row);
            $i++;
        }

        return new Response();
    }

    public function exportCsvAction()
    {
        $qb = $this->admin->getObjectManager()->getMasterQueryBuilder();

        $this->admin->buildCsvQuery($qb);

        $rows = $qb->getQuery()->execute();

        $csv = $this->admin->buildCsv($rows);

        $filename = $this->admin->getCsvFilename().".csv";

        $response = new Response();
        $response->headers->set("Content-type", "text/csv; charset=utf-8");
        $response->headers->set("Content-Disposition", "attachment; filename=".$filename);
        $response->headers->set("Pragma", "no-cache");
        $response->headers->set("Expires", "0");
        $response->setContent($csv);

        return $response;
    }

    protected function getIndexQueryBuilder()
    {
        $where = [];
        $join = [];
        $sort = $this->admin->getOption('order_by');

        // sortable
        if ($this->admin->hasTrait('Sortable')) {
            $sort = ['a.position' => 'ASC'];
        }

        // translations
        if ($this->admin->hasTrait('Translatable')) {
            $join['a.translations'] = 'translations';
            // $where['t.locale'] = $this->getRequest()->query->get('locale', $this->getRequest()->getLocale());
        }

        // nested crud
        if ($this->admin->hasParent() && $this->get('request')->query->get('parentId')) {
            foreach ($this->admin->getObjectManager()->getMetadata()->associationMappings as $association) {
                if (in_array($association['type'], [8, 2]) && $association['targetEntity'] === $this->admin->getParent()->getClass()) {
                    $relation = $association;
                }
            }
            if ($relation['type'] === 8) {
                $join['a.'.$relation['fieldName']] = $relation['fieldName'];
                $where[$relation['fieldName'].'.id'] = $this->get('request')->query->get('parentId');
            } else {
                $where['a.'.$relation['fieldName']] = $this->get('request')->query->get('parentId');
            }
        }

        if (!$this->get('request')->query->get('q')) {
            $qb = $this->admin->getObjectManager()->getFindByQueryBuilder($where, $join, $sort);
        } else {
            $qb = $this->admin->getObjectManager()->getSearchQueryBuilder($this->get('request')->query->get('q'), $this->admin->getOption('search_fields'), $where, $join, $sort);
        }

        // soft delete
        if ($this->admin->hasTrait('SoftDeletable')) {
            $qb->andWhere($qb->expr()->isNull('a.deletedAt'));
        }

        $this->admin->buildListQuery($qb);

        return $qb;
    }

    protected function processForm()
    {
        $form = $this->admin->getForm();
        $process = $this->get('msi_admin.admin.form.handler')->setAdmin($this->admin)->process($form);

        return $process;
    }

    protected function getResponse()
    {
        // if ($this->get('request')->isXmlHttpRequest()) {
        //     $defaultData = [
        //         'status' => 'ok',
        //     ];
        //     $data = array_merge($defaultData, $data);

        //     return new JsonResponse($data);
        // } else {
            return $this->redirect($this->admin->getSaveAndQuitRoute());
        // }
    }

    protected function isGranted($role, $object = null)
    {
        if ($object !== null) {
            if (!$this->get('security.context')->isGranted($role, $this->admin->getObject())) {
                throw new AccessDeniedException();
            }
        } else {
            if (!$this->admin->isGranted($role)) {
                throw new AccessDeniedException();
            }
        }
    }

    protected function addSuccessFlash()
    {
        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('alert_success', ['%label%' => strtolower($this->admin->getLabel(1))]));
    }
}
