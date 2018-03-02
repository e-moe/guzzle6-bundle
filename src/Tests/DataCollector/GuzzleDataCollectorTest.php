<?php

namespace Emoe\GuzzleBundle\Tests\DataCollector;

use Emoe\GuzzleBundle\DataCollector\GuzzleDataCollector;
use Emoe\GuzzleBundle\Log\ArrayLogAdapter;
use GuzzleHttp\MessageFormatter;
use Symfony\Component\HttpFoundation\Response;

class GuzzleDataCollectorTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ArrayLogAdapter */
    protected $logAdapter;

    /** @var  MessageFormatter */
    protected $requestFormatter;

    /** @var  MessageFormatter */
    protected $responseFormatter;

    /** @var  GuzzleDataCollector */
    protected $dataCollector;

    public function setUp()
    {
        $this->logAdapter = $this->getMockBuilder('Emoe\GuzzleBundle\Log\ArrayLogAdapter')->getMock();
        $this->requestFormatter = $this->getMockBuilder('GuzzleHttp\MessageFormatter')->getMock();
        $this->responseFormatter = $this->getMockBuilder('GuzzleHttp\MessageFormatter')->getMock();

        $this->dataCollector = new GuzzleDataCollector(
            $this->logAdapter,
            $this->requestFormatter,
            $this->responseFormatter
        );
    }

    public function testName()
    {
        $this->assertEquals('guzzle', $this->dataCollector->getName());
    }

    public function testCollect()
    {
        $guzzleRequest = $this->getMockBuilder('GuzzleHttp\Psr7\Request')->disableOriginalConstructor()->getMock();
        $guzzleRequest->expects($this->once())
            ->method('getMethod')
            ->willReturn('GET');

        $guzzleResponse = $this->getMockBuilder('GuzzleHttp\Psr7\Response')->getMock();
        $guzzleResponse->expects($this->exactly(2))
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_OK);

        $this->logAdapter->expects($this->once())
            ->method('getLogs')
            ->willReturn([
                [
                    'message' => 'test message',
                    'extras' => [
                        'request' => $guzzleRequest,
                        'response' => $guzzleResponse,
                        'time' => 42,
                    ],
                ],
            ]);

        $this->requestFormatter->expects($this->once())
            ->method('format')
            ->willReturn('test request');

        $this->responseFormatter->expects($this->once())
            ->method('format')
            ->willReturn('test response');

        $response  = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $request   = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();

        $this->dataCollector->collect($request, $response);

        foreach ($this->dataCollector->getRequests() as $actual) {
            $this->assertSame(
                [
                    'message' => 'test message',
                    'time' => 42,
                    'request' => 'test request',
                    'response' => 'test response',
                    'is_error' => false,
                    'status_code' => Response::HTTP_OK,
                    'method' => 'GET',
                ],
                $actual
            );
        }
    }

    /**
     * @dataProvider totalDurationProvider
     */
    public function testGetTotalDuration(array $times, $expected)
    {
        $this->logAdapter->expects($this->once())
            ->method('getLogs')
            ->willReturn($this->getDurationLogs($times));

        $this->requestFormatter->expects($this->exactly(count($times)))->method('format');

        $this->responseFormatter->expects($this->exactly(count($times)))->method('format');

        $response  = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $request   = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();

        $this->dataCollector->collect($request, $response);

        $this->assertEquals($expected, $this->dataCollector->getTotalDuration());
    }

    /**
     * @dataProvider errorRequestsProvider
     */
    public function testGetErrorRequests(array $logs, $expected)
    {
        $this->logAdapter->expects($this->once())
            ->method('getLogs')
            ->willReturn($logs);

        $this->requestFormatter->expects($this->atMost(count($logs)))->method('format');
        $this->responseFormatter->expects($this->atMost(count($logs)))->method('format');

        $response  = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $request   = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();

        $this->dataCollector->collect($request, $response);

        $this->assertCount($expected, $this->dataCollector->getErrorRequests());
    }

    public function testReset()
    {
        $this->logAdapter->expects($this->once())
            ->method('getLogs')
            ->willReturn($this->getDurationLogs([42]));

        $this->requestFormatter->expects($this->once())->method('format');

        $this->responseFormatter->expects($this->once())->method('format');

        $response  = $this->getMockBuilder('Symfony\Component\HttpFoundation\Response')->getMock();
        $request   = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')->getMock();

        $this->dataCollector->collect($request, $response);

        $this->assertCount(1, $this->dataCollector->getRequests());
        $this->dataCollector->reset();
        $this->assertCount(0, $this->dataCollector->getRequests());
    }

    public function totalDurationProvider()
    {
        return [
            [ [0], 0, ],
            [ [0, 10, 20], 30, ],
            [ [5, 13, 7], 25, ],
        ];
    }

    protected function getDurationLogs(array $input)
    {
        $logs = [];
        foreach ($input as $time) {
            $logs[] = [
                'message' => 'test message',
                'extras' => [
                    'request' => $this->getMockBuilder('GuzzleHttp\Psr7\Request')->disableOriginalConstructor()
                        ->getMock(),
                    'response' => $this->getMockBuilder('GuzzleHttp\Psr7\Response')->getMock(),
                    'time' => $time,
                ],
            ];
        }
        return $logs;
    }

    public function errorRequestsProvider()
    {
        $guzzleRequestOne = $this->getMockBuilder('GuzzleHttp\Psr7\Request')->disableOriginalConstructor()->getMock();
        $guzzleRequestTwo = $this->getMockBuilder('GuzzleHttp\Psr7\Request')->disableOriginalConstructor()->getMock();
        $guzzleRequestThree = $this->getMockBuilder('GuzzleHttp\Psr7\Request')->disableOriginalConstructor()->getMock();

        $guzzleResponseOK = $this->getMockBuilder('GuzzleHttp\Psr7\Response')->getMock();
        $guzzleResponseOK->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_OK);

        $guzzleResponseErr = $this->getMockBuilder('GuzzleHttp\Psr7\Response')->getMock();
        $guzzleResponseErr->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(Response::HTTP_NOT_FOUND);

        return [
            [
                [
                    [
                        'message' => 'test message',
                        'extras' => [
                            'request' => $guzzleRequestOne,
                            'response' => $guzzleResponseOK,
                            'time' => 42,
                        ],
                    ],
                    [
                        'message' => 'test message',
                        'extras' => [
                            'request' => $guzzleRequestOne,
                            'response' => $guzzleResponseOK,
                            'time' => 42,
                        ],
                    ],
                    [
                        'message' => 'test message',
                        'extras' => [
                            'request' => $guzzleRequestTwo,
                            'response' => $guzzleResponseErr,
                            'time' => 42,
                        ],
                    ],
                    [
                        'message' => 'test message',
                        'extras' => [
                            'request' => $guzzleRequestThree,
                            'response' => $guzzleResponseErr,
                            'time' => 42,
                        ],
                    ],
                ],
                2
            ]
        ];
    }
}
