<?php

declare(strict_types=1);

namespace Phel\Command;

use Phel\Command\Export\DirectoryRemover;
use Phel\Command\Export\DirectoryRemoverInterface;
use Phel\Command\Export\ExportCommand;
use Phel\Command\Export\FunctionsToExportFinder;
use Phel\Command\Export\FunctionsToExportFinderInterface;
use Phel\Command\Format\FormatCommand;
use Phel\Command\Format\PathFilterInterface;
use Phel\Command\Format\PhelPathFilter;
use Phel\Command\Repl\ColorStyle;
use Phel\Command\Repl\ColorStyleInterface;
use Phel\Command\Repl\ReplCommand;
use Phel\Command\Repl\ReplCommandIoInterface;
use Phel\Command\Repl\ReplCommandSystemIo;
use Phel\Command\Run\RunCommand;
use Phel\Command\Shared\CommandIoInterface;
use Phel\Command\Shared\CommandSystemIo;
use Phel\Command\Shared\NamespaceExtractor;
use Phel\Command\Shared\NamespaceExtractorInterface;
use Phel\Command\Test\TestCommand;
use Phel\Compiler\Analyzer\Environment\GlobalEnvironmentInterface;
use Phel\Compiler\CompilerFactoryInterface;
use Phel\Formatter\FormatterFactoryInterface;
use Phel\Interop\Generator\WrapperGeneratorInterface;
use Phel\Interop\InteropFactoryInterface;
use Phel\Printer\Printer;
use Phel\Printer\PrinterInterface;
use Phel\Runtime\Exceptions\ExceptionPrinterInterface;
use Phel\Runtime\Exceptions\TextExceptionPrinter;
use Phel\Runtime\RuntimeInterface;

final class CommandFactory implements CommandFactoryInterface
{
    private string $projectRootDir;
    private CommandConfigInterface $commandConfig;
    private CompilerFactoryInterface $compilerFactory;
    private FormatterFactoryInterface $formatterFactory;
    private InteropFactoryInterface $interopFactory;

    public function __construct(
        string $projectRootDir,
        CommandConfigInterface $commandConfig,
        CompilerFactoryInterface $compilerFactory,
        FormatterFactoryInterface $formatterFactory,
        InteropFactoryInterface $interopFactory
    ) {
        $this->projectRootDir = $projectRootDir;
        $this->commandConfig = $commandConfig;
        $this->compilerFactory = $compilerFactory;
        $this->formatterFactory = $formatterFactory;
        $this->interopFactory = $interopFactory;
    }

    public function createReplCommand(RuntimeInterface $runtime): ReplCommand
    {
        $runtime->loadFileIntoNamespace('user', __DIR__ . '/Repl/startup.phel');

        return new ReplCommand(
            $this->createReplCommandIo(),
            $this->compilerFactory->createEvalCompiler($runtime->getEnv()),
            $this->createColorStyle(),
            $this->createPrinter()
        );
    }

    public function createRunCommand(RuntimeInterface $runtime): RunCommand
    {
        return new RunCommand(
            $runtime,
            $this->createCommandIo(),
            $this->createNamespaceExtractor($runtime->getEnv())
        );
    }

    public function createTestCommand(RuntimeInterface $runtime): TestCommand
    {
        return new TestCommand(
            $this->projectRootDir,
            $runtime,
            $this->createCommandIo(),
            $this->createNamespaceExtractor($runtime->getEnv()),
            $this->compilerFactory->createEvalCompiler($runtime->getEnv()),
            $this->commandConfig->getDefaultTestDirectories()
        );
    }

    public function createFormatCommand(): FormatCommand
    {
        return new FormatCommand(
            $this->formatterFactory->createFormatter(),
            $this->createCommandIo(),
            $this->createPathFilter()
        );
    }

    public function createExportCommand(RuntimeInterface $runtime): ExportCommand
    {
        return new ExportCommand(
            $this->createWrapperGenerator(),
            $this->createCommandIo(),
            $this->createFunctionsToExportFinder($runtime),
            $this->createDirectoryRemover(),
            $this->commandConfig->getExportTargetDirectory()
        );
    }

    private function createReplCommandIo(): ReplCommandIoInterface
    {
        return new ReplCommandSystemIo(
            $this->projectRootDir . '.phel-repl-history',
            $this->createExceptionPrinter()
        );
    }

    private function createCommandIo(): CommandIoInterface
    {
        return new CommandSystemIo(
            $this->createExceptionPrinter()
        );
    }

    private function createExceptionPrinter(): ExceptionPrinterInterface
    {
        return TextExceptionPrinter::create();
    }

    private function createPathFilter(): PathFilterInterface
    {
        return new PhelPathFilter();
    }

    private function createFunctionsToExportFinder(RuntimeInterface $runtime): FunctionsToExportFinderInterface
    {
        return new FunctionsToExportFinder(
            $this->projectRootDir,
            $runtime,
            $this->createNamespaceExtractor($runtime->getEnv()),
            $this->commandConfig->getExportDirectories()
        );
    }

    private function createNamespaceExtractor(GlobalEnvironmentInterface $globalEnv): NamespaceExtractorInterface
    {
        return new NamespaceExtractor(
            $this->compilerFactory->createLexer(),
            $this->compilerFactory->createParser(),
            $this->compilerFactory->createReader($globalEnv),
            $this->createCommandIo()
        );
    }

    private function createDirectoryRemover(): DirectoryRemoverInterface
    {
        return new DirectoryRemover();
    }

    private function createWrapperGenerator(): WrapperGeneratorInterface
    {
        return $this->interopFactory->createWrapperGenerator();
    }

    private function createColorStyle(): ColorStyleInterface
    {
        return ColorStyle::withStyles();
    }

    private function createPrinter(): PrinterInterface
    {
        return Printer::nonReadableWithColor();
    }
}
