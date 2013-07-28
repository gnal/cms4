<?php

namespace Msi\AdminBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ActionColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'actions' => [],
            'attr' => ['class' => 'col-lg-1'],
            'delete' => true,
            'edit' => true,
            'children' => true,
        ]);
    }
}
