<?php

namespace Msi\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class FindAdminPass implements CompilerPassInterface
{
    function process(ContainerBuilder $container)
    {
        $ids = [];
        foreach ($container->findTaggedServiceIds('msi.admin') as $id => $tags) {
            $ids[] = $id;
            $admin = $container->getDefinition($id);
            $admin->addMethodCall('setId', [$id]);
        }
        $container->setParameter('msi_admin.admin_ids', $ids);
    }
}
