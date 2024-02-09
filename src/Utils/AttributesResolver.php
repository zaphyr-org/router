<?php

declare(strict_types=1);

namespace Zaphyr\Router\Utils;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use Zaphyr\Router\Attributes\Group;
use Zaphyr\Router\Attributes\Route;
use Zaphyr\Router\Contracts\Attributes\GroupInterface;
use Zaphyr\Router\Contracts\Attributes\RouteInterface;
use Zaphyr\Router\Contracts\RouterInterface;
use Zaphyr\Router\Exceptions\RouteException;

/**
 * @author   merloxx <merloxx@zaphyr.org>
 * @internal This class is not part of the public API of this package and may change at any time without notice
 */
class AttributesResolver
{
    /**
     * @param class-string     $controller
     * @param RouteInterface[] $routes
     *
     * @throws RouteException if the controller does not exist
     * @return void
     */
    public static function appendRoutes(string $controller, array &$routes): void
    {
        $reflection = self::getReflectionClass($controller);

        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                if ($reflection->getAttributes(Group::class, ReflectionAttribute::IS_INSTANCEOF)) {
                    continue;
                }

                $route = $attribute->newInstance();
                $route->setCallable([$controller, $method->getName()]);

                $routes[] = $route;
            }
        }
    }

    /**
     * @param class-string     $controller
     * @param GroupInterface[] $groups
     * @param RouterInterface  $router
     *
     * @throws RouteException if the controller does not exist
     * @return void
     */
    public static function appendGroups(string $controller, array &$groups, RouterInterface $router): void
    {
        $reflection = self::getReflectionClass($controller);
        $attributeGroups = $reflection->getAttributes(Group::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributeGroups as $attribute) {
            $group = $attribute->newInstance();

            $group->setCallable(
                static function (GroupInterface $group) use ($reflection, $controller) {
                    foreach ($reflection->getMethods() as $method) {
                        $routes = $method->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF);

                        foreach ($routes as $attribute) {
                            $route = $attribute->newInstance();
                            $route->setGroup($group);

                            $groupRoute = $group
                                ->add($route->getPath(), $route->getMethods(), [$controller, $method->getName()])
                                ->setName($route->getName())
                                ->setMiddleware($route->getMiddlewareStack());

                            if ($route->getScheme() !== null) {
                                $groupRoute->setScheme($route->getScheme());
                            }

                            if ($route->getHost() !== null) {
                                $groupRoute->setHost($route->getHost());
                            }

                            if ($route->getPort() !== null) {
                                $groupRoute->setPort($route->getPort());
                            }
                        }
                    }
                }
            )->setRouter($router);

            $groups[] = $group;
        }
    }

    /**
     * @param string $controller
     *
     * @throws RouteException if the controller does not exist
     * @return ReflectionClass
     */
    protected static function getReflectionClass(string $controller): ReflectionClass
    {
        try {
            return new ReflectionClass($controller);
        } catch (ReflectionException $exception) {
            throw new RouteException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
