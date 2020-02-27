<?php

namespace Emoe\GuzzleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see
 * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    const ROOT_NODE = 'emoe_guzzle';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(self::ROOT_NODE);
        $methodExists = method_exists(TreeBuilder::class, 'getRootNode');
        $rootNode = $methodExists ? $treeBuilder->getRootNode() : $treeBuilder->root(self::ROOT_NODE);

        $rootNode
            ->children()
                ->arrayNode('log')
                    ->canBeDisabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('format')->defaultValue('CLF')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
