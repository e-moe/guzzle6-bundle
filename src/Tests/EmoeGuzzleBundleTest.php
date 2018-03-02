<?php

namespace Emoe\GuzzleBundle\Tests;

use Emoe\GuzzleBundle\EmoeGuzzleBundle;
use Prophecy\Argument;

class EmoeGuzzleBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testBuild()
    {
        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->addCompilerPass(
            Argument::type('Emoe\GuzzleBundle\DependencyInjection\Compiler\ClientCompilerPass')
        );
        $container->addCompilerPass(
            Argument::type('Emoe\GuzzleBundle\DependencyInjection\Compiler\MonologCompilerPass')
        );

        $bundle = new EmoeGuzzleBundle();
        $bundle->build($container->reveal());
    }
}
