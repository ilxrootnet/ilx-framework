Resource-ok kezelése
*************************

Először is fontos tisztázni, hogy mit is értünk resource alatt.
Az Ilx-Framework-ben minden olyan fájlt, aminek a kiterjesztése nem .php resource-nak (erőforrásnak) tekintünk. Például:

    * css fájlok
    * js fájlok
    * twig fájlok
    * .jpg, .jpeg, .gif, .png állományok
    * stb.

A resource-ok két forrásból érkezhetnek:
    1. egy projektben használt modulból
    2. a projekt saját moduljából

A második mód kvázi nyilvánvaló, hiszen a projektjeink nagy részénél előfordul, hogy szükséges saját twig sablonokat
készíteni, egyedi design biztosítani, stb.

Az első módra pedig jó példa, az authentikációs modul, ami a regisztrációhoz, bejelentkezéshez vagy az elfelejtett
jelszóhoz egy-egy sablon .twig fájlt biztosít.



Ezeknek a fájloknak megfelelő menedzselése nem egyszerű feladat, hiszen egy projekt életciklusa során gyakran előfordul,
hogy módosítani szeretnénk egy használt modul sablonjait, ami sok kérdést vethet fel:

    * Mi történik akkor, ha frissíteni szeretnék egy modult? Ilyenkor elvesznek a változtatásaim?
    * Mi történik akkor, ha a projektemet telepíteni szeretném egy új környezetben? A más modulokon végzett változtatások hogyan jelennek meg majd meg?


Resource-ok kezelése a fejlesztés közben
===========================================


    * erőforrások linkelése (hardlink) igény szerint
    * legyen soft_copy és hard_copy:
        * soft copy esetén csak egy linket állítunk be a forrásfájlra: ez akkor frankó, ha nem akarjuk folyton updatelni aprojektet hogy megjelennek a módosított forrásfájlok
        * hard copy esetén ténylegesen átmásol
    * még azt kell megoldani, hogy az egyes template másolások ne mindig történjenek meg
    * TODO: megnézni, hogy lehet e könyvtárat linkelni






















