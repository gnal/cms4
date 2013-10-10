<?php

namespace Msi\AdminBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Msi\AdminBundle\DependencyInjection\Compiler\FindAdminPass;

class MsiAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new FindAdminPass());
    }
}
