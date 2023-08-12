<?php

declare(strict_types=1);

namespace Zaphyr\RouterTests\TestAssets;

use Psr\Http\Message\ResponseInterface;
use Zaphyr\HttpMessage\Response;
use Zaphyr\Router\Attributes\Get;
use Zaphyr\Router\Attributes\Group;

#[Group('middleware', [new Middleware(2)])]
class MiddlewareController
{
    #[Get('index', 'middleware.index', [new Middleware(3)])]
    public function index(): ResponseInterface
    {
        return new Response();
    }
}
