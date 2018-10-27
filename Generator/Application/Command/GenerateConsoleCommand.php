<?php

namespace Sfynx\CoreBundle\Generator\Application\Command;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

use Sfynx\CoreBundle\Generator\Domain\Report\Exception\ReportException;
use Sfynx\CoreBundle\Generator\Domain\Widget\Exception\WidgetException;
use Sfynx\CoreBundle\Generator\Domain\Widget\WidgetParser;
use Sfynx\CoreBundle\Generator\Application\Config\Config;
use Sfynx\CoreBundle\Generator\Application\Config\Validator;
use Sfynx\CoreBundle\Generator\Application\Config\Exception\ConfigException;
use Sfynx\CoreBundle\Generator\Application\Config\Parser;
use Sfynx\CoreBundle\Generator\Domain\Component\Xmi\Php2Xmi;

/**
 * Class GenerateConsoleCommand
 * @package Sfynx\CoreBundle\Generator\Application\Command
 */
class GenerateConsoleCommand implements ConsoleCommandInterface
{
    /** @var array  */
    protected $arguments;
    /** @var OutputInterface */
    protected $output;

    /**
     * GenerateConsoleCommand constructor.
     * @param array $args
     * @param OutputInterface $output
     */
    public function __construct(array $args, OutputInterface $output)
    {
        $this->arguments = $args;
        $this->output = $output;
    }

    /**
     * @return array
     */
    public function process(): array
    {
        // initialize parameters
        $isFolderScan = true;
        $confFile = null;
        $confFiles = [];
        $confDir = $this->arguments['conf-dir'];
        $configDirectories = \array_map('realpath', \explode(',', $confDir));

        if (empty($confDir)) {
            // use only a single file
            $confFile = \realpath($this->arguments['conf-file']);

            if (!\file_exists($confFile)) {
                throw new \InvalidArgumentException(
                    sprintf('Configuration file %s does not exists.', $confFile)
                );
            }

            $confDir = \dirname($confFile);
            $isFolderScan = false;
        }

        // run parse file for all directories
        foreach ($configDirectories as $confDir) {
            foreach ($this->fileProvider($confDir, $confFile) as $yamlFile) {
                $confFiles[] = $yamlFile;

                $this->run($configDirectories, $yamlFile);
            }
        }

        return $confFiles;
    }

    /**
     * @param $configDirectory
     * @param null $confFile
     * @return \Generator
     */
    protected function fileProvider($configDirectory, $confFile = null)
    {
        if (empty($confFile)) {
            $finder = new Finder();
            $finder
                ->ignoreUnreadableDirs()
                ->in($configDirectory)
                ->depth('== 0')
                ->files()
                ->name('*.yml');

            foreach ($finder as $file) {
                $confFile = $file->getRealPath();
                yield $confFile;
            }
        } else {
            yield $confFile;
        }
    }

    /**
     * @param string $configDirectories
     * @param string $file
     */
    protected function run($configDirectories, string $file): void
    {
        // initialize Config instance
        $config = (new Parser())->parse($this->arguments);

        // initialize directories path to parse
        $config->set('conf-directories', $configDirectories);

        // initialize conf-file
        $config->set('conf-file', $file);

        // parse validators
        try {
            (new Validator())->validate($config);
        } catch (ConfigException $e) {
            $this->output->writeln(\sprintf("<error>%s</error>", $e->getMessage()));
            exit(1);
        }

        // parse widgets
        try {
            $reporter = (new WidgetParser($config, $this->output))->run();
        } catch (WidgetException $e) {
            $this->output->writeln(\sprintf('<error>%s</error>', $e->getMessage()));
            exit(1);
        }
        $this->output->writeln('<info>++</info> Executing system analyzes...');

        // create artifact
        try {
            $reporter->generate();
        } catch (ReportException $e) {
            $this->output->writeln(\sprintf('<error>%s</error>', $e->getMessage()));
            exit(1);
        }

        // generate xmi if wanted
        if ($config->has('report-xmi')) {
            Php2Xmi::php2xmi_main($this->output, $this->config->get('report-xmi'));
        }
    }
}
