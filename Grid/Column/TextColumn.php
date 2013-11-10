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
            'route' => null,
            'route_parameters' => [],
            'url_as_label' => false,
        ]);
    }

    public function getRouteParameters()
    {
        $dada=[];
        foreach ($this->getOptions()['route_parameters'] as $parameter) {
            $getter = 'get'.ucfirst($parameter);
            $value = $this->getObject()->$getter();
            $dada[$parameter] = $value;
        }

        return $dada;
    }
}
