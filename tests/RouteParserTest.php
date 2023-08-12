<?php

declare(strict_types=1);

namespace Zaphyr\RouterTests;

use PHPUnit\Framework\TestCase;
use Zaphyr\Router\RouteParser;

class RouteParserTest extends TestCase
{
    protected RouteParser $routeParser;

    public function setUp(): void
    {
        $this->routeParser = new RouteParser();
    }

    public function tearDown(): void
    {
        unset($this->routeParser);
    }

    /* -------------------------------------------------
     * PARSE
     * -------------------------------------------------
     */

    public function testParse(): void
    {
        $routeData = $this->routeParser->parse('/test');

        self::assertEquals([['/test']], $routeData);
    }
}
