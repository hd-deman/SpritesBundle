<?php

/*
 * This file is part of the SpritesBundle package.
 *
 * (c) Pierre Minnieur <pm@pierre-minnieur.de>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Pminnieur\SpritesBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * A command to generate sprites from a symfony configuration
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class GenerateSpritesCommand extends Command
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('sprites:generate')
            ->addArgument('configuration', InputOption::VALUE_OPTIONAL, 'The configuration name to generate sprites from.')
            ->setDescription('Generate an image sprite and CSS stylesheet from configuration settings.')
            ->setHelp(<<<EOT
The <info>sprites:generate</info> command generates image sprites and CSS
stylesheets. If you specify the configuration this configuration will be
generated. Without argument, all configured spritesets will be generated.
Sample usage:

  <info>app/console sprites:generate configuration1 ... configurationN</info>
EOT
        )
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $provider = $this->getApplication()->getKernel()->getContainer()->get('pminnieur_sprites.configuration_provider');

        $configurations = $input->getArgument('configuration');
        if (! count($configurations)) {
            $configurations = $provider->getConfigurationNames();
        }
        if (! count($configurations)) {
            throw new \RuntimeException('Please configure at least one spriteset before running this command');
        }

        foreach($configurations as $configuration) {
            $options = $provider->getOptions($configuration);
            $processor = $this->getApplication()->getKernel()->getContainer()->get($options['processor']);

            if (isset($options['resize'])) {
                $processor->setOption('resize', $options['resize']);
            }
            $processor->process($provider->getConfiguration($configuration, $input));
        }
    }
}
