<?php

namespace Msi\AdminBundle\Breadcrumb;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Crumb
{
    protected $label;
    protected $url;
    protected $options;

    public function __construct($label, $url, $options)
    {
        $this->label = $label;
        $this->url = $url;

        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'attr' => [],
        ]);
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
