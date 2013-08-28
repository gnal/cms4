<?php

namespace Msi\AdminBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BooleanColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'toggle' => true,
            'btn_true' => 'label-success',
            'btn_false' => 'label-default',
            'icon_true' => 'icon-check',
            'icon_false' => 'icon-check-empty',
        ]);
    }
}
