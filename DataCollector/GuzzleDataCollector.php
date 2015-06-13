<?php

namespace Emoe\GuzzleBundle\DataCollector;

use Emoe\GuzzleBundle\Log\ArrayLogAdapter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class GuzzleDataCollector extends DataCollector
{
    protected $logAdapter;

    public function __construct(ArrayLogAdapter $logAdapter)
    {
        $this->logAdapter = $logAdapter;
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
            //$datum['time'] = $this->getRequestTime($log['extras']['response']);
            $datum['request'] = $this->requestToString($log['extras']['request']);
            $datum['response'] = $this->responseToString($log['extras']['response']);
            $datum['is_error'] = $this->isError($log['extras']['response']);

            $this->data['requests'][$requestId] = $datum;
        }
    }

    /*
    private function getRequestTime(GuzzleResponse $response)
    {
        $time = $response->getInfo('total_time');

        if (null === $time) {
            $time = 0;
        }

        return (int) ($time * 1000);
    }
    */

    // todo: wrap request/response classes
    protected function requestToString(RequestInterface $request)
    {
        // todo: show full info (headers, body, ...)
        return (string) $request->getBody();
    }

    protected function responseToString(ResponseInterface $response)
    {
        // todo: show full info (headers, body, ...)
        return (string) $response->getBody();
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
