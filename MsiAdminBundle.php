<?php

namespace Msi\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Msi\AdminBundle\DependencyInjection\Compiler\FindAdminPass;
use Msi\AdminBundle\Doctrine\Mapping\DefaultNamingStrategy;

class MsiAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FindAdminPass());
    }

    public function boot()
    {
        $this->container->get('doctrine')->getManager()->getConfiguration()->setNamingStrategy(new DefaultNamingStrategy());
    }
}
