<?php

declare(strict_types=1);

namespace App\Serializer;

use App\Entity\User\Profile;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

class ProfilePhotoNormalizer implements NormalizerInterface
{
    private const ALREADY_CALLED = 'PROFILE_PHOTO_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private readonly NormalizerInterface $normalizer,
        private readonly StorageInterface $storage,
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context[self::ALREADY_CALLED] = true;

        $object->photoUrl = $this->storage->resolveUri($object, 'photoFile');

        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof Profile;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Profile::class => true,
        ];
    }
}
