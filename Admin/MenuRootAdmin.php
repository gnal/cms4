<?php

namespace Msi\AdminBundle\Admin;

use Msi\AdminBundle\Grid\GridBuilder;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;

class MenuRootAdmin extends Admin
{
    public function configure()
    {
        $this->options = [
            'search_fields' => ['a.id', 't.name'],
            'form_template' => 'MsiAdminBundle:MenuRoot:form.html.twig',
            'sidebar_template' => 'MsiAdminBundle:MenuRoot:sidebar.html.twig',
        ];
    }

    public function buildGrid(GridBuilder $builder)
    {
        $builder
            ->add('published', 'boolean')
            ->add('name')
            ->add('', 'action')
        ;
    }

    public function buildForm(FormBuilder $builder)
    {
        if ($this->container->get('security.context')->getToken()->getUser()->isSuperAdmin()) {
            $builder->add('operators', 'entity', [
                'class' => 'MsiUserBundle:Group',
                'multiple' => true,
                'expanded' => true,
            ]);
        }
    }

    public function buildTranslationForm(FormBuilder $builder)
    {
        $builder
            ->add('name')
        ;
    }

    public function buildListQuery(QueryBuilder $qb)
    {
        $qb->andWhere('a.lvl = 0');
    }

    public function postLoad(ArrayCollection $collection)
    {
        $this->container->get('msi_admin.bouncer')->operatorFilter($collection);
    }
}
