<?php

namespace Emoe\GuzzleBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Add middleware to Guzzle clients created as services.
 */
class ClientCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $stack = $container->findDefinition('guzzle.handler_stack');
        $profilerMiddleware = $container->findDefinition('guzzle.request_profiler_middleware');
        $profilerMiddleware->addMethodCall('attachMiddleware', [$stack]);
        $loggerMiddleware = $container->findDefinition('guzzle.request_logger_middleware');
        $loggerMiddleware->addMethodCall('attachMiddleware', [$stack]);

        foreach ($container->findTaggedServiceIds('guzzle.client') as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $arguments = $definition->getArguments();
            if (isset($arguments[0]['handler'])) {
                $stack = $arguments[0]['handler'];
                $profilerMiddleware->addMethodCall('attachMiddleware', [$stack]);
                $loggerMiddleware->addMethodCall('attachMiddleware', [$stack]);
            }
            $arguments[0]['handler'] = $stack;
            $arguments[0]['profiler_middleware'] = $profilerMiddleware;
            $arguments[0]['logger_middleware'] = $loggerMiddleware;
            $definition->setArguments($arguments);
        }
    }
}
