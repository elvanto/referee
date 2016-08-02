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
 * Extract global variables from static class methods and form
 * a constructor to manage their use as instance variables.
 */
class ExtractClassCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('extract-globals')
            ->setDescription('Extract global variables from static class methods')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'File containing class with static methods'
            )
            ->addArgument(
                'search',
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Paths to search for static method usage'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');
        $fs = new Filesystem();
        $finder = new Finder();
    }
}
