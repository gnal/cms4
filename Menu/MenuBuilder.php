<?php

namespace Msi\AdminBundle\Menu;

use Knp\Menu\FactoryInterface;

class MenuBuilder extends BaseMenuBuilder
{
    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $menu = $this->getMenu('admin');

        $menu->setChildrenAttribute('class', 'nav navbar-nav');
        $this->setBootstrapDropdownMenuAttributes($menu);

        return $this->execute($menu);
    }
}
