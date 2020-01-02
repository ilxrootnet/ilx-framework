SecurityModule
****************

A SecurityModule feladata egy alkalmazáshoz kapcsolódó authentikáció és authorizáció megvalósítása. Az alábbiakban a
modul funkcionalitásának részletes bemutatása következik. Első lépésben az alap beállításokat mutatjuk be, ezután az
authentikációs lehetőségek következnek végén pedig az authorizáció működését mutatjuk be.


Függőségek
=================
Itt azoknak a moduloknak a listáját találod, amelyek szükségesek a SecurityModule működéséhez:

* DatabaseModule *(kötelező)*
* FrameModule *(opcionális)*
* MailerModule *(opcionális)*


Importálás
========================

A modules.json-höz egyszerűen hozzá kell adni a következő bejegyzést:

.. code-block::

    "Security": []

*Ebben az esetben a modul egyszerű jelszavas authentikációt biztosít, authorizáció nélkül, 900 másodperces session lifetime-mal (15 perc).*

Paraméterek
========================

* admin: Admin email cím amit a rendszer telepítésénél regisztrál be
* auth_modes: Használható authentikációs módok felsorolása
* auth_selector: Authentikációs mód választó neve vagy hivatkozás a metódusra
* sess_exp_time: Session lifetime
* permissions: Route-okra definiált jogosultságok.

Paraméter: admin
-----------------

A AUTH_REMOTE authentikációs módot leszámítva minden esetben szükség lehet egy admin felhasználó beállítására. Az admin
felhasználót az alkalmazás telepítésénél generálja a modul. Azért, hogy ne legyen beégetve jelszó a konfigurációs fájlba
az admin felhasználóhoz csak egy email cím megadása a szükséges. Az alkalmazás indítása után az elfelejtett jelszó
funkciót használva lehet jelszót beállítani az admin felhasználónak. (Az admin felhasználó a telepítéskor jön létre.)


Például, ha azt szeretnénk, hogy az admin felhasználóhoz tartozó email cím a admin@mysystem.com legyen:

.. code-block::

    "Security": {
        "admin": "admin@mysystem.com",
    }




Paraméter: auth_modes
-----------------------

Az auth_modes paraméterrel lehet beállítani, hogy az alkalmazásban milyen authentikácós módokat tudnak használni a
felhasználók. Az authentikációs módok közül párhuzamosan több is használható. Azt, hogy éppen melyik legyen aktív a
auth_selector paraméter beállításával tudjuk módosítani. A következő authentikációs módokat támogatja:

* AUTH_BASIC: Alap felhasználónév és jelszó authentikáció
* AUTH_TWO_FACTOR: Alap authentikáció két-faktorossal kiegészítve
* AUTH_JWT: JSON Web Token authentikáció
* AUTH_REMOTE: Távoli rendszeren keresztüli authentikáció

Az authentikációs módokról és paraméterezési lehetőségeiről bővebben az :ref:`auth-label` alfejezetben olvashatsz.

Az előző példát folytatva, csak AUTH_BASIC-et használva (alapméretezett beállításaival):


.. code-block::

    "Security": {
        "admin": "admin@mysystem.com",
        "auth_modes": [
            "auth_basic": []
        ]
    }





.. _auth-label:

Authentikáció
========================

TYPE_BASIC
------------
