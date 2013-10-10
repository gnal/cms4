<?php

namespace Msi\AdminBundle\Menu;

use Symfony\Component\DependencyInjection\ContainerAware;

class BaseMenuBuilder extends ContainerAware
{
    protected $walkers = [];

    protected function getMenu($name)
    {
        $node = $this->container->get('msi_cms.menu_root_manager')->findRootByName($name);

        if (!$node || !$node->getTranslation()->getPublished()) {
            return $this->container->get('knp_menu.factory')->createItem('default');
        }

        return $this->create($node);
    }

    public function create($node)
    {
        $item = $this->container->get('knp_menu.node_loader')->load($node);

        $this->addWalker('removeUnpublished');
        $this->addWalker('setSafeLabel');
        $this->addWalker('checkRole');

        $this->checkRootAcl($item);

        return $item;
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

    protected function checkRootAcl($node)
    {
        if (!count($node->getExtra('groups'))) {
            return;
        }

        foreach ($node->getExtra('groups') as $group) {
            if ($this->container->get('security.context')->getToken()->getUser()->getGroups()->contains($group)) {
                return;
            }
        }

        foreach ($node->getChildren() as $child) {
            $node->removeChild($child);
        }
    }

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
