<?php

namespace Msi\AdminBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class NumberColumn extends BaseColumn
{
    public function fixValue()
    {
        $this->value = number_format(floatval($this->value), $this->options['decimals']);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'decimals' => 0,
        ]);
    }
}
