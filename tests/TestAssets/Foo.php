<?php

declare(strict_types=1);

namespace Zaphyr\RouteTests\TestAssets;

class Foo
{
    public function greet(): string
    {
        return 'hello from foo';
    }
}
