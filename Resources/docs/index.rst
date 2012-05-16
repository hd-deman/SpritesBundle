Overview
========

Contents:

.. toctree::
   :maxdepth: 2

Overview
--------

SpritesBundle provides the `Sprites library<https://github.com/pminnieur/sprites>`_ into Symfony2.

It reads folders of image files and outputs:

- an image file with all the images stitched together
- a css file with customizable entries based on the image filenames

For an introduction to the concept of CSS sprites, see for example `CSS-Sprites<http://css-tricks.com/css-sprites/>`_.

Installation
------------

Composer
~~~~~~~~

Add the following entry to your ``composer.json``:

.. code-block:: json

    { "require": { "pminnieur/sprites-bundle": "dev-master" }}

Checkout `detailed package information on Packagist`_.

Clone from GitHub
~~~~~~~~~~~~~~~~~

Clone Sprites git repository:

.. code-block:: console

    git clone git://github.com/pminnieur/SpritesBundle.git

Download ``composer.phar`` file and install dependencies:

.. code-block:: console

    wget -nc http://getcomposer.org/composer.phar
    php composer.phar install

Enable the bundle
-----------------

Add the bundle to your application kernel:

.. code-block:: php

    // app/AppKernel.php
    public function registerBundles()
    {
      return array(
          // ...
          new \Pminnieur\SpritesBundle\PminnieurSpritesBundle(),
          // ...
      );
    }


Usage
-----

The Sprites library reads source folders for images and dumps a css file and
an image file to locations you specify. You include the CSS file in your
project and make sure all sprites use the generated image as background.

Typically this means:

- Add the class ``sprite`` to all your sprite HTML elements
- Add the class corresponding to the filename (resp. the selector you specified)
- In CSS, set the generated sprite image as ``background`` for all .sprite elements
- In CSS, set the correct dimensions and repeat information to the corresponding classes
- Include the generated sprites CSS which will position the background correctly

Assuming one of your sprites is called logo and has size 336 x 63 pixels, you would do:

.. code-block:: html

    <span class="sprite logo"></span>

.. code-block:: css

    .sprite {
        display:block;
        background-repeat:no-repeat;
        background-image:url(/assets/images/sprite.png);
    }

    .logo {
        width:336px;
        height:63px;
    }

This bundle adds 3 commands to your app/console.

- **sprites:generate**: Generate an image sprite and CSS stylesheet from
    configuration settings.
- **sprites:generate:dynamic**: Generate an image sprite and CSS stylesheet
    with dynamic dimensions using command line arguments.
- **sprites:generate:fixed**: Generate an image sprite and CSS stylesheet with
    a fixed width dimension using command line arguments.

``sprites:generate`` is the recommended way and allows you to customize more
things than the other commands. See the configuration_ section for details on
how to configure the spritesets to generate.

To use the other commands, run them with --help to get an explanation of the
arguments and options.

If neither is flexible enough, see implementation_ to customize further.


configuration_
Configuration
-------------

The configuration fragment for the sprites bundle looks like this:

.. code-block:: yml

    pminnieur_sprites:
        # defaults apply to all spritesets
        defaults:

            # imagine backend for default imagine service. leave empty to let
            # Sprites determine what library is provided on your system.
            driver: gd | gmagick | imagick

            # list of options for the Imagine\Image\ManipulatorInterface::save() method
            options:

            # color code for the background color of the sprite image. defaults to fff (white)
            color: fff
            # alpha value for the background color of the sprite image. defaults to 100 (fully transparent)
            alpha: 100

            # the filename pattern to look for sprite source images. defaults to *.png
            pattern: "*.png"

            # CSS fragment for each image, see http://sprites.readthedocs.org/en/latest/#selector-optional
            selector: ".{{filename}}{background-position:{{pointer}}px 0px}"

        spritesets:
            # the names you use here are the configuration names for sprites:generate
            mysprites:

                # shortcut to choose whether to use the dynamic or fixed processor. defaults to true, using dynamic
                dynamic: true | false

                # use to specify your own processor service. if you do, do not specify the dynamic option
                processor: my_sprites_processor_service_id

                # the imagine service to use, defaults to the trivial service provided by the SpritesBundle
                # see also the "driver" default option
                imagine: pminnieur_sprites.imagine

                # either a directory name or a list of directory names to load the sprites from. required
                sources: "%kernel.root_dir%/../src/Organization/Bundle/CoreBundle/Resources/sprites/"
                # for a list, use
                # sources:
                #   - sprites_path
                #   - other_path

                # target path where to output the image. required
                image: "%kernel.root_dir%/../web/assets/images/sprites.png"

                # target path where to output the generated stylesheet. required
                # you can combine this stylesheet with the others using assetic
                stylesheet: "%kernel.root_dir%/../src/Organization/Bundle/CoreBundle/Resources/css/sprites.css"

                # overwrite the default options
                options:

                # overwrite the default color
                color: 000
                alpha: 100

                # overwrite the default pattern
                pattern: "*.gif"

                # overwrite the default selector
                selector: ".{{filename}}-sprite{background-position:{{pointer}}px 0px}"

                # whether to resize sprite images. only allowed with the fixed processor
                resize: true | false

                # specify the width of the sprite images in pixels to speed up generation. only used with the fixed processor
                width: 100


implementation_
Implementation
--------------

This bundle provides a couple of services and the commands plus the
configuration handling. It builds on top of the
`Sprites library<https://github.com/pminnieur/sprites>`_


Processor services
~~~~~~~~~~~~~~~~~~

The services ``pminnieur_sprites.dynamic_processor`` and
``pminnieur_sprites.fixed_processor`` expose the corresponding
``Sprites\Processor\ProcessorInterface``. If needed, you can add your own
processor services and define a ``processor`` configuration option.

The services are defined with ``prototype`` scope to avoid problems with
setting options on them.

Configuration service
~~~~~~~~~~~~~~~~~~~~~

The ``pminnieur_sprites.configuration_provider`` service provides the
``Sprites\Configuration`` instances for the configured spritesets.

Imagine service
~~~~~~~~~~~~~~~

The sprites bundle provides a trivial ``pminnieur_sprites.imagine`` service
with the Imagine object. If defined, it uses the ``driver`` default option
to select the image library, otherwise it determines what library is installed.

If you need further customization, you should configure the ``imagine`` option
to point to an imagine service you set up as needed.






A simple configuration of a ``Sprites\ProcessorInterface`` requires a
``Sprites\Configuration`` instance

.. code-block:: php

    <?php

    use Sprites\Configuration;
    use Sprites\Processor\DynamicProcessor;
    use Imagine\Gd\Imagine;
    use Imagine\Image\Color;

    $imagine = new Imagine();

    $config = new Configuration();
    $config->setImagine($imagine);
    $config->setColor(new Color('fff', 100));
    $config->setImage('web/images/icons.png');
    $config->setStylesheet('web/css/icons.css');
    $config->getFinder()->name('*.png')->in('web/images/icons');
    $config->setSelector(".icon.{{filename}}{background-position:{{pointer}}px 0px}\n");

    $processor = new DynamicProcessor();
    $processor->process($config);

    // ...

Configuration Options
~~~~~~~~~~~~~~~~~~~~~
The following sections describe all the configuration options available on a
``Sprites\Configuration`` instance.

Imagine (***REQUIRED***)
^^^^^^^^^^^^^^^^^^^^^^^^
.. code-block:: php

    <?php
    $config->setImagine($imagine);
    $config->getImagine();

The ``Imagine\ImagineInterface`` instance to use.

Options (***RECOMMENDED***)
^^^^^^^^^^^^^^^^^^^^^^^^^^^
.. code-block:: php

    <?php
    $config->setOptions($options);
    $config->getOptions();

An array of options for the ``Imagine\Image\ManipulatorInterface::save()``
method.

.. note::

    Each ``Imagine\ImageInterface`` adapter has its own subset of options.

Finder (***REQUIRED***)
^^^^^^^^^^^^^^^^^^^^^^^
.. code-block:: php

    <?php
    $config->setFinder($finder);
    $config->getFinder();

The ``Symfony\Component\Finder\Finder`` instance used to find files for your
sprites.

.. note::

    To unleash the full power of this component, read the `Finder`_
    documentation.

Image (***REQUIRED***)
^^^^^^^^^^^^^^^^^^^^^^
.. code-block:: php

    <?php
    $config->setImage($path);
    $config->getImage();

The path to the target image sprite.

.. note::

    If the directory does not exist yet, it will automatically be created.

Color (***OPTIONAL***)
^^^^^^^^^^^^^^^^^^^^^^
.. code-block:: php

    <?php
    $config->setColor($color);
    $config->getColor();

The ``Imagine\Image\Color`` instance to use as background color.

Processor (***OPTIONAL***)
^^^^^^^^^^^^^^^^^^^^^^^^^^
.. code-block:: php

    <?php
    $config->setProcessor($processor);
    $config->getProcessor();

The name of the ``Sprites\Processor\ProcessorInterface`` to use. This
configuration value is only needed if you use the ``Sprites\Generator``
and may be guessed automatically, depending if you set a fixed width or not.

.. note::

    Sprites already supports two different kind of processors:

    - **dynamic:** ``Sprites\Processor\DynamicProcessor``
    - **fixed:** ``Sprites\Processor\FixedProcessor``

Width (***OPTIONAL***)
^^^^^^^^^^^^^^^^^^^^^^
.. code-block:: php

    <?php
    $config->setWidth($width);
    $config->getWidth();

A fixed width for each image in the sprite. This configuration value is only
used in the ``Sprites\Processor\FixedProcessor`` and speeds up generating
the image sprite.

.. note::

    The ``Sprites\Processor\FixedProcessor`` could optionally resize
    your images if they exceed the fixed width.

Stylesheet (***REQUIRED***)
^^^^^^^^^^^^^^^^^^^^^^^^^^^
.. code-block:: php

    <?php
    $config->setStylesheet($path);
    $config->getStylesheet();

The path to the target stylesheet.

.. note::

    If the directory does not exist yet, it will automatically be created.

Selector (***OPTIONAL***)
^^^^^^^^^^^^^^^^^^^^^^^^^
.. code-block:: php

    <?php
    $config->setSelector($selector);
    $config->getSelector();

A string parsed for each image, to be used as the CSS in the generated
stylesheet.

.. note::

    The default value of the ``selector`` is
    ``".{{filename}}{background-position:{{pointer}}px 0px}\n"``.

.. note::

    The string is parsed with `Mustache`_ and there are two available parameters:

    - **``pointer``:** horizontal position of current pointer (in ``px``)
    - **``filename``:** an `ASCIIfied`_ version of the filename.

Usage
-----

Processor
~~~~~~~~~

DynamicProcessor
^^^^^^^^^^^^^^^^
The ``DynamicProcessor`` class is used to generate image sprites from images
with a dynamic width and height.

.. code-block:: php

    <?php

    use Sprites\Configuration;
    use Sprites\Processor\DynamicProcessor;

    $config = new Configuration();
    // ... configure your configuration

    $processor = new DynamicProcessor();
    $processor->process($config);


FixedProcessor
^^^^^^^^^^^^^^
The ``FixedProcessor`` class works similar to the ``DynamicProcessor``. There
are two main differences:

- the ``getWidth()`` configuration value is used to determine the absolute width
  of the final image sprite, to spare recalculating the absolute width each
  time a new image is pasted into the sprite.
- you can optionally enable resizing, resulting in images exceeding the fixed
  width to be scaled down with a correct aspect ratio to fit the fixed width.

.. note::

    The height of the image sprite is still calculated dynamically, but ideally
    only once (e.g. if you use `famfamfam`_ icons which usually are dimensioned
    in ``16px x 16px``, the first image sets the sprites' height to ``16px`` and
    then the height must not be adjusted again).

.. code-block:: php

    <?php

    use Sprites\Configuration;
    use Sprites\Processor\FixedProcessor;

    $config = new Configuration();
    $config->setWidth(16); // fixed width of 16px per image
    // ... configure your configuration

    $processor = new FixedProcessor(array('resize' => true));
    $processor->process($config);

Generator
~~~~~~~~~

The ``Sprites\Generator`` class is used for batch processing multiple
``Sprites\Configuration`` instances with their corresponding
``Sprites\Processor\ProcessorInterface`` instances.

.. code-block:: php

    <?php

    use Sprites\Configuration;
    use Sprites\Generator;
    use Sprites\Processor\DynamicProcessor;
    use Sprites\Processor\FixedProcessor;
    // ... add your processor classes

    $generator = new Generator();

    $config = new Configuration();
    // ... configure your configuration
    $generator->addConfiguration($config);
    // ... add your configurations

    $dynamic = new DynamicProcessor();
    $generator->addProcessor($dynamic);
    $fixed = new FixedProcessor();
    $generator->addProcessor($fixed);
    // ... add your processors

    $generator->generate();

Command Line Interface
~~~~~~~~~~~~~~~~~~~~~~
The Sprites Console is a Command Line Interface tool for simplifying the usage
and generation of image sprites without the need of you actually writing a
single line of PHP code.

sprites generate:dynamic
^^^^^^^^^^^^^^^^^^^^^^^^
The ``generate:dynamic`` command generates image sprites and CSS stylesheets
with dynamic dimensions::

    php bin/sprites generate:dynamic --help

sprites generate:fixed
^^^^^^^^^^^^^^^^^^^^^^
The ``generate:fixed`` command generates image sprites and CSS stylesheets with
a fixed width dimension::

    php bin/sprites generate:fixed --help

.. _`Imagine`: https://github.com/avalanche123/Imagine
.. _`Symfony`: http://symfony.com/
.. _`Finder`: http://symfony.com/doc/current/cookbook/tools/finder.html#index-0
.. _`Mustache`: https://github.com/weierophinney/phly_mustache
.. _`ASCIIfied`: http://sourcecookbook.com/en/recipes/8/function-to-slugify-strings-in-php
.. _`famfamfam`: http://famfamfam.com/
.. _`detailed package information on Packagist`: http://packagist.org/packages/pminnieur/sprites
