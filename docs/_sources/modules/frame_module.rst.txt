Frame modul
*************

A frame modul célja, hogy alap twig sablonokat biztosítson, amelyek kompatibilisek a többi modullal és emellett definiáljon
eg egyszerű oldal keretet, amit aztán már könnyen ki lehet egészíteni.


A használni kívánt frame-ket a 'frame_names'-ben lehet beállítani. Itt egy listát vár, mert elképzlehető egyszerre többre
is szükség van. Jelenleg csak a 'basic' template elérhető.

A default frame-t a 'default_frame'-ben lehet megadni. Itt a a frame_names-ben elehelyezett frame-k egyikére lehet hivatkozni.

Ha nem szeretnénk, hogy telepítéskor másolja is a frame fájlokat, akkor a copy_on_install-t false-ra kell állítani (ennek
amúgy ez a default értéke is). Ennek akkor van jelentősége, amikor egy kész rendszert akarunk feltelepíteni egy tetszőleges
erőforrásra és már nincs szükség a frame-kre mert azokat a fejlesztés során már elkészítettük.
copy_on_install = true esetén

page_title paraméterrel a html oldal title értékét lehet beállítani.
stylesheets paraméterben lehet megadni, hogy milyen css fájlokat kell beilleszteni az oldal forrásába
javascripts paraméterben lehet megadni, hogy milyen js fájlokat kell beilleszteni az oldal forrásába


Függőségek
=================

twig modul
menu modul


Importálás
========================


Paraméterek
========================


Használata
========================


