<?php
declare(strict_types=1);

namespace AEATech\Tests;

use AEATech\WebSnapshotProfilerEventSubscriber\EventMatcher\AllEventMatcher;
use AEATech\WebSnapshotProfilerEventSubscriber\EventMatcher\EventMatcher;
use AEATech\WebSnapshotProfilerEventSubscriber\EventMatcher\HeaderEventMatcher;
use AEATech\WebSnapshotProfilerEventSubscriber\EventMatcher\RequestParamAwareRouteEventMatcher;
use AEATech\WebSnapshotProfilerEventSubscriber\EventMatcher\RouteEventMatcher;
use AEATech\WebSnapshotProfilerXhprofBundle\AEATechWebSnapshotProfilerXhprofBundle;
use Nyholm\BundleTest\TestKernel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class AEATechWebSnapshotProfilerXhprofBundleTest extends KernelTestCase
{
    /**
     * {@inheritDoc}
     */
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(AEATechWebSnapshotProfilerXhprofBundle::class);
        $kernel->handleOptions($options);

        return $kernel;
    }

    /**
     * @return array
     */
    public static function checkDisabledStateDataProvider(): array
    {
        return [
            'check disabled state' => [
                'config' => __DIR__ . '/Fixtures/Resources/disabled.yaml',
            ],
            'check enabled without event matchers state' => [
                'config' => __DIR__ . '/Fixtures/Resources/enabled_without_event_matchers.yaml',
            ],
        ];
    }

    /**
     * @param string $config
     *
     * @return void
     */
    #[Test]
    #[DataProvider('checkDisabledStateDataProvider')]
    public function checkDisabledState(string $config): void
    {
        self::bootKernel(['config' => static function (TestKernel $kernel) use ($config): void {
            $kernel->addTestConfig($config);
        }]);

        $container = self::getContainer();

        $actualEventMatchers = $this->getActualEventMatchers($container);

        self::assertSame([], $actualEventMatchers);
    }

    /**
     * @return array
     */
    public static function checkEnabledWithEventMatcherDataProvider(): array
    {
        return [
            AllEventMatcher::class => [
                'config' => __DIR__ . '/Fixtures/Resources/enabled_with_all_event_matcher.yaml',
                'expectedEventMatcherClassName' => AllEventMatcher::class,
            ],
            HeaderEventMatcher::class => [
                'config' => __DIR__ . '/Fixtures/Resources/enabled_with_header_event_matcher.yaml',
                'expectedEventMatcherClassName' => HeaderEventMatcher::class,
            ],
            RequestParamAwareRouteEventMatcher::class => [
                'config' => __DIR__ . '/Fixtures/Resources/enabled_with_request_event_matcher.yaml',
                'expectedEventMatcherClassName' => RequestParamAwareRouteEventMatcher::class,
            ],
            RouteEventMatcher::class => [
                'config' => __DIR__ . '/Fixtures/Resources/enabled_with_route_event_matcher.yaml',
                'expectedEventMatcherClassName' => RouteEventMatcher::class,
            ],
        ];
    }

    /**
     * @param string $config
     * @param string $expectedEventMatcherClassName
     *
     * @return void
     */
    #[Test]
    #[DataProvider('checkEnabledWithEventMatcherDataProvider')]
    public function checkEnabledWithEventMatcher(string $config, string $expectedEventMatcherClassName): void
    {
        self::bootKernel(['config' => static function (TestKernel $kernel) use ($config): void {
            $kernel->addTestConfig($config);
        }]);

        $container = self::getContainer();

        $actualEventMatchers = $this->getActualEventMatchers($container);

        self::assertCount(1, $actualEventMatchers);
        self::assertInstanceOf($expectedEventMatcherClassName, $actualEventMatchers[0]);
    }

    /**
     * @return array
     */
    public static function getRouteToRandProbabilityDataProvider(): array
    {
        return [
            'empty' => [
                'routeToProbability' => [],
                'expected' => [],
            ],
            '100% - probability' => [
                'routeToProbability' => [
                    [
                        AEATechWebSnapshotProfilerXhprofBundle::CONFIG_KEY_ROUTE_NAME => 'route',
                        AEATechWebSnapshotProfilerXhprofBundle::CONFIG_KEY_PROBABILITY => 100,
                    ],
                ],
                'expected' => [
                    'route' => 1,
                ],
            ],
            '1% - probability' => [
                'routeToProbability' => [
                    [
                        AEATechWebSnapshotProfilerXhprofBundle::CONFIG_KEY_ROUTE_NAME => 'route',
                        AEATechWebSnapshotProfilerXhprofBundle::CONFIG_KEY_PROBABILITY => 1,
                    ],
                ],
                'expected' => [
                    'route' => 100,
                ],
            ],
            '50% - probability' => [
                'routeToProbability' => [
                    [
                        AEATechWebSnapshotProfilerXhprofBundle::CONFIG_KEY_ROUTE_NAME => 'route',
                        AEATechWebSnapshotProfilerXhprofBundle::CONFIG_KEY_PROBABILITY => 50,
                    ],
                ],
                'expected' => [
                    'route' => 2,
                ],
            ],
            '33% - probability' => [
                'routeToProbability' => [
                    [
                        AEATechWebSnapshotProfilerXhprofBundle::CONFIG_KEY_ROUTE_NAME => 'route',
                        AEATechWebSnapshotProfilerXhprofBundle::CONFIG_KEY_PROBABILITY => 33,
                    ],
                ],
                'expected' => [
                    'route' => 4,
                ],
            ],
            '66% - probability' => [
                'routeToProbability' => [
                    [
                        AEATechWebSnapshotProfilerXhprofBundle::CONFIG_KEY_ROUTE_NAME => 'route',
                        AEATechWebSnapshotProfilerXhprofBundle::CONFIG_KEY_PROBABILITY => 66,
                    ],
                ],
                'expected' => [
                    'route' => 2,
                ],
            ],
        ];
    }

    /**
     * @param array $routeToProbability
     * @param array $expected
     *
     * @return void
     */
    #[Test]
    #[DataProvider('getRouteToRandProbabilityDataProvider')]
    public function getRouteToRandProbability(array $routeToProbability, array $expected): void
    {
        $actual = AEATechWebSnapshotProfilerXhprofBundle::getRouteToRandProbability($routeToProbability);

        self::assertSame($expected, $actual);
    }

    /**
     * @param ContainerInterface $container
     *
     * @return array
     */
    private function getActualEventMatchers(ContainerInterface $container): array
    {
        $reflectionProperty = new ReflectionProperty(EventMatcher::class, 'eventMatchers');

        $actualEventMatchers = [];

        $eventMatcher = $container->get('aea_tech_web_snapshot_profiler_xhprof.event_matcher');
        foreach ($reflectionProperty->getValue($eventMatcher) as $innerEventMatcher) {
            $actualEventMatchers[] = $innerEventMatcher;
        }

        return $actualEventMatchers;
    }
}
