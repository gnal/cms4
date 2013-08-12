<?php

namespace Msi\AdminBundle\Admin;

use Msi\AdminBundle\Grid\GridBuilder;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\QueryBuilder;

class PageAdmin extends Admin
{
    public function configure()
    {
        $this->options = [
            'form_template' => 'MsiAdminBundle:Page:form.html.twig',
            'sidebar_template' => 'MsiAdminBundle:Page:sidebar.html.twig',
            'search_fields' => ['a.id', 't.title'],
            'order_by'      => ['t.title' => 'ASC'],
        ];
    }

    public function buildGrid(GridBuilder $builder)
    {
        $builder
            ->add('published', 'boolean')
            ->add('title')
            ->add('', 'action')
        ;
    }

    public function buildForm(FormBuilder $builder)
    {
        $builder->add('published');

        $collection = $this->container->get('router')->getRouteCollection();
        $choices = [];
        foreach ($collection->all() as $name => $route) {
            if (preg_match('#^_#', $name)) {
                continue;
            }
            if (preg_match('#^msi_page_#', $name)) {
                continue;
            }
            $choices[$name] = $name;
        }

        $builder
            ->add('template', 'choice', ['choices' => $this->container->getParameter('msi_admin.page.layouts')])
            ->add('showTitle')
            ->add('route', 'choice', [
                'empty_value' => '',
                'choices' => $choices,
            ])
            ->add('css', 'textarea')
            ->add('js', 'textarea')
            // ->add('blocks', 'collection', [
            //     'type' => new \Msi\AdminBundle\Form\Type\BlockType($this->container),
            //     'allow_add' => true,
            // ])
        ;

        if ($this->container->getParameter('msi_admin.multisite')) {
            $builder->add('site', 'entity', [
                'class' => $this->container->getParameter('msi_admin.site.class'),
            ]);
        }
    }

    public function buildTranslationForm(FormBuilder $builder)
    {
        $builder
            ->add('title')
            ->add('body', 'textarea', [
                'attr' => [
                    'class' => 'tinymce',
                ],
            ])
            ->add('metaKeywords', 'textarea')
            ->add('metaDescription', 'textarea')
        ;
    }

    // public function buildFilterForm(FormBuilder $builder)
    // {
    //     if ($this->container->getParameter('msi_admin.multisite')) {
    //         $builder->add('site', 'entity', [
    //             'label' => ' ',
    //             'empty_value' => '- Site -',
    //             'class' => $this->container->getParameter('msi_admin.site.class'),
    //         ]);
    //     }

    //     $builder->add('home', 'choice', [
    //         'label' => ' ',
    //         'empty_value' => '- '.$this->container->get('translator')->trans('Home').' -',
    //         'choices' => [
    //             '1' => $this->container->get('translator')->trans('Yes'),
    //             '0' => $this->container->get('translator')->trans('No'),
    //         ],
    //     ]);
    // }

    public function buildListQuery(QueryBuilder $qb)
    {
        $qb->addOrderBy('t.title', 'ASC');
    }

    public function prePersist($entity)
    {
        if (!$this->container->getParameter('msi_admin.multisite')) {
            $entity->setSite($this->container->get('msi_admin.provider')->getSite());
        }
    }
}
