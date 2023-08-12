<?php

declare(strict_types=1);

namespace Zaphyr\RouterTests\TestAssets;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\HttpMessage\Response;
use Zaphyr\Router\Attributes\Any;
use Zaphyr\Router\Attributes\Delete;
use Zaphyr\Router\Attributes\Get;
use Zaphyr\Router\Attributes\Head;
use Zaphyr\Router\Attributes\Options;
use Zaphyr\Router\Attributes\Patch;
use Zaphyr\Router\Attributes\Post;
use Zaphyr\Router\Attributes\Put;
use Zaphyr\Router\Attributes\Route;

class Controller
{
    public function __invoke(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('callable');

        return $response;
    }

    #[Route('/index')]
    public function index(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('index');

        return $response;
    }

    #[Route('/name', name: 'controller.name')]
    public function name(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('name');

        return $response;
    }

    #[Any('/any')]
    public function any(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('any');

        return $response;
    }

    #[Get('/get')]
    public function get(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('get');

        return $response;
    }

    #[Post('/post')]
    public function post(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('post');

        return $response;
    }

    #[Put('/put')]
    public function put(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('put');

        return $response;
    }

    #[Patch('/patch')]
    public function patch(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('patch');

        return $response;
    }

    #[Delete('/delete')]
    public function delete(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('delete');

        return $response;
    }

    #[Head('/head')]
    public function head(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('head');

        return $response;
    }

    #[Options('/options')]
    public function options(): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write('options');

        return $response;
    }

    #[Route('/wildcard-route/{id}', methods: ['GET'])]
    public function wildcard(ServerRequestInterface $request, array $params): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode($params));

        return $response;
    }
}
