<?php

declare(strict_types=1);

namespace Zaphyr\RouteTests;

use PHPUnit\Framework\TestCase;
use Zaphyr\Route\RouteParser;

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
