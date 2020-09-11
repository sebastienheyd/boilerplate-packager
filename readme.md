# Package manager for [sebastienheyd/boilerplate](https://github.com/sebastienheyd/boilerplate).

[![Version](https://img.shields.io/packagist/v/sebastienheyd/boilerplate-packager.svg?style=flat-square)](https://packagist.org/packages/sebastienheyd/boilerplate-packager)
[![Downloads](https://img.shields.io/packagist/dt/sebastienheyd/boilerplate-packager.svg?style=flat-square)](https://packagist.org/packages/sebastienheyd/boilerplate-packager)
[![StyleCI](https://styleci.io/repos/292614089/shield)](https://styleci.io/repos/292614089)
[![Scrutinizer](https://scrutinizer-ci.com/g/sebastienheyd/boilerplate-packager/badges/quality-score.png?b=master&style=flat-square)](https://scrutinizer-ci.com/g/sebastienheyd/boilerplate-packager/?branch=master)
[![License](https://img.shields.io/github/license/sebastienheyd/boilerplate-packager.svg)](license.md)

This package will allow you to easily create and manage your own local packages for [sebastienheyd/boilerplate](https://github.com/sebastienheyd/boilerplate).

## Installation

Via Composer

```bash
composer require sebastienheyd/boilerplate-packager --dev
```

**Optionnal**: publish configuration file

```bash
php artisan vendor:publish --provider="Sebastienheyd\BoilerplatePackager\ServiceProvider"
```

## Usage

By default, a help is displayed when calling `boilerplate:packager` without any argument.

```bash
php artisan boilerplate:packager
```

### Create a new package

By default, every package will be generated using [sebastienheyd/boilerplate-package-skeleton](https://github.com/sebastienheyd/boilerplate-package-skeleton).
Note that you can use a local path instead by setting the `skeleton` value in the [configuration file](src/config/packager.php).

You can also modify the default creation data (author, e-mail, description, license) by modifying the values in the [configuration file](src/config/packager.php)

```bash
php artisan boilerplate:packager create my-vendor/my-package
```

In this example, after asking some questions about author, email, description, license and resource, the command will load the skeleton package in a `packages/my-vendor/my-package` directory.
Then, it will require the local package in your project, a symlink is created in the `vendor` directory pointing to the local package. 
All you have to do after that is to code in you freshly installed package.

**Options**

You can create a package with the option `--dev` to add the package to the `require-dev` section in `composer.json`

### Require a package

You can require a package from packagist or by giving a git repository url.

```bash
php artisan boilerplate:packager require my-vendor/my-package
php artisan boilerplate:packager require https://github.com/my-vendor/my-package
php artisan boilerplate:packager require git@github.com/my-vendor/my-package
```

This will clone the package in the `packages` folder and require it via composer.

**Options**

You can require a package with the option `--dev` to add the package to the `require-dev` section in `composer.json`

### Remove a local package

You can remove a local package by using the `remove` command. 
If you called the command without a package name you will be asked to chose the package to remove :

```bash
php artisan boilerplate:packager remove
php artisan boilerplate:packager remove my-vendor/my-package
```

This will remove the local package from your project. The command will ask to confirm the deletion of the local folder.

### List local package

```bash
php artisan boilerplate:packager list
```

This will display a table with all local packages, installed or not.

## Test and coding standard

This package is delivered with tests, to launch tests just launch:

```bash
make test
```

Coding standard can also be checked by using `phpcs`:

```bash
make cs
```

## Contributing

Please see [contributing.md](contributing.md) for details.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [Sébastien HEYD][link-author]
- [All Contributors][link-contributors]

## License

MIT license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/sebastienheyd/boilerplate-packager.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/sebastienheyd/boilerplate-packager.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/sebastienheyd/boilerplate-packager/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/292614089/shield

[link-packagist]: https://packagist.org/packages/sebastienheyd/boilerplate-packager
[link-downloads]: https://packagist.org/packages/sebastienheyd/boilerplate-packager
[link-travis]: https://travis-ci.org/sebastienheyd/boilerplate-packager
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/sebastienheyd
[link-contributors]: ../../contributors
