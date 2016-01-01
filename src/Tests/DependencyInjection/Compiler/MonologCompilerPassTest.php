<?php

namespace Doctrine\Bundle\DoctrineBundle\Tests\DependencyInjection\Compiler;

use Emoe\GuzzleBundle\DependencyInjection\Compiler\MonologCompilerPass;
use Prophecy\Argument;

class MonologCompilerClassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider processProvider
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testProcess($hasLogger, $hasHandler)
    {
        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->has('monolog.logger')->willReturn($hasLogger);
        $container->getParameter('emoe_guzzle.log.enabled')->willReturn($hasLogger);

        $monologMiddleware = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $monologMiddleware->addMethodCall('attachMiddleware', Argument::type('array'));

        $container->findDefinition('emoe_guzzle.request_monolog_middleware')->willReturn(
            $monologMiddleware->reveal()
        );

        $container->findTaggedServiceIds('guzzle.client')->willReturn([
            'test_service_id' => 'test service'
        ]);

        $testService = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $testService->getArguments()->willReturn($hasHandler ? [['handler' => 'test handler']] : []);
        if ($hasLogger && $hasHandler) {
            $testService->setArguments(Argument::type('array'))->shouldBeCalled();
        }

        $container->getDefinition('test_service_id')->willReturn($testService->reveal());

        $compilerPass = new MonologCompilerPass();
        $compilerPass->process($container->reveal());
    }

    public function processProvider()
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }
}
