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

    /**
     * GuzzleDataCollector constructor.
     *
     * @param ArrayLogAdapter $logAdapter
     * @param MessageFormatter $requestFormatter
     * @param MessageFormatter $responseFormatter
     */
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

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Throwable $exception = null)
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

            $datum = array(
                'message' => $log['message'],
                'time' => $log['extras']['time'],
                'request' => $this->requestFormatter->format($guzzleRequest),
                'response' => $this->responseFormatter->format($guzzleRequest, $guzzleResponse),
                'is_error' => $this->isError($guzzleResponse),
                'status_code' => $guzzleResponse->getStatusCode(),
                'method' => $guzzleRequest->getMethod(),
            );

            $this->data['requests'][$requestId] = $datum;
        }
    }

    /**
     * @param ResponseInterface $response
     * @return bool Returns true if response code is 4xx or 5xx
     */
    protected function isError(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 400 && $response->getStatusCode() < 600;
    }

    /**
     * @return array List of requests with 4xx or 5xx response codes
     */
    public function getErrorRequests()
    {
        return array_filter(
            $this->getRequests(),
            function ($item) {
                return $item['is_error'];
            }
        );
    }

    /**
     * @return array List of all requests
     */
    public function getRequests()
    {
        return $this->data['requests'];
    }

    /**
     * @return int Total requests duration
     */
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'guzzle';
    }

    /**
     * Resets this data collector to its initial state.
     */
    public function reset()
    {
        $this->data['requests'] = array();
    }
}
