<?php

namespace Msi\AdminBundle\Admin;

use Msi\AdminBundle\Grid\GridBuilder;
use Symfony\Component\Form\FormBuilder;

class SiteAdmin extends Admin
{
    public function configure()
    {
        $this->options = [
            'form_template' => 'MsiAdminBundle:Site:form.html.twig',
            'sidebar_template' => 'MsiAdminBundle:Site:sidebar.html.twig',
            // 'search_fields' => ['a.id', 'a.host', 'translations.brand'],
        ];
    }

    public function buildGrid(GridBuilder $builder)
    {
        $builder
            ->add('enabled', 'boolean')
            ->add('brand')
            ->add('host')
            ->add('', 'action')
        ;
    }

    public function buildForm(FormBuilder $builder)
    {
        $choices = [];
        foreach ($this->container->getParameter('msi_admin.app_locales') as $locale) {
            $choices[$locale] = strtoupper($locale);
        }

        $builder
            ->add('host', 'text', [
                'attr' => [
                    'data-help' => 'Pro tip: Enter the correct host name instead of relying of the "isDefault" field to reduce number of database queries.',
                ],
            ])
            ->add('isDefault')
            ->add('locale', 'choice', [
                'choices' => $choices,
                'label' => 'Default language',

            ])
            ->add('locales', 'choice', [
                'multiple' => true,
                'expanded' => true,
                'choices' => $choices,
                'label' => 'Available languages',

            ])
        ;
    }

    public function buildTranslationForm(FormBuilder $builder)
    {
        $builder
            ->add('brand')
            ->add('offlineMessage', 'textarea')
            ->add('metaKeywords', 'textarea')
            ->add('metaDescription', 'textarea')
        ;
    }
}
