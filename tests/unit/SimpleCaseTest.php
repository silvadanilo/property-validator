<?php

declare(strict_types=1);

namespace PropertyValidator;

use PHPUnit\Framework\TestCase;

class SimpleCaseTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testHappyPath()
    {
        SimpleCase::create([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'nationality' => 'Italian',
            'age' => 42,
        ]);
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testNoExceptionOnNonMandatoryMissingField()
    {
        SimpleCase::create([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'age' => 42,
        ]);
    }

    public function testExceptionOnMissingMandatoryField()
    {
        $this->expectException(InvalidDataException::class);

        SimpleCase::create([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'nationality' => 'Italian',
        ]);
    }

    public function testExceptionOnWrongType()
    {
        $this->expectException(InvalidDataException::class);

        SimpleCase::create([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'age' => '42',
        ]);
    }

    public function testExceptionMessage()
    {
        $this->markTestIncomplete();
    }
}

class SimpleCase
{
    use PropertyValidator;

    private string $name;

    private string $surname;

    private ?string $nationality;

    private int $age;
}
