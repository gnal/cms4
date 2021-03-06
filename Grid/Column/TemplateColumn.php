<?php

namespace Msi\AdminBundle\Grid\Column;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TemplateColumn extends BaseColumn
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(array('template'));
    }
}
