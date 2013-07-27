<?php

namespace Msi\CmfBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaseMenuBuilder extends ContainerAware
{
    protected $walkers = [];

    protected function getMenu(FactoryInterface $factory, $name)
    {
        $root = $this->container->get('msi_cmf.menu_root_manager')->findRootByName($name)[0];

        return $this->create($factory, $root, $name);
    }

    public function create(FactoryInterface $factory, $node)
    {
        $array = [
            'name' => $node['translations'][0]['name'],
            'extras' => [
                'groups' => $node['operators'],
                'published' => $node['published'],
            ],
        ];

        $this->buildArray($node, $array);

        $item = $factory->createFromArray($array);

        if (!$item->getExtra('published')) {
            return $factory->createItem('default');
        }

        $this->addWalker('removeUnpublished');
        $this->addWalker('setSafeLabel');
        $this->addWalker('checkRole');

        return $item;
    }

    public function buildArray($node, &$array)
    {
        $locale = $this->container->get('request')->getLocale() === 'fr' ? 1 : 0;

        foreach ($node['children'] as $child) {
            $route = $child['translations'][$locale]['route'];
            $options = [];

            if ($child['page']) {
                if (!$child['page']['route']) {
                    $options['route'] = 'msi_page_show';
                    $options['routeParameters'] = ['slug' => $child['page']['translations'][$locale]['slug']];
                } else {
                    $options['route'] = $child['page']['route'];
                }
            } elseif (preg_match('#^@#', $route)) {
                $options['route'] = substr($route, 1);
            } else {
                $options['uri'] = $route;
            }

            if ($child['targetBlank']) {
                $options['linkAttributes'] = ['target' => '_blank'];
            }

            $options['extras'] = [
                'groups' => $child['operators'],
                'published' => $child['published'],
            ];

            $array['children'][$child['translations'][$locale]['name']] = $options;

            if (count($child['children'])) {
                $this->buildArray($child, $array['children'][$child['translations'][$locale]['name']]);
            }
        }
    }

    public function setBootstrapDropdownMenuAttributes($node)
    {
        foreach ($node->getChildren() as $child) {
            if ($child->hasChildren()) {
                $child->setLabel($child->getName().' <b class="caret"></b>');

                $child->setAttribute('class', 'dropdown');
                $child->setLinkAttribute('class', 'dropdown-toggle');
                $child->setLinkAttribute('data-toggle', 'dropdown');
                $child->setChildrenAttribute('class', 'dropdown-menu');
            }
            $this->setBootstrapDropdownSubmenuAttributes($child);
        }
    }

    public function setBootstrapDropdownSubmenuAttributes($node)
    {
        foreach ($node->getChildren() as $child) {
            if ($child->hasChildren()) {
                $child->setAttribute('class', 'dropdown-submenu');
                $child->setChildrenAttribute('class', 'dropdown-menu');
                $child->setLinkAttribute('tabindex', -1);
            }
        }
    }

    protected function setSafeLabel($node)
    {
        $node->setExtra('safe_label', true);
    }

    protected function removeUnpublished($node)
    {
        if (!$node->getExtra('published') && !$node->isRoot()) {
            $node->getParent()->removeChild($node);
        }
    }

    protected function checkRole($node)
    {
        if (!$node->getParent()) {
            return;
        }

        if (!count($node->getExtra('groups'))) {
            return;
        }

        foreach ($node->getExtra('groups') as $group) {
            if ($this->container->get('security.context')->getToken()->getUser()->getGroups()->contains($group)) {
                return;
            }
        }

        $node->getParent()->removeChild($node);
    }

    // protected function findCurrent($node)
    // {
    //     $requestUri = $this->container->get('request')->getRequestUri();
    //     if ($pos = strrpos($requestUri, '?')) {
    //         $requestUri = substr($requestUri, 0, $pos);
    //     }
    //     foreach ($node->getChildren() as $child) {
    //         $menuUri = $child->getUri();
    //         if ($pos = strrpos($menuUri, '?')) {
    //             $menuUri = substr($menuUri, 0, $pos);
    //         }
    //         if ($menuUri === $requestUri) {
    //             $child->setCurrent(true);
    //         } else {
    //             $child->setCurrent(false);
    //             $this->findCurrent($child);
    //         }
    //     }
    // }

    public function execute($menu)
    {
        $this->walk($menu);

        return $menu;
    }

    protected function walk($node)
    {
        foreach ($node->getChildren() as $child) {
            if ($child->hasChildren()) {
                $this->walk($child);
            }
            foreach ($this->walkers as $walker) {
                $this->$walker($child);
            }
        }
    }

    protected function addWalker($name)
    {
        $this->walkers[] = $name;
    }
}
