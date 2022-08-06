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

$IDE = __DIR__."/..";

// IF the IDE subsystem dependencies are not installed...
if (!file_exists($IDE."/vendor"))
{
    // ...THEN change to the correct folder and install them!
    $owd = getcwd();
    chdir($IDE);
    exec("composer --ansi install", $output);
    chdir($owd);
}

require_once $IDE."/vendor/autoload.php";

// Remove our unused globally scoped variables to prevent pollution.
unset($IDE, $owd);

# IMPORTANT: Only now are our globals available!
