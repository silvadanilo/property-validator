<?php

declare(strict_types=1);

use PropertyValidator\InvalidDataException;
use PropertyValidator\PropertyValidator;
use Symfony\Component\Validator\Constraints as Assert;

require_once __DIR__ . '/../vendor/autoload.php';

class IP
{
    use PropertyValidator;

    /**
     * @Assert\Ip
     */
    private string $ip;
}

class Annotations
{
    use PropertyValidator;

    const VALID_PROTOCOLS = ['http', 'ftp'];

    /**
     * @Assert\Choice(Annotations::VALID_PROTOCOLS)
     */
    private string $protocol;

    /**
     * @Assert\All({
     *    @Assert\Type({"string", IP::class})
     * })
     */
    private ?array $addresses;
}

try {
    Annotations::create([
        'protocol' => 'http',
        'addresses' => [
            '127.0.0.1',
            IP::create('192.168.0.1'),
        ],
    ]);
} catch (InvalidDataException $e) {
    echo PHP_EOL;
    var_export($e->getErrorList());
    echo PHP_EOL;
    echo PHP_EOL;
    echo $e->getMessage();
    echo PHP_EOL;
}
