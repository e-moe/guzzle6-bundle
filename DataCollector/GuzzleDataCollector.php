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

            /** @var RequestInterface $guzzleRequest */
            $guzzleRequest = $log['extras']['request'];

            /** @var ResponseInterface $guzzleResponse */
            $guzzleResponse = $log['extras']['response'];

            $datum['message'] = $log['message'];
            $datum['time'] = $log['extras']['time'];
            $datum['request'] = $this->requestFormatter->format($guzzleRequest);
            $datum['response'] = $this->responseFormatter->format($guzzleRequest, $guzzleResponse);
            $datum['is_error'] = $this->isError($log['extras']['response']);

            $this->data['requests'][$requestId] = $datum;
        }
    }

    protected function isError(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 400 && $response->getStatusCode() < 600;
    }

    public function getRequests()
    {
        return $this->data['requests'];
    }

    public function getTotalDuration()
    {
        return array_reduce(
            $this->getRequests(),
            function ($carry, array $request) {
                return $carry + $request['time'];
            },
            0
        );
    }

    public function getName()
    {
        return 'guzzle';
    }
}
