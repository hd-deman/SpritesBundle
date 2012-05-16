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

use Sprites\Command\GenerateDynamicSpritesCommand as BaseCommand;

class GenerateDynamicSpritesCommand extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('sprites:generate:dynamic')
            ->setDescription('Generate an image sprite and CSS stylesheet with dynamic dimensions using command line arguments.')
        ;
    }
}
