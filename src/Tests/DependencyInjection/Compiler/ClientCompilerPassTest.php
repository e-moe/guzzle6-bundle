<?php

namespace Emoe\GuzzleBundle\Tests\DependencyInjection\Compiler;

use Emoe\GuzzleBundle\DependencyInjection\Compiler\ClientCompilerPass;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class ClientCompilerClassTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testProcess()
    {
        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->findDefinition('emoe_guzzle.handler_stack')->shouldBeCalled();

        $profilerMiddleware = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $profilerMiddleware->addMethodCall('attachMiddleware', Argument::type('array'))->willReturn($profilerMiddleware->reveal());

        $container->findDefinition('emoe_guzzle.request_profiler_middleware')->willReturn(
            $profilerMiddleware->reveal()
        );

        $loggerMiddleware = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $loggerMiddleware->addMethodCall('attachMiddleware', Argument::type('array'))->willReturn($loggerMiddleware->reveal());

        $container->findDefinition('emoe_guzzle.request_logger_middleware')->willReturn(
            $loggerMiddleware->reveal()
        );

        $container->findTaggedServiceIds('guzzle.client')->willReturn([
            'test_service_id_1' => 'Service with no arguments',
            'test_service_id_2' => 'Service with implicit parameter argument',
            'test_service_id_3' => 'Service with an explicit list of arguments',
            'test_service_id_4' => 'Service created by factory with handler'
        ]);

        /*
         * guzzle.client:
         *     class: GuzzleHttp\Client
         *     tags:
         *         - { name: guzzle.client }
         */
        $testService1 = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $testService1->getArguments()->willReturn([
        ]);
        $container->getDefinition('test_service_id_1')->willReturn($testService1->reveal());
        $testService1->setArguments(Argument::type('array'))->willReturn($testService1->reveal())->shouldBeCalled();

        /*
         * guzzle.client:
         *     class: GuzzleHttp\Client
         *     arguments:
         *         - "%my.param.for.this.service%"
         *     tags:
         *         - { name: guzzle.client }
         */
        $testService2 = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $testService2->getArguments()->willReturn([
            '%my.param.for.this.service%'
        ]);
        $container->getParameter('my.param.for.this.service')->willReturn([
            ['connect_timeout' => 42]
        ]);
        $container->getDefinition('test_service_id_2')->willReturn($testService2->reveal());
        $testService2->setArguments(Argument::type('array'))->willReturn($testService2->reveal())->shouldBeCalled();

        /*
         * guzzle.client:
         *     class: GuzzleHttp\Client
         *     arguments:
         *         -
         *             base_uri: "http://test.com/test"
         *             connect_timeout: 1
         *             timeout: 3
         *     tags:
         *         - { name: guzzle.client }
         */
        $testService3 = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $testService3->getArguments()->willReturn([
            ['base_uri' => 'http://test.com/test', 'connect_timeout' => 1, 'timeout' => 3]
        ]);
        $container->getDefinition('test_service_id_3')->willReturn($testService3->reveal());
        $testService3->setArguments(Argument::type('array'))->willReturn($testService3->reveal())->shouldBeCalled();

        /*
         * Similar to test_service_id_3, but with "handler",
         * could be result of factory usage
         */
        $testService4 = $this->prophesize('Symfony\Component\DependencyInjection\Definition');
        $testService4->getArguments()->willReturn([
            ['base_uri' => 'http://test.com/test', 'handler' => 'some']
        ]);
        $container->getDefinition('test_service_id_4')->willReturn($testService4->reveal());
        $testService4->setArguments(Argument::type('array'))->willReturn($testService4->reveal())->shouldBeCalled();

        $compilerPass = new ClientCompilerPass();
        $compilerPass->process($container->reveal());
    }
}
