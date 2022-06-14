<?php

namespace Emoe\GuzzleBundle\Tests\DependencyInjection;

use Emoe\GuzzleBundle\DependencyInjection\EmoeGuzzleExtension;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\DependencyInjection\Alias;

class EmoeGuzzleExtensionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testLoad()
    {
        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');
        $container->setParameter('emoe_guzzle.request.format', Argument::type('string'))->shouldBeCalled();
        $container->setParameter('emoe_guzzle.response.format', Argument::type('string'))->shouldBeCalled();
        $container->removeBindings(Argument::type('string'))->shouldBeCalled();
        $container->fileExists(Argument::type('string'))->willReturn(true);
        $container->setDefinition(Argument::type('string'), Argument::type('\Symfony\Component\DependencyInjection\Definition'))->will(function ($args) use ($container) {
            return $args[1];
        });
        $container->setAlias(Argument::type('string'), Argument::type('string'))->willReturn(new Alias('bla'));
        $container->setAlias(Argument::type('string'), Argument::type('\Symfony\Component\DependencyInjection\Alias'))->will(function ($args) {
            return $args[1];
        });

        $extension = new EmoeGuzzleExtension();
        $extension->load([], $container->reveal());

        $container->setParameter('emoe_guzzle.log.enabled', Argument::type('bool'))->shouldHaveBeenCalled();
        $container->setParameter('emoe_guzzle.log.format', Argument::type('string'))->shouldHaveBeenCalled();
    }
}
