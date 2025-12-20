<?php
declare(strict_types=1);

namespace AEATech\WebSnapshotProfilerXhprofBundle;

use AEATech\WebSnapshotProfilerEventSubscriber\OptionsFactoryInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;

class OptionsFactory implements OptionsFactoryInterface
{
    public const OPTIONS = [];

    /**
     * {@inheritDoc}
     */
    public function factory(KernelEvent $event): array
    {
        return self::OPTIONS;
    }
}
