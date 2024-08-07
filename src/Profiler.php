<?php

namespace SpiralPackages\Profiler;

use SpiralPackages\Profiler\Driver\DriverInterface;
use SpiralPackages\Profiler\Storage\StorageInterface;

/**
 * ct: number of calls to bar() from foo()
 * wt: time in bar() when called from foo()
 * cpu: cpu time in bar() when called from foo()
 * mu: change in PHP memory usage in bar() when called from foo()
 * pmu: change in PHP peak memory usage in bar() when called from foo()
 *
 * @psalm-type TTrace = array<non-empty-string, array{
 *     ct: int,
 *     wt: int,
 *     cpu: int,
 *     mu: int,
 *     pmu: int
 * }>
 */
final class Profiler
{
    public const IGNORED_FUNCTIONS_KEY = 'ignored_functions';

    private  StorageInterface $storage;
    private  DriverInterface $driver;
    private  string $appName;
    private  array $tags = [];

    /**\
     * @param non-empty-string $appName
     * @param non-empty-string[] $tags
     */
    public function __construct(
        StorageInterface $storage,
        DriverInterface $driver,
        string $appName,
        array $tags = []
    ) {
        $this->storage = $storage;
        $this->driver = $driver;
        $this->appName = $appName;
        $this->tags = $tags;
    }

    /**
     * Start application profiling.
     *
     * @param non-empty-string[] $ignoredFunctions
     */
    public function start(array $ignoredFunctions = []): void
    {
        $ignoredFunctions = \array_merge(
            $ignoredFunctions,
            ['SpiralPackages\Profiler\Profiler::end']
        );

        $this->driver->start([
            self::IGNORED_FUNCTIONS_KEY => $ignoredFunctions,
        ]);
    }

    /**
     * Finish application profiling and send trace to the storage.
     *
     * @return TTrace
     */
    public function end(array $tags = []): array
    {
        $result = $this->driver->end();
        $this->storage->store(
            $this->appName,
            \array_merge($this->tags, $tags),
            new \DateTimeImmutable(),
            $result
        );

        return $result;
    }
}
