<?php

namespace Msi\AdminBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImageColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'collection' => false,
            'translation' => false,
        ]);
    }
}
