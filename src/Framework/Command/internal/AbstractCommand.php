<?php

/**
 * Static Analysis Results Baseliner (sarb).
 *
 * (c) Dave Liddament
 *
 * For the full copyright and licence information please view the LICENSE file distributed with this source code.
 */

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Framework\Command\internal;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\FileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\SarbException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\File\FileImportException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

abstract class AbstractCommand extends Command
{
    private const RESULTS_FILE = 'static-analysis-output-file';
    private const BASELINE_FILE = 'baseline-file';
    private const PROJECT_ROOT = 'project-root';

    /**
     * {@inheritdoc}
     */
    final protected function configure(): void
    {
        $this->addOption(
            self::PROJECT_ROOT,
            null,
            InputOption::VALUE_REQUIRED,
            'Path to the root of the project you are creating baseline for'
        );

        $this->addArgument(
            self::RESULTS_FILE,
            InputArgument::REQUIRED,
            'Static analysis output file'
        );

        $this->addArgument(
            self::BASELINE_FILE,
            InputArgument::REQUIRED, 'Baseline file'
        );

        $this->configureHook();
    }

    abstract protected function configureHook(): void;

    /**
     * {@inheritdoc}
     */
    final protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $resultsFileName = $this->getFileName($input, self::RESULTS_FILE);
            $baseLineFileName = $this->getFileName($input, self::BASELINE_FILE);
            $projectRootAsString = $this->getOption($input, self::PROJECT_ROOT);

            $cwd = getcwd();
            if (false === $cwd) {
                throw new SarbException('Can not get current working directory. Specify project root with options: '.self::PROJECT_ROOT);
            }

            if (null === $projectRootAsString) {
                $projectRootAsString = $cwd;
            }

            $projectRoot = new ProjectRoot($projectRootAsString, $cwd);

            return $this->executeHook(
                $input,
                $output,
                $resultsFileName,
                $baseLineFileName,
                $projectRoot
            );
        } catch (InvalidConfigException $e) {
            $output->writeln("<error>{$e->getProblem()}</error>");

            return 2;
        } catch (FileImportException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return 3;
        } catch (SarbException $e) {
            $output->writeln("<error>Something went wrong: {$e->getMessage()}");

            return 4;
        } catch (Throwable $e) {
            // This should never happen. All exceptions should extend SarbException
            $output->writeln("<error>Unexpected critical error: {$e->getMessage()}</error>");

            return 5;
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param FileName $resultsFileName
     * @param FileName $baseLineFileName
     * @param ProjectRoot $projectRoot
     *
     * @throws SarbException
     * @throws InvalidConfigException
     * @throws FileImportException
     *
     * @return int
     */
    abstract protected function executeHook(
        InputInterface $input,
        OutputInterface $output,
        FileName $resultsFileName,
        FileName $baseLineFileName,
        ProjectRoot $projectRoot
    ): int;

    /**
     * @param InputInterface $input
     * @param string $argumentName
     *
     * @throws InvalidConfigException
     *
     * @return FileName
     */
    protected function getFileName(InputInterface $input, string $argumentName): FileName
    {
        return new FileName($this->getArgument($input, $argumentName));
    }

    /**
     * @param InputInterface $input
     * @param string $argumentName
     *
     * @throws InvalidConfigException
     *
     * @return string
     */
    protected function getArgument(InputInterface $input, string $argumentName): string
    {
        return $this->asString($input->getArgument($argumentName), $argumentName);
    }

    /**
     * @param InputInterface $input
     * @param string $optionName
     *
     * @throws InvalidConfigException
     *
     * @return string|null
     */
    protected function getOption(InputInterface $input, string $optionName): ?string
    {
        if (null === $input->getOption($optionName)) {
            return null;
        }

        return $this->asString($input->getOption($optionName), $optionName);
    }

    /**
     * @param mixed $value
     * @param string $errorMessageContext
     *
     * @throws InvalidConfigException
     *
     * @return string
     */
    private function asString($value, string $errorMessageContext): string
    {
        if (is_string($value)) {
            return $value;
        }
        throw new InvalidConfigException($errorMessageContext, 'Invalid value');
    }
}
