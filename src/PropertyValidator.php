<?php

declare(strict_types=1);

namespace PropertyValidator;

use Doctrine\Common\Annotations\AnnotationRegistry;
use ReflectionClass;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

trait PropertyValidator
{
    private static $initiated = false;

    private array $properties = [];

    public static function create(array $data = []): Self
    {
        self::init();

        $rc = new \ReflectionClass(__CLASS__);
        $nullProperties = self::nullProperties($rc);

        $data = array_merge($nullProperties, $rc->getDefaultProperties(), $data);
        $properties = array_keys($nullProperties);

        $rules = [];
        foreach ($properties as $property) {
            $rules[$property] = [];
            $type = $rc->getProperty($property)->getType();
            if ($type) {
                if (!$type->allowsNull()) {
                    $rules[$property][] = new Assert\NotNull();
                }

                $rules[$property][] = new Assert\Type([
                    'type' => $type->getName(),
                ]);
            }
        }

        $validator = Validation::createValidatorBuilder()
            ->addMethodMapping('loadValidatorMetadata')
            ->enableAnnotationMapping()
            ->getValidator()
        ;

        $metadata = $validator->getMetadataFor(__CLASS__);
        foreach ($metadata->getConstrainedProperties() as $p) {
            $rules[$p] = array_merge(
                $rules[$p],
                $metadata->getPropertyMetadata($p)[0]->getConstraints()
            );
        }

        $errors = $validator->validate($data, new Assert\Collection($rules));
        if (count($errors)) {
            throw new InvalidDataException(iterator_to_array($errors), (string) $errors);
        }

        $self = new Self();
        $self->__properties = $properties;

        foreach ($data as $key => $value) {
            $self->$key = $value;
        }

        return $self;
    }

    public function __call($method, $args)
    {
        if (!in_array($method, $this->__properties)) {
            throw \RuntimeException("`$method` field is missing in the " . __CLASS__ . ' object');
        }

        return $this->$method;
    }

    private static function nullProperties(ReflectionClass $rc): array
    {
        return array_fill_keys(
            array_map(fn($p) => $p->getName(), $rc->getProperties()),
            null
        );
    }

    private static function init(): void
    {
        if (self::$initiated) {
            return;
        }

        AnnotationRegistry::registerLoader('class_exists');
        self::$initiated = true;
    }
}
