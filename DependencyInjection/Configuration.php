<?php

namespace Msi\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('msi_admin');

        $rootNode
            ->children()
                ->booleanNode('multisite')->defaultFalse()->end()
                ->scalarNode('tiny_mce')
                    ->defaultValue('MsiAdminBundle:Form:tiny_mce.html.twig')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('app_locales')
                    ->prototype('scalar')->end()
                    ->defaultValue(['en', 'fr'])
                    ->cannotBeEmpty()
                ->end()
            ->end();

        $this->addSiteSection($rootNode);
        $this->addMenuSection($rootNode);
        $this->addPageSection($rootNode);
        $this->addBlockSection($rootNode);
        $this->addAdminSection($rootNode);

        return $treeBuilder;
    }

    private function addSiteSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('site_class')
                    ->defaultValue('Msi\AdminBundle\Entity\Site')
                    ->cannotBeEmpty()
                ->end()
            ->end();
    }

    private function addMenuSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('menu_class')
                    ->defaultValue('Msi\AdminBundle\Entity\Menu')
                    ->cannotBeEmpty()
                ->end()
            ->end();
    }

    private function addPageSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('page_class')
                    ->defaultValue('Msi\AdminBundle\Entity\Page')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('page_layouts')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
            ->end();
    }

    private function addBlockSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('block_class')
                    ->defaultValue('Msi\AdminBundle\Entity\Block')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('block_actions')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('block_templates')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('block_slots')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;
    }

    protected function addAdminSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('site_admin')->defaultValue('Msi\AdminBundle\Admin\SiteAdmin')->cannotBeEmpty()->end()
                ->scalarNode('menu_root_admin')->defaultValue('Msi\AdminBundle\Admin\MenuRootAdmin')->cannotBeEmpty()->end()
                ->scalarNode('menu_node_admin')->defaultValue('Msi\AdminBundle\Admin\MenuNodeAdmin')->cannotBeEmpty()->end()
                ->scalarNode('page_admin')->defaultValue('Msi\AdminBundle\Admin\PageAdmin')->cannotBeEmpty()->end()
                ->scalarNode('block_admin')->defaultValue('Msi\AdminBundle\Admin\BlockAdmin')->cannotBeEmpty()->end()
            ->end()
        ;
    }
}
