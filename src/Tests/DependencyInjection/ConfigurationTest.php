<?php

namespace Emoe\GuzzleBundle\Tests\DependencyInjection;

use Emoe\GuzzleBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testConfiguration()
    {
        $config = [
            'emoe_guzzle' => [
                'log' => [
                    'enabled' => true,
                    'format' => 'test',
                ]
            ]
        ];
        $processor = new Processor();
        $processedConfig = $processor->processConfiguration(new Configuration(), $config);
        $this->assertEquals($config['emoe_guzzle'], $processedConfig);
    }
}
