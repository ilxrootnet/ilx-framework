<?php


namespace Ilx\Module\Mailer;


use Ilx\Module\IlxModule;
use Ilx\Module\ModuleManager;

class MailerModule extends IlxModule
{

    function defaultParameters()
    {
        return [
            "host"  => "localhost",
            "port"  => 25,
            "username" => "user",
            "password" => "passw",
            "auth_mode" => "login",
            "encryption" => "tls",
            "source" => [
                "name" => "Ilx System",
                "address" => "sys@ilx.hu"
            ],
            "templates" => []
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
                "class_name" => MailerProvider::class,
                "parameters" => [
                    "host"      => $this->parameters["host"],
                    "port"      => $this->parameters["port"],
                    "user"      => $this->parameters["user"],
                    "password"  => $this->parameters["password"],
                    "auth_mode" => $this->parameters["auth_mode"],
                    "encryption"=> $this->parameters["encryption"],
                    "source"    => $this->parameters["source"],
                    "templates" => $this->parameters["templates"]
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

    public function addTemplate($template_name, $subject, $template_path) {
        $this->parameters[$template_name] = [
            Mailer::SUBJECT         => $subject,
            Mailer::TEMPLATE_PATH   => $template_path
        ];
    }
}
