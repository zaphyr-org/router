<?php

declare(strict_types=1);

namespace Zaphyr\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zaphyr\Router\Attributes\Group;
use Zaphyr\Router\Attributes\Route;
use Zaphyr\Router\Contracts\Attributes\GroupInterface;
use Zaphyr\Router\Contracts\Attributes\RouteInterface;
use Zaphyr\Router\Contracts\DispatcherInterface;
use Zaphyr\Router\Contracts\RouteParserInterface;
use Zaphyr\Router\Contracts\RouterInterface;
use Zaphyr\Router\Exceptions\MethodNotAllowedException;
use Zaphyr\Router\Exceptions\MiddlewareException;
use Zaphyr\Router\Exceptions\NotFoundException;
use Zaphyr\Router\Exceptions\RouteException;
use Zaphyr\Router\Traits\ContainerAwareTrait;
use Zaphyr\Router\Traits\MiddlewareAwareTrait;
use Zaphyr\Router\Traits\RouteCollectorTrait;
use Zaphyr\Router\Utils\AttributesResolver;

/**
 * @author merloxx <merloxx@zaphyr.org>
 */
class Router implements RouterInterface
{
    use RouteCollectorTrait;
    use MiddlewareAwareTrait;
    use ContainerAwareTrait;

    /**
     * @var RouteInterface[]
     */
    protected array $routes = [];

    /**
     * @var GroupInterface[]
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
    public function add(string $path, array $methods, array|callable|string $callable): RouteInterface
    {
        $route = (new Route($path, $methods))->setCallable($callable);

        $this->routes[] = $route;

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function group(string $path, callable $callable): GroupInterface
    {
        $group = (new Group($path))->setCallable($callable)->setRouter($this);

        $this->groups[] = $group;

        return $group;
    }

    /**
     * {@inheritdoc}
     *
     * @throws RouteException if the path could not be prepared or the container could not be set
     * @throws MiddlewareException if the middleware could not be resolved
     * @throws NotFoundException if the route could not be found
     * @throws MethodNotAllowedException if the HTTP method is not allowed for the requested route
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->isPrepared) {
            $this->prepareRoutes($request);
        }

        return $this->dispatcher->setMiddleware($this->getMiddlewareStack())->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @throws RouteException if the path could not be prepared or the container could not be set
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
            if ($this->getContainer() !== null) {
                $route->setContainer($this->getContainer());
                $this->dispatcher->setContainer($this->getContainer());
            }

            $this->dispatcher->addRoute($route->setPath($this->prepareRoutePath($route->getPath())));
        }

        $this->isPrepared = true;
    }

    /**
     * @param string $path
     *
     * @throws RouteException if the path could not be prepared
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
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamedRoute(string $name): RouteInterface
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
