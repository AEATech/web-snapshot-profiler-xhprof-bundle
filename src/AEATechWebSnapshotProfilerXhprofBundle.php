<?php
declare(strict_types=1);

namespace AEATech\WebSnapshotProfilerXhprofBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Xhgui\Profiler\Profiler;

class AEATechWebSnapshotProfilerXhprofBundle extends AbstractBundle
{
    public const CONFIG_KEY_IS_PROFILING_ENABLED = 'is_profiling_enabled';

    public const CONFIG_KEY_APP_VERSION = 'app_version';

    public const CONFIG_KEY_XHGUI = 'xhgui';
    public const CONFIG_KEY_IMPORT_URI = 'import_uri';
    public const CONFIG_KEY_IMPORT_TIMEOUT = 'import_timeout';

    public const CONFIG_KEY_XHPROF = 'xhprof';
    public const CONFIG_KEY_COLLECT_ADDITIONAL_INFO = 'collect_additional_info';
    public const CONFIG_KEY_FLAGS = 'flags';

    public const CONFIG_KEY_EVENT_MATCHER = 'event_matcher';

    public const CONFIG_KEY_IS_PROFILE_ALL_ROUTES = 'is_profile_all_routes';
    public const CONFIG_KEY_IS_ENABLED = 'is_enabled';

    public const CONFIG_KEY_HEADER = 'header';
    
    public const CONFIG_KEY_NAME = 'name';
    public const CONFIG_KEY_VALUE = 'value';

    public const CONFIG_KEY_REQUEST = 'request';

    public const CONFIG_KEY_ROUTE = 'route';
    public const CONFIG_KEY_ROUTE_TO_PROBABILITY = 'route_to_probability';
    public const CONFIG_KEY_ROUTE_NAME = 'route_name';
    public const CONFIG_KEY_PROBABILITY = 'probability';

    public const SERVICE_NAME_PROFILER_BACKEND = self::BUNDLE_NAME_PREFIX . 'profiler_backend';
    public const SERVICE_NAME_FILTER = self::BUNDLE_NAME_PREFIX . 'filter';
    public const SEVICE_NAME_ADAPTER = self::BUNDLE_NAME_PREFIX . 'adapter';
    public const SERVICE_NAME_ALL_EVENT_MATCHER = self::EVENT_MATCHER_NAME_PREFIX . 'all';
    public const SERVICE_NAME_HEADER_EVENT_MATCHER = self::EVENT_MATCHER_NAME_PREFIX . 'header';
    public const SERVICE_NAME_REQUEST_PARAM_AWARE_ROUTE_EVENT_MATCHER_INNER = self::EVENT_MATCHER_NAME_PREFIX .
        'request_param_aware.route_event.inner';
    public const SERVICE_NAME_REQUEST_PARAM_AWARE_ROUTE_EVENT_MATCHER = self::EVENT_MATCHER_NAME_PREFIX .
        'request_param_aware_route_event';
    public const SERVICE_NAME_ROUTE_EVENT_MATCHER = self::EVENT_MATCHER_NAME_PREFIX . 'route';

    public const TAG_EVENT_MATCHER_ITEM = self::BUNDLE_NAME_PREFIX . 'event_matcher.item';

    private const BUNDLE_NAME_PREFIX = 'aea_tech_web_snapshot_profiler_xhprof.';
    private const EVENT_MATCHER_NAME_PREFIX = self::BUNDLE_NAME_PREFIX . 'event_matcher.';

    /**
     * @param array $routeToProbability
     *
     * @return array
     */
    public static function getRouteToRandProbability(array $routeToProbability): array
    {
        $routeToRandProbability = [];

        foreach ($routeToProbability as [
                 self::CONFIG_KEY_ROUTE_NAME => $routeName,
                 self::CONFIG_KEY_PROBABILITY => $probability,
        ]) {
            $routeToRandProbability[$routeName] = (int)ceil(1 / ($probability / 100));
        }

        return $routeToRandProbability;
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->booleanNode(self::CONFIG_KEY_IS_PROFILING_ENABLED)
                    ->isRequired()
                    ->info('Is profiling enabled')
                ->end()
                ->scalarNode(self::CONFIG_KEY_APP_VERSION)
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->info('Application version will be displayed in xhgui')
                ->end()
                ->arrayNode(self::CONFIG_KEY_XHGUI)
                    ->isRequired()
                    ->children()
                        ->scalarNode(self::CONFIG_KEY_IMPORT_URI)
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->info('https://{login}:{password}@host/run/import')
                        ->end()
                        ->integerNode(self::CONFIG_KEY_IMPORT_TIMEOUT)
                            ->isRequired()
                            ->min(1)
                            ->info('Upload timeout must be as low as possible to prevent slowdown of app')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(self::CONFIG_KEY_XHPROF)
                    ->isRequired()
                    ->children()
                        ->scalarNode(self::CONFIG_KEY_COLLECT_ADDITIONAL_INFO)
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->info('Collected additional info about internal functions ("1" || "0")')
                        ->end()
                        ->arrayNode(self::CONFIG_KEY_FLAGS)
                            ->isRequired()
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(self::CONFIG_KEY_EVENT_MATCHER)
                    ->isRequired()
                    ->children()
                        ->booleanNode(self::CONFIG_KEY_IS_PROFILE_ALL_ROUTES)
                            ->isRequired()
                            ->info('Is profiling all routes enabled')
                        ->end()
                        ->arrayNode(self::CONFIG_KEY_HEADER)
                            ->isRequired()
                            ->children()
                                ->booleanNode(self::CONFIG_KEY_IS_ENABLED)
                                    ->isRequired()
                                    ->info('Is profiling by header enabled')
                                ->end()
                                ->scalarNode(self::CONFIG_KEY_NAME)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->info('Header name to execute profiling')
                                ->end()
                                ->scalarNode(self::CONFIG_KEY_VALUE)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->info('Header value to execute profiling')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode(self::CONFIG_KEY_REQUEST)
                            ->isRequired()
                            ->children()
                                ->booleanNode(self::CONFIG_KEY_IS_ENABLED)
                                    ->isRequired()
                                    ->info('Is profiling by request enabled')
                                ->end()
                                ->scalarNode(self::CONFIG_KEY_NAME)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                    ->info('Request param to execute profiling')
                                ->end()
                                ->arrayNode(self::CONFIG_KEY_ROUTE_TO_PROBABILITY)
                                    ->isRequired()
                                    ->arrayPrototype()
                                        ->children()
                                            ->scalarNode(self::CONFIG_KEY_ROUTE_NAME)
                                                ->isRequired()
                                                ->cannotBeEmpty()
                                                ->info('Route name to execute profiling')
                                            ->end()
                                            ->integerNode(self::CONFIG_KEY_PROBABILITY)
                                                ->isRequired()
                                                ->min(1)
                                                ->max(100)
                                                ->info('Probability to execute profiling (1 - 1% of requests, 100 - 100% of requests)')
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                         ->arrayNode(self::CONFIG_KEY_ROUTE)
                            ->isRequired()
                            ->children()
                                ->booleanNode(self::CONFIG_KEY_IS_ENABLED)
                                    ->isRequired()
                                    ->info('Is profiling by route enabled')
                                ->end()
                                ->arrayNode(self::CONFIG_KEY_ROUTE_TO_PROBABILITY)
                                    ->isRequired()
                                    ->arrayPrototype()
                                        ->children()
                                            ->scalarNode(self::CONFIG_KEY_ROUTE_NAME)
                                                ->isRequired()
                                                ->cannotBeEmpty()
                                                ->info('Route name to execute profiling')
                                            ->end()
                                            ->integerNode(self::CONFIG_KEY_PROBABILITY)
                                                ->isRequired()
                                                ->min(1)
                                                ->max(100)
                                                ->info('Probability to execute profiling (1 - 1% of requests, 100 - 100% of requests)')
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $path = $this->getPath();

        $container->import($path . '/config/services.yaml');

        $services = $container->services();

        $this->initProfilerBackend($config, $services);

        if ($config[self::CONFIG_KEY_IS_PROFILING_ENABLED]) {
            $this->initFilter($config, $services, $container);
            $this->initAdapter($config, $services);
            $this->initEventMatcher($config, $services);
        }
    }

    /**
     * @param array $config
     * @param ServicesConfigurator $services
     * @param ContainerConfigurator $container
     *
     * @return void
     */
    private function initFilter(array $config, ServicesConfigurator $services,ContainerConfigurator $container): void
    {
        $services->get(self::SERVICE_NAME_FILTER)
            ->arg('$version', $config[self::CONFIG_KEY_APP_VERSION])
            ->arg('$envServiceName', $container->env());
    }

    /**
     * @param array $config
     * @param ServicesConfigurator $services
     *
     * @return void
     */
    private function initProfilerBackend(array $config, ServicesConfigurator $services): void
    {
        $xhgui = $config[self::CONFIG_KEY_XHGUI];
        $profilerBackendConfig = [
            'profiler' => Profiler::PROFILER_XHPROF,
            'profiler.flags' => $config[self::CONFIG_KEY_XHPROF][self::CONFIG_KEY_FLAGS],
            'save.handler' => Profiler::SAVER_UPLOAD,
            'save.handler.upload' => [
                'uri' => $xhgui[self::CONFIG_KEY_IMPORT_URI],
                'timeout' => $xhgui[self::CONFIG_KEY_IMPORT_TIMEOUT],
            ],
        ];

        $services->get(self::SERVICE_NAME_PROFILER_BACKEND)
            ->arg('$config', $profilerBackendConfig);
    }

    /**
     * @param array $config
     * @param ServicesConfigurator $services
     *
     * @return void
     */
    private function initAdapter(array $config, ServicesConfigurator $services): void
    {
        $xhprof = $config[self::CONFIG_KEY_XHPROF];
        $profilerAdapterConfig = [
            'xhprof.collect_additional_info' => $xhprof[self::CONFIG_KEY_COLLECT_ADDITIONAL_INFO],
        ];

        $services->get(self::SEVICE_NAME_ADAPTER)
            ->arg('$iniSettings', $profilerAdapterConfig);
    }

    /**
     * @param array $config
     * @param ServicesConfigurator $services
     *
     * @return void
     */
    private function initEventMatcher(array $config, ServicesConfigurator $services): void
    {
        $taggedEventMatchers = [];

        $eventMatcher = $config[self::CONFIG_KEY_EVENT_MATCHER];
        if ($eventMatcher[self::CONFIG_KEY_IS_PROFILE_ALL_ROUTES]) {
            $this->initAllEventMatcher($services, $taggedEventMatchers);
        } else {
            $this->initHeaderEventMatcher($eventMatcher, $services, $taggedEventMatchers);
            $this->initRequestParamAwareRouteEventMatcher($eventMatcher, $services, $taggedEventMatchers);
            $this->initRouteEventMatcher($eventMatcher, $services, $taggedEventMatchers);
        }

        foreach ($taggedEventMatchers as $taggedEventMatcher) {
            $taggedEventMatcher->tag(self::TAG_EVENT_MATCHER_ITEM);
        }
    }

    /**
     * @param ServicesConfigurator $services
     * @param array $taggedEventMatchers
     *
     * @return void
     */
    private function initAllEventMatcher(ServicesConfigurator $services, array &$taggedEventMatchers): void
    {
        $taggedEventMatchers[] = $services->get(self::SERVICE_NAME_ALL_EVENT_MATCHER);
    }

    /**
     * @param array $eventMatcher
     * @param ServicesConfigurator $services
     * @param array $taggedEventMatchers
     *
     * @return void
     */
    private function initHeaderEventMatcher(
        array $eventMatcher,
        ServicesConfigurator $services,
        array &$taggedEventMatchers
    ): void {
        $headerProfiling = $eventMatcher[self::CONFIG_KEY_HEADER];

        if ($headerProfiling[self::CONFIG_KEY_IS_ENABLED]) {
            $headerEventMatcher = $services->get(self::SERVICE_NAME_HEADER_EVENT_MATCHER);
            $headerEventMatcher->arg('$headerProfilingName', $headerProfiling[self::CONFIG_KEY_NAME])
                ->arg('$headerProfilingValueEnabled', $headerProfiling[self::CONFIG_KEY_VALUE]);

            $taggedEventMatchers[] = $headerEventMatcher;
        }
    }

    /**
     * @param array $eventMatcher
     * @param ServicesConfigurator $services
     * @param array $taggedEventMatchers
     *
     * @return void
     */
    private function initRequestParamAwareRouteEventMatcher(
        array $eventMatcher,
        ServicesConfigurator $services,
        array &$taggedEventMatchers
    ): void {
        $requestProfiling = $eventMatcher[self::CONFIG_KEY_REQUEST];

        if ($requestProfiling[self::CONFIG_KEY_IS_ENABLED]) {
            $routeToRandProbability = $this->getRouteToRandProbability(
                $requestProfiling[self::CONFIG_KEY_ROUTE_TO_PROBABILITY]
            );
            $services->get(self::SERVICE_NAME_REQUEST_PARAM_AWARE_ROUTE_EVENT_MATCHER_INNER)
                ->arg('$routeToRandProbability', $routeToRandProbability);

            $requestParamAwareRouteEventMatcher = $services->get(
                self::SERVICE_NAME_REQUEST_PARAM_AWARE_ROUTE_EVENT_MATCHER
            );
            $requestParamAwareRouteEventMatcher->arg('$requestParamName', $requestProfiling[self::CONFIG_KEY_NAME]);

            $taggedEventMatchers[] = $requestParamAwareRouteEventMatcher;
        }
    }

    /**
     * @param array $eventMatcher
     * @param ServicesConfigurator $services
     * @param array $taggedEventMatchers
     *
     * @return void
     */
    private function initRouteEventMatcher(
        array $eventMatcher,
        ServicesConfigurator $services,
        array &$taggedEventMatchers
    ): void {
        $routeProfiling = $eventMatcher[self::CONFIG_KEY_ROUTE];

        if ($routeProfiling[self::CONFIG_KEY_IS_ENABLED]) {
            $routeToRandProbability = $this->getRouteToRandProbability(
                $routeProfiling[self::CONFIG_KEY_ROUTE_TO_PROBABILITY]
            );

            $routeEventMatcher = $services->get(self::SERVICE_NAME_ROUTE_EVENT_MATCHER);
            $routeEventMatcher->arg('$routeToRandProbability', $routeToRandProbability);

            $taggedEventMatchers[] = $routeEventMatcher;
        }
    }
}
