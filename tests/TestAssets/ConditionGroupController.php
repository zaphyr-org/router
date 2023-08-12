<?php

declare(strict_types=1);

namespace Zaphyr\RouteTests\TestAssets;

use Psr\Http\Message\ResponseInterface;
use Zaphyr\HttpMessage\Response;
use Zaphyr\Route\Attributes\Get;
use Zaphyr\Route\Attributes\Group;

#[Group('/condition-group', scheme: 'https', host: 'example.com', port: 8080)]
class ConditionGroupController
{
    #[Get('/index')]
    public function index(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('condition-group.index');

        return $response;
    }
}
