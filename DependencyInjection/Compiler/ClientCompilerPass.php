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
        $middleware = $container->findDefinition('guzzle.profiler_request_middleware');
        $middleware->addMethodCall('attachMiddleware', [$stack]);

        foreach ($container->findTaggedServiceIds('guzzle.client') as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $arguments = $definition->getArguments();
            if (isset($arguments[0]['handler'])) {
                $stack = $arguments[0]['handler'];
                $middleware->addMethodCall('attachMiddleware', [$stack]);
            }
            $arguments[0]['handler'] = $stack;
            $arguments[0]['middleware'] = $middleware;
            $definition->setArguments($arguments);
        }
    }
}
