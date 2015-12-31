<?php

namespace Emoe\GuzzleBundle\Middleware;

use GuzzleHttp\HandlerStack;
use Symfony\Component\Stopwatch\Stopwatch;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RequestProfilerMiddleware implements MiddlewareInterface
{
    const NAME = 'Guzzle Profiler';

    /**
     * @var Stopwatch|null
     */
    protected $stopwatch;

    /**
     * Cache of request hashes against their open order
     *
     * @var array
     */
    protected $requests = array();

    /**
     * Constructor.
     *
     * @param Stopwatch|null $stopwatch
     */
    public function __construct(Stopwatch $stopwatch = null)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.StaticAccess)
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
                        $this->onRequestComplete($request);
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
        if (null !== $this->stopwatch) {
            $this->start($request);
        }
    }

    /**
     * Stops the stopwatch.
     *
     * @param RequestInterface $request
     */
    private function onRequestComplete(RequestInterface $request)
    {
        if (null !== $this->stopwatch) {
            $this->stop($request);
        }
    }

    /**
     * @param RequestInterface $request
     */
    private function start(RequestInterface $request)
    {
        $this->requests[$this->hash($request)] = count($this->requests) + 1;
        $name = $this->getEventName($request);

        $this->stopwatch->start($name, self::NAME);
    }

    /**
     * @param RequestInterface $request
     */
    private function stop(RequestInterface $request)
    {
        $name = $this->getEventName($request);

        $this->stopwatch->stop($name);
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
     * @param RequestInterface $request
     *
     * @return string
     */
    private function getEventName(RequestInterface $request)
    {
        return sprintf(
            '%s: [%d] %s %s',
            self::NAME,
            $this->requests[$this->hash($request)],
            $request->getMethod(),
            urldecode((string)$request->getUri())
        );
    }
}
