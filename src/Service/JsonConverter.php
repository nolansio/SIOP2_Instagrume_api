<?php

namespace App\Service;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class JsonConverter {

    public function encodeToJson($data, $groups = ['all']): string {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $normalizers = [
            new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s']),
            new ObjectNormalizer($classMetadataFactory, new CamelCaseToSnakeCaseNameConverter()),
        ];
        $serializer = new Serializer($normalizers, [new JsonEncoder()]);

        $jsonContent = $serializer->serialize($data, 'json', [
            'circular_reference_handler' => fn($object) => $object->getId(),
            AbstractNormalizer::GROUPS => $groups,
            AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true
        ]);

        return $jsonContent;
    }

    public function decodeFromJSon($data, $className) {
        $normalizers = [new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter())];

        $serializer = new Serializer($normalizers, [new JsonEncoder()]);
        $object = $serializer->deserialize($data, $className, 'json');

        return $object;
    }

}
