<?php

namespace Msi\AdminBundle\Admin;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Msi\AdminBundle\Doctrine\Manager;
use Doctrine\Common\Collections\ArrayCollection;
use Msi\AdminBundle\Form\Type\DynamicType;

use Symfony\Component\Form\FormBuilder;
use Msi\AdminBundle\Grid\GridBuilder;
use Doctrine\ORM\QueryBuilder;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class Admin
{
    protected $options = [];

    protected $id;
    protected $children = [];
    protected $parent;
    protected $entity;
    protected $parentEntity;
    protected $container;
    protected $object = null;
    protected $parentObject = null;
    protected $forms = [];
    protected $objectManager;
    protected $grids;
    protected $defaultPersistant = [];

    public function __construct(Manager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    abstract public function buildGrid(GridBuilder $builder);

    public function buildForm(FormBuilder $builder)
    {
    }

    public function buildTranslationForm(FormBuilder $builder)
    {
    }

    public function buildFilterForm(FormBuilder $builder)
    {
    }

    public function buildListQuery(QueryBuilder $qb)
    {
    }

    public function configure()
    {
    }

    public function prePersist($entity)
    {
    }

    public function postPersist($entity)
    {
    }

    public function preUpdate($entity)
    {
    }

    public function postUpdate($entity)
    {
    }

    public function postLoad(ArrayCollection $collection)
    {
    }

    public function newPreRender(&$parameters)
    {
    }

    public function editPreRender(&$parameters)
    {
    }

    public function buildCsvQuery(QueryBuilder $qb)
    {
    }

    public function buildCsv($rows)
    {
        return '';
    }

    public function fixCsv($string)
    {
        $string = utf8_decode($string);

        $string = '"'.$string.'",';

        return $string;
    }

    public function getCsvFilename()
    {
        return $this->id;
    }

    public function getSearchFields()
    {
        foreach ($this->getOption('search_fields') as $field) {
            $parts = explode('.', $field);
            $dada[$parts[0]] = $parts[1];
        }

        return $dada;
    }

    public function getLabel($number = 1, $locale = null)
    {
        return $this->container->get('translator')->transChoice('entity.'.$this->getClassName(), $number, [], 'messages', $locale);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        $this->configure();
        $this->init();

        return $this;
    }

    public function getBundleName()
    {
        $parts = explode('_', $this->id);

        return ucfirst($parts[0]).ucfirst($parts[1]).'Bundle';
    }

    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    public function getAction()
    {
        preg_match('@[a-z]+$@', $this->container->get('request')->attributes->get('_route'), $matches);

        return $matches[0];
    }

    public function hasTrait($traitName, $namespace = 'Msi\AdminBundle\Doctrine\Extension\Model\\')
    {
        return $this->container->get('msi_admin.class_analyzer')->hasTrait($this->getMetadata()->reflClass, $namespace.$traitName);
    }

    public function isSortable()
    {
        return $this->container->get('msi_admin.class_analyzer')->hasTrait($this->getMetadata()->reflClass, 'Msi\AdminBundle\Doctrine\Extension\Model\Sortable');
    }

    public function isUploadable()
    {
        return $this->container->get('msi_admin.class_analyzer')->hasTrait($this->getMetadata()->reflClass, 'Msi\AdminBundle\Doctrine\Extension\Model\Uploadable');
    }

    public function isTranslatable()
    {
        return $this->container->get('msi_admin.class_analyzer')->hasTrait($this->getMetadata()->reflClass, 'Msi\AdminBundle\Doctrine\Extension\Model\Translatable');
    }

    public function getMetadata()
    {
        return $this->getObjectManager()->getMetadata();
    }

    public function isTranslationField($field)
    {
        if (!$this->isTranslatable()) {
            return false;
        }

        return property_exists($this->getObject()->getTranslation(), $field);
    }

    public function getClass()
    {
        return $this->getObjectManager()->getClass();
    }

    public function getClassName()
    {
        return $this->getMetadata()->reflClass->getShortName();
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }

    public function getObject()
    {
        if (!$this->object) {
            $where = $this->getRequest()->get('id') ? ['a.id' => $this->getRequest()->get('id')] : [];
            $this->object = $this->objectManager->findOrCreate(
                $this->container->get('msi_admin.provider')->getWorkingLocale(),
                $where
            );
        }

        return $this->object;
    }

    public function getParentObject($child = null)
    {
        if (!$this->hasParent()) {
            return null;
        }

        if ($child) {
            $getter = 'get'.ucfirst($this->getParentFieldName());
            $parentStuff = $child->$getter();
            $parentClass = $this->getParent()->getClass();

            return $parentStuff instanceof $parentClass ? $child->$getter() : null;
        }

        if (!$this->parentObject) {
            $where = $this->getRequest()->get('parentId') ? ['a.id' => $this->getRequest()->get('parentId')] : [];
            $this->parentObject = $this->getParent()->objectManager->findOrCreate(
                $this->container->get('msi_admin.provider')->getWorkingLocale(),
                $where
            );
        }

        return $this->parentObject;
    }

    public function getParentEntityId($child = null)
    {
        if (!$this->hasParent()) {
            return null;
        }

        if ($child) {
            $getter = 'get'.ucfirst($this->getParentFieldName());
            $parentStuff = $child->$getter();
            $parentClass = $this->getParent()->getClass();

            return $parentStuff instanceof $parentClass ? $child->$getter()->getId() : null;
        }

        return $this->getParentObject() ? $this->getParentObject()->getId() : null;
    }

    public function getOption($key, $default = null)
    {
        return array_key_exists($key, $this->options) ? $this->options[$key] : $default;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    public function hasChild($id)
    {
        return array_key_exists($id, $this->children);
    }

    public function addChild(Admin $child)
    {
        $this->children[$child->getId()] = $child;

        return $this;
    }

    public function getChild($id)
    {
        return $this->children[$id];
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function hasChildren()
    {
        return count($this->children);
    }

    public function hasParent()
    {
        return $this->parent instanceof Admin;
    }

    public function setParent(Admin $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function createGridBuilder()
    {
        return new GridBuilder();
    }

    public function getGrid($name = '')
    {
        if (!isset($this->grids[$name])) {
            $method = 'build'.ucfirst($name).'Grid';

            if (!method_exists($this, $method)) return false;

            $builder = $this->createGridBuilder();
            $this->$method($builder);
            $this->grids[$name] = $builder->getGrid();
            $this->grids[$name]->setAdmin($this);
        }

        return $this->grids[$name];
    }

    public function createFormBuilder($name, $data = null, array $options = array())
    {
        $name = $name ?: preg_replace(['|^[a-z]+_[a-z]+_|', '|_admin$|'], ['', ''], $this->id);

        return $this->container->get('form.factory')->createNamedBuilder($name, 'form', $data, $options);
    }

    public function getForm($name = '', $csrf = false)
    {
        if (!isset($this->forms[$name])) {
            $method = 'build'.ucfirst($name).'Form';

            $builder = $this->createFormBuilder($name, $name ? null : $this->getObject(), array('csrf_protection' => $csrf, 'cascade_validation' => true));

            $this->$method($builder);

            if (!$name && $this->isTranslatable()) {
                $type = (new DynamicType('translation', ['data_class' => $this->getClass().'Translation']))->setBuilder($this->container->get('form.factory')->createBuilder());
                $this->buildTranslationForm($type->getBuilder());
                if ($type->getBuilder()->all()) {
                    $builder->add('translations', 'collection', [
                        'label' => ' ',
                        'type' => $type,
                        'options' => [
                            'label' => ' ',
                        ]
                    ]);
                }
            }

            $this->forms[$name] = $builder->getForm();
        }

        return $this->forms[$name];
    }

    public function isGranted($role)
    {
        if (!$this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN') && !$this->container->get('security.context')->isGranted(strtoupper('ROLE_'.$this->id.'_'.$role))) {
            return false;
        } else {
            return true;
        }
    }

    public function getSaveAndQuitRoute()
    {
        return $this->genUrl($this->getOption('wq_route'), ['id' => $this->getOption('wq_route') === 'show' ? $this->getObject()->getId() : null]);
    }

    public function genUrl($route, $parameters = array(), $mergePersistentParameters = true, $absolute = false)
    {
        if (true === $mergePersistentParameters) {
            $query = $this->container->get('request')->query;
            $persistant = array(
                'locale' => $query->get('locale'),
                'page' => $query->get('page'),
                'q' => $query->get('q'),
                'parentId' => $query->get('parentId'),
                'filter' => $query->get('filter'),
            );
            $parameters = array_merge($this->defaultPersistant, $persistant, $parameters);
        }

        return $this->container->get('router')->generate($this->id.'_'.$route, $parameters, $absolute);
    }

    public function getParentFieldName()
    {
        if (!$this->hasParent()) {
            return null;
        }

        foreach ($this->getMetadata()->associationMappings as $value) {
            if ($value['targetEntity'] === $this->getParent()->getClass()) {
                return $value['fieldName'];
            }
        }
    }

    public function getParentAssociationMapping()
    {
        if (!$this->hasParent()) {
            return null;
        }

        foreach ($this->getMetadata()->associationMappings as $value) {
            if ($value['targetEntity'] === $this->getParent()->getClass()) {
                return $value;
            }
        }
    }

    public function buildBreadcrumb($action = null)
    {
        // $action = $action ?: $this->getAction();

        // if ($this->hasParent()) $this->buildParentBreadcrumb($crumbs, $this->getParent(), $this->getParentObject());

        // $crumbs[] = [
        //     'label' => '<i class="icon-folder-open"></i> '.$this->getLabel(2),
        //     'path' => 'list' !== $action ? $this->genUrl('index') : '',
        //     'class' => 'list' === $action ? 'active' : '',
        // ];

        // if ($action === 'new') {
        //     $crumbs[] = array('label' => '<i class="icon-plus"></i> '.$this->getLabel(1), 'path' => '', 'class' => 'active');
        // }

        // if ($action === 'edit') {
        //     $crumbs[] = array('label' => '<i class="icon-pencil"></i> '.$this->getObject(), 'path' => '', 'class' => 'active');
        // }

        // if ($action === 'show') {
        //     $crumbs[] = array('label' => $this->getObject(), 'path' => '', 'class' => 'active');
        // }

        // if ($action === 'list' && $this->hasParent()) {
        //     $getter = 'get'.ucfirst($this->getParent()->getParentFieldName());
        //     $crumbs[] = [
        //         'label' => $this->container->get('translator')->trans('Back'),
        //         'path' => $this->getParent()->genUrl('edit', [
        //             'id' => $this->getParentObject()->getId(),
        //             'parentId' => $this->getParent()->hasParent() ? $this->getParentObject()->$getter()->getId() : null,
        //         ]),
        //         'class' => 'pull-right',
        //     ];
        // } elseif ($action === 'list' && !$this->hasParent()) {
        // } else {
        //     $collection = $this->container->get('router')->getRouteCollection();
        //     foreach ($collection->all() as $name => $route) {
        //         if ($this->getId().'_show' === $name) {
        //             $hasShow = true;
        //             break;
        //         }
        //     }
        //     if ($action === 'edit' && !empty($hasShow)) {
        //         $path = $this->genUrl('show', ['id' => $this->getObject()->getId()]);
        //     } else {
        //         $path = $this->genUrl('index');
        //     }
        //     $crumbs[] = [
        //         'label' => $this->container->get('translator')->trans('Back'),
        //         'path' => $path,
        //         'class' => 'pull-right',
        //     ];
        // }

        // return $this->buildNewBreadcrumb();
    }

    public function buildParentBreadcrumb(&$breadcrumb, $parent, $object)
    {
        if (!$parent) {
            return;
        }

        if ($parent->hasParent()) {
            $getter = 'get'.ucfirst($parent->getParentFieldName());
            $this->buildParentBreadcrumb($breadcrumb, $parent->getParent(), $object->$getter());
        }

        if ($parent->hasParent() && !$object->$getter()) {
            die('"'.$parent->getLabel().'" has no "'.$parent->getParent()->getLabel().'". Better make sure that all "'.$parent->getLabel(2).'" have a "'.$parent->getParent()->getLabel().'".');
        }

        $breadcrumb->add(
            '<span class="icon-folder-open-alt icon-large"></span> '.ucfirst($parent->getLabel(2)),
            $parent->genUrl('index', [
                'parentId' => $parent->hasParent() ? $object->$getter()->getId() : null,
            ], false)

        );

        $breadcrumb->add(
            '<span class="icon-file-alt icon-large"></span> '.$object,
            $parent->genUrl('edit', [
                'id' => $object->getId(),
                'parentId' => $parent->hasParent() ? $object->$getter()->getId() : null,
            ], false)
        );
    }

    public function buildBaseBreadcrumb($action)
    {
        $breadcrumb = $this->container->get('msi_admin.breadcrumb.factory')->create();

        if ($this->hasParent() && $this->getParentObject()->getId()) {
            $this->buildParentBreadcrumb($breadcrumb, $this->getParent(), $this->getParentObject());
        }

        $breadcrumb
            ->add('<span class="icon-folder-open-alt icon-large"></span> '.ucfirst($this->getLabel(2)), $action === 'list' ? null : $this->genUrl('index'))
        ;

        return $breadcrumb;
    }

    public function buildIndexBreadcrumb($breadcrumb)
    {
        if ($this->hasParent()) {
            $breadcrumb
                ->add('<span class="icon-share-alt icon-large"></span> '.$this->container->get('translator')->trans('back'), $this->getParent()->genUrl('index'), [
                    'attr' => [
                        'class' => 'pull-right',
                    ],
                ])
            ;
        }
    }

    public function buildNewBreadcrumb($breadcrumb)
    {
        $breadcrumb
            ->add('<span class="icon-plus-sign-alt icon-large"></span> '.$this->container->get('translator')->trans('add'))
            ->add('<span class="icon-share-alt icon-large"></span> '.$this->container->get('translator')->trans('back'), $this->genUrl('index'), [
                'attr' => [
                    'class' => 'pull-right',
                ],
            ])
        ;
    }

    public function buildEditBreadcrumb($breadcrumb)
    {
        $breadcrumb
            ->add('<span class="icon-file-alt icon-large"></span> '.$this->getObject(), $this->hasShow() ? $this->genUrl('show', ['id' => $this->getObject()->getId()]) : null)
            ->add('<span class="icon-edit-sign icon-large"></span> '.$this->container->get('translator')->trans('edit'))
            ->add('<span class="icon-share-alt icon-large"></span> '.$this->container->get('translator')->trans('back'), $this->genUrl('index'), [
                'attr' => [
                    'class' => 'pull-right',
                ],
            ])
        ;
    }

    public function buildShowBreadcrumb($breadcrumb)
    {
        $breadcrumb
            ->add('<span class="icon-file-alt icon-large"></span> '.$this->getObject())
        ;
    }

    public function getBreadcrumb($action = null)
    {
        $action = $action ?: $this->getAction();

        $breadcrumb = $this->buildBaseBreadcrumb($action);

        $method = 'build'.ucfirst($action).'Breadcrumb';
        $this->$method($breadcrumb);

        return $breadcrumb;
    }

    public function hasShow()
    {
        $collection = $this->container->get('router')->getRouteCollection();
        foreach ($collection->all() as $name => $route) {
            if ($this->getId().'_show' === $name) {
                return true;
            }
        }

        return false;
    }

    public function getUser()
    {
        if (!$this->container->has('security.context')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->container->get('security.context')->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    public function getRequest()
    {
        return $this->container->get('request');
    }

    protected function init()
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($this->options);
    }

    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'controller'           => null,
            'form_template'        => 'MsiAdminBundle:Admin:form.html.twig',
            'sidebar_template'     => null,
            'index_template'       => 'MsiAdminBundle:Admin:index.html.twig',
            'grid_action_template' => 'MsiAdminBundle:Admin:grid_action.html.twig',
            'new_template'         => 'MsiAdminBundle:Admin:new.html.twig',
            'edit_template'        => 'MsiAdminBundle:Admin:edit.html.twig',
            'search_fields'        => [],
            'order_by'             => ['a.id' => 'DESC'],
            'uploadify'            => false,
            'wq_route'             => 'index',
        ]);

        $resolver->setOptional([
            'form_css_template',
            'form_js_template',
        ]);
    }
}
