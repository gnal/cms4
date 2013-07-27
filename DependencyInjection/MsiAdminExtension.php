<?php

namespace Msi\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MsiAdminExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        // $loader->load('services.xml');
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('admin.yml');
        $loader->load('services.yml');

        $this->registerConfiguration($config, $container);
    }

    private function registerConfiguration($config, ContainerBuilder $container)
    {
        $container->setParameter('msi_admin.multisite', $config['multisite']);
        $container->setParameter('msi_admin.tiny_mce', $config['tiny_mce']);
        $container->setParameter('msi_admin.app_locales', $config['app_locales']);
        $container->setParameter('msi_admin.site.class', $config['site_class']);
        $container->setParameter('msi_admin.menu.class', $config['menu_class']);
        $container->setParameter('msi_admin.page.class', $config['page_class']);
        $container->setParameter('msi_admin.page.layouts', $config['page_layouts']);
        $container->setParameter('msi_admin.block.class', $config['block_class']);
        $container->setParameter('msi_admin.block.actions', $config['block_actions']);
        $container->setParameter('msi_admin.block.templates', $config['block_templates']);
        $container->setParameter('msi_admin.block.slots', $config['block_slots']);

        $container->setParameter('msi_admin.site.admin', $config['site_admin']);
        $container->setParameter('msi_admin.menu_root.admin', $config['menu_root_admin']);
        $container->setParameter('msi_admin.menu_node.admin', $config['menu_node_admin']);
        $container->setParameter('msi_admin.page.admin', $config['page_admin']);
        $container->setParameter('msi_admin.block.admin', $config['block_admin']);
    }
}
