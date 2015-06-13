<?php

namespace Emoe\GuzzleBundle\Middleware;

use Emoe\GuzzleBundle\Log\LogAdapterInterface;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RequestLoggerMiddleware implements MiddlewareInterface
{
    /** @var LogAdapterInterface Adapter responsible for writing log data */
    protected $logAdapter;

    /** @var MessageFormatter Formatter used to format messages before logging */
    protected $formatter;

    /**
     * Requests timing
     *
     * @var array
     */
    protected $requests = array();

    public function __construct(LogAdapterInterface $logAdapter, $formatter = null)
    {
        $this->logAdapter = $logAdapter;
        $this->formatter = $formatter instanceof MessageFormatter ? $formatter : new MessageFormatter($formatter);
    }

    /**
     * @inheritdoc
     */
    public function attachMiddleware(HandlerStack $stack)
    {
        $stack->push(Middleware::mapRequest(function (RequestInterface $request) {
            $this->onRequestBeforeSend($request);
            return $request;
        }));

        $stack->push(function (callable $handler) {
            return function (
                RequestInterface $request,
                array $options
            ) use ($handler) {
                $promise = $handler($request, $options);
                return $promise->then(
                    function (ResponseInterface $response) use ($request) {
                        $this->onRequestComplete($request, $response);
                        return $response;
                    }
                );
            };
        });

        return $stack;
    }

    /**
     * Starts the stopwatch.
     *
     * @param RequestInterface $request
     */
    private function onRequestBeforeSend(RequestInterface $request)
    {
        $hash = $this->hash($request);
        $this->requests[$hash] = -microtime(true);
    }

    /**
     * Stops the stopwatch.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     */
    private function onRequestComplete(RequestInterface $request, ResponseInterface $response)
    {
        $hash = $this->hash($request);
        // Send the log message to the adapter, adding a category and host
        $priority = $response && $this->isError($response) ? LOG_ERR : LOG_DEBUG;
        $message = $this->formatter->format($request, $response);
        $this->requests[$hash] += microtime(true);
        $this->logAdapter->log($message, $priority, array(
            'request'  => $request,
            'response' => $response,
            'time'     => $this->requests[$hash],
        ));
    }

    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    private function hash(RequestInterface $request)
    {
        return spl_object_hash($request);
    }

    /**
     * Checks if HTTP Status code is a Client Error (4xx)
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public function isClientError(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 400 && $response->getStatusCode() < 500;
    }

    /**
     * Checks if HTTP Status code is Server OR Client Error (4xx or 5xx)
     *
     * @param ResponseInterface $response
     *
     * @return boolean
     */
    public function isError(ResponseInterface $response)
    {
        return $this->isClientError($response) || $this->isServerError($response);
    }

    /**
     * Checks if HTTP Status code is Server Error (5xx)
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    public function isServerError(ResponseInterface $response)
    {
        return $response->getStatusCode() >= 500 && $response->getStatusCode() < 600;
    }
}