<?php

declare(strict_types=1);

namespace Zaphyr\RouterTests\TestAssets;

use Psr\Http\Message\ResponseInterface;
use Zaphyr\HttpMessage\Response;
use Zaphyr\Router\Attributes\Group;
use Zaphyr\Router\Attributes\Route;

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
