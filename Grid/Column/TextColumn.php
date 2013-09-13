<?php

namespace Msi\AdminBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TextColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'truncate' => true,
            'truncate_length' => 30,
            'truncate_preserve' => false,
            'truncate_separator' => '...',
        ]);
    }
}
