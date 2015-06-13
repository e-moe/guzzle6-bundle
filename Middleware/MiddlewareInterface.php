<?php

namespace Emoe\GuzzleBundle\Middleware;

use GuzzleHttp\HandlerStack;

interface MiddlewareInterface
{
    /**
     * Attaches middleware functions to handle request lifecycle
     *
     * @param HandlerStack $stack
     *
     * @return HandlerStack
     */
    public function attachMiddleware(HandlerStack $stack);
}