<?php

namespace Msi\AdminBundle\Twig\Extension;

class AdminExtension extends \Twig_Extension
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            'trans_date' => new \Twig_Function_Method($this, 'transDate', array('is_safe' => array('html'))),
        );
    }

    public function getGlobals()
    {
        $globals = [];
        if (!$this->container->isScopeActive('request')) {
            return $globals;
        }
        $request = $this->container->get('request');

        $globals['working_locale'] = $this->container->get('msi_admin.provider')->getWorkingLocale();

        if ($admin = $request->attributes->get('_admin')) {
            $globals['admin'] = $this->container->get($admin);
        }

        return $globals;
    }

    public function transDate($date, $format = 'd F')
    {
        return preg_replace([
            '#January#',
            '#February#',
            '#March#',
            '#April#',
            '#May#',
            '#June#',
            '#July#',
            '#August#',
            '#September#',
            '#October#',
            '#November#',
            '#December#',
        ], [
            'janvier',
            'février',
            'mars',
            'avril',
            'mai',
            'juin',
            'juillet',
            'août',
            'septembre',
            'octobre',
            'novembre',
            'décembre',
        ], date($format, strtotime($date)));
    }

    public function getName()
    {
        return 'msi_admin_admin';
    }
}
