<?php

declare(strict_types=1);

namespace Zaphyr\RouteTests\TestAssets;

use Psr\Http\Message\ResponseInterface;
use Zaphyr\HttpMessage\Response;
use Zaphyr\Route\Attributes\Get;
use Zaphyr\Route\Attributes\Group;

#[Group('middleware', [new Middleware(2)])]
class MiddlewareController
{
    #[Get('index', 'middleware.index', [new Middleware(3)])]
    public function index(): ResponseInterface
    {
        return new Response();
    }
}
