<?php

namespace Tivins\DI;

use ReflectionClass;
use ReflectionException;

class ClassAnalyzer
{
    private string $cacheDir;

    /**
     * @param string $cacheDir Directory for cache files (created if missing)
     */
    public function __construct(string $cacheDir = '')
    {
        $this->cacheDir = $cacheDir !== '' ? $cacheDir : (sys_get_temp_dir() . '/di-cache');
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
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

        $cachePath = $this->getCachePath($class);
        if ($sourceFile !== false && $sourceMtime > 0) {
            $cached = $this->readCache($cachePath);
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
            $this->writeCache($cachePath, $sourceFile, $sourceMtime, $analysis);
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

    private function getCachePath(string $class): string
    {
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
        $safe = str_replace(['\\', ':'], ['-', '-'], $class);
        return $this->cacheDir . '/' . $safe . '.json';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function readCache(string $cachePath): ?array
    {
        if (!is_file($cachePath)) {
            return null;
        }
        $raw = @file_get_contents($cachePath);
        if ($raw === false) {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    /**
     * @param array{hasConstructor: bool, isInstantiable: bool, constructorPrivate: bool, parameters: list<array{name: string, type: string}>} $analysis
     */
    private function writeCache(string $cachePath, string $sourceFile, int $sourceMtime, array $analysis): void
    {
        $data = [
            'sourceFile' => $sourceFile,
            'sourceMtime' => $sourceMtime,
            'hasConstructor' => $analysis['hasConstructor'],
            'isInstantiable' => $analysis['isInstantiable'],
            'constructorPrivate' => $analysis['constructorPrivate'],
            'parameters' => $analysis['parameters'],
        ];
        @file_put_contents($cachePath, json_encode($data, JSON_UNESCAPED_SLASHES));
    }
}
