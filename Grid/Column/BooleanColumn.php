<?php

namespace Msi\AdminBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BooleanColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'toggle' => true,
            'btn_true' => 'btn-success',
            'btn_false' => 'btn-default',
            'icon_true' => 'icon-ok-circle',
            'icon_false' => 'icon-ok-circle',
            'attr' => ['class' => 'text-center'],
        ]);
    }
}
