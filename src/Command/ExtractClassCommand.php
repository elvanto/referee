<?php

namespace Referee\Command;

use Referee\Transformation\ExtractClassTransformation;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Extract a static class from a collection of functions.
 */
class ExtractClassCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('extract-static')
            ->setDescription('Extract a static class definition from a function file')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'File containing function definitions'
            )
            ->addArgument(
                'classname',
                InputArgument::REQUIRED,
                'Fully qualified name for the generated static class'
            )
            ->addArgument(
                'search',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Paths to search for function usage'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Report changes that would be made without changing files'
            )
            ->addOption(
                'exclude',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Specify a directory to exclude when replacing usage'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $fs = new Filesystem();
        $finder = new Finder();

        /* Parse class name */
        $qualified = explode('\\', $input->getArgument('classname'));
        $class_name = array_pop($qualified);
        $namespace = implode('\\', $qualified);

        if (empty($namespace)) {
            $output->writeln('<error>Class name must include namespace</error>');
            return 1;
        }

        /* Open source file */
        if (!$fs->exists($filename)) {
            $output->writeln("<error>File $filename doesn't exist</error>");
            return 1;
        }

        /* Replace function definitions with a static class */
        $transformation = new ExtractClassTransformation($namespace, $class_name);
        $source = file_get_contents(realpath($filename));
        $class_file = $transformation->transform($source);

        /* Replace function usage with static calls */
        $functions = $transformation->getFunctionNames();

        $finder->files();
        foreach ($input->getArgument('search') as $dir) {
            $finder
                ->in(realpath($dir))
                ->name('*.php');
        }

        foreach ($input->getOption('exclude') as $exclude) {
            $finder->exclude($exclude);
        }

        foreach ($finder as $file) {
            try {
                $changes = 0;
                $content = $file->getContents();
                foreach ($functions as $function) {
                    $content = preg_replace(
                        "/(?<=[^:>])\b$function\(/",
                        "\\$namespace\\$class_name::$function(",
                        $content, -1, $replacements
                    );

                    $changes += $replacements;
                }

                if ($changes > 0) {
                    $output->writeln(
                        '<info>' .
                        $file->getRelativePathname() . 
                        '</info>: ' . 
                        $changes .
                        ' calls replaced'
                    );

                    if (!$input->getOption('dry-run')) {
                        $fs->dumpFile(
                            $file,
                            $content
                        );
                    }
                }
            } catch (\Exception $e) {
                $output->writeln('<error>' . $file . ': ' . $e->getMessage() . '</error>');
            }
        }

        /* Write changes to the source file */
        if (!$input->getOption('dry-run')) {
            $fs->dumpFile($filename, $class_file);
        }
    }
}
