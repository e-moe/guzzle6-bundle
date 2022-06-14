<?php

namespace Emoe\GuzzleBundle\Tests\DependencyInjection\Compiler;

use Emoe\GuzzleBundle\DependencyInjection\Compiler\MonologCompilerPass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class MonologCompilerClassTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider processProvider
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testProcess($hasLogger, $hasHandler)
    {
        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->has('monolog.logger')->willReturn($hasLogger)->shouldBeCalled();
        $container->getParameter('emoe_guzzle.log.enabled')->willReturn($hasLogger);

        if ($hasLogger) {
            $container->findDefinition('emoe_guzzle.handler_stack')->shouldBeCalled();
        }

        $monologMiddleware = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $monologMiddleware->addMethodCall('attachMiddleware', Argument::type('array'))->willReturn($monologMiddleware->reveal());

        $container->findDefinition('emoe_guzzle.request_monolog_middleware')->willReturn(
            $monologMiddleware->reveal()
        );

        $container->findTaggedServiceIds('guzzle.client')->willReturn([
            'test_service_id_1' => 'test service #1',
            'test_service_id_2' => 'test service #2',
        ]);

        $testService1 = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $testService1->getArguments()->willReturn($hasHandler ? [['handler' => 'test handler']] : []);
        $testService2 = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $testService2->getArguments()->willReturn($hasHandler ? [['handler' => 'test handler #2']] : []);

        if ($hasLogger) {
            $testService1->setArguments(Argument::type('array'))->willReturn($testService1->reveal())->shouldBeCalled();
            $testService2->setArguments(Argument::type('array'))->willReturn($testService2->reveal())->shouldBeCalled();
        }

        $container->getDefinition('test_service_id_1')->willReturn($testService1->reveal());
        $container->getDefinition('test_service_id_2')->willReturn($testService2->reveal());

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
