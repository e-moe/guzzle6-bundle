<?php

namespace Emoe\GuzzleBundle\Tests;

use Emoe\GuzzleBundle\EmoeGuzzleBundle;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class EmoeGuzzleBundleTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testBuild()
    {
        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->addCompilerPass(
            Argument::type('Emoe\GuzzleBundle\DependencyInjection\Compiler\ClientCompilerPass')
        )->willReturn($container->reveal())->shouldBeCalled();
        $container->addCompilerPass(
            Argument::type('Emoe\GuzzleBundle\DependencyInjection\Compiler\MonologCompilerPass')
        )->willReturn($container->reveal())->shouldBeCalled();

        $bundle = new EmoeGuzzleBundle();
        $bundle->build($container->reveal());
    }
}
