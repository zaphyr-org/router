<?php

declare(strict_types=1);

namespace Zaphyr\RouterTests\TestAssets;

use Psr\Http\Message\ResponseInterface;
use Zaphyr\HttpMessage\Response;
use Zaphyr\Router\Attributes\Get;
use Zaphyr\Router\Attributes\Route;

class ConditionController
{
    #[Route('/condition', ['GET'], scheme: 'https', host: 'example.com', port: 443)]
    public function __invoke(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('condition');

        return $response;
    }

    #[Get('/port/1', port: 80)]
    public function portOne(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('port 1');

        return $response;
    }

    #[Get('/port/2', port: 443)]
    public function portTwo(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('port 2');

        return $response;
    }
}
