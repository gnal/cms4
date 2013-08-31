<?php

namespace Msi\AdminBundle\Manager;

use Msi\AdminBundle\Doctrine\Manager as BaseManager;

class PageManager extends BaseManager
{
    public function findByRoute($route)
    {
        $page = $this->getFindByQueryBuilder(
            [
                'translations.published' => true,
                'a.route' => $route,
            ],
            [
                'a.translations' => 'translations',
                'a.blocks' => 'blocks',
                'blocks.translations' => 'blocks_translations',
            ],
            [
                'blocks.position' => 'ASC',
            ]
        )->getQuery()->execute();

        return isset($page[0]) ? $page[0] : null;
    }
}
