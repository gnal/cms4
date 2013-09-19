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
            'msi_pager_render' => new \Twig_Function_Method($this, 'renderPaginator', array('is_safe' => array('html'))),
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

    public function renderPaginator($paginator, array $options = [])
    {
        $numPages = $paginator->countPages();
        $options = array_merge($paginator->getOptions(), $options);

        if ($numPages < 2) return;

        $pagination = array();
        // previous
        if ($paginator->getPage() != 1) {
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($paginator->getPage() - 1), 'label' => '«');
        } else {
            $pagination[] = array('attr' => array('class' => 'disabled'), 'url' => $this->generateUrl(1), 'label' => '«');
        }
        // first
        if ($paginator->getPage() > 4) {
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl(1), 'label' => 1);
            $pagination[] = array('attr' => array('class' => 'disabled'), 'label' => '...', 'url' => '#');
        }
        // middle
        if ($numPages > 1) {
            for ($i=$paginator->getPage() - 4; $i < $paginator->getPage() - 4 + 7; $i++) {
                if ($i + 1 == $paginator->getPage()) {
                    $pagination[] = array('attr' => array('class' => 'active'), 'url' => $this->generateUrl($i + 1), 'label' => $i + 1);
                } else if ($i >= 0 && $i <= $numPages - 1) {
                    $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($i + 1), 'label' => $i + 1);
                }
            }
        }
        // last
        if ($paginator->getPage() < $numPages - 3) {
            $pagination[] = array('attr' => array('class' => 'disabled'), 'label' => '...', 'url' => '#');
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($numPages), 'label' => $numPages);
        }
        // next
        if ($paginator->getPage() != $numPages) {
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($paginator->getPage() + 1), 'label' => '»');
        } else {
            $pagination[] = array('attr' => array('class' => 'disabled'), 'url' => $this->generateUrl($numPages), 'label' => '»');
        }

        if ($paginator->getPage() > $numPages) return;

        return $this->container->get('templating')->render('MsiAdminBundle:Pager:'.$options['template'].'.html.twig', array('paginator' => $paginator, 'pagination' => $pagination));
    }
}
