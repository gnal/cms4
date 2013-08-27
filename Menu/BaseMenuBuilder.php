<?php

namespace Msi\AdminBundle\Menu;

use Symfony\Component\DependencyInjection\ContainerAware;

class BaseMenuBuilder extends ContainerAware
{
    protected $walkers = [];

    protected function getMenu($name)
    {
        $node = $this->container->get('msi_cms.menu_root_manager')->findRootByName($name);

        if (!isset($node[0])) {
            return $this->container->get('knp_menu.array_loader')->load([]);
        }

        return $this->create($node[0]);
    }

    public function create($node)
    {
        foreach ($node['translations'] as $k => $translation) {
            if ($this->container->get('request')->getLocale() === $translation['locale']) {
                $nodeLocale = $k;
                break;
            }
        }

        if (!$node['translations'][$nodeLocale]['published']) {
            return $this->container->get('knp_menu.array_loader')->load([]);
        }

        $array = [
            'name' => $node['translations'][$nodeLocale]['name'],
            'extras' => [
                'groups' => $node['operators'],
                'published' => $node['translations'][$nodeLocale]['published'],
            ],
        ];

        $this->buildArray($node, $array);

        $item = $this->container->get('knp_menu.array_loader')->load($array);

        if (!$item->getExtra('published')) {
            // return $this->container->get('knp_menu.array_loader')->load('default');
            return null;
        }

        $this->addWalker('removeUnpublished');
        $this->addWalker('setSafeLabel');
        $this->addWalker('checkRole');

        return $item;
    }

    public function buildArray($node, &$array)
    {
        foreach ($node['children'] as $child) {
            foreach ($child['translations'] as $k => $translation) {
                if ($this->container->get('request')->getLocale() === $translation['locale']) {
                    $childLocale = $k;
                    break;
                }
            }
            if ($child['page']) {
                foreach ($child['page']['translations'] as $k => $translation) {
                    if ($this->container->get('request')->getLocale() === $translation['locale']) {
                        $pageLocale = $k;
                        break;
                    }
                }
            }
            $route = $child['translations'][$childLocale]['route'];
            $options = [];

            if ($child['page']) {
                if (!$child['page']['route']) {
                    $options['route'] = 'msi_page_show';
                    $options['routeParameters'] = ['slug' => $child['page']['translations'][$pageLocale]['slug']];
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
                'published' => $child['translations'][$childLocale]['published'],
            ];

            $array['children'][$child['translations'][$childLocale]['name']] = $options;

            if (count($child['children'])) {
                $this->buildArray($child, $array['children'][$child['translations'][$childLocale]['name']]);
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
