<?php

namespace Emoe\GuzzleBundle\DataCollector;

use Emoe\GuzzleBundle\Log\ArrayLogAdapter;
use GuzzleHttp\MessageFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class GuzzleDataCollector extends DataCollector
{
    /** @var ArrayLogAdapter */
    protected $logAdapter;

    /** @var MessageFormatter */
    protected $requestFormatter;

    /** @var MessageFormatter */
    protected $responseFormatter;

    public function __construct(
        ArrayLogAdapter $logAdapter,
        MessageFormatter $requestFormatter,
        MessageFormatter $responseFormatter
    ) {
        $this->logAdapter = $logAdapter;
        $this->requestFormatter = $requestFormatter;
        $this->responseFormatter = $responseFormatter;
        $this->data['requests'] = array();
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        foreach ($this->logAdapter->getLogs() as $log) {
            $requestId = spl_object_hash($log['extras']['request']);

            if (isset($this->data['requests'][$requestId])) {
                continue;
            }

            $datum['message'] = $log['message'];
            $datum['time'] = $this->formatTime($log['extras']['time']);
            $datum['request'] = $this->requestFormatter->format($log['extras']['request']);
            $datum['response'] = $this->responseFormatter->format($log['extras']['request'], $log['extras']['response']);
            $datum['is_error'] = $this->isError($log['extras']['response']);

            $this->data['requests'][$requestId] = $datum;
        }
    }

    protected function formatTime($time)
    {
        if ($time < 0) {
            $time = 0;
        }

        return (int) ($time * 1000);
    }

    protected function isError(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 400 && $response->getStatusCode() < 600;
    }

    public function getRequests()
    {
        return $this->data['requests'];
    }

    public function getName()
    {
        return 'guzzle';
    }
}
