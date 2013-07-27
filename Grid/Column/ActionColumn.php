<?php

namespace Msi\CmfBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ActionColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'actions' => [],
            'attr' => ['class' => 'span1'],
            'delete' => true,
            'edit' => true,
            'children' => true,
        ]);
    }
}
