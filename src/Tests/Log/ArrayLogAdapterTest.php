<?php

namespace Emoe\GuzzleBundle\Tests\Log;

use Emoe\GuzzleBundle\Log\ArrayLogAdapter;
use PHPUnit\Framework\TestCase;

class ArrayLogAdapterTest extends TestCase
{
    /** @var  ArrayLogAdapter */
    protected $logAdapter;

    public function setUp(): void
    {
        $this->logAdapter = new ArrayLogAdapter();
    }

    /**
     * @dataProvider logsProvider
     */
    public function testLogs(array $input, array $expected)
    {
        foreach ($input as $data) {
            call_user_func_array([$this->logAdapter, 'log'], $data);
        }
        $this->assertSame($expected, $this->logAdapter->getLogs());
    }

    /**
     * @dataProvider logsProvider
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testClearLogs(array $input, array $expected)
    {
        $this->assertCount(0, $this->logAdapter->getLogs());
        foreach ($input as $data) {
            call_user_func_array([$this->logAdapter, 'log'], $data);
        }
        $this->assertCount(count($input), $this->logAdapter->getLogs());
        $this->logAdapter->clearLogs();
        $this->assertCount(0, $this->logAdapter->getLogs());
    }

    public function logsProvider()
    {
        return [
            [
                [],
                [],
            ],
            [
                [
                    ['test'],
                ],
                [
                    [
                        'message' => 'test',
                        'priority' => LOG_INFO,
                        'extras' => [],
                    ],
                ],
            ],
            [
                [
                    ['test 1', LOG_DEBUG],
                    ['test 2', LOG_ERR, ['extra' => 'data']],
                ],
                [
                    [
                        'message' => 'test 1',
                        'priority' => LOG_DEBUG,
                        'extras' => [],
                    ],
                    [
                        'message' => 'test 2',
                        'priority' => LOG_ERR,
                        'extras' => ['extra' => 'data'],
                    ],
                ],
            ],
        ];
    }
}
