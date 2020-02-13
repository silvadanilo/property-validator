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

IP::create('192.168.0.1'); //valid
IP::create(['ip' => '192.168.0.1']); //valid
