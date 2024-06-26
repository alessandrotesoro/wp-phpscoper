# ℹ️ WP-PHPScoper

This package provides an opinionated PHP-Scoper configuration for WordPress plugins. Built-in CLI commands are provided to generate exclusions and to install php-scoper.

The package has been built for my own projects and it has been open-sourced in the hope that it will be useful to others.

<!-- TOC -->
- [ℹ️ WP-PHPScoper](#ℹ️-wp-phpscoper)
	- [📦 Installation](#-installation)
	- [📝 Usage](#-usage)
		- [Setup](#setup)
		- [Exclusions](#exclusions)
		- [About the exclusions](#about-the-exclusions)
			- [WordPress](#wordpress)
	- [⚙️ Scoper Configuration](#️-scoper-configuration)
		- [`Configurator::makeFinder()`](#configuratormakefinder)
		- [`Configurator::addFinder()`](#configuratoraddfinder)
		- [`Configurator::getScoperConfiguration()`](#configuratorgetscoperconfiguration)
	- [📄 Aknowledgements](#-aknowledgements)
	- [❔ Support](#-support)
	- [🚨 Security Issues](#-security-issues)
	- [🔖 License](#-license)
<!-- /TOC -->

## 📦 Installation

Navigate to the folder of your WordPress plugin and run the following command:

```bash
composer require sematico/wp-phpscoper --dev
```

This will install the package and add the `php-scoper` binary to your project's `vendor/bin` directory.

## 📝 Usage

### Setup

To install php-scoper, run the following command:

```bash
vendor/bin/wp-phpscoper setup
```

This will run specific composer commands to install php-scoper.

### Exclusions

To generate exclusions, run the following command:

```bash
vendor/bin/wp-phpscoper generate:exclusions
```

This will generate a list of exclusions for php-scoper. Follow the prompts to select the types of exclusions you want to generate.

### About the exclusions

Exclusions are generated inside the `.phpscoper` folder in your project's root directory. The exclusions files are JSON files that contain an array of classes, functions, and constants that should be excluded from being scoped.

The currently supported exclusions are:

- `WordPress`

#### WordPress

WordPress exclusions are generated by using the `php-stubs/wordpress-globals` and `php-stubs/wordpress-stubs` packages. Your plugin must have those packages installed in order for the exclusions to be generated.

Attempting to generate exclusions for a plugin that does not have those packages installed will result in an error.

## ⚙️ Scoper Configuration

The package provides a `Configurator` class that can be used to generate the scoper configuration file.

```php
<?php

declare(strict_types=1);

use Sematico\Scoper\Configurator;

require_once __DIR__ . '/../vendor/sematico/wp-phpscoper/src/Modules/Support/Manager.php';
require_once __DIR__ . '/../vendor/sematico/wp-phpscoper/src/Modules/Support/PackageInterface.php';
require_once __DIR__ . '/../vendor/sematico/wp-phpscoper/src/Modules/Support/AbstractSupported.php';
require_once __DIR__ . '/../vendor/sematico/wp-phpscoper/src/Modules/Support/WordPress.php';
require_once __DIR__ . '/../vendor/sematico/wp-phpscoper/src/Configurator.php';

$configurator = new Configurator( 'MyProject' );

$finder = $configurator::makeFinder(
	[
		'vendor/psr/container',
		'vendor/php-di/php-di',
	],
);

$configurator->addFinder( $finder );

return $configurator->getScoperConfiguration();
```

> **Note**
> The `Configurator` class and it's dependencies must be manually required in order to use it.

### `Configurator::makeFinder()`

The `makeFinder()` method takes an array of vendor paths and returns a `Finder` instance that can be used to find PHP files in the specified paths.

```php
$finder = $configurator::makeFinder(
	[
		'vendor/psr/container',
		'vendor/php-di/php-di',
	],
);
```

For more information on how to use the `makeFinder()` method, see the source code.

### `Configurator::addFinder()`

The `addFinder()` method takes a `Finder` instance and adds it to the list of finders used to find PHP files.

```php
$finder = $configurator::makeFinder(
	[
		'vendor/psr/container',
		'vendor/php-di/php-di',
	],
);

$configurator->addFinder( $finder );
```

### `Configurator::getScoperConfiguration()`

The `getScoperConfiguration()` method returns an array that can be used to configure php-scoper. For more information on the configuration options, see the [php-scoper documentation](https://github.com/humbug/php-scoper#configuration).

## 📄 Aknowledgements

- [snicco/php-scoper-excludes](https://github.com/snicco/php-scoper-excludes)
- [php-stubs/wordpress-globals](https://github.com/php-stubs/wordpress-globals)
- [php-stubs/wordpress-stubs](https://github.com/php-stubs/wordpress-stubs)

## ❔ Support

Please note that this is a personal project and support will be limited to bug reports and minor issues only.

## 🚨 Security Issues
If you discover a security vulnerability, please email [alessandro.tesoro@icloud.com](mailto:alessandro.tesoro@icloud.com). All security vulnerabilities will be promptly addressed.

<!-- LICENSE -->
## 🔖 License

Distributed under the MIT License. See `LICENSE` for more information.
