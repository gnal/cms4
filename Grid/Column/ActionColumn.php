<?php

namespace Msi\AdminBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ActionColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'actions' => [],
            'delete' => true,
            'ajax_delete' => true,
            'edit' => true,
            'children' => true,
            'attr' => ['class' => 'text-right'],
        ]);
    }
}
