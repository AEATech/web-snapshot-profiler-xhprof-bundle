# AEATech - Web snapshot profiler xhprof bundle

[![Code Coverage](.build/coverage_badge.svg)](.build/clover.xml)

The package contains symfony bundle to profile applications with xhprof.
It can be used for production profiling.

System requirements:
- PHP >= 8.2
- ext-xhprof
- xhgui (tested on [xhgui/xhgui:0.22.1](https://hub.docker.com/r/xhgui/xhgui/tags?name=0.22.1)) 

Installation (Composer):
```bash
composer require aeatech/web-snapshot-profiler-xhprof-bundle
```

## Installation

Enable bundle in dev and prod env.

```php
// config/bundles.php

return [
    // ...
    AEATech\WebSnapshotProfilerXhprofBundle\AEATechWebSnapshotProfilerXhprofBundle::class => ['dev' => true, 'prod' => true],
    // ...
];
```

## Configuration

Symfony Flex generates a default configuration in config/packages/aea_tech_web_snapshot_profiler_xhprof.yaml

```yaml
aea_tech_web_snapshot_profiler_xhprof:
    # Enable/Disable profiling
    is_profiling_enabled: false

    # Application info for snapshot naming
    app_version: '0.0.1'

    # XHGUI configuration
    xhgui:
        import_uri: '%env(string:AEA_TECH_WEB_SNAPSHOT_PROFILER_XHPROF_XHGUI)%'
        import_timeout: 1

    ###
    # xhprof configuration
    # - collect internal functions info
    # - collect memory allocation info
    ###
    xhprof:
        collect_additional_info: '1'
        flags:
            - !php/const Xhgui\Profiler\ProfilingFlags::MEMORY

    ###
    # Event matched configuration - START
    ###
    event_matcher:
        # Enable/Disable all routes profiling
        is_profile_all_routes: true

        # Enable profile if header was set (\AEATech\WebSnapshotProfilerEventSubscriber\EventMatcher\HeaderEventMatcher)
        header:
            is_enabled: false
            name: 'x-profiling-header'
            value: '1'

        # Enable profile if request param was set and route matched (\AEATech\WebSnapshotProfilerEventSubscriber\EventMatcher\RequestParamAwareRouteEventMatcher)
        request:
            is_enabled: false
            name: 'x-profile-request-param'
            route_to_probability:
                # 1% probability to profile route
                -
                    route_name: 'app_route_name_1'
                    probability: 1
                # 100% probability to profile route
                -
                    route_name: 'app_route_name_2'
                    probability: 100

        # Enabled profile if route matched and probability happened
        route:
            is_enabled: false
            route_to_probability:
                # 1% probability to profile route
                -
                    route_name: 'app_route_name_1'
                    probability: 1
                # 100% probability to profile route
                -
                    route_name: 'app_route_name_2'
                    probability: 100
    ###
    # Event matched configuration - END
    ###
```

## License

MIT License. See [LICENSE](./LICENSE) for details.