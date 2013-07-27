<?php

namespace Msi\CmfBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class CmfLoader implements LoaderInterface
{
    private $loaded = false;
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add this loader twice');
        }

        $collection = new RouteCollection();

        foreach ($this->container->getParameter('msi_cmf.admin_ids') as $id) {
            $collection->addCollection($this->buildRoutes($id));
        }

        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return 'msi_cmf' === $type;
    }

    public function getResolver()
    {
    }

    public function setResolver(LoaderResolverInterface $resolver)
    {
    }

    protected function buildRoutes($id)
    {
        $collection = new RouteCollection();

        $namespace = preg_replace(['|^[a-z]+_[a-z]+_|', '|_admin$|', '|_|'], ['', '', '-'], $id);

        $prefix = '/admin/'.$namespace;
        $suffix = '.html';

        $names = [
            'list',
            'new',
            'edit',
            'delete',
            'toggle',
            'sort',
            'deleteUpload',
        ];

        foreach ($names as $name) {
            $collection->add(
                $id.'_'.$name,
                new Route(
                    $prefix.'/'.$name.$suffix,
                    [
                        '_controller' => 'MsiCmfBundle:Core:'.$name,
                        '_admin' => $id,
                    ]
                )
            );
        }

        return $collection;
    }
}
