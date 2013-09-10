<?php

namespace Msi\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    protected $parameters = [];

    public function renderStuff()
    {
        $_controller = $this->getRequest()->attributes->get('_controller');

        $parts = explode('\\', $_controller);
        $partz = explode('::', $parts[3]);

        $bundle = $parts[0].$parts[1];
        $controller = str_replace('Controller', '', $partz[0]);
        $action = str_replace('Action', '', $partz[1]);

        $template = $bundle.':'.$controller.':'.$action.'.html.twig';

        return $this->render($template, $this->parameters);
    }

    public function validateForm()
    {
        if (!isset($this->parameters['form'])) {
            throw new \Exception();
        }

        return $this->parameters['form']->bind($this->getRequest())->isValid();
    }

    public function addFlash($type, $message)
    {
        $this
            ->get('session')
            ->getFlashBag()
            ->add($type, $message)
        ;
    }
}
