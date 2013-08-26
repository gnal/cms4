<?php

namespace Msi\AdminBundle\Admin;

use Msi\AdminBundle\Grid\GridBuilder;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Msi\AdminBundle\Form\Type\DynamicType;

class BlockAdmin extends Admin
{
    public function configure()
    {
        $this->options = [
            'search_fields' => ['a.type', 'a.name', 'a.slot'],
            'form_template' => 'MsiAdminBundle:Block:form.html.twig',
            'sidebar_template' => 'MsiAdminBundle:Block:sidebar.html.twig',
        ];
    }

    public function buildGrid(GridBuilder $builder)
    {
        $builder
            ->add('name')
            ->add('type')
            ->add('', 'action')
        ;
    }

    public function buildForm(FormBuilder $builder)
    {
        $builder->add('name');

        if ($typeId = $this->getObject()->getType()) {
            $blockHandler = $this->container->get($typeId);
            $settingsBuilder = $this->container->get('form.factory')->createBuilder();
            $blockHandler->buildForm($settingsBuilder);
            $settingsType = (new DynamicType('block_settings'))->setBuilder($settingsBuilder);
            if ($settingsBuilder->all()) {
                $builder->add('settings', $settingsType);
            }

            $builder->add('pages', 'entity', [
                'multiple' => true,
                'expanded' => true,
                'class' => $this->container->getParameter('msi_admin.page.class'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->leftJoin('a.translations', 't')
                        ->addSelect('t')
                        ->addOrderBy('t.title', 'ASC')
                    ;
                },
            ]);
        }

        $types = [];
        foreach ($this->container->getServiceIds() as $id) {
            if (preg_match('@^.+_.+\.block\..+$@', $id)) {
                $types[$id] = $id;
            }
        }

        $builder->add('type', 'choice', [
            'choices' => $types,
        ]);

        if ($this->container->get('security.context')->getToken()->getUser()->isSuperAdmin()) {
            $builder->add('operators', 'entity', [
                'class' => 'MsiUserBundle:Group',
                'multiple' => true,
                'expanded' => true,
            ]);
        }

        $builder->add('slot', 'choice', ['choices' => $this->container->getParameter('msi_admin.block.slots')]);
    }

    public function buildTranslationForm(FormBuilder $builder)
    {
        $builder->add('published', 'checkbox');

        if ($typeId = $this->getObject()->getType()) {
            $blockHandler = $this->container->get($typeId);
            $settingsBuilder = $this->container->get('form.factory')->createBuilder();
            $blockHandler->buildTranslationForm($settingsBuilder);
            $settingsType = (new DynamicType('block_translation_settings'))->setBuilder($settingsBuilder);
            if ($settingsBuilder->all()) {
                $builder->add('settings', $settingsType);
            }
        }
    }

    public function buildFilterForm(FormBuilder $builder)
    {
        $types = [];
        foreach ($this->container->getServiceIds() as $id) {
            if (preg_match('@^.+_.+\.block\..+$@', $id)) {
                $types[$id] = $id;
            }
        }

        $builder
            ->add('pages', 'entity', array(
                'class' => $this->container->getParameter('msi_admin.page.class'),
                'label' => ' ',
                'empty_value' => '- '.$this->container->get('translator')->transchoice('entity.Page', 1).' -',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->leftJoin('a.translations', 't')
                        ->addSelect('t')
                    ;
                },
            ))
            ->add('type', 'choice', array(
                'label' => ' ',
                'empty_value' => '- Type -',
                'choices' => $types,
            ))
        ;
    }

    public function postLoad(ArrayCollection $collection)
    {
        $this->container->get('msi_admin.bouncer')->operatorFilter($collection);
    }
}
