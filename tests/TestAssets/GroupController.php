<?php

declare(strict_types=1);

namespace Zaphyr\RouteTests\TestAssets;

use Psr\Http\Message\ResponseInterface;
use Zaphyr\HttpMessage\Response;
use Zaphyr\Route\Attributes\Group;
use Zaphyr\Route\Attributes\Route;

#[Group('/group')]
class GroupController
{
    #[Route('/foo')]
    public function foo(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('group.foo');

        return $response;
    }

    #[Route('/bar')]
    public function bar(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('group.bar');

        return $response;
    }
}
