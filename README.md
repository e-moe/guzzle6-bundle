# Symfony Guzzle6Bundle

[![Latest Stable Version](https://poser.pugx.org/e-moe/guzzle6-bundle/v/stable)](https://packagist.org/packages/e-moe/guzzle6-bundle)
[![Total Downloads](https://poser.pugx.org/e-moe/guzzle6-bundle/downloads)](https://packagist.org/packages/e-moe/guzzle6-bundle)
[![Latest Unstable Version](https://poser.pugx.org/e-moe/guzzle6-bundle/v/unstable)](https://packagist.org/packages/e-moe/guzzle6-bundle)
[![License](https://poser.pugx.org/e-moe/guzzle6-bundle/license)](https://packagist.org/packages/e-moe/guzzle6-bundle)

This bundle integrates [Guzzle 6.x][guzzle] into Symfony. Guzzle is a PHP framework for building RESTful web service clients.

## Requirements
 - PHP 5.5 or above ([Guzzle 6][guzzle] requrenment)
 - [Guzzle PHP Framework][guzzle] (included by composer)

 
## Installation
To install this bundle, run the command below and you will get the latest version by [Packagist][packagist].

``` bash
composer require e-moe/guzzle6-bundle
```

To use the newest (maybe unstable) version please add following into your composer.json:

``` json
{
    "require": {
        "e-moe/guzzle6-bundle": "dev-master"
    }
}
```


## Usage
Load bundle in AppKernel.php:
``` php
new Emoe\GuzzleBundle\EmoeGuzzleBundle(),
```

Configuration in config.yml:
``` yaml
emoe_guzzle:
    log:
        enabled: true # Logging requests to Monolog
```

Using services in controller:
``` php
$client   = $this->get('guzzle.client');
$response = $client->get('http://example.com');
```

## Features

### Symfony Debug Profiler

<img src="/Resources/doc/img/profiler.png" alt="Guzzle Requests" title="Symfony Debug Toolbar - Guzzle Logs" style="max-width: 360px" />

### Symfony Debug Timeline

<img src="/Resources/doc/img/timeline.png" alt="Guzzle Timeline Integration" title="Symfony Debug Toolbar - Timeline Integration" style="max-width: 360px" />

### Symfony Debug Toolbar

<img src="/Resources/doc/img/toolbar.png" alt="Guzzle Toolbar Integration" title="Symfony Debug Toolbar Integration" style="max-width: 360px" />

## Suggestions
Adding aliases:
If you want to use different names for provided services you can use aliases. This is a good idea if you don't want 
have any dependency to guzzle in your service name.
``` yaml
services:
   http.client:
       alias: guzzle.client
```

## Authors
 - Nikolay Labinskiy aka e-moe
 
Inspired by Chris Wilkinson's and Florian Preusner's GuzzleBundles ([1][misd-guzzle], [2][8p]).

See also the list of [contributors][contributors] who participated in this project.

## License
This bundle is released under the [MIT license](Resources/meta/LICENSE)

[guzzle]:       http://guzzlephp.org/
[packagist]:    https://packagist.org/packages/e-moe/guzzle6-bundle
[contributors]: https://github.com/e-moe/guzzle6-bundle/graphs/contributors
[misd-guzzle]:  https://github.com/misd-service-development/guzzle-bundle
[8p]:           https://github.com/8p/GuzzleBundle