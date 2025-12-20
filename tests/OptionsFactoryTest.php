<?php
declare(strict_types=1);

namespace AEATech\Tests;

use AEATech\WebSnapshotProfilerXhprofBundle\OptionsFactory;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\KernelEvent;

class OptionsFactoryTest extends TestCase
{
    private OptionsFactory $optionsFactory;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->optionsFactory = new OptionsFactory();
    }

    /**
     * @return void
     */
    #[Test]
    public function factory(): void
    {
        self::assertSame(
            OptionsFactory::OPTIONS,
            $this->optionsFactory->factory(Mockery::mock(KernelEvent::class))
        );
    }
}
