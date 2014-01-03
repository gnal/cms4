<?php

namespace Msi\AdminBundle\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

class AdminLoader implements LoaderInterface
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

        foreach ($this->container->getParameter('msi_admin.admin_ids') as $id) {
            $collection->addCollection($this->buildRoutes($id));
        }

        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return 'msi_admin' === $type;
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

        $collection->add(
            $id.'_index',
            new Route(
                $prefix,
                [
                    '_controller' => 'MsiAdminBundle:Core:index',
                    '_admin' => $id,
                ]
            )
        );

        $collection->add(
            $id.'_new',
            new Route(
                $prefix.'/new',
                [
                    '_controller' => 'MsiAdminBundle:Core:new',
                    '_admin' => $id,
                ]
            )
        );

        $collection->add(
            $id.'_edit',
            new Route(
                $prefix.'/edit',
                [
                    '_controller' => 'MsiAdminBundle:Core:edit',
                    '_admin' => $id,
                ]
            )
        );

        $collection->add(
            $id.'_delete',
            new Route(
                $prefix.'/delete',
                [
                    '_controller' => 'MsiAdminBundle:Core:delete',
                    '_admin' => $id,
                ]
            )
        );

        $collection->add(
            $id.'_deleteupload',
            new Route(
                $prefix.'/delete-upload',
                [
                    '_controller' => 'MsiAdminBundle:Core:deleteUpload',
                    '_admin' => $id,
                ]
            )
        );

        $collection->add(
            $id.'_toggle',
            new Route(
                $prefix.'/toggle',
                [
                    '_controller' => 'MsiAdminBundle:Core:toggle',
                    '_admin' => $id,
                ]
            )
        );

        $collection->add(
            $id.'_sort',
            new Route(
                $prefix.'/sort',
                [
                    '_controller' => 'MsiAdminBundle:Core:sort',
                    '_admin' => $id,
                ]
            )
        );

        $collection->add(
            $id.'_exportcsv',
            new Route(
                $prefix.'/export-csv',
                [
                    '_controller' => 'MsiAdminBundle:Core:exportCsv',
                    '_admin' => $id,
                ]
            )
        );

        return $collection;
    }
}
