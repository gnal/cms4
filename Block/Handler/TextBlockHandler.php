<?php

namespace Msi\AdminBundle\Block\Handler;

use Msi\AdminBundle\Block\BaseBlockHandler;
use Msi\AdminBundle\Model\Block;
use Msi\AdminBundle\Model\Page;
use Symfony\Component\Form\FormBuilder;

class TextBlockHandler extends BaseBlockHandler
{
    public function execute(Block $block, Page $page)
    {
        return $block->getTranslation()->getSettings()['body'];
    }

    public function buildTranslationForm(FormBuilder $builder)
    {
        $builder->add('body', 'textarea', ['attr' => ['class' => 'tinymce']]);
    }

    public function getName()
    {
        return 'text';
    }
}
