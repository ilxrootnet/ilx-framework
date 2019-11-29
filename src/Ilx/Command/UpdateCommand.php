<?php


namespace Ilx\Command;


use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;
use GetOpt\Option;
use Ilx\Ilx;

/**
 * Class UpdateCommand
 *
 * A paraméterben megadott modules.json alapján frissíti az alkalmazást. Az update futattásánál alapvetően csak a
 * Kodi konfiguráció frissül. Ha szeretnénk valamilyen resource-t is frissíteni vagy modul inicializáló szkripteket
 * futtatni, akkor mellékelni kell a parancs mellé a meglfelő kapcsolót.
 *
 * Például:
 *  # egyszerű futtatás
 *  php bin/ilx.php update modules.json
 *
 *  # futtatás init szkriptekkel
 *  php bin/ilx.php update modules.json -r
 *
 *  # futtatás init szkriptekkel és resource másolással
 *  php bin/ilx.php update modules.json -r -t
 *
 *
 *
 * Kötelező paraméter:
 *  - modules_config: a modules.json fájl elérési útvonala, ami alapján frissítenénk az alkalmazást
 *
 * Opcionális paraméterek:
 *  nincsen
 *
 * Kötelező kapcsolók:
 *  nincsen
 *
 * Opcionális kapcsolók:
 *  -r (--run_scripts): Végrehajtja a modulok initscriptjeit is.
 *  -t (--include_templates): Frissíti/Átmásolja a resource-okat is.
 *
 * @package Ilx\Command
 */
class UpdateCommand extends Command
{
    public function __construct()
    {
        parent::__construct('update', [$this, 'handle']);

        $this->addOperands([
            Operand::create('modules_config', Operand::REQUIRED)
                ->setDescription("Path to updated modules.json")
        ]);

        $this->addOptions([
            Option::create('r', 'run_scripts', GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription("Rerun module's install scripts."),
            Option::create('t', 'include_templates', GetOpt::OPTIONAL_ARGUMENT)
                ->setDescription('Refresh the view templates and resources like css an js sources.'),
        ]);

    }

    public function handle(GetOpt $getOpt)
    {
        Ilx::update(
            $getOpt->getOperand('modules_config'),
            $getOpt->getOption("run_scripts") ? true : false,
            $getOpt->getOption("include_templates") ? true : false);
    }
}