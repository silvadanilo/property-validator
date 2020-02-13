<?php

declare(strict_types=1);

use PropertyValidator\InvalidDataException;
use PropertyValidator\PropertyValidator;

require_once __DIR__ . '/../vendor/autoload.php';

class Simple
{
    use PropertyValidator;

    const VALID_SURNAMES = ['Rossi', 'Verdi'];

    private string $name;

    private string $surname;

    private ?string $address;

    private DateTimeImmutable $birdthday;
}

try {

    Simple::create([
        'name' => 'Mario',
        'surname' => 'Rossi',
        'birdthday' => new DateTimeImmutable('1982-10-01T04:00:00Z'),
    ]);

} catch (InvalidDataException $e) {
    echo PHP_EOL;
    var_export($e->getErrorList());
    echo PHP_EOL;
    echo PHP_EOL;
    echo $e->getMessage();
    echo PHP_EOL;
}
