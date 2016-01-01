<?php

namespace Doctrine\Bundle\DoctrineBundle\Tests\DependencyInjection;

use Emoe\GuzzleBundle\DependencyInjection\EmoeGuzzleExtension;
use Prophecy\Argument;

class EmoeGuzzleExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testLoad()
    {
        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerBuilder');

        $extension = new EmoeGuzzleExtension();
        $extension->load([], $container->reveal());

        $container->setParameter('emoe_guzzle.log.enabled', Argument::type('bool'))->shouldHaveBeenCalled();
        $container->setParameter('emoe_guzzle.log.format', Argument::type('string'))->shouldHaveBeenCalled();
    }
}
