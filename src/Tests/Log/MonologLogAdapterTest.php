<?php

namespace Emoe\GuzzleBundle\Tests\Log;

use Emoe\GuzzleBundle\Log\MonologLogAdapter;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class MonologLogAdapterTest extends TestCase
{
    /** @var  MonologLogAdapter */
    protected $logAdapter;

    protected $logger;

    public function setUp(): void
    {
        $this->logger = $this->getMockBuilder('Monolog\Logger')->disableOriginalConstructor()->getMock();
        $this->logAdapter = new MonologLogAdapter($this->logger);
    }

    public function testGetLogObject()
    {
        $this->assertSame($this->logger, $this->logAdapter->getLogObject());
    }

    /**
     * @dataProvider logsProvider
     */
    public function testLogs(array $input)
    {
        $this->logger->expects($this->exactly(count($input)))->method('addRecord');
        foreach ($input as $data) {
            call_user_func_array([$this->logAdapter, 'log'], $data);
        }
    }

    /**
     * @dataProvider mapProvider
     */
    public function testMapping($input, $expected)
    {
        $this->logger->expects($this->once())
            ->method('addRecord')
            ->with(
                $expected,
                'test'
            );
        $this->logAdapter->log('test', $input);
    }

    public function logsProvider()
    {
        return [
            [
                [],
            ],
            [
                [
                    ['test'],
                ],
            ],
            [
                [
                    ['test 1', LOG_DEBUG],
                    ['test 2', LOG_ERR, ['extra' => 'data']],
                ],
            ],
        ];
    }

    public function mapProvider()
    {
        return [
            [LOG_DEBUG, Logger::DEBUG],
            [LOG_INFO, Logger::INFO],
            [LOG_WARNING, Logger::WARNING],
            [LOG_ERR, Logger::ERROR],
            [LOG_CRIT, Logger::CRITICAL],
            [LOG_ALERT, Logger::ALERT],
        ];
    }
}
