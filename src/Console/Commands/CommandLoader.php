<?php
/** @noinspection PhpUnused */
declare(strict_types=1);

namespace SpaethTech\Console\Commands;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use SpaethTech\Console\Commands\Exceptions\LoaderNotReadyException;
use SpaethTech\Console\Commands\Exceptions\ModuleNotFoundException;
use SpaethTech\Support\FileSystem;
use Symfony\Component\Console\Application;

class CommandLoader
{
    protected Application $application;
    protected bool $useExceptions;
    protected ?string $path = NULL;
    protected ?string $namespace = NULL;

    /**
     * @param Application $application The associated Console Application.
     * @param bool $useExceptions Whether to throw Exceptions on errors.
     */
    public function __construct(Application $application, bool $useExceptions = TRUE)
    {
        $this->application = $application;
        $this->useExceptions = $useExceptions;
    }

    /**
     * Sets the base path for the CommandLoader.
     *
     * @param string $path The path at which to begin scanning for Commands.
     *
     * @return CommandLoader for method chaining.
     */
    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @throws LoaderNotReadyException
     */
    public function getPath(): ?string
    {
        if ($this->path === null)
        {
            $message = "Use CommandLoader::setPath() prior to CommandLoader::getPath()";
            if ($this->useExceptions)
            {
                throw new LoaderNotReadyException("\n$message");
            }
            else
            {
                echo "$message\n";
                return NULL;
            }
        }

        $path = FileSystem::path($this->path);

        if (!($real = realpath($path)))
        {
            if ($this->useExceptions)
                throw new LoaderNotReadyException("\nCommandLoader path is invalid!");
            else
                return NULL;
        }

        return $real;
    }

    /**
     * @throws ModuleNotFoundException
     */
    public function getModulePath(string $module): ?string
    {
        if ($module === "")
        {
            $message = "Module must be provided!";
            if ($this->useExceptions)
            {
                throw new InvalidArgumentException("\n$message");
            }
            else
            {
                echo "$message\n";
                return NULL;
            }
        }

        $path = FileSystem::path("$this->path/$module");

        if (!($real = realpath($path)))
        {
            $message = "Could not find a Module at $path!";
            if ($this->useExceptions)
            {
                throw new ModuleNotFoundException("\n$message");
            }
            else
            {
                echo "$message\n";
                return NULL;
            }
        }

        return $real;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }


    /**
     * @param string $caller
     *
     * @return bool
     * @throws LoaderNotReadyException
     */
    protected function isReady(string $caller = "getModuleCommands"): bool
    {
        $class = basename(__CLASS__);
        $function = "";

        if ($this->path === null)
            $function = "$class::setPath()";

        if ($this->namespace === null)
            $function = "$class::setNamespace()";

        if ($function && $this->useExceptions)
            throw new LoaderNotReadyException("\nUse $function prior to $class::$caller()");

        return ($function === "");
    }


    /**
     * Loads all commands for the specified module.
     *
     * @param string $module The base Command module for scanning
     * @param callable|null $filter A function to filter the scanned classes
     *
     * @return array
     * @throws ModuleNotFoundException
     * @throws LoaderNotReadyException
     */
    public function getModuleCommands(string $module, callable $filter = null): array
    {
        //if(!$this->isReady(__FUNCTION__))
        //    return [];

        //$path = FileSystem::path($this->getPath()."/$module");
        $path = $this->getModulePath($module);

        if($path === null)
            return [];

        //if ($module == "" || !realpath($path))
        //    throw new ModuleNotFoundException("Could not find module at $path");

        $commands = [];

        foreach(FileSystem::scan($path) as $file)
        {
            $file = str_replace("/", "\\", $file);

            // Construct the fully qualified class name.
            $fqcn = "$this->namespace\\$module\\".(str_replace(".php", "", $file));

            try
            {
                // Attempt to reflect the class.
                $reflected = new ReflectionClass($fqcn);

                // IF the current class is abstract, we can't instantiate, so skip!
                if($reflected->isAbstract() || ($filter !== null && !$filter($reflected)))
                    continue;
            }
            catch (ReflectionException $e)
            {
                echo "Unable to load $fqcn, skipping\n";
                continue;
            }

            // We should be able to instantiate and add the Command at this point.
            $commands[] = new $fqcn();  //$application->add(new $fqcn());
        }

        return $commands;
    }


}
