<?php

/*
 * This file is part of the SpritesBundle package.
 *
 * (c) Pierre Minnieur <pm@pierre-minnieur.de>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Pminnieur\SpritesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This class contains the configuration information for the bundle
 *
 * This information is solely responsible for how the different configuration
 * sections are normalized, and merged.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pminnieur_sprites', 'array');

        $rootNode
            ->fixXmlConfig('default', 'defaults')
            ->children()
                ->arrayNode('defaults')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('driver')->end()
                        ->arrayNode('options')->end()
                        ->scalarNode('color')->defaultValue('fff')->end()
                        ->scalarNode('alpha')->defaultValue(100)->end()
                        ->scalarNode('pattern')->defaultValue('*.png')->end()
                        ->scalarNode('selector')->end()
                    ->end()
                ->end()
            ->end()

            ->fixXmlConfig('spriteset', 'spritesets')
            ->children()
                ->arrayNode('spritesets')
                ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('source', 'sources')
                        ->children()
                            ->scalarNode('dynamic')->end()
                            ->scalarNode('processor')->end()
                            ->scalarNode('imagine')->defaultValue('pminnieur_sprites.imagine')->end()
                            ->arrayNode('sources')
                                ->beforeNormalization()
                                    ->ifString()
                                    ->then(function($v) { return array($v); })
                                ->end()
                                ->prototype('scalar')->end()
                                ->isRequired()
                            ->end()
                            ->scalarNode('image')->isRequired()->end()
                            ->scalarNode('stylesheet')->isRequired()->end()
                            ->arrayNode('options')->end()
                            ->scalarNode('color')->end()
                            ->scalarNode('alpha')->end()
                            ->scalarNode('pattern')->end()
                            ->scalarNode('selector')->end()
                            ->scalarNode('resize')->end()
                            ->scalarNode('width')->end()
                        ->end()
                        ->validate()
                            ->ifTrue(function($v) {
                                return isset($v['dynamic']) && isset($v['processor']);
                            })
                            ->thenInvalid('Do not set dynamic and processor at the same time.')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
