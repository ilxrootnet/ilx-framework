
Resource-ok kezelése a deployment során
===========================================

Amikor készen van az alkalmazásunk és szeretnénk azt tetszőleges helyen deploy-olni felmerül a probléma, hogy a
projekt fejlesztése során hozzányúltunk más modulok resource fájljaihoz, amiknek a változtatásai nem fognak megjelenni
a telepítés során. Miért? Amikor lefuttatjuk az install parancsot::

    php bin/ilx.php install modules.json


a keretrendszer végighalad a beállított modulokon, inicializálja őket, feloldja a függőségeket, lefuttatja a telepítő
szkripteket és ami számunkra probléma, átmásolja a megfelelő helyekre a sablon fájlokat. Ez azt jelenti, hogy
alapértelmezetten nem fognak megjelenni azok a változtatásokat, amiket mi más modulok sablonjain végrehajtottunk.