<?php
declare(strict_types=1);

/**
 * IMPORTANT: All custom commands should "require" this script.
 *
 * @author Ryan Spaeth <rspaeth@spaethtech.com>
 * @copyright 2022 Spaeth Technologies Inc.
 */

// IF this script is not being called from the CLI, THEN exit!
if (php_sapi_name() !== "cli")
    exit;

echo PROJECT_DIR;
exit;

//$IDE = __DIR__."/..";
if (!($ide = realpath( PROJECT_DIR ."/ide"))
{
 echo "Test;"
}

// IF the IDE subsystem dependencies are not installed...
if (!file_exists($ide."/vendor"))
{
    // ...THEN change to the correct folder and install them!
    $owd = getcwd();
    chdir($ide);
    exec("composer --ansi install");
    chdir($owd);
}

require_once $ide."/vendor/autoload.php";

// Remove our unused globally scoped variables to prevent pollution.
unset($ide, $owd);

# IMPORTANT: Only now are our globals available!
