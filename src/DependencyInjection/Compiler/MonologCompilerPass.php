<?php

namespace Emoe\GuzzleBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MonologCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!($container->has('monolog.logger') && $container->getParameter('emoe_guzzle.log.enabled'))) {
            return;
        }

        $monologMiddleware = $container->findDefinition('emoe_guzzle.request_monolog_middleware');

        foreach (array_keys($container->findTaggedServiceIds('guzzle.client')) as $id) {
            $definition = $container->getDefinition($id);
            $arguments = $definition->getArguments();
            if (!isset($arguments[0]['handler'])) {
                continue;
            }
            $stack = $arguments[0]['handler'];
            $monologMiddleware->addMethodCall('attachMiddleware', [$stack]);
            $arguments[0]['monolog_middleware'] = $monologMiddleware;
            $definition->setArguments($arguments);
        }
    }
}
