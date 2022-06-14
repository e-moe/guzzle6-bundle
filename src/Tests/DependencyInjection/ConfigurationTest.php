<?php

namespace Emoe\GuzzleBundle\Tests\DependencyInjection;

use Emoe\GuzzleBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
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
