<?php


namespace Ilx\Module\Logger;


use Ilx\Ilx;
use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;
use Kodiak\ServiceProvider\MonologProvider\MonologProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerModule extends IlxModule
{

    function defaultParameters()
    {
        return [
            "path" => Ilx::logPath().DIRECTORY_SEPARATOR."ilx.log",
            "level"=> Logger::INFO
        ];
    }

    function environmentalVariables()
    {
        return [];
    }

    function routes()
    {
        return [];
    }

    function serviceProviders()
    {
        return [
            [
                "class_name" => MonologProvider::class,
                "parameters" => [
                    [
                        'name'      => 'ilx_sys',
                        'handlers'  => [
                            [
                                'class_name'=> StreamHandler::class,
                                'file_path' => $this->parameters["path"],
                                'log_level' => $this->parameters["level"]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    function hooks()
    {
        return [];
    }

    function bootstrap(ModuleManager $moduleManager)
    {

    }

    function initScript($include_templates)
    {

    }
}