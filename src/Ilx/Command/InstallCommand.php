<?php


namespace Ilx\Command;


use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;
use Ilx\Ilx;

/**
 * Class InstallCommand
 *
 * A parancs használatával telepíthető egy Ilx-Framework alapú alkalmazás. A parancs 1 darab kötelező inputot vár,
 * ami a modules.json fájl elérési útvonala.
 *
 * Az alkalmazás az aktuális working directoryhoz képest fogja elhelyezni a fájlokat. Célszerű emiatt ugyanabból a
 * könyvtárból futtatni a parancsot, ahol a modules.json leírónk is van.
 *
 * Például:
 *  php bin/ilx.php install modules.json
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
 *  nincsen
 *
 * @package Ilx\Command
 */
class InstallCommand extends Command
{
    public function __construct()
    {
        parent::__construct('install', [$this, 'handle']);

        $this->addOperands([
            Operand::create('modules_config', Operand::REQUIRED)
                ->setDescription("Path to modules.json")
        ]);
    }

    public function handle(GetOpt $getOpt)
    {
        Ilx::install($getOpt->getOperand('modules_config'));
    }
}