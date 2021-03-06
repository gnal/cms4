<?php

namespace Msi\AdminBundle\Block\Handler;

use Msi\AdminBundle\Block\BaseBlockHandler;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Msi\CmsBundle\Model\Block;
use Msi\CmsBundle\Model\Page;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints\NotBlank;

class TemplateBlockHandler extends BaseBlockHandler
{
    protected $templates;
    protected $templating;

    public function __construct($templates, EngineInterface $templating)
    {
        $this->templates = $templates;
        $this->templating = $templating;
    }

    public function execute(Block $block, Page $page)
    {
        return $this->templating->render($block->getSettings()['template'], ['page' => $page]);
    }

    public function buildForm(FormBuilder $builder)
    {
        $builder->add('template', 'choice', [
            'constraints' => new NotBlank(),
            'choices' => $this->templates,
        ]);
    }

    public function getName()
    {
        return 'template';
    }
}
