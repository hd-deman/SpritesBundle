<?php

/*
 * This file is part of the SpritesBundle package.
 *
 * (c) Pierre Minnieur <pm@pierre-minnieur.de>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Pminnieur\SpritesBundle\Provider;

use Symfony\Component\Config\Definition\Processor,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Reference,
    Symfony\Component\DependencyInjection\DefinitionDecorator;

use Symfony\Component\Console\Input\InputInterface;
use Sprites\Configuration;
use Symfony\Component\DependencyInjection\ContainerAware;
use Imagine\Image\Color;

/**
 * A service to get sprites configuration objects from
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class ConfigurationProvider extends ContainerAware
{
    private $config;

    public function __construct($container, array $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    /**
     * @return array list of all available configuration names
     */
    public function getConfigurationNames()
    {
        return array_keys($this->config);
    }

    /**
     * Get the configuration options for the specified service
     *
     * @param $name
     *
     * @return array with the fields for
     *
     * @throws \InvalidArgumentException if there is no configuration with the specified name
     */
    public function getOptions($name)
    {
        if (! isset($this->config[$name])) {
            throw new \InvalidArgumentException("No spriteset named $name");
        }

        $keys = array('processor' => true, 'resize' => true);

        return array_intersect_key($this->config[$name], $keys);
    }

    /**
     * Get the specified configuration.
     *
     * @param string $name the name of the configuration to get
     * @param null|InputInterface $input the console input to overwrite configured settings (optional)
     *
     * @return Configuration the configuration object
     *
     * @throws \InvalidArgumentException if there is no configuration with the specified name
     */
    public function getConfiguration($name, InputInterface $input = null)
    {
        if (! isset($this->config[$name])) {
            throw new \InvalidArgumentException("No spriteset named $name");
        }
        $config = $this->config[$name];

        $configuration = new Configuration();

        $configuration->setImagine($this->container->get($config['imagine']));
        if (isset($config['options'])) {
            $configuration->setOptions($config['options']);
        }

        $finder = $configuration->getFinder();
        $finder->name($config['pattern']);
        foreach($config['sources'] as $source) {
            $finder->in($source);
        }

        $configuration->setImage($config['image']);
        $configuration->setColor(new Color($config['color'], $config['alpha']));
        $configuration->setStylesheet($config['stylesheet']);
        if (isset($config['selector'])) {
            $configuration->setSelector($config['selector']);
        }

        if (isset($config['width'])) {
            $configuration->setWidth($config['width']);
        }

        return $configuration;
    }
}
