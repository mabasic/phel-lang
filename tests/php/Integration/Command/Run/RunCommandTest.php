<?php

declare(strict_types=1);

namespace PhelTest\Integration\Command\Run;

use Phel\Command\CommandConfigInterface;
use Phel\Command\CommandFactory;
use Phel\Command\CommandFactoryInterface;
use Phel\Compiler\Analyzer\Environment\GlobalEnvironment;
use Phel\Compiler\CompilerFactory;
use Phel\Formatter\FormatterFactoryInterface;
use Phel\Interop\InteropFactoryInterface;
use Phel\Runtime\RuntimeFactory;
use Phel\Runtime\RuntimeInterface;
use PHPUnit\Framework\TestCase;

final class RunCommandTest extends TestCase
{
    public function testRunByNamespace(): void
    {
        $this->expectOutputRegex('~hello world~');

        $runtime = $this->createRuntime();
        $runtime->addPath('test\\', [__DIR__ . '/Fixtures']);

        $this->createCommandFactory()
            ->createRunCommand($runtime)
            ->run('test\\test-script');
    }

    public function testRunByFilename(): void
    {
        $this->expectOutputRegex('~hello world~');

        $runtime = $this->createRuntime();
        $runtime->addPath('test\\', [__DIR__ . '/Fixtures']);

        $this->createCommandFactory()
            ->createRunCommand($runtime)
            ->run(__DIR__ . '/Fixtures/test-script.phel');
    }

    public function testCannotParseFile(): void
    {
        $filename = __DIR__ . '/Fixtures/test-script-not-parsable.phel';
        $this->expectOutputRegex(sprintf('~Cannot parse file: %s~', $filename));

        $this->createCommandFactory()
            ->createRunCommand($this->createRuntime())
            ->run($filename);
    }

    public function testCannotReadFile(): void
    {
        $filename = __DIR__ . '/Fixtures/this-file-does-not-exist.phel';
        $this->expectOutputRegex(sprintf('~Cannot load namespace: %s~', $filename));

        $this->createCommandFactory()
            ->createRunCommand($this->createRuntime())
            ->run($filename);
    }

    public function testFileWithoutNs(): void
    {
        $filename = __DIR__ . '/Fixtures/test-script-without-ns.phel';
        $this->expectOutputRegex(sprintf('~Cannot extract namespace from file: %s~', $filename));

        $this->createCommandFactory()
            ->createRunCommand($this->createRuntime())
            ->run($filename);
    }

    private function createRuntime(): RuntimeInterface
    {
        return RuntimeFactory::initializeNew(new GlobalEnvironment());
    }

    private function createCommandFactory(): CommandFactoryInterface
    {
        return new CommandFactory(
            __DIR__,
            $this->createStub(CommandConfigInterface::class),
            new CompilerFactory(),
            $this->createStub(FormatterFactoryInterface::class),
            $this->createStub(InteropFactoryInterface::class)
        );
    }
}
