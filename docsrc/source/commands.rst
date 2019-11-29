.. _commands-label:

Parancsok
**********

.. toctree::
   :maxdepth: 1


.. _quick-start-cmd-label:

quick-start
============

A parancs segítséget nyújt a modules.json leíró összeállításában. A modules.json tartalmazza a szükséges információkat
az Ilx-Framework telepítéséhez.

A parancs futtatása során végighalad azokon a fő elemeken ami szükséges lehet az alkalmazás működéséhez és a megadott
inputok alapján készíti el a modules.json-t.

Kapcsolók és paraméterek
--------------------------

**Kötelező paraméter:**
    - nincsen

**Opcionális paraméterek:**
    - nincsen

**Kötelező kapcsolók:**
    - nincsen

**Opcionális kapcsolók:**
    - nincsen


Példák
--------

1. példa::

    php bin/ilx.php quick-start


.. _install-cmd-label:

install
=========

A parancs használatával telepíthető egy Ilx-Framework alapú alkalmazás. A parancs 1 darab kötelező inputot vár,
ami a modules.json fájl elérési útvonala.

Az alkalmazás az aktuális working directoryhoz képest fogja elhelyezni a fájlokat. Célszerű emiatt ugyanabból a
könyvtárból futtatni a parancsot, ahol a modules.json leírónk is van.

Kapcsolók és paraméterek
--------------------------

**Kötelező paraméter:**
    - *modules_config:* a modules.json fájl elérési útvonala, ami alapján frissítenénk az alkalmazást

**Opcionális paraméterek:**
    - nincsen

**Kötelező kapcsolók:**
    - nincsen

**Opcionális kapcsolók:**
    - nincsen


Példák
--------

1. példa::

    php bin/ilx.php install modules.json


.. _update-cmd-label:

update
========

A paraméterben megadott modules.json alapján frissíti az alkalmazást. Az update futattásánál alapvetően csak a
Kodi konfiguráció frissül. Ha szeretnénk valamilyen resource-t is frissíteni vagy modul inicializáló szkripteket
futtatni, akkor mellékelni kell a parancs mellé a megfelelő kapcsolót.


Kapcsolók és paraméterek
--------------------------

**Kötelező paraméter:**
    - *modules_config:* a modules.json fájl elérési útvonala, ami alapján frissítenénk az alkalmazást

**Opcionális paraméterek:**
    - nincsen

**Kötelező kapcsolók:**
    - nincsen

**Opcionális kapcsolók:**
    - *-r (--run_scripts):* Végrehajtja a modulok initscriptjeit is.
    - *-t (--include_templates):* Frissíti/Átmásolja a resource-okat is.


Példák
--------

Példa egyszerű futtatásra::

    php bin/ilx.php update modules.json

Példa futtatásra init szkriptekkel::

    php bin/ilx.php update modules.json -r

Példa futtatásra init szkriptekkel és resource másolással::

    php bin/ilx.php update modules.json -r -t

