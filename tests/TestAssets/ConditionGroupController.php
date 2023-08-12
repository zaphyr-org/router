<?php

declare(strict_types=1);

namespace Zaphyr\RouterTests\TestAssets;

use Psr\Http\Message\ResponseInterface;
use Zaphyr\HttpMessage\Response;
use Zaphyr\Router\Attributes\Get;
use Zaphyr\Router\Attributes\Group;

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
