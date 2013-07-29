<?php

/************************************************************************/
/* phpAdsNew 2                                                          */
/* ===========                                                          */
/*                                                                      */
/* Copyright (c) 2000-2003 by the phpAdsNew developers                  */
/* For more information visit: http://www.phpadsnew.com                 */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/


// German
// Settings help translation strings
$GLOBALS['phpAds_hlp_dbhost'] = "
       Geben Sie den Hostname an, der als Datenbankserver f�r ".$phpAds_dbmsname." fungiert.
		";
		
$GLOBALS['phpAds_hlp_dbport'] = "
        Geben Sie die Port-Nummer f�r den Datenbank-Server von ".$phpAds_dbmsname." an.. Die Voreinstellung f�r eine".$phpAds_dbmsname."-Datenbank ist <i>".
		($phpAds_dbmsname == 'MySQL' ? '3306' : '5432')."</i>.
		";
		
$GLOBALS['phpAds_hlp_dbuser'] = "
        Geben Sie den Benutzername f�r die <i>Datenbank</i> an. Mit diesem Benutzername greift ".$phpAds_productname." auf den Datenbankserver von ".$phpAds_dbmsname." zu.
		";
		
$GLOBALS['phpAds_hlp_dbpassword'] = "
Geben Sie das Passwort f�r die <b>Datenbank</b> an. Mit diesem Passwort kann ".$phpAds_productname." auf den Datenbankserver von ".$phpAds_dbmsname." zugreifen. 
		";

$GLOBALS['phpAds_hlp_dbname'] = "
         Geben Sie den Namen der Datenbank an, in die ".$phpAds_productname." die Daten speichern soll. 
		Wichtig! Die Datenbank mu� bereits auf dem Datenbankserver vorhanden sein.
".$phpAds_productname." erstellt <b>nicht</b> diese
		Datenbank, wenn sie noch nicht existiert.
		";
		
$GLOBALS['phpAds_hlp_persistent_connections'] = "
        Die dauerhafte Verbindung zur Datenbank kann die Geschwindigkeit von ".$phpAds_productname." durchaus erh�hen, insbesondere wird die Ladezeiten vom Server verringert. Andererseits k�nnen bei Seiten mit sehr vielen Besuchern - genau gegenteilig zu oben - durch die dauerhafte Verbindung die Ladezeiten erheblich ansteigen. <br>
Welche Art der Verbindung gew�hlt wird, <i>normale</i> oder <i>dauerhafte</i>, ist abh�ngig von der Besucherzahl und der eingesetzten Hardware. Wenn ".$phpAds_productname." zu viele Ressourcen belegt, k�nnte dies durch die Einstellung <i>dauerhafte Verbindung </i> hervorgerufen worden sein.
		";
		
$GLOBALS['phpAds_hlp_insert_delayed'] = "
        ".$phpAds_dbmsname." sperrt w�hrend eines Schreibvorganges den Zugriff auf die jeweilige Tabelle f�r andere Benutzer. Hat die WEB-Seite viele Besucher, entsteht eine Warteschleife; jeder hat abzuwarten, bis f�r den vorderen Besucher die Tabellen beschrieben wurden und dann die Tabellen freigegeben werden. <br>
Der Schreibvorgang kann aber auch zeitlich versetzt erfolgen. Hierbei werden die Tabellen zu einem Zeitpunkt mit geringerer Datenbankbelastung geb�ndelt beschrieben. Mit einem Schreibvorgang werden mehrere Tabellenzeilen beschrieben. Die Tabellen sind dann w�hrend der Besucherabfrage nicht gesperrt.
		";
		
$GLOBALS['phpAds_hlp_compatibility_mode'] = "
        Wird die Datenbank ".$phpAds_dbmsname." nicht nur von ".$phpAds_productname.", sondern auch von weiteren Anwendungen genutzt, k�nnen unter Umst�nden Kompatibilit�tsprobleme auftreten. Durch setzen des Kompatibilit�tsmodus (Datenbank) werden diese Probleme behoben.<br>
Wenn die Bannerauslieferung im <i>lokalen Modus</i> erfolgt und Kompatibilit�tsmodus (Datenbank) gesetzt ist, hinterl��t ".$phpAds_productname." die Einstellungen der Datenbank so, wie diese vorher vorgefunden wurden. <br>
Diese Option verlangsamt den Adserver etwas (sehr gering) und ist in der Voreinstellung deaktiv.
		";
		
$GLOBALS['phpAds_hlp_table_prefix'] = "
Wird die Datenbank ".$phpAds_dbmsname." nicht nur von ".$phpAds_productname.", sondern auch von weiteren Anwendungen genutzt, ist es angeraten, den von ".$phpAds_productname." genutzten Tabellen, einen individuellen <i>Pr�fix</i> voranzustellen. Damit kann sichergestellt werden, da� dieselben Tabellennamen nicht von verschiedenen Programmen verwendet werden. <br>
".$phpAds_productname." kann mehrfach installiert werden , wobei alle installierten Programme auf dieselbe Datenbank zugreifen k�nnen. Es mu� dann f�r jede Anwendung den Tabellennamen ein eigener eindeutiger  <i>Pr�fix</i> vorangestellt werden.
		";
		
$GLOBALS['phpAds_hlp_table_type'] = "
        ".$phpAds_dbmsname." unterst�tzt mehrere Tabellentypen. Jeder Tabellentype hat seine eigenen Eigenschaften, die es durchaus erm�glichen, ".$phpAds_productname." stark zu beschleunigen. <br>
MyISAM ist der immer vorhandene Tabellentype und daher Voreinstellung bei ".$phpAds_dbmsname.". 
		";
		
$GLOBALS['phpAds_hlp_url_prefix'] = "
        ".$phpAds_productname." ben�tigt f�r das korrekte Funktionieren die genaue Angabe �ber den eigenen Standort auf dem Server. Ben�tigt wird die URL und das Verzeichnis, in dem ".$phpAds_productname." installiert ist. Z.B.: <i>http://www.your-url.com/".$phpAds_productname."</i>.
		";
		
$GLOBALS['phpAds_hlp_my_header'] =
$GLOBALS['phpAds_hlp_my_footer'] = "
        Damit Kopf- bzw. Fu�zeilen im Admin-Bereich eingeblendet werden k�nnen, m�ssen dies
         als HTML-Datei vorhanden sein. Eingegeben werden mu� die Adresse dieser Dateien  (z.B.: /home/login/www/header.htm). <br>
In den HTML-Dateien ist der entsprechende Text f�r die Kopf- oder Fu�zeile zu hinterlegen. Es k�nnen auch HTML-Tags verwendet werden. Wird HTML verwendet, sind Tags wie <i><body>, <html> </i>usw. nicht zugelassen. <br>
		";
		
$GLOBALS['phpAds_hlp_content_gzip_compression'] = "
		Die Kompression durch GZIP vermindert dem Umfang der Daten, die aus dem 	Administrationsberich zum Browser �bertragen werden. Dadurch wird die Daten�bertragung stark beschleunigt. Ben�tigt wird hierf�r mindestens PHP 4.0.5 mit GZIP-Erweiterung.
		";
		
$GLOBALS['phpAds_hlp_language'] = "
        Voreinstellung der Sprache. Diese Sprache wird als Voreinstellung in alle Module und
        Programmteile, auch f�r Verleger- und Inserentenbereiche �bernommen. <br>
        Unabh�ngig hiervon kann f�r jeden Verleger oder Inserenten eine eigene Sprache eingestellt werden.
        Auch kann ihnen in den jeweiligen Einstellungen gestattet werden, selbst die Sprachauswahl zu �ndern.
		";
		
$GLOBALS['phpAds_hlp_name'] = "
        Es besteht die M�glichkeit, f�r die Anwendung anstelle <i>".$phpAds_productname." </i> eine
        eigene Bezeichnung oder einen eigenen Namen zu vergeben. Dieser Name erscheint auf allen Seiten
        im Administations-, Verleger- und Inserentenbereich. Bleibt dieses Feld leer, wird ein Logo von ".$phpAds_productname." angezeigt.
		";
		
$GLOBALS['phpAds_hlp_company_name'] = "
        Dieser Firmenname wird bei den automatischen eMails an Verleger und Inserenten verwendet.
		";
		
$GLOBALS['phpAds_hlp_override_gd_imageformat'] = "
         ".$phpAds_productname." pr�ft standardm��ig, ob die Bibliothek GD-library installiert ist und
          welche Bildformate unterst�tzt werden. Einige Versionen von PHP gestatten diese automatische
           Pr�fung nicht. Das Ergebnis kann fehlerhaft sein. In diesem Fall k�nnen die unterst�tzten
           Bildformate manuell eingegeben werden. G�ltige Format sind z.B. <i> none, pgn, jpeg, gif.</i> .
		";
		
$GLOBALS['phpAds_hlp_p3p_policies'] = "
        Wenn P3P Privacy Policies genutzt werden sollen, mu� diese Option hier gesetzt werden. 
		";
		
$GLOBALS['phpAds_hlp_p3p_compact_policy'] = "
        Die Zeile f�r die P3P Policies ist bei ".$phpAds_productname." wie folgt festgelegt: 'CUR ADM OUR NOR STA NID'. Sie wird gemeinsam mit Cookies an den Besucher gesendet. Durch dieses Verfahren wird sichergestellt, da� Internet Explorer 6 die Cookies akzeptiert. Der Inhalt der Zeile kann durch eigene g�ltige Texte ge�ndert werden.
		";
		
$GLOBALS['phpAds_hlp_p3p_policy_location'] = "
        Wenn Sie eine vollst�ndige Privacy Policy nutzen, k�nnen Sie hier deren den Standort eingeben.
		";
		
$GLOBALS['phpAds_hlp_log_beacon'] = "
		Beacons sind kleine, nicht sichtbare Bilddateien. Zu jeder Seite, zu der ein Banner ausgeliefert wird, wird nach dieser Auslieferung ein Beacon ausgeliefert. Ist die Verwendung von Beacons gew�hlt worden, wird nicht die Auslieferung des Banners protokolliert, sondern die der Beacon. Denn da ein Beacon direkt nach einem Banner ausgeliefert wird, ist dokumentiert, da� der Banner  <i><b>vollst�ndig</i></b> ausgeliefert wurde. <br>
Wird diese Option nicht gesetzt, z�hlt ".$phpAds_productname." die Banner w�hrend der Auslieferung. Banner, die zwar ausgeliefert wurden, nicht oder nicht vollst�ndig beim Besucher ankamen, werden mitgez�hlt. Solcherart Verluste entstehen z.B. wenn der Besucher mit der ESC-Taste die �bertragung unterbricht oder wenn er rasch die Seite wechselt. 
		";
		
$GLOBALS['phpAds_hlp_compact_stats'] = "
	".$phpAds_productname." bietet die M�glichkeit, entweder detaillierte Statistiken oder kompakte zu w�hlen. F�r die kompakten Statistiken werden AdViews und AdClicks gesammelt und st�ndlich verarbeitet. Die detaillierten Statistiken ben�tigen mehr Datenbank- Ressourcen.
		";
		
$GLOBALS['phpAds_hlp_log_adviews'] = "
	Normalerweise werden alle AdViews aufgezeichnet und flie�en in die Statistiken ein. Diese Option kann deaktiviert werden. 
		";
		
$GLOBALS['phpAds_hlp_block_adviews'] = "
		Die Reloadsperre verhindert, da� ein Banner mehrfach gez�hlt wird, wenn er auf derselben Seite demselben Besucher �fters pr�sentiert wird; z.B. dadurch, da� der Besucher die Browserseite aktualisiert. An dieser Stelle kann die Dauer in Sekunden eingegeben werden, f�r die die Reloadsperre aktiv ist. Die Sperre funktioniert nur, wenn der Besucher Cookies zul��t.
		";
		
$GLOBALS['phpAds_hlp_log_adclicks'] = "
	Normalerweise werden alle AdClicks aufgezeichnet und flie�en in die Statistiken ein. Diese Option kann deaktiviert werden. 
		";
		
$GLOBALS['phpAds_hlp_block_adclicks'] = "
		Die Reclicksperre verhindert, da� Clicks auf ein Banner mehrfach gez�hlt wird, wenn  derselben Besucher mehr als einmal auf einen Banner klickt. An dieser Stelle kann die Dauer in Sekunden eingegeben werden, f�r die die Reclicksperre aktiv ist. Die Sperre funktioniert nur, wenn der Besucher Cookies zul��t.
		";
		
$GLOBALS['phpAds_hlp_log_source'] = "
		Wenn Sie die Parameter des Quellcodes in den Code f�r die Bannerauslieferung �bernehmen, k�nnen sie diese Informationen auch in der Datenbank speichern. Das erm�glicht, die Entwicklung der unterschiedlichen Parameter zu verfolgen. Wenn Sie keine Parameter aus dem Quellcode verwenden, oder wenn diese Daten nicht wichtig sind, empfiehlt es sich aus Sicherheitsgr�nden, diese Option zu deaktivieren.
		";
		
$GLOBALS['phpAds_hlp_geotracking_stats'] = "
		Geotargeting ist die Standortbestimmung des Besuchers. Wenn Sie eine Datenbank f�r
		Geotargeting einsetzen, werden als geographische Informationen gespeichert: <br><br>
Das Herkunftsland des Besuchers und die Entwicklung der Banner nach L�ndern. Die Option Geotargeting kann nur in Verbindung mit detaillierten Statistiken aktiviert werden. 
		";
		
$GLOBALS['phpAds_hlp_log_hostname'] = "
		Sowohl IP-Adresse al auch der Hostname k�nnen f�r jeden Besucher aufgezeichnet werden.
		Die Speicherung beider Informationen gestattet die Auswertung, wie sich Banner in Abh�ngigkeit zum Besucherhost entwickeln. Diese Option kann nur in Verbindung mit detaillierten Statistiken aktiviert werden. Der Speicherbedarf ist gr��er als bei alleiniger Speicherung der IP-Adresse.
		";
		
$GLOBALS['phpAds_hlp_log_iponly'] = "
		Es wird nur die IP-Adresse des jeweiligen Besuchers aufgezeichnet. Das gilt auch, wenn der Hostname bekannt ist oder w�hrend des Besuchs mitgeliefert wird. Der Speicherbedarf ist geringer als durch die Speicherung von IP-Adresse und Hostname. 
		";
		
$GLOBALS['phpAds_hlp_reverse_lookup'] = "
		Der Hostname des Besuchers wird in der Regel vom WEB-Server bestimmt. 
		Bestimmt der (eigene) WEB-Server nicht den Hostname, kann die Information von 
		".$phpAds_productname." ermittelt werden. Wird diese Option gesetzt, kann es zur Verlangsamung der Bannerauslieferung kommen. 
		";
		
$GLOBALS['phpAds_hlp_proxy_lookup'] = "
		Verwendet der Besucher einen Proxy-Zugang, protokolliert ".$phpAds_productname." normalerweise die IP-Adresse und/oder den Hostname des Proxy-Servers. Diese Erfassung kann so gesetzt werden, da� ".$phpAds_productname." versucht, den tats�chlichen Hostname oder die IP-Adresse zu ermitteln, die hinter dem Proxy-Server steht. Kann diese  <i>richtige</i> Adresse des Besuchers nicht ermittelt werden, werden die Daten des Proxy-Servers verwendet. In der Voreinstellung ist diese Option deaktiviert, da sie die Bannerauslieferung verlangsamt.
		";
		
$GLOBALS['phpAds_hlp_auto_clean_tables'] = 
$GLOBALS['phpAds_hlp_auto_clean_tables_interval'] = "
		Die aufgezeichneten Daten und erstellten Statistiken k�nnen nach einer bestimmten Zeit gel�scht werden. An dieser Stelle kann in Wochen eingegeben werden, nach welchem Zeitraum die Daten gel�scht werden sollen. Mindestens sind 3 Wochen einzugeben.
		";
		
$GLOBALS['phpAds_hlp_auto_clean_userlog'] = 
$GLOBALS['phpAds_hlp_auto_clean_userlog_interval'] = "
		Durch diese Funktion k�nnen alle Eintr�ge im Benutzerprotokoll nach einer bestimmten Zeit gel�scht werden. An dieser Stelle kann in Wochen eingegeben werden, nach welchem Zeitraum die aufgezeichneten Benutzerdaten gel�scht werden sollen. Mindestens sind 3 Wochen einzugeben
		";
		


$GLOBALS['phpAds_hlp_geotracking_type'] = "
		�ber Geotargeting wird anhand der IP-Adresse der Standort bzw. die Herkunft des Besuchers ermittelt. Ermittelt wird das Land. N�tzlich ist das, wenn mehrsprachige Banner oder Banner in mehreren Sprachen ausgeliefert werden k�nnen. Die Auslieferung eines Banners kann auf bestimmte Herkunftsl�nder (der Besucher) eingeschr�nkt oder nach L�nderkategorien ausgewertet werden.<br>
F�r Geotargeting wird eine spezielle Datenbank ben�tigt. ".$phpAds_productname." unterst�tzt die Datenbanken von  <a href='http://hop.clickbank.net/?phpadsnew/ip2country' target='_blank'>IP2Country</a> und <a href='http://www.maxmind.com/?rId=phpadsnew' target='_blank'>GeoIP</a>.
		";
		
$GLOBALS['phpAds_hlp_geotracking_location'] = "
		Wenn nicht das Modul GeoIP Apache verwendet wird, mu� f�r ".$phpAds_productname." das Verzeichnis eingegeben werden, in dem sich die Geotargeting-Datenbank befindet. Empfehlenswert ist es, diese au�erhalb des Dokumentenverzeichnisses des WEB-Servers abzulegen; da diese andernfalls f�r Dritte einsehbar sein k�nnte.
		";
		
$GLOBALS['phpAds_hlp_geotracking_cookie'] = "
		Die Umwandlung der IP-Adresse in l�nderspezifische Informationen ben�tigt Zeit. Damit diese Informationen nicht bei jeder Bannerauslieferung an denselben Besucher berechnet werden m�ssen, werden sie bei der ersten Bannerauslieferung berechnet und in einem Cookie gespeichert. Die geographischen Informationen aus dem Cookie werden bei nachfolgenden Bannerauslieferungen f�r das Geotargeting verwendet.
		";
		
$GLOBALS['phpAds_hlp_ignore_hosts'] = "
	Sollen AdViews und AdClicks von bestimmten Host nicht aufgezeichnet werden, k�nnen diese hier aufgelistet werden. Wurde eingestellt, da� der Hostname ermittelt wird, k�nnen Domain-Name oder IP-Adresse eingegeben werden. Wurde diese Option deaktiviert, ist nur die Eingabe der IP-Adresse m�glich. Platzhalter/Wildcards sind zugelassen (z.B. '*.altavista.com' or '192.168.*')
		";
		
$GLOBALS['phpAds_hlp_begin_of_week'] = "
	Der Tag des Wochenbeginns kann eingestellt werden. Voreinstellung ist Montag.
		";
		
$GLOBALS['phpAds_hlp_percentage_decimals'] = "
	Die Anzahl der Nachkommastellen f�r die Darstellung von Prozentangaben innerhalb der Statistiken.
		";
		
$GLOBALS['phpAds_hlp_warn_admin'] = "
	Es besteht die M�glichkeit, da� ".$phpAds_productname." eine eMail an den Administrator sendet, wenn f�r eine Kampagne ein bestimmtes Restguthaben f�r AdViews oder AdClicks unterschritten wurde.
In der Voreinstellung ist diese Option gesetzt.
		";
		
$GLOBALS['phpAds_hlp_warn_client'] = "
	Es besteht die M�glichkeit, da� ".$phpAds_productname." eine eMail an den Inserenten sendet, wenn f�r eine Kampagne ein bestimmtes Restguthaben f�r AdViews oder AdClicks unterschritten wurde.
In der Voreinstellung ist diese Option gesetzt.
		";
		
$GLOBALS['phpAds_hlp_qmail_patch'] = "
	Einigen Versionen von <i>qMail</i> sind fehlerbehaftet. Die Kopfzeile der eMail wird in den Haupttext verschoben. Hier kann festgelegt werden, da� ".$phpAds_productname." jede eMail in einem Format versendet, das von <i>qMail</i> korrekt wiedergegeben ist.
		";
		
$GLOBALS['phpAds_hlp_warn_limit'] = "
	Eingegeben wird das Restguthaben, nach dessen Unterschreitung ".$phpAds_productname." Warnungen per eMail versendet. Voreinstellung ist 100.
		";
		
$GLOBALS['phpAds_hlp_allow_invocation_plain'] = 
$GLOBALS['phpAds_hlp_allow_invocation_js'] = 
$GLOBALS['phpAds_hlp_allow_invocation_frame'] = 
$GLOBALS['phpAds_hlp_allow_invocation_xmlrpc'] = 
$GLOBALS['phpAds_hlp_allow_invocation_local'] = 
$GLOBALS['phpAds_hlp_allow_invocation_interstitial'] = 
$GLOBALS['phpAds_hlp_allow_invocation_popup'] = "
		Mit dieser Einstellung werden die Verfahren der Bannerauslieferung festgelegt. F�r jedes Verfahren wird ein eigener Bannercode erstellt. Ist eines der Verfahren an dieser Stelle deaktiviert, erfolgt diese Sperre nur f�r den Bannercode-Generator. Der Ausschlu� des Verfahrens betrifft Bannercodes an sich nicht. Existierende Banner eines ausgeschlossenen Verfahrens bleiben weiterhin lauff�hig.
		";
		
$GLOBALS['phpAds_hlp_con_key'] = "
		F�r den lokalen Modus als Verfahren zur Bannerauslieferung bietet ".$phpAds_productname." m�chtige Werkzeuge zur Selektion. U. a. k�nnen hier logische Operatoren verwendet werden. Weitere Informationen finden sich im Handbuch. In der Voreinstellung ist diese Option gesetzt.
		";
		
$GLOBALS['phpAds_hlp_mult_key'] = "
		Banner k�nnen nach Schl�sselw�rtern ausgew�hlt werden. Es k�nnen ein oder mehrere Schl�sselw�rter je Banner oder je Bannerauslieferung bestimmt werden. Die Anzahl der Schl�sselw�rter kann auf ein Schl�sselwort begrenzt werden; andernfalls sind immer mehrere zul�ssig. Die Option f�r mehrere Schl�sselw�rter ist in der Voreinstellung gesetzt. Ist nur ein Schl�sselwort gew�nscht, mu� die Option deaktiviert werden.
		";
		
$GLOBALS['phpAds_hlp_acl'] = "
	Sollen f�r die Bannerauslieferung keine Beschr�nkungen festgelegt werden, k�nnte durch die Deaktivierung dieser Option die Geschwindigkeit von ".$phpAds_productname." etwas beschleunigt werden.
		";
		
$GLOBALS['phpAds_hlp_default_banner_url'] = 
$GLOBALS['phpAds_hlp_default_banner_target'] = "
	Wenn ".$phpAds_productname." keine Verbindung zur Datenbank herstellen kann, bzw. wenn kein g�ltiger Banner gefunden wurde, wird der hier definierte Banner ersatzweise ausgeliefert. F�r diesen Ersatzbanner werden weder AdViews noch AdClicks aufgezeichnet. In der Voreinstellung ist diese Funktion deaktiviert.
		";
		
$GLOBALS['phpAds_hlp_delivery_caching'] = "
		Die Verwendung eines Caches als Zwischenspeicher beschleunigt die Bannerauslieferung. In dem Cache sind alle Informationen, die f�r die Bannerauslieferung notwendig sind, hinterlegt. Als Voreinstellung ist der Cache Teil der Datenbank. Alternativ hierzu kann er in einer Datei oder in einem shared memory abgelegt werden. Die schnellste Bannerauslieferung bietet shared memory; doch bereits der Einsatz einer Datei beschleunigt die Bannerauslieferung erheblich. Wird die Option des Caches f�r die Bannerauslieferung deaktiviert, verlangsamt sich die Leistung von".$phpAds_productname." sehr und wird daher nicht empfohlen.
		";
		
$GLOBALS['phpAds_hlp_type_sql_allow'] = 
$GLOBALS['phpAds_hlp_type_web_allow'] = 
$GLOBALS['phpAds_hlp_type_url_allow'] = 
$GLOBALS['phpAds_hlp_type_html_allow'] = 
$GLOBALS['phpAds_hlp_type_txt_allow'] = "
	".$phpAds_productname." unterst�tzt unterschiedliche Bannerformate, die auf unterschiedlicher Weise gespeichert werden. Die ersten zwei werden f�r das lokale Speichern verwendet. Banner k�nnen �ber das Administrationsmodul in die Datenbank geladen oder auf dem WEB-Server gespeichert werden. Banner k�nnen auch auf externen Servern �ber die URL verwaltet werden. Sie k�nnen HTML-Banner sein und einfache Texte f�r Textanzeigen.
		";
		
$GLOBALS['phpAds_hlp_type_web_mode'] = "
Wenn Banner auf dem WEB-Server gespeichert werden sollen, m�ssen die Einstellungen hierf�r konfiguriert werden. Sollen die Banner auf einem lokalen Verzeichnis gespeichert werden, mu�  <i>Lokales Verzeichnis</i> gew�hlt werden. F�r die Speicherung auf einem FTP-Server ist <i> Externer FTP-Server</i> einzustellen. Es ist durchaus m�glich, da� der eigene WEB-Server nicht lokal, sondern als FTP-Server eingerichtet wird.
		";
		
$GLOBALS['phpAds_hlp_type_web_dir'] = "
	In das hier festgelegte Verzeichnis speichert ".$phpAds_productname." die hochgeladenen Banner. Das Verzeichnis darf f�r PHP nicht schreibgesch�tzt sein. Es mu�, ggf. mit Hilfe eines FTP-Programmes, die Zugriffs- und Schreibberechtigung gesetzt werden. Das Verzeichnis mu� im Root-Verzeichnis von ".$phpAds_productname." sein. Bei der Eingabe des Verzeichnisses darf als Ende kein  Slash (/) eingegeben werden. Diese Angaben werden nur bei  <i>Lokales Verzeichnis</i> als Wahl f�r die Speicherung ben�tigt.
		";
		
$GLOBALS['phpAds_hlp_type_web_ftp_host'] = "
		F�r das Speicherungsverfahren  <i> Externer FTP-Server</i> wird die IP-Adresse oder der Domain-Name des FTP-Servers ben�tigt.
		";

$GLOBALS['phpAds_hlp_type_web_ftp_path'] = "
		F�r das Speicherungsverfahren  <i> Externer FTP-Server</i> mu� das Verzeichnis, in das die Banner gespeichert werden, genau bezeichnet werden (auf Gro�-/Kleinschreibung ist ggf. zu achten).
		";
      
$GLOBALS['phpAds_hlp_type_web_ftp_user'] = "
		F�r das Speicherungsverfahren  <i> Externer FTP-Server</i> mu� der Benutzername f�r den Zugang zum FTP-Server eingegeben werden.
		";
      
$GLOBALS['phpAds_hlp_type_web_ftp_password'] = "
		F�r das Speicherungsverfahren  <i> Externer FTP-Server</i> wird zum Benutzername ein g�ltiges Passwort ben�tigt.
		";
      
$GLOBALS['phpAds_hlp_type_web_url'] = "
	Werden die Banner auf einem WEB-Server gespeichert, m�ssen sowohl die (�ffentliche) URL als auch das mit ihr korrespondierende lokale Verzeichnis eingegeben werden. Z. B.<br>
(�ffentliche) URL       = <i>http://www.Werbeplatzvermarktung.de/ads</i><br>
Lokales Verzeichnis   = <i>/var/www/htdocs/ads</i><br>
Bei der Eingabe des Verzeichnisses darf als Ende kein  Slash (/) eingegeben werden.
		";
		
$GLOBALS['phpAds_hlp_type_html_auto'] = "
	Durch diese Option modifiziert ".$phpAds_productname." den HTML-Code so, da� AdClicks aufgezeichnet werden k�nnen. Auch wenn diese Option an dieser Stelle aktiviert wird, ist es dennoch m�glich, sie f�r jeden Banner individuell zu deaktivieren.
		";
		
$GLOBALS['phpAds_hlp_type_html_php'] = "
	Es kann zugelassen werden, da� ausf�hrbare PHP-Codes in HTML-Banner eingebettet sind. Diese Funktion ist in der Voreinstellung deaktiviert.
		";
		
$GLOBALS['phpAds_hlp_admin'] = "
	Bitte geben Sie den Benutzername des Administrators ein. Mit diesem Benutzername ist der Zugang zum Administrationsmodul m�glich
		";
		
$GLOBALS['phpAds_hlp_admin_pw'] =
$GLOBALS['phpAds_hlp_admin_pw2'] = "
	Bitte geben Sie ein Passwort f�r den Administrator ein. Die Passworteingabe mu� durch erneute Eingabe best�tigt werden.
		";
		
$GLOBALS['phpAds_hlp_pwold'] = 
$GLOBALS['phpAds_hlp_pw'] = 
$GLOBALS['phpAds_hlp_pw2'] = "
	Um das Passwort des Administrators zu �ndern, mu� zun�chst das alte Passwort eingegeben werden. Das neue Passwort ist zweimal einzugeben.
		";
		
$GLOBALS['phpAds_hlp_admin_fullname'] = "
	Eingabe des vollen Namen (Name, Vorname) des Administrators. Die Angaben werden f�r eMails ben�tigt.
		";
		
$GLOBALS['phpAds_hlp_admin_email'] = "
	Eingabe der eMail-Adresse des Administrators. Die Angaben werden f�r eMails ben�tigt.
		";
		
$GLOBALS['phpAds_hlp_admin_email_headers'] = "
	Die Kopfzeile f�r die eMails, die ".$phpAds_productname." versenden soll, kann hier ge�ndert werden.
		";
		
$GLOBALS['phpAds_hlp_admin_novice'] = "
	Wenn eine Warnung erfolgen soll, bevor Zonen, Kampagnen, Banner, Verleger, Inserenten endg�ltig gel�scht werden, mu� diese Option gesetzt sein.
		";
		
$GLOBALS['phpAds_hlp_client_welcome'] = "
		Wenn diese Option gesetzt wird, erscheint eine Begr��ungszeile auf der ersten Inserentenseite. Dieser Begr��ungstext, der in der Datei welcome.html gespeichert ist, kann personalisiert oder erg�nzt werden. M�glich w�ren Firmenlogo, Kontaktinformationen, Links zu Angeboten ....
		";

$GLOBALS['phpAds_hlp_client_welcome_msg'] = "
		Der Begr��ungstext f�r Inserenten kann hier eingegeben werden. HTML-Tags sind zugelassen. 
		Ist hier ein Text eingegeben, wird die Datei welcome.html ignoriert.
		";
		
$GLOBALS['phpAds_hlp_updates_frequency'] = "
		".$phpAds_productname." wird st�ndig optimiert. Es kann in selbst definierten Intervallen direkt beim Update-Server nach neuen Versionen gepr�ft werden. Ist eine neue Version vorhanden, werden in einem Dialogfenster zun�chst weitere Informationen �ber das Update gegeben.
		";
		
$GLOBALS['phpAds_hlp_userlog_email'] = "
		Soll von allen durch ".$phpAds_productname."  versandte eMails eine Kopie angefertigt werden, kann das durch diese Option erm�glicht werden. Die eMails werden in das Benutzerprotokoll eingetragen.
		";
		
$GLOBALS['phpAds_hlp_userlog_priority'] = "
		Es kann in einem Bericht gespeichert werden, ob die st�ndlichen Neuberechnungen korrekt durchgef�hrt werden. Der Bericht enth�lt die voraussichtlichen Einblendungen und Priorit�ten. Hilfreich kann der Bericht sein, um Fehler in der Kalkulation zu finden. Er wird in das Benutzerprotokoll eingetragen.
		";
		
$GLOBALS['phpAds_hlp_userlog_autoclean'] = "
		Um �berpr�fen zu k�nnen, ob die Datenbank fehlerfrei ges�ubert wurde, kann ein Bericht �ber den Datenbanklauf erstellt werden. Er wird in das Benutzerprotokoll eingetragen.
		";
		
$GLOBALS['phpAds_hlp_default_banner_weight'] = "
		Die Gewichtung f�r Banner ist als Voreinstellung <i>1</i>. Die Voreinstellung kann an dieser Stelle h�her gesetzt werden.
		";
		
$GLOBALS['phpAds_hlp_default_campaign_weight'] = "
		Die Gewichtung f�r Kampagnen ist als Voreinstellung <i>1</i>. Die Voreinstellung kann an dieser Stelle h�her gesetzt werden.
		";
		
$GLOBALS['phpAds_hlp_gui_show_campaign_info'] = "
		Wenn diese Option aktiviert ist, werden weitere Informationen �ber jede Kampagne auf der Seite <i>�bersicht Kampagnen</i> dargestellt. Diese Informationen sind: Restguthaben an AdViews und AdClicks., Aktivierungsdatum, Auslaufdatum und Priorit�t.
		";
		
$GLOBALS['phpAds_hlp_gui_show_banner_info'] = "
		Wenn diese Option aktiviert ist, werden weitere Informationen �ber jeden Banner auf der Seite <i>�bersicht Banner</i> dargestellt. Diese Informationen sind: Ziel-URL, Schl�sselw�rter, Gr��e und Bannergewichtung.
		";
		
$GLOBALS['phpAds_hlp_gui_show_campaign_preview'] = "
		Wenn diese Option aktiviert ist, erfolgt eine Vorschau aller Banner auf der Seite <i>�bersicht Banner</i>. Ist diese Option deaktiviert, ist f�r jeden Banner einzeln dennoch eine Vorschau m�glich. Hierzu mu� auf das Dreieck neben dem Banner geklickt werden.
		";
		
$GLOBALS['phpAds_hlp_gui_show_banner_html'] = "
		Wenn diese Option aktiviert ist, wird der aktuelle HTML-Banner anstelle des HTML-Codes angezeigt. Diese Funktion ist in der Voreinstellung deaktiviert; denn auf diesem Wege angezeigte HTML-Banner k�nnen Konflikte produzieren. Jeder HTML-Banner kann in der <i>�bersicht Banner</i> durch anklicken von <i>Banner anzeigen</i> dargestellt werden.
		";
		
$GLOBALS['phpAds_hlp_gui_show_banner_preview'] = "
		Wenn diese Option aktiviert ist, erfolgt eine Vorschau auf den Seiten <i>Bannermerkmale</i>, <i> Auslieferungsoptionen</i> und <i> Verkn�pfte Zonen</i>.<br>
Ist diese Option deaktiviert, ist die Bannerdarstellung m�glich, wenn auf <i>Banner anzeigen</i> geklickt wird.
		";
		
$GLOBALS['phpAds_hlp_gui_hide_inactive'] = "
		Wenn diese Option gesetzt ist, werden alle inaktiven Banner, Kampagnen und Inserenten auf den Seiten <i> Inserenten & Kampagnen</i> und <i>�bersicht Kampagnen</i> verborgen. Diese verborgenen Informationen k�nnen durch anklicken von </i> Alle anzeigen</i> dargestellt werden.
		";
		
$GLOBALS['phpAds_hlp_gui_show_matching'] = "
	Wenn diese Option aktiviert ist, werden alle gefundene Banner auf der Seite <i> Verkn�pfte Banner</i> dargestellt, wenn als Methode <i> Kampagne (Auswahl)</i> gew�hlt wurde. Hierdurch wird genau dargestellt, welche Banner zur Auslieferung vorgesehen sind. Auch eine Vorschau der zugeh�renden Banner ist m�glich.
		";
		
$GLOBALS['phpAds_hlp_gui_show_parents'] = "
		Wenn diese Option aktiviert ist, werden die zugeh�renden Kampagnen der Banner auf der Seite <i>Verkn�pfte Banner</i> angezeigt, wenn als Methode <i> Banner (Auswahl)</i> gew�hlt wurde. Hierdurch wird es - vor der Verkn�pfung - m�glich, anzuzeigen, welcher Banner den jeweiligen Kampagnen zugeordnet ist. Die Banner sind in der Sortierung den Kampagnen eingeordnet und werden nicht alphabetisch angezeigt.
		";
		
$GLOBALS['phpAds_hlp_gui_link_compact_limit'] = "
	Als Voreinstellung werden auf der Seite <i>Verkn�pfte Banner</i> alle verf�gbaren Banner oder Kampagnen angezeigt. Da diese Anzeige sehr lang werden kann, gestattet diese Option die Darstellung einer maximalen Anzahl von Positionen. Sind mehr Positionen als festgelegt vorhanden, aber verschiedene Wege der Darstellung, wird die Darstellungsart gew�hlt, die weniger Platz ben�tigt.
		";
?>