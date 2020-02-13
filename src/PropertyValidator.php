<?php

declare(strict_types=1);

namespace PropertyValidator;

use Doctrine\Common\Annotations\AnnotationRegistry;
use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

trait PropertyValidator
{
    private static bool $__initiated = false;

    private static array $__traitProperties = [];

    private static ?array $__classProperties = [];

    public static function create(...$arguments)
    {
        $data = isset($arguments[0]) ? $arguments[0] : null;

        if (is_object($data) && get_class($data) == get_called_class()) {
            return $data;
        }

        self::init();

        $rc = new \ReflectionClass(static::class);
        $nullProperties = self::nullProperties($rc);
        $properties = array_keys($nullProperties);

        if (!is_array($data)) {
            if (count($properties) === 1) {
                $singlePropertyValue = $data;
                $data = [];
                $data[$properties[0]] = $singlePropertyValue;
            } else {
                throw new InvalidArgumentException(static::class . ': Data to be boxed must be an associative array.');
            }
        }

        $data = array_diff_key(
            array_merge($nullProperties, $rc->getDefaultProperties(), $data),
            array_flip(self::$__traitProperties)
        );
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
            throw new InvalidDataException(iterator_to_array($errors), 'Error on creation of `' . static::class . '`: ' . (string) $errors);
        }

        $self = new static();

        foreach ($data as $key => $value) {
            $self->$key = $value;
        }

        return $self;
    }

    private static function classProperties(?ReflectionClass $rc = null): array
    {
        if (isset(self::$__classProperties[static::class])) {
            return self::$__classProperties[static::class];
        }

        if (is_null($rc)) {
            $rc = new \ReflectionClass(static::class);
        }

        self::$__classProperties[static::class] = array_diff(
            array_map(fn ($p) => $p->getName(), $rc->getProperties()),
            self::$__traitProperties
        );

        return self::$__classProperties[static::class];
    }

    public function toArray(bool $recursive = false): array
    {
        $arrayData = array_reduce(self::classProperties(), function ($acc, $property) {
            $acc[$property] = $this->$property();

            return $acc;
        }, []);

        if (!$recursive) {
            return $arrayData;
        }

        $recursiveUnboxing = function ($data) use (&$recursiveUnboxing, $recursive) {
            foreach ($data as $key => $value) {
                if ($value instanceof PropertyValidator) {
                    $data[$key] = $value->toArray($recursive);
                } elseif (is_array($value)) {
                    $data[$key] = $recursiveUnboxing($value);
                }

                if (isset($this->objects[$key]) && is_array($this->objects[$key])) {
                    $data[$key] = array_map(function ($v) use ($recursive) {
                        return $v->toArray($recursive);
                    }, $value);
                }
            }

            return $data;
        };

        return $recursiveUnboxing($arrayData);
    }

    public function __call($method, $args)
    {
        if (!in_array($method, self::classProperties())) {
            throw new \RuntimeException("`$method` field is missing in the " . __CLASS__ . ' object');
        }

        return $this->$method;
    }

    private static function nullProperties(ReflectionClass $rc): array
    {
        $fetchReflectionPropertyName = fn ($p) => $p->getName();

        return array_diff_key(array_fill_keys(
            array_map($fetchReflectionPropertyName, $rc->getProperties()),
            null
        ), array_flip(self::$__traitProperties));
    }

    private static function init(): void
    {
        if (self::$__initiated) {
            return;
        }

        AnnotationRegistry::registerLoader('class_exists');
        self::$__initiated = true;

        $traitRc = new \ReflectionClass(PropertyValidator::class);
        self::$__traitProperties = array_map(fn ($p) => $p->getName(), $traitRc->getProperties());
    }
}
