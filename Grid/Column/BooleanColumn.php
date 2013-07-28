<?php

namespace Msi\AdminBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BooleanColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'toggle' => true,
            'attr' => ['class' => 'col-lg-1'],
            'btn_true' => 'label-success',
            'btn_false' => '',
            'icon_true' => 'icon-ok',
            'icon_false' => 'icon-ok',
        ]);
    }
}
