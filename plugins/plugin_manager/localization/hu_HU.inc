<?php
# 
# This file is part of Roundcube "plugin_manager" plugin.
# 
# Your are not allowed to distribute this file or parts of it.
# 
# This file is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
# 
# Copyright (c) 2012 - 2014 Roland 'Rosali' Liebl - all rights reserved.
# dev-team [at] myroundcube [dot] net
# http://myroundcube.com
# 

$labels=array();
$labels['pluginname']='Beépülőmodul kezelő';
$labels['plugindescription']='A beépülőmodul kezelő lehetőséget ad a beépülőmodulok felhasználói szintű engedélyezésére, tiltására. Emelett segíti a rendszgazdákat a beépülők telepítésében és frissen tartásában. A modul nem végez módosításokat a fáljrendszeren, illetve nem telepít semmit automatikusan.';
$labels['plugin_manager_title']='Beépülő modulok kezelése';
$labels['plugin_manager_center']='Beépülőmodul kezelő központ';
$labels['updatepm']='Beépülőmodul kezelőhöz frissítés érhető el';
$labels['updatepmrequired']='A beépülőmodul kezelőt frissíteni kell.';
$labels['downloadnow']='Letöltés';
$labels['misc']='Egyebek';
$labels['downloads']='letöltések';
$labels['issue']='Hibajelentés';
$labels['submitissue']='Hibajelentés. Kérlük ellenőrizze a hibanaplót és a relevéns információkat írja a jegybe. Megjegyzés: Google Account szükséges.';
$labels['showall']='Összes beépülőmodul megjelenítése';
$labels['hideuptodate']='Csak a frissítések megjelenítése';
$labels['connectionerror']='A távoli szerver nem válaszolt a csatlakozési kérésre.';
$labels['trylater']='Próbálkozzon később...';
$labels['translate']='Beépüélőmodul nyelvének fordítása';
$labels['restoredefaults']='Alapbeállítások visszaállítása';
$labels['checkall']='Mindegyik';
$labels['uncheckall']='Semelyik';
$labels['toggle']='Kijelölés megfordítása';
$labels['status']='Státusz';
$labels['globalplugins']='Globális beépülők';
$labels['backend']='Server beépülők';
$labels['messagesmanagement']='Üzenetkezelés';
$labels['furtherconfig']='Most szeretné konfigurálni a beépülőt?';
$labels['uninstall']='A modul kikapcsolása a hozzá kapcsolodó összes beállítást véglegesen törli. Ennek tudatában is kikapcsolod?';
$labels['areyousure']='Ez a lépés nem visszaállítható, biztos, hogy folytatja?';
$labels['successfullydeleted']='A beállítások sikeresen eltávolításra kerültek.';
$labels['successfullysaved']='Sikeresen elmentve.';
$labels['errorsaving']='Hiba lépett fel a mentés közben.';
$labels['yes']='Igen';
$labels['no']='Nem';
$labels['disable']='Letiltva';
$labels['remove']='Eltávaolítás';
$labels['savewarning']='El akarok menteni a változásokat?';
$labels['authors_comments']='A szerző megjegyzései (nincs lefordítva)';
$labels['requiredby']='Erre a beépülőre az alábbiaknak van szüksége:';
$labels['requires']='Ez a beépülő a következőket igényli:';
$labels['recommended']='Ajánlott beépülők';
$labels['update_plugins']='Beépülő letöltések';
$labels['ziparchive']='ZIP letöltése';
$labels['demoaccount']='Sajnálhu, de ez a funkció nem elérhető (Demó hozzáférés)';
$labels['serverversion']='Telepített verzió';
$labels['mirrorversion']='Elérhető legújabb verzió';
$labels['mirrorhost']='Tükör szerver';
$labels['comments']='Követelmények és frissítési megjegyzések';
$labels['update_error']='Verzió eltérés';
$labels['update_ok']='A legújabb verziók vannak telepítve, nincs szükség frissítésre.';
$labels['update_edit']='A beépülőt újra kell konfigurálni vagy adatbázis frissítést igényel. Kérjük olvassa el a frissítéssel kapcsolatos megjegyzéseket.';
$labels['servernewer']='A regisztrált beépülő újabb, mint a tükörszerveren található.';
$labels['orhigher']='vagy magasabb';
$labels['rc_ok']='A Roundcube verzió aktuális.';
$labels['update_update']='A beépülő elavult, frissítse, olvassa el a frissítési megjegyzéseket.';
$labels['noupdates']='Nincs frissítés kiválasztva.';
$labels['rc_update']='A Rouncube elavult, kérjük frissítse.';
$labels['rc_uptodate']='A Rouncube verziója aktuális.';
$labels['rc_newer']='A Roundcube verziója frissebb, mint a tükör szerveren lévő!';
$labels['justunzip']='Csak tömörítse ki a ZIP fáljt a meglévő Roundcube könyvtárába.';
$labels['guide']='További információk...';
$labels['thirdparty']='Ez a beépülő harmadik féltől származik.';
$labels['thirdpartywarning']='Ez a beépülőmodul harmadik féltől származik. Javasoljuk, hogy ne innen töltse le, hanem egyenesen a fejlesztő oldaláról, hogy biztosan a legfrissebb verziót és az aktuális dokumentációt kapja.';
$labels['develsite']='Letöltés';
$labels['notinstalled']='nincs telepítve';
$labels['notregistered']='nincs regisztrálva';
$labels['roundcubeurl']='Roundcube letöltése';
$labels['languageupdate']='Nyelvi frissítés előrhető.';
$labels['localizationfilesonly']='Megjegyzés: a letöltés csak nyelvi frissítést tartalmaz.';
$labels['donotregister']='Ne regisztrálja a beépülőt, mert automatikusan betöltődik.';
$labels['register']='Regisztrálja a beépülőt a Beépülő kezelőben vagy a Rouncube konfigurációs fájljában: ./config/config.inc.php [$config[\'plugins\'] = array("foo", "bar");].';
$labels['customer_account']='Felhasználói fiók';
$labels['customer_id']='Ügyfélazonosító';
$labels['invalid_customer_id']='Hibás ügyfélazonosító';
$labels['invalid_credits']='Hibás kreditek';
$labels['successfully_transferred']='Kreditek sikeresen átadva';
$labels['merge']='Kreditek összevonása';
$labels['credits']='Kreditek';
$labels['creditsupdated']='A kreditek változtak az oldal utolsó betöltése óta';
$labels['buynow']='Kredit vásárlása';
$labels['history']='Felhasználó fiók történet';
$labels['details']='Részletek';
$labels['receipt']='Számla';
$labels['plugins']='Beépülő modulok';
$labels['clickhere']='Kattintson ide';
$labels['discardliabletopaycosts']='Fizetős letöltések figyelmen kivül hagyása';
$labels['unchecknotinstalledplugins']='Nem telepített beépülő modulok figyelmen kívül hagyása';
$labels['sum']='Összesen';
$labels['show']='Mutat';
$labels['hide']='Elrejt';
$labels['view']='nézet';
$labels['expired']='lejárt';
$labels['terms']='Feltételek';
$labels['pricelist']='Árlista letöltése';
$labels['forthisdownload']='ezért a letöltésért';
$labels['remainingcredits']='megmaradt kreditek';
$labels['initialdownload']='Első letöltés';
$labels['keyfeatureaddition']='Új fontosabb képesség';
$labels['codeimprovements']='Kód fejlesztése';
$labels['servicenotavailable']='A szolgáltatás jelenleg nem elérhető, próbálja később';
$labels['myrcerror']='A MyRoundcube szolgáltatás jelenleg nem elérhető';
$labels['getnew']='Új felhasználói azonosító igénylése';
$labels['getnew_hint']='Javasoljuk, hogy rendszeresen újjítsa meg a felhasználói azonosítóját. Ha bármilyen gyanús változást lát a felhasználói fiók adatai körül, azonnal ujjítsa meg!';
$labels['transfer']='Kreditek átadása';
$labels['message_highlight_pluginname']='Üzenetek kiemelése';
$labels['message_highlight_plugindescription']='Üzenetek kiemelése egy megadott feltétel alapján';
$labels['google_contacts_pluginname']='Google Névjegyek';
$labels['contextmenu_pluginname']='Környezetérzékeny menü';
$labels['contextmenu_plugindescription']='Környezettől függő, jobb egérgombra felbukkanó menü engedélyezése.';
$labels['globaladdressbook_pluginname']='Google Címjegyzék';
$labels['manage_admins']='Adminisztrátorok';
$labels['plugin_manager_admins']='Beépülő modul kezelő adminisztrátorok';
$labels['allow_plugins_configuration']='Beépölő beállítási jogkör delegálása';
$labels['share_credits']='Kreditek megosztása';
$labels['add']='Hozzáadás';
$labels['accountnotexists']='A fiók nem létezik';
$labels['sharedby']='A felhasználói fiók megosztva';
$labels['switch']='Váltás';
$labels['ownaccount']='a saját fiókhoz';
$labels['shareinvitation']='a megosztott fikóhoz';
$labels['use_ssl']='Használjon SSL titkosítást a MyRouncube szerverekhez való csatlakozáskor';
$labels['use_hmail']='hMeilserver kompatibilis beépülők használata';
$labels['show_myrc_messages']='MyRoundcube-tól érkező értesítések megjelenítése';
$labels['serverwide']='Server szintű konfiguráció';
$labels['file_based_config']='Fálj alapú, globális konfiguráció engedélyezése';
$labels['advanced_admins']='Csak gyarkolott rendszergazdáknak';
$labels['compress_html']='HTML kimenet tömörítése';
$labels['enabled']='Engedélyezve (alapértelmezett)';
$labels['protected']='Védett';
$labels['skins']='Bőrök';
$labels['loads_always']='Elengedhetetlen';
$labels['loads_never']='Beépülő letiltva';
$labels['documentation']='Dokumentáció';
$labels['select_plugin']='Válasszon beépülőt';
$labels['legend']='Jelmagyarázat';
$labels['show_about_link']='Névjegy link megjelenítése';
$labels['show_support_link']='Támogatás link megjelenítése';
$labels['use_myroundcube_watermark']='"MyRoundcube" vízjel használata';
$labels['remove_watermark']='Ne legyen semmilyen vízjel';
$labels['database']='Adatbázis';