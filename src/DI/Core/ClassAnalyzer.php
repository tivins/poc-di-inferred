<?php

namespace Tivins\DI;

use ReflectionClass;
use ReflectionException;

class ClassAnalyzer
{
    private CacheInterface $cache;

    /**
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Result of analyzing a class constructor.
     *
     * @phpstan-type Analysis array{
     *   hasConstructor: bool,
     *   isInstantiable: bool,
     *   constructorPrivate: bool,
     *   parameters: list<array{name: string, type: string}>
     * }
     */

    /**
     * Get constructor analysis for a class, using file cache when source file mtime is unchanged.
     *
     * @param class-string $class
     * @return array{hasConstructor: bool, isInstantiable: bool, constructorPrivate: bool, parameters: list<array{name: string, type: string}>}
     * @throws ReflectionException
     */
    public function getConstructorAnalysis(string $class): array
    {
        $reflection = new ReflectionClass($class);
        $sourceFile = $reflection->getFileName();
        $sourceMtime = ($sourceFile !== false && is_file($sourceFile)) ? (int)filemtime($sourceFile) : 0;

        $cacheKey = $class;
        if ($sourceFile !== false && $sourceMtime > 0) {
            $cached = $this->readCache($cacheKey);
            if ($cached !== null
                && isset($cached['sourceFile'], $cached['sourceMtime'])
                && $cached['sourceFile'] === $sourceFile
                && $cached['sourceMtime'] === $sourceMtime
            ) {
                return [
                    'hasConstructor' => $cached['hasConstructor'],
                    'isInstantiable' => $cached['isInstantiable'],
                    'constructorPrivate' => $cached['constructorPrivate'],
                    'parameters' => $cached['parameters'],
                ];
            }
        }

        $analysis = $this->analyze($reflection);
        if ($sourceFile !== false && $sourceMtime > 0) {
            $this->writeCache($cacheKey, $sourceFile, $sourceMtime, $analysis);
        }
        return $analysis;
    }

    /**
     * @return array{hasConstructor: bool, isInstantiable: bool, constructorPrivate: bool, parameters: list<array{name: string, type: string}>}
     */
    private function analyze(ReflectionClass $reflection): array
    {
        $constructor = $reflection->getConstructor();
        $hasConstructor = $constructor !== null;
        $constructorPrivate = $constructor !== null && $constructor->isPrivate();
        $parameters = [];

        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                $type = $param->getType();
                $typeName = ($type !== null && !$type->isBuiltin()) ? $type->getName() : '';
                $parameters[] = [
                    'name' => $param->getName(),
                    'type' => $typeName,
                ];
            }
        }

        return [
            'hasConstructor' => $hasConstructor,
            'isInstantiable' => $reflection->isInstantiable(),
            'constructorPrivate' => $constructorPrivate,
            'parameters' => $parameters,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readCache(string $cacheKey): ?array
    {
        $raw = $this->cache->get($cacheKey);
        if ($raw === null) {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    /**
     * @param array{hasConstructor: bool, isInstantiable: bool, constructorPrivate: bool, parameters: list<array{name: string, type: string}>} $analysis
     */
    private function writeCache(string $cacheKey, string $sourceFile, int $sourceMtime, array $analysis): void
    {
        $data = [
            'sourceFile' => $sourceFile,
            'sourceMtime' => $sourceMtime,
            'hasConstructor' => $analysis['hasConstructor'],
            'isInstantiable' => $analysis['isInstantiable'],
            'constructorPrivate' => $analysis['constructorPrivate'],
            'parameters' => $analysis['parameters'],
        ];
        $this->cache->set($cacheKey, json_encode($data, JSON_UNESCAPED_SLASHES));
    }

    public function deleteCache(string $cacheKey): void
    {
        $this->cache->delete($cacheKey);
    }

    public function purgeCache(): void
    {
        $this->cache->clear();
    }
}
