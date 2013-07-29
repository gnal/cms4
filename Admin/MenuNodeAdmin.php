<?php

namespace Msi\AdminBundle\Admin;

use Msi\AdminBundle\Grid\GridBuilder;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;

class MenuNodeAdmin extends Admin
{
    public function configure()
    {
        $this->options = [
            'sidebar_template' => 'MsiAdminBundle:MenuNode:sidebar.html.twig',
            'index_template' => 'MsiAdminBundle:MenuNode:index.html.twig',
            'controller' => 'MsiAdminBundle:Admin/MenuNode:',
            'search_fields' => ['a.id', 't.name'],
            'form_template' => 'MsiAdminBundle:MenuNode:form.html.twig',
            'order_by' => [],
        ];
    }

    public function buildGrid(GridBuilder $builder)
    {
        $builder
            ->add('published', 'boolean')
            ->add('name', 'tree')
            ->add('', 'action')
        ;
    }

    public function buildForm(FormBuilder $builder)
    {
        $qb = $this->getObjectManager()->getFindByQueryBuilder(
            ['a.menu' => $this->container->get('request')->query->get('parentId')],
            ['a.translations' => 't', 'a.children' => 'c'],
            ['a.lft' => 'ASC']
        );

        if ($this->getObject()->getId()) {
            $qb->andWhere('a.id != :match')->setParameter('match', $this->getObject()->getId());
            $i = 0;
            foreach ($this->getObject()->getChildren() as $child) {
                $qb->andWhere('a.id != :match'.$i)->setParameter('match'.$i, $child->getId());
                $i++;
            }
        }

        if ($this->getObject()->getChildren()->count()) {
            $qb->andWhere('a.lvl <= :bar')->setParameter('bar', $this->getObject()->getLvl() - 1);
        }

        $qb->andWhere('a.lvl <= :foo')->setParameter('foo', 2);

        $choices = $qb->getQuery()->execute();

        $builder
            ->add('page', 'entity', [
                'empty_value' => '',
                'class' => $this->container->getParameter('msi_admin.page.class'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->leftJoin('a.translations', 't')
                        ->orderBy('t.title', 'ASC')
                    ;
                },
            ])
            ->add('parent', 'entity', [
                'class' => $this->container->getParameter('msi_admin.menu.class'),
                'choices' => $choices,
                'property' => 'toTree',
            ])
            ->add('targetBlank', 'checkbox')
        ;
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
            ->add('route', 'text', ['label' => 'Url'])
        ;
    }

    public function buildListQuery(QueryBuilder $qb)
    {
        $qb->resetDQLPart('where');
        $qb->andWhere('a.menu = :eqMatch2');
        $qb->andWhere('a.lvl != 0');
        $qb->addOrderBy('a.lft', 'ASC');
        $qb->andWhere('t.locale = :eqMatch1');
    }

    public function prePersist($entity)
    {
        $this->validateRoute($entity);
    }

    public function preUpdate($entity)
    {
        $this->validateRoute($entity);
    }

    public function postLoad(ArrayCollection $collection)
    {
        $this->container->get('msi_admin.bouncer')->operatorFilter($collection);
    }

    public function validateRoute($entity)
    {
        if (!preg_match('#^@#', $entity->getTranslation()->getRoute())) {
            return true;
        }

        $collection = $this->container->get('router')->getRouteCollection();
        foreach ($collection->all() as $name => $route) {
            if ($entity->getTranslation()->getRoute() === '@'.$name) {
                return true;
            }
        }

        // throw new \InvalidArgumentException('Route '.$entity->getTranslation()->getRoute().' doesn\'t exist');
        foreach ($entity->getTranslations() as $translation) {
            $translation->setRoute('#INVALID_ROUTE');
        }
    }
}
