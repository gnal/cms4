<?php

namespace Msi\AdminBundle\Block\Handler;

use Msi\AdminBundle\Block\BaseBlockHandler;
use Msi\CmsBundle\Model\Block;
use Msi\CmsBundle\Model\Page;
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
