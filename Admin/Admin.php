<?php

namespace Msi\CmfBundle\Admin;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Msi\CmfBundle\Doctrine\Manager;
use Doctrine\Common\Collections\ArrayCollection;
use Msi\CmfBundle\Form\Type\DynamicType;

use Symfony\Component\Form\FormBuilder;
use Msi\CmfBundle\Grid\GridBuilder;
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

    public function editPreRender(&$parameters)
    {
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

    public function getAction()
    {
        preg_match('@[a-z]+$@', $this->container->get('request')->attributes->get('_route'), $matches);

        return $matches[0];
    }

    public function hasTrait($traitName, $namespace = 'Msi\CmfBundle\Doctrine\Extension\Model\\')
    {
        return $this->container->get('msi_cmf.class_analyzer')->hasTrait($this->getMetadata()->reflClass, $namespace.$traitName);
    }

    public function isSortable()
    {
        return $this->container->get('msi_cmf.class_analyzer')->hasTrait($this->getMetadata()->reflClass, 'Msi\CmfBundle\Doctrine\Extension\Model\Sortable');
    }

    public function isUploadable()
    {
        return $this->container->get('msi_cmf.class_analyzer')->hasTrait($this->getMetadata()->reflClass, 'Msi\CmfBundle\Doctrine\Extension\Model\Uploadable');
    }

    public function isTranslatable()
    {
        return $this->container->get('msi_cmf.class_analyzer')->hasTrait($this->getMetadata()->reflClass, 'Msi\CmfBundle\Doctrine\Extension\Model\Translatable');
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
            $this->object = $this->objectManager->findOneOrCreate(
                $this->container->getParameter('msi_cmf.app_locales'),
                $this->container->get('request')->query->get('id')
            );
        }

        return $this->object;
    }

    public function getParentObject()
    {
        if (!$this->parentObject) {
            $this->parentObject = $this->getParent()->objectManager->findOneOrCreate(
                $this->container->getParameter('msi_cmf.app_locales'),
                $this->container->get('request')->query->get('parentId')
            );
        }

        return $this->parentObject;
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

    public function getSaveAndQuitUrl()
    {
        return $this->genUrl('list');
    }

    public function genUrl($route, $parameters = array(), $mergePersistentParameters = true, $absolute = false)
    {
        if (true === $mergePersistentParameters) {
            $query = $this->container->get('request')->query;
            $persistant = array(
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
        foreach ($this->getMetadata()->associationMappings as $value) {
            if ($value['targetEntity'] === $this->getParent()->getClass()) {
                return $value['fieldName'];
            }
        }
    }

    public function buildParentBreadcrumb(&$crumbs, $parent, $object)
    {
        if (!$parent) {
            return;
        }

        if ($parent->hasParent()) {
            $getter = 'get'.ucfirst($parent->getParentFieldName());
            $this->buildParentBreadcrumb($crumbs, $parent->getParent(), $object->$getter());
        }

        $crumbs[] = [
            'label' => '<i class="icon-folder-open"></i> '.$parent->getLabel(2),
            'path' => $parent->genUrl('list', [
                'parentId' => $parent->hasParent() ? $object->$getter()->getId() : null,
            ], false)

        ];

        $crumbs[] = [
            'label' => '<i class="icon-pencil"></i> '.$object,
            'path' => $parent->genUrl('edit', [
                'id' => $object->getId(),
                'parentId' => $parent->hasParent() ? $object->$getter()->getId() : null,
            ], false)
        ];
    }

    public function buildBreadcrumb()
    {
        $action = $this->getAction();
        $crumbs = [];

        if ($this->hasParent()) $this->buildParentBreadcrumb($crumbs, $this->getParent(), $this->getParentObject());

        $crumbs[] = [
            'label' => '<i class="icon-folder-open"></i> '.$this->getLabel(2),
            'path' => 'list' !== $action ? $this->genUrl('list') : '',
            'class' => 'list' === $action ? 'active' : '',
        ];

        if ($action === 'new') {
            $crumbs[] = array('label' => '<i class="icon-plus"></i> '.$this->getLabel(1), 'path' => '', 'class' => 'active');
        }

        if ($action === 'edit') {
            $crumbs[] = array('label' => '<i class="icon-pencil"></i> '.$this->getObject(), 'path' => '', 'class' => 'active');
        }

        if ($action === 'show') {
            $crumbs[] = array('label' => $this->getObject(), 'path' => '', 'class' => 'active');
        }

        if ($action === 'list' && $this->hasParent()) {
            $crumbs[] = [
                'label' => $this->container->get('translator')->trans('Back'),
                'path' => $this->getParent()->genUrl('edit', ['id' => $this->getParentObject()->getId()]),
                'class' => 'pull-right',
            ];
        } elseif ($action === 'list' && !$this->hasParent()) {
        } else {
            $crumbs[] = [
                'label' => $this->container->get('translator')->trans('Back'),
                'path' => $this->genUrl('list'),
                'class' => 'pull-right',
            ];
        }

        return $crumbs;
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
            'form_template'        => 'MsiCmfBundle:Admin:form.html.twig',
            'sidebar_template'     => null,
            'sidebar_nav_template' => null,
            'index_template'       => 'MsiCmfBundle:Admin:index.html.twig',
            'new_template'         => 'MsiCmfBundle:Admin:new.html.twig',
            'edit_template'        => 'MsiCmfBundle:Admin:edit.html.twig',
            'search_fields'        => [],
            'order_by'             => ['a.id' => 'DESC'],
            'uploadify'            => false,
            'show_children'        => true,
            'save_quit_url'        => $this->genUrl('list'),
        ]);

        $resolver->setOptional([
            'form_css_template',
            'form_js_template',
        ]);
    }
}
