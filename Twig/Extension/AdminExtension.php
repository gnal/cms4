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
        if ($date instanceof \DateTime) {
            $date = $date->format($format);
        } else {
            $date = date($format, strtotime($date));
        }

        if ($this->container->get('request')->getLocale() === 'en') {
            return $date;
        }

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

            '#Mon#',
            '#Tue#',
            '#Wed#',
            '#Thu#',
            '#Fri#',
            '#Sat#',
            '#Sun#',
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

            'Lun',
            'Mar',
            'Mer',
            'Jeu',
            'Ven',
            'Sam',
            'Dim',
        ], $date);
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
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($paginator->getPage() - 1, $options['suffix']), 'label' => '«');
        } else {
            $pagination[] = array('attr' => array('class' => 'disabled'), 'url' => $this->generateUrl(1, $options['suffix']), 'label' => '«');
        }
        // first
        if ($paginator->getPage() > 3) {
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl(1, $options['suffix']), 'label' => 1);
            $pagination[] = array('attr' => array('class' => 'disabled'), 'label' => '...', 'url' => '#');
        }
        // middle
        if ($numPages > 1) {
            if ($paginator->getPage() == 1 || $paginator->getPage() == $numPages - 2) {
                $modifier = 1;
            } else {
                $modifier = 0;
            }
            if ($paginator->getPage() == $numPages || $paginator->getPage() == 3) {
                $imodifier = 1;
            } else {
                $imodifier = 0;
            }

            if ($paginator->getPage() == 1 || $paginator->getPage() == 2) {
                $modifier = $modifier + 1;
            }
            if ($paginator->getPage() == $numPages || $paginator->getPage() == $numPages - 1) {
                $imodifier = $imodifier + 1;
            }
            for ($i=$paginator->getPage() - 2 - $imodifier; $i < $paginator->getPage() + 1 + $modifier; $i++) {
                if ($i + 1 == $paginator->getPage()) {
                    $pagination[] = array('attr' => array('class' => 'active'), 'url' => $this->generateUrl($i + 1, $options['suffix']), 'label' => $i + 1);
                } else if ($i >= 0 && $i <= $numPages - 1) {
                    $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($i + 1, $options['suffix']), 'label' => $i + 1);
                }
            }
        }
        // last
        if ($paginator->getPage() < $numPages - 2) {
            $pagination[] = array('attr' => array('class' => 'disabled'), 'label' => '...', 'url' => '#');
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($numPages, $options['suffix']), 'label' => $numPages);
        }
        // next
        if ($paginator->getPage() != $numPages) {
            $pagination[] = array('attr' => array(), 'url' => $this->generateUrl($paginator->getPage() + 1, $options['suffix']), 'label' => '»');
        } else {
            $pagination[] = array('attr' => array('class' => 'disabled'), 'url' => $this->generateUrl($numPages, $options['suffix']), 'label' => '»');
        }

        if ($paginator->getPage() > $numPages) return;

        return $this->container->get('templating')->render('MsiAdminBundle:Pager:'.$options['template'].'.html.twig', array('paginator' => $paginator, 'pagination' => $pagination));
    }

    protected function generateUrl($page, $suffix = '')
    {
        $request = $this->container->get('request');

        $parameters = array_merge($request->get('_route_params'), $request->query->all(), ['page' => $page]);

        return $this->container->get('router')->generate($request->attributes->get('_route'), $parameters).$suffix;
    }
}
