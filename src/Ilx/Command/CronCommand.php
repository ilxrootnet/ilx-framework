<?php


namespace Ilx\Command;


use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;
use Ilx\Ilx;

class CronCommand extends Command
{
    public function __construct()
    {
        parent::__construct('cron', [$this, 'handle']);

        $this->addOperands([
            Operand::create('cron_configuration', Operand::REQUIRED)
                ->setDescription("Path to a json file which contains all parameters required to")
        ]);
    }

    public function handle(GetOpt $getOpt)
    {
        $config = json_decode(file_get_contents($getOpt->getOperand('cron_configuration')), true);
        Ilx::run(true, $config);
    }
}