<?php

declare(strict_types=1);

namespace Zaphyr\RouteTests\TestAssets;

use Psr\Http\Message\ResponseInterface;
use Zaphyr\HttpMessage\Response;
use Zaphyr\Route\Attributes\Route;

class ConditionController
{
    #[Route('/condition', ['GET'], scheme: 'https', host: 'example.com', port: 443)]
    public function __invoke(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('condition');

        return $response;
    }
}
