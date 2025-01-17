# Convert a pdf to an image

[![Latest Version on Packagist](https://img.shields.io/packagist/v/offspring/pdf-to-image.svg?style=flat-square)](https://packagist.org/packages/offspring/pdf-to-image)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/offspring/pdf-to-image/master.svg?style=flat-square)](https://travis-ci.org/offspring/pdf-to-image)
[![Quality Score](https://img.shields.io/scrutinizer/g/offspring/pdf-to-image.svg?style=flat-square)](https://scrutinizer-ci.com/g/offspring/pdf-to-image)
[![StyleCI](https://styleci.io/repos/38419604/shield?branch=master)](https://styleci.io/repos/38419604)
[![Total Downloads](https://img.shields.io/packagist/dt/offspring/pdf-to-image.svg?style=flat-square)](https://packagist.org/packages/offspring/pdf-to-image)

This package provides an easy to work with class to convert pdf's to images.

Offspring is a webdesign agency in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://offspring.be/opensource).

## Requirements

You should have [Imagick](http://php.net/manual/en/imagick.setresolution.php) and [Ghostscript](http://www.ghostscript.com/) installed. See [issues regarding Ghostscript](#issues-regarding-ghostscript).

## Installation

The package can be installed via composer:
``` bash
$ composer require offspring/pdf-to-image
```

## Usage

Converting a pdf to an image is easy.

```php
$pdf = new Offspring\FileToThumbnail\FileToThumbnail($pathToPdf);
$pdf->saveImage($pathToWhereImageShouldBeStored);
```

If the path you pass to `saveImage` has the extensions `jpg`, `jpeg`, or `png` the image will be saved in that format.
Otherwise the output will be a jpg.

## Other methods

You can get the total number of pages in the pdf:
```php
$pdf->getNumberOfPages(); //returns an int
```

By default the first page of the pdf will be rendered. If you want to render another page you can do so:
```php
$pdf->setPage(2)
    ->saveImage($pathToWhereImageShouldBeStored); //saves the second page
```

You can override the output format:
```php
$pdf->setOutputFormat('png')
    ->saveImage($pathToWhereImageShouldBeStored); //the output wil be a png, no matter what
```

You can set the quality of compression from 0 to 100:
```php
$pdf->setCompressionQuality(100); // sets the compression quality to maximum
```

## Issues regarding Ghostscript

This package uses Ghostscript through Imagick. For this to work Ghostscripts `gs` command should be accessible from the PHP process. For the PHP CLI process (e.g. Laravel's asynchronous jobs, commands, etc...) this is usually already the case. 

However for PHP on FPM (e.g. when running this package "in the browser") you might run into the following problem:

```
Uncaught ImagickException: FailedToExecuteCommand 'gs'
```

This can be fixed by adding the following line at the end of your `php-fpm.conf` file and restarting PHP FPM. If you're unsure where the `php-fpm.conf` file is located you can check `phpinfo()`. If you are using Laravel Valet the `php-fpm.conf` file will be located in the `/usr/local/etc/php/YOUR-PHP-VERSION` directory.

```
env[PATH] = /usr/local/bin:/usr/bin:/bin
```

This will instruct PHP FPM to look for the `gs` binary in the right places.

## Testing

``` bash
$ composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@offspring.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Offspring, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://offspring.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/offspring)
- [All Contributors](../../contributors)

## Support us

Offspring is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://offspring.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/offspring). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
