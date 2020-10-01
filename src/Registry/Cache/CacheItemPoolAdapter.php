<?php

declare(strict_types=1);

namespace FlixTech\SchemaRegistryApi\Registry\Cache;

use AvroSchema;
use AvroSchemaParseException;
use FlixTech\SchemaRegistryApi\Registry\CacheAdapter;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Exception\InvalidArgumentException;

class CacheItemPoolAdapter implements CacheAdapter
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    public function __construct(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * {@inheritdoc}
     */
    public function cacheSchemaWithId(AvroSchema $schema, int $schemaId): void
    {
        $item = $this->cacheItemPool->getItem((string) $schemaId);
        $item->set((string) $schema);
        $this->cacheItemPool->save($item);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function cacheSchemaIdByHash(int $schemaId, string $schemaHash): void
    {
        $item = $this->cacheItemPool->getItem($schemaHash);
        $item->set($schemaId);
        $this->cacheItemPool->save($item);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function cacheSchemaWithSubjectAndVersion(AvroSchema $schema, string $subject, int $version): void
    {
        $item = $this->cacheItemPool->getItem($this->makeKeyFromSubjectAndVersion($subject, $version));
        $item->set((string) $schema);
        $this->cacheItemPool->save($item);
    }

    /**
     * {@inheritdoc}
     *
     * @throws AvroSchemaParseException|InvalidArgumentException
     */
    public function getWithId(int $schemaId): ?AvroSchema
    {
        $item = $this->cacheItemPool->getItem((string) $schemaId);

        if (!$item->isHit()) {
            return null;
        }

        $rawSchema = $item->get();

        return AvroSchema::parse($rawSchema);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdWithHash(string $hash): ?int
    {
        $item = $this->cacheItemPool->getItem($hash);

        if (!$item->isHit()) {
            return null;
        }

        return $item->get();
    }

    /**
     * {@inheritdoc}
     *
     * @throws AvroSchemaParseException|InvalidArgumentException
     */
    public function getWithSubjectAndVersion(string $subject, int $version): ?AvroSchema
    {
        $item = $this->cacheItemPool->getItem(
            $this->makeKeyFromSubjectAndVersion($subject, $version)
        );

        if (!$item->isHit()) {
            return null;
        }

        $rawSchema = $item->get();

        return AvroSchema::parse($rawSchema);
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaForId(int $schemaId): bool
    {
        return $this->cacheItemPool
            ->getItem((string) $schemaId)
            ->isHit();
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaIdForHash(string $schemaHash): bool
    {
        return $this->cacheItemPool
            ->getItem($schemaHash)
            ->isHit();
    }

    /**
     * {@inheritdoc}
     */
    public function hasSchemaForSubjectAndVersion(string $subject, int $version): bool
    {
        return $this->cacheItemPool
            ->getItem(
                $this->makeKeyFromSubjectAndVersion($subject, $version)
            )
            ->isHit();
    }

    private function makeKeyFromSubjectAndVersion(string $subject, int $version): string
    {
        return sprintf('%s_%d', $subject, $version);
    }
}
