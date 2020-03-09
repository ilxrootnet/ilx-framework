Theme modul (Frontend kezelése)
***************************************

A megjelenítés alapegységét a rendszerben témának nevezzük.
Egy téma feladata, hogy összefogja azokat a forrásfájlokat, amelyek egy adott stílus megjelenítéséhez kellenek.
A forrásfájlok kiterjesztése tetszőleges, nincs velük kapcsolatban megkötés.


A témához kapcsolódó összes fájl egy közös gyökér mappában van elhelyezve, amin belül meghatározott almappákba kerülhetnek a forrásfájlok.
A gyökér mappán belül található a témát leíró osztály, ami a Theme absztrakt osztályból származik le.

Ennek a dokumentumnak nem célja az implementációs feladatok leírása, hanem csak a fő irányelvek bemutatása.
Implementációs kérdésekre a Theme osztályban találsz választ.
A forrásfájlokat oldal keretek, egyedi js és css fájlok, egyéb forrásfájlok csoportokba soroljuk.
A továbbiakban ezeknek a bemutatása következik.

Oldal keretek
==============

Az oldal keretek olyan twig fájlok, amik leírják egy weboldal megjelenését, illetve definiálják a dinamikus tartalom helyét.
Egy témához egyszerre több keret is tartozhat.
A kereteknek a téma mappáján belül a :code:`/frames/` almappában kell elhelyezkedniük.
Minden kerethez tartozik:

 - egy név, ami alapján azonosítható
 - egy fájl elérési útvonal, ami a  :code:`/frames/` almappán belüli relatív elérési útvonal

Az rendszer ezeket a keretek az alkalmazás gyökér view mappáján belül a :code:`/theme_name/` almappába fogja átmásolni (vagy linkelni ha az lett beállítva).
Így biztosan nem lesz névütközés a twig fájlok között.

.. note::
    A témán belüli frames mappa tartalmazhat más view fájlokat (vagy almappákat) is, amiket a behivatkozott oldal keretek használnak.
    Ez a témát használó fejlesztő számára amúgy sem látható, ő a témára csak a neve segítségével hivatkozik.

.. warning::
    A téma nem foglalkozik azzal, hogy az éppen szükséges js és/vagy css fájlokat betöltse a html kódba.
    A szükséges js,css és egyéb forrásfájlok linkelését statikusan kell megoldani az aktuális frame twig-en belül.


Egyedi js és css fájlok
==========================

Az egyedi js és css fájlok esetén olyan fájlokról beszélünk, amik kifejezetten az adott témához lettek fejlesztve és a témához kapcsolódóan aktív fejlesztés alatt állnak.
Ebbe nem tartoznak bele a különböző pluginek vagy más third-party package-k.
Feltételezve, hogy ezekből a fájlokból nincsen sok, egyesével kell őket megadni a js és css fájlok esetén is az abszolút elérési útvonalukkal.
Ezekből a rendszer, minden update parancs lefutása esetén egy minified js/css fájlt készít, amit a projekt web mappája alatt:

 - js fájlok esetén a :code:`/js/theme_name.min.js`
 - css fájlok esetén a :code:`/css/theme_name.min.css`

fájlokban helyez el.
A rendszer támogatja a less fájlok kezelését is.
Minden megadott less fájl esetén egy plusz lépésben átfordítja őket egy css fájlá majd őt is beleveszi a minified verzióba.
Ha esetleg nincsenek megadva css vagy js fájlok, akkor az adott minified fájl nem fog létrejönni.
Az egyedi js és css fájlok a téma gyökér mappájában ajánlottan a js és css almappákba lehet elhelyezni, de mivel abszolút elérési útvonalakat használunk ez nem kötelező.


Egyéb forrásfájlok
=====================

Az egyéb forrásfájlokba minden olyan fájl besorolható, amely nem tartozott az egyik megelőzőbe sem. Például plugin-ek js, plugin-ek css fájljai, képek, fontok, stb. Mivel ezek kezelése nagyon komplikált lenne, ha dinamikusan akarnánk őket kezelni, emiatt ezeket kötelezően a téma mappájának resources/ almappájában kell elhelyezni, amit aztán a rendszer a  /resources/theme_name almappába fog átmásolni (vagy linkelni ha az lett beállítva) a web mappába.


Függőségek
=================
Itt azoknak a moduloknak a listáját találod, amelyek szükségesek a SecurityModule működéséhez:

* TwigModule *(kötelező)*


Importálás
========================

A modules.json-höz egyszerűen hozzá kell adni a következő bejegyzést:

.. code-block::

    "Theme": []


Paraméterek
========================

.. code-block::

    /*
     * Létező erőforrások felülírása
     *
     * Ha igaz, az install és update fázisban is felülírja a már létező erőforrásfájlokat (css, js, image fájlok).
     * Célszerű false értéken tartani és csak indokolt esetben változtatni.
     */
    ThemeModule::OVERWRITE => false,

    /*
     * Témák listája
     *
     * A téma listában felsorolt témák lesznek elérhetőek az alkalmazásban.
     * Itt hivatkozhatunk vagy egy ismert téma nevére ($themes_mapping)-ben definiáltak, vagy egy általunk definiált sémára
     * aminek megadjuk az osztályának a nevét namespace-szel együtt.
     *
     */
    ThemeModule::THEMES => [
        [BasicTheme::class, ResourcePath::SOFT_COPY]
    ],

    /*
     * Az alapméretezett frame neve.
     *
     * A frame névnek szerepelnie kell a témák által definiált frame nevek között.
     * Ha egy twig renderhez nincsen beállítva, hogy azt milyen keretben kell megjeleníteni, akkor ezt a témát
     * fogja használni
     */
    ThemeModule::DEFAULT_FRAME => "basic",

    /*
     * Az authentikációhoz használt téma osztályána kneve.
     *
     * A ThemeModule ez alapján határozza meg, hogy melyik témával és formokkal kell megjeleníteni az
     * authentikációs felületeket.
     *
     * Ha egyik téma sem megfelelő saját témát kell készíteni, majd azt beállítani
     */
    ThemeModule::AUTH_THEME => BasicTheme::class,

    /*
     * A megjelenített cím
     */
    ThemeModule::PAGE_TITLE  => "PageTitle",

Használata
========================


