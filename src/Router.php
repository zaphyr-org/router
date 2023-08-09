<?php

declare(strict_types=1);

namespace Zaphyr\Route;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\Route\Attributes\Group;
use Zaphyr\Route\Attributes\Route;
use Zaphyr\Route\Contracts\DispatcherInterface;
use Zaphyr\Route\Contracts\RouteParserInterface;
use Zaphyr\Route\Contracts\RouterInterface;
use Zaphyr\Route\Exceptions\MethodNotAllowedException;
use Zaphyr\Route\Exceptions\MiddlewareException;
use Zaphyr\Route\Exceptions\NotFoundException;
use Zaphyr\Route\Exceptions\RouteException;
use Zaphyr\Route\Traits\MiddlewareAwareTrait;
use Zaphyr\Route\Traits\RouteCollectorTrait;
use Zaphyr\Route\Utils\AttributesResolver;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Router implements RouterInterface
{
    use RouteCollectorTrait;
    use MiddlewareAwareTrait;

    /**
     * @var Route[]
     */
    protected array $routes = [];

    /**
     * @var Group[]
     */
    protected array $groups = [];

    /**
     * @var bool
     */
    protected bool $isPrepared = false;

    /**
     * @var array<string, string>
     */
    protected static array $routePatterns = [
        '/{(.+?):numeric}/' => '{$1:\d+}',
        '/{(.+?):alpha}/' => '{$1:[a-zA-Z]+}',
        '/{(.+?):alphanum}/' => '{$1:[a-zA-Z0-9]+}',
        '/{(.+?):alphanum_dash}/' => '{$1:[a-zA-Z0-9-_]+}',
    ];

    /**
     * @param DispatcherInterface   $dispatcher
     * @param RouteParserInterface  $routeParser
     * @param class-string[]        $controllers
     * @param array<string, string> $routePatterns
     */
    public function __construct(
        protected DispatcherInterface $dispatcher = new Dispatcher(),
        protected RouteParserInterface $routeParser = new RouteParser(),
        array $controllers = [],
        array $routePatterns = []
    ) {
        $this->setControllerRoutes($controllers);
        $this->setRoutePatterns($routePatterns);
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerRoutes(array $controllers): void
    {
        foreach ($controllers as $controller) {
            AttributesResolver::appendRoutes($controller, $this->routes);
            AttributesResolver::appendGroups($controller, $this->groups, $this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setRoutePatterns(array $routePatterns): void
    {
        foreach ($routePatterns as $alias => $regex) {
            $pattern = '/{(.+?):' . $alias . '}/';
            $regex = '{$1:' . $regex . '}';

            self::$routePatterns[$pattern] = $regex;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(string $path, array $methods, array|callable|string $callable): Route
    {
        $route = (new Route($path, $methods))->setCallable($callable);

        $this->routes[] = $route;

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function group(string $path, callable $callable): Group
    {
        $group = (new Group($path))->setCallable($callable)->setRouter($this);

        $this->groups[] = $group;

        return $group;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RouteException
     * @throws MiddlewareException
     * @throws NotFoundException
     * @throws MethodNotAllowedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPrepared) {
            $this->prepareRoutes($request);
        }

        return $this->dispatcher->addMiddlewares($this->getMiddlewareStack())->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws RouteException
     * @return void
     */
    protected function prepareRoutes(ServerRequestInterface $request): void
    {
        foreach ($this->groups as $key => $group) {
            unset($this->groups[$key]);

            $group();
        }

        $uri = $request->getUri();

        foreach ($this->routes as $route) {
            if ($route->getScheme() !== $uri->getScheme()) {
                break;
            }

            if ($route->getHost() !== $uri->getHost()) {
                break;
            }

            if ($route->getPort() !== $uri->getPort()) {
                break;
            }

            $this->dispatcher->addRoute($route->setPath($this->prepareRoutePath($route->getPath())));
        }

        $this->isPrepared = true;
    }

    /**
     * @param string $path
     *
     * @throws RouteException
     * @return string
     */
    protected function prepareRoutePath(string $path): string
    {
        $pattern = array_keys(static::$routePatterns);
        $replacement = array_values(static::$routePatterns);
        $preparedPath = preg_replace($pattern, $replacement, $path);

        if ($preparedPath === null) {
            throw new RouteException('Could not prepare path "' . $path . '"');
        }

        return $preparedPath;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedRoute(string $name): Route
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }

        throw new RouteException('Could not find route with name "' . $name . '"');
    }

    /**
     * {@inheritdoc}
     */
    public function getPathFromName(string $name, array $params = []): string
    {
        $route = $this->getNamedRoute($name);
        $path = $this->prepareRoutePath($route->getPath());
        $routeData = array_reverse($this->routeParser->parse($path));

        $segments = [];
        $segmentName = '';

        foreach ($routeData as $data) {
            /** @var array<int, string|array<int, string>> $data */
            foreach ($data as $item) {
                if (is_string($item)) {
                    $segments[] = $item;
                    continue;
                }

                if (!array_key_exists($item[0], $params)) {
                    $segments = [];
                    $segmentName = $item[0];
                    break;
                }

                $segments[] = $params[$item[0]];
            }

            if (!empty($segments)) {
                break;
            }
        }

        if (empty($segments)) {
            throw new RouteException('Missing data for URL param "' . $segmentName . '"');
        }

        return implode('', $segments);
    }
}
