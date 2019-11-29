<?php

use GetOpt\GetOpt;
use GetOpt\ArgumentException;
use GetOpt\ArgumentException\Missing;
use Ilx\Command\InstallCommand;
use Ilx\Command\QuickStartCommand;
use Ilx\Command\UpdateCommand;

require_once getcwd() . '/vendor/autoload.php';


$getOpt = new GetOpt();
$getOpt->addCommand(new InstallCommand());
$getOpt->addCommand(new UpdateCommand());
$getOpt->addCommand(new QuickStartCommand());


try {
    try {
        $getOpt->process();
    } catch (Missing $exception) {
        // catch missing exceptions if help is requested
        if (!$getOpt->getOption('help')) {
            throw $exception;
        }
    }
} catch (ArgumentException $exception) {
    file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
    echo PHP_EOL . $getOpt->getHelpText();
    exit;
}

// show help and quit
$command = $getOpt->getCommand();
if (!$command || $getOpt->getOption('help')) {
    echo $getOpt->getHelpText();
    exit;
}
call_user_func($command->getHandler(), $getOpt);