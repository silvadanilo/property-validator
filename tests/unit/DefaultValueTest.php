<?php

declare(strict_types=1);

namespace PropertyValidator;

use PHPUnit\Framework\TestCase;

class DefaultValueTest extends TestCase
{
    public function testHappyPath()
    {
        $obj = DefaultValues::create([]);

        $this->assertEquals('Mario', $obj->name());
        $this->assertEquals('Rossi', $obj->surname());
        $this->assertEquals(42, $obj->age());
    }
}

class DefaultValues
{
    use PropertyValidator;

    private string $name = 'Mario';

    private string $surname = 'Rossi';

    private int $age = 42;
}
