<?php

namespace Emoe\GuzzleBundle\Tests\Middleware;

use Emoe\GuzzleBundle\Log\ArrayLogAdapter;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;
use Emoe\GuzzleBundle\Middleware\RequestLoggerMiddleware;
use GuzzleHttp\Client;

class RequestLoggerMiddlewareTest extends TestCase
{
    /** @var  ArrayLogAdapter */
    protected $logAdapter;

    /** @var  Stopwatch */
    protected $stopwatch;

    /** @var  RequestLoggerMiddleware */
    protected $middleware;

    public function setUp(): void
    {
        $this->logAdapter = $this->getMockBuilder('Emoe\GuzzleBundle\Log\ArrayLogAdapter')->getMock();
        $this->stopwatch = $this->getMockBuilder('Symfony\Component\Stopwatch\Stopwatch')->getMock();

        $this->middleware = new RequestLoggerMiddleware($this->logAdapter, $this->stopwatch);
    }

    public function testRequest()
    {
        $event = $this->getMockBuilder('Symfony\Component\Stopwatch\StopwatchEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getDuration')
            ->willReturn(42);

        $this->stopwatch->expects($this->once())
            ->method('stop')
            ->willReturn($event);

        $response = $this->getMockBuilder('GuzzleHttp\Psr7\Response')->getMock();
        $mock = new MockHandler([$response]);
        $stack = new HandlerStack($mock);
        $this->middleware->attachMiddleware($stack);


        $client = new Client(['handler' => $stack]);
        $client->get('http://example.com');
    }

    /**
     * @dataProvider clientErrorProvider
     */
    public function testIsClientError($code, $expected)
    {
        $response = $this->getMockBuilder('GuzzleHttp\Psr7\Response')->getMock();
        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn($code);

        $this->assertSame($expected, $this->middleware->isClientError($response));
    }

    /**
     * @dataProvider serverErrorProvider
     */
    public function testIsServerError($code, $expected)
    {
        $response = $this->getMockBuilder('GuzzleHttp\Psr7\Response')->getMock();
        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn($code);

        $this->assertSame($expected, $this->middleware->isServerError($response));
    }

    /**
     * @dataProvider errorProvider
     */
    public function testIsError($code, $expected)
    {
        $response = $this->getMockBuilder('GuzzleHttp\Psr7\Response')->getMock();
        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn($code);

        $this->assertSame($expected, $this->middleware->isError($response));
    }

    public function clientErrorProvider()
    {
        return [
            [200, false],
            [301, false],
            [400, true],
            [404, true],
            [500, false],
        ];
    }

    public function serverErrorProvider()
    {
        return [
            [200, false],
            [301, false],
            [400, false],
            [404, false],
            [500, true],
        ];
    }

    public function errorProvider()
    {
        return [
            [200, false],
            [301, false],
            [400, true],
            [404, true],
            [500, true],
        ];
    }
}
