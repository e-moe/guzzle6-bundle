<?php

namespace Emoe\GuzzleBundle\Tests\Middleware;

use Emoe\GuzzleBundle\Middleware\RequestProfilerMiddleware;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Symfony\Component\Stopwatch\Stopwatch;
use GuzzleHttp\Client;

class RequestProfilerMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Stopwatch */
    protected $stopwatch;

    /** @var  RequestProfilerMiddleware */
    protected $middleware;

    public function setUp()
    {
        $this->stopwatch = $this->getMockBuilder('Symfony\Component\Stopwatch\Stopwatch')->getMock();
        $this->middleware = new RequestProfilerMiddleware($this->stopwatch);
    }

    public function testRequest()
    {
        $this->stopwatch->expects($this->once())->method('start');
        $this->stopwatch->expects($this->once())->method('stop');

        $response = $this->getMockBuilder('GuzzleHttp\Psr7\Response')->getMock();
        $mock = new MockHandler([$response]);
        $stack = new HandlerStack($mock);
        $this->middleware->attachMiddleware($stack);


        $client = new Client(['handler' => $stack]);
        $client->get('http://example.com');
    }
}
