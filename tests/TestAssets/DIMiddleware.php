<?php

declare(strict_types=1);

namespace Zaphyr\RouterTests\TestAssets;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zaphyr\HttpMessage\Response;

class DIMiddleware implements MiddlewareInterface
{
    public function __construct(protected Foo $foo)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write($this->foo->greet());

        return $response;
    }
}
