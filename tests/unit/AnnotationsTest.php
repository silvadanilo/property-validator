<?php

declare(strict_types=1);

namespace PropertyValidator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints as Assert;

class AnnotationsTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testNoExceptionOnRightChoice()
    {
        Annotation::create([
            'gender' => 'M',
        ]);
    }

    public function testExceptionOnWrongChoice()
    {
        $this->expectException(InvalidDataException::class);

        Annotation::create([
            'gender' => 'Z',
        ]);
    }
}

class Annotation
{
    use PropertyValidator;

    const GENDERS = ['M', 'F'];

    /**
     * @Assert\Choice(Annotation::GENDERS)
     */
    private string $gender;
}
