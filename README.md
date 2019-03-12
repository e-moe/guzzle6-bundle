# Symfony Guzzle6Bundle

[![Latest Stable Version](https://poser.pugx.org/e-moe/guzzle6-bundle/v/stable)](https://packagist.org/packages/e-moe/guzzle6-bundle)
[![Total Downloads](https://poser.pugx.org/e-moe/guzzle6-bundle/downloads)](https://packagist.org/packages/e-moe/guzzle6-bundle)
[![Latest Unstable Version](https://poser.pugx.org/e-moe/guzzle6-bundle/v/unstable)](https://packagist.org/packages/e-moe/guzzle6-bundle)
[![License](https://poser.pugx.org/e-moe/guzzle6-bundle/license)](https://packagist.org/packages/e-moe/guzzle6-bundle)

[![Build Status](https://travis-ci.org/e-moe/guzzle6-bundle.svg?branch=master)](https://travis-ci.org/e-moe/guzzle6-bundle)
[![Coverage Status](https://coveralls.io/repos/e-moe/guzzle6-bundle/badge.svg?branch=master&service=github)](https://coveralls.io/github/e-moe/guzzle6-bundle?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b78325e0-a85f-477a-bd98-13ab1a551742/mini.png)](https://insight.sensiolabs.com/projects/b78325e0-a85f-477a-bd98-13ab1a551742)

This bundle integrates [Guzzle 6.x][guzzle] into Symfony. Guzzle is a PHP framework for building RESTful web service clients.

## Requirements

 - PHP 7.1 or above
 - Symfony 3.4 or above (including 4.x)

 
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

Load bundle in AppKernel.php (should be done automatically if you are using Symfony Flex):
``` php
new Emoe\GuzzleBundle\EmoeGuzzleBundle(),
```

Configuration in config.yml (optional):
``` yaml
emoe_guzzle:
    log:
        enabled: true # Logging requests to Monolog
        format: 'Guzzle: [{ts}] "{method} {uri} HTTP/{version}" {code}' # Optional log format customization
```
see more about [log format syntax][log-format].

Using Guzzle in controllers:
``` php
$client   = $this->get('guzzle.client');
$response = $client->get('http://example.com');
```

Using Guzzle in your own services:
``` yaml
  application.my_service:
    class: App\Service\MyService
    arguments:
      - "@guzzle.client"
```
or you can just use [autowire][autowire] feature with `GuzzleHttp\ClientInterface` type hint.

## Features

### Symfony Debug Profiler

<img src="/src/Resources/doc/img/profiler.png" alt="Guzzle Requests" title="Symfony Debug Toolbar - Guzzle Logs" />

### Symfony Debug Timeline

<img src="/src/Resources/doc/img/timeline.png" alt="Guzzle Timeline Integration" title="Symfony Debug Toolbar - Timeline Integration" />

### Symfony Debug Toolbar

<img src="/src/Resources/doc/img/toolbar.png" alt="Guzzle Toolbar Integration" title="Symfony Debug Toolbar Integration" />

### Symfony Debug Logs (Monolog Integration)

<img src="/src/Resources/doc/img/logs.png" alt="Guzzle Monolog Logs" title="Symfony Debug Toolbar Logs" />


## Suggestions

Adding aliases:
If you want to use different names for provided services you can use aliases. This is a good idea if you don't want 
have any dependency to guzzle in your service name.
``` yaml
services:
   http.client:
       alias: guzzle.client
```

Creating multiple clients:
If you want to have different Guzzle clients in your application all you need is to define them in services file and
add "guzzle.client" tag to turn on Symfony integration (Debug toolbar, logs, so on..).
``` yaml
services:
    guzzle.client_one:
        class: GuzzleHttp\Client
        tags:
            - { name: guzzle.client }

    guzzle.client_two:
        class: GuzzleHttp\Client
        tags:
            - { name: guzzle.client }
```


## Authors
 - Nikolay Labinskiy aka e-moe
 
Inspired by Chris Wilkinson's and Florian Preusner's GuzzleBundles ([1][misd-guzzle], [2][8p]).

See also the list of [contributors][contributors] who participated in this project.

## License

This bundle is released under the [MIT license](LICENSE)

[guzzle]:       http://guzzlephp.org/
[packagist]:    https://packagist.org/packages/e-moe/guzzle6-bundle
[autowire]:     https://symfony.com/doc/current/service_container.html#the-autowire-option
[contributors]: https://github.com/e-moe/guzzle6-bundle/graphs/contributors
[misd-guzzle]:  https://github.com/misd-service-development/guzzle-bundle
[8p]:           https://github.com/8p/GuzzleBundle
[log-format]:   https://github.com/guzzle/guzzle/blob/master/src/MessageFormatter.php#L12
