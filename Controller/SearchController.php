<?php

namespace Msi\AdminBundle\Controller;

use Msi\BaseBundle\Controller\Controller;
use Doctrine\Common\Collections\ArrayCollection;

class SearchController extends Controller
{
    public function indexAction()
    {
        $parameters['q'] = $this->getRequest()->query->get('q');
        $parameters['a'] = $this->getRequest()->query->get('a');

        if ($parameters['q']) {
            if ($parameters['a']) {
                $parameters['results'] = $this->findSpecificResults();
            } else {
                $parameters['results'] = $this->findSummaryResults();
            }
        }

        return $this->render('MsiAdminBundle:Search:index.html.twig', $parameters);
    }

    public function findSummaryResults()
    {
        $parameters['q'] = $this->getRequest()->query->get('q');

        foreach ($this->container->getParameter('msi_admin.admin_ids') as $id) {
            $admin = $this->get($id);
            if ($admin->getOption('search_fields')) {
                if ($admin->hasTrait('Translatable')) {
                    $join['a.translations'] = 'translations';
                } else {
                    $join = [];
                }

                $qb = $admin->getObjectManager()->getSearchQueryBuilder(
                    $parameters['q'],
                    $admin->getOption('search_fields'),
                    [],
                    $join
                );

                $results[$id]['pager'] = $this->get('msi_admin.pager.factory')->create($qb);
                $results[$id]['pager']->paginate($this->getRequest()->query->get('page', 1), 3);
                $results[$id]['results'] = new ArrayCollection(
                    $results[$id]['pager']->getIterator()->getArrayCopy()
                );
                $results[$id]['admin'] = $admin;
            }
        }

        return $results ?: [];
    }

    public function findSpecificResults()
    {

    }
}
