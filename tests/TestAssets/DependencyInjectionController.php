<?php

declare(strict_types=1);

namespace Zaphyr\RouterTests\TestAssets;

use Psr\Http\Message\ResponseInterface;
use Zaphyr\HttpMessage\Response;
use Zaphyr\Router\Attributes\Get;

class DependencyInjectionController
{
    public function __construct(protected Foo $foo)
    {
    }

    #[Get('/invoke')]
    public function __invoke(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('Hello World!');

        return $response;
    }

    #[Get('/container')]
    public function container(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write($this->foo->greet());

        return $response;
    }
}
