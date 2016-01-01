<?php

namespace Doctrine\Bundle\DoctrineBundle\Tests\DependencyInjection\Compiler;

use Emoe\GuzzleBundle\DependencyInjection\Compiler\ClientCompilerPass;
use Prophecy\Argument;

class ClientCompilerClassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testProcess()
    {
        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->findDefinition('emoe_guzzle.handler_stack')->shouldBeCalled();

        $profilerMiddleware = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $profilerMiddleware->addMethodCall('attachMiddleware', Argument::type('array'));

        $container->findDefinition('emoe_guzzle.request_profiler_middleware')->willReturn(
            $profilerMiddleware->reveal()
        );

        $loggerMiddleware = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $loggerMiddleware->addMethodCall('attachMiddleware', Argument::type('array'));

        $container->findDefinition('emoe_guzzle.request_logger_middleware')->willReturn(
            $loggerMiddleware->reveal()
        );

        $container->findTaggedServiceIds('guzzle.client')->willReturn([
            'test_service_id' => 'test service'
        ]);

        $testService = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $testService->getArguments()->willReturn([
            ['handler' => 'test handler']
        ]);
        $testService->setArguments(Argument::type('array'))->shouldBeCalled();

        $container->getDefinition('test_service_id')->willReturn($testService->reveal());

        $compilerPass = new ClientCompilerPass();
        $compilerPass->process($container->reveal());
    }
}
