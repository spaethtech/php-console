<?php /** @noinspection PhpUnused */
declare(strict_types=1);

namespace SpaethTech\Console\Commands;

use ReflectionClass;
use ReflectionException;
use SpaethTech\Support\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * BaseCommand
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 *
 */
abstract class BaseCommand extends Command
{
    protected const NAMESPACE_PATTERN = "#^\s*namespace\s*([\w\\\d_-]*).*$#m";

    protected SymfonyStyle $io;

    private string $owd;
    private string $cwd;

    /**
     * @param string|NULL $name
     */
    public function __construct(string $name = NULL)
    {
        parent::__construct($name);

        $this->owd = getcwd();
    }

    /**
     *
     */
    public function __destruct()
    {
        chdir($this->owd);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * Fixes some formatting issues with the built-in error() function.
     *
     * @param string $message   The message to display
     * @param bool $die         Optionally, calls die() after displaying the message
     *
     * @return void
     */
    protected function error(string $message, bool $die = FALSE): void
    {
        $this->io->newLine();
        $this->io->writeln("<error> [ERROR] $message</>");
        $this->io->newLine();

        if($die)
            die();
    }

    /**
     * @param string $command
     *
     * @return string
     */
    protected function getVendorBin(string $command = ""): string
    {
        return FileSystem::path(PROJECT_DIR."/vendor/ide/$command");
    }

    /**
     * @param string $dir
     *
     * @return string
     */
    protected function chdir(string $dir = ""): string
    {
        return ($dir === "") ? $this->cwd : (chdir($this->cwd = FileSystem::path($dir)) ? $this->cwd : $dir);
    }

    /**
     * Loads all commands from the specified path.
     *
     * @param string $path The path to a directory with zero or more commands to load.
     *
     * @return array Returns an array of instantiated classes or an empty array on failure or no classes found.
     */
    public static function loadFromDirectory(string $path): array
    {
        // NOTE: When should simply return an empty array on failure!

        $commands = [];

        if (!($path = realpath($path)) || !is_dir($path))
            return [];

        foreach(FileSystem::scan($path) as $file)
        {
            $contents = file_get_contents($path.DIRECTORY_SEPARATOR.$file);

            if (!preg_match(self::NAMESPACE_PATTERN, $contents, $matches) || count($matches) < 2)
                continue;

            // Construct the fully qualified class name.
            $class = $matches[1] . "\\" . (str_replace(".php", "", $file));

            try
            {
                // Attempt to reflect the class.
                $reflected = new ReflectionClass($class);

                // IF the current class is abstract, we can't instantiate, so skip!
                if($reflected->isAbstract())
                    continue;
            }
            catch (ReflectionException $e)
            {
                echo "Unable to load $class, skipping\n";
                continue;
            }

            // We should be able to instantiate and add the Command at this point.
            $commands[] = new $class();
        }

        return $commands;
    }

}
