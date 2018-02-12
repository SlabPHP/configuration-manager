# configuration-manager

SlabPHP Configuration Manager Library. This is an extracted version of the configuration utility from the Slab framework. It is designed to work with "PHP Configuration files".

## Installation

Include this project with composer:

    composer require slabphp/configuration-manager
    
## Usage

Configure the class and create a manager.
    
    $configuration = new \Slab\Configuration\Configuration();
    $configuration->setCascadingSearchDirectories([
        '/some/directory',
        '/some/other/directory'
    ]);
    $configuration->setFileList(['default.php', 'override.php']);

    $manager = new \Slab\Configuration\Manager($configuration);

This will instantiate a configuration manager that will look for "php configuration" files in the following directories:

* /some/directory/default.php
* /some/directory/override.php
* /some/other/directory/default.php
* /some/other/directory/override.php

The values in each will override the previously read files. You can use the secondary files to use stages based on an environment variable or server name.

Assuming this is the contents of the "php configuration" file at /some/directory/default.php:

    <?php
       
    $config['someValue'] = true;
    $config['something'] = [
        'something' => 'something else',
        'subValue' => [
            'thing' => 'fromage'
        ]
    ];

You would have available to you the following values in your calling code:

    echo $manager->someValue; //outputs true (or 1, whatever php)
    echo $manager->something->something; //outputs 'something else'
    echo $manager->something->subValue->thing; //outputs 'fromage'
    
## Library Caveats

You can feel free to use this, submit bugs, contact us, or submit change requests. The SlabPHP framework is largely in maintenance mode at the moment and is in the process of being released open source under the Apache 2.0 license. We're well aware that better alternatives exist in this day and age, please see the SlabPHP main documentation for more details. 