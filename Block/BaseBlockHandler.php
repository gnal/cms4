<?php

namespace Msi\AdminBundle\Block;

use Msi\CmsBundle\Model\Block;
use Msi\CmsBundle\Model\Page;
use Symfony\Component\Form\FormBuilder;

abstract class BaseBlockHandler
{
    abstract public function execute(Block $block, Page $page);

    abstract public function getName();

    public function buildForm(FormBuilder $builder)
    {
    }

    public function buildTranslationForm(FormBuilder $builder)
    {
    }

    public function setDefaultTranslationOptions(OptionsResolverInterface $resolver)
    {
    }
}
