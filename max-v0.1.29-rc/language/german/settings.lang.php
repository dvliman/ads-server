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
// Installer translation strings
$GLOBALS['strInstall']				= "Installation";
$GLOBALS['strChooseInstallLanguage']		= "W�hlen Sie die Sprache f�r den Installationsproze�";
$GLOBALS['strLanguageSelection']		= "Sprachauswahl";
$GLOBALS['strDatabaseSettings']			= "Datenbankeinstellungen";
$GLOBALS['strAdminSettings']			= "Einstellung f�r Administrator";
$GLOBALS['strAdvancedSettings']		= "Erg�nzende Einstellungen";
$GLOBALS['strOtherSettings']			= "Andere Einstellungen";

$GLOBALS['strWarning']				= "Warnung";
$GLOBALS['strFatalError']			= "Ein schwerer Fehler ist aufgetreten";
$GLOBALS['strUpdateError']			= "W�hrend des Updates ist ein Fehler aufgetreten";
$GLOBALS['strUpdateDatabaseError']	= "Aus unbekannten Gr�nden war die Aktualisierung der Datenbankstruktur nicht erfolgreich. Es wird empfohlen, zu versuchen, mit <b>Wiederhole Update</b> das Problem zu beheben. Sollte der Fehler - Ihrer Meinung nach - die Funktionalit�t von ".$phpAds_productname." nicht ber�hren, k�nnen Sie durch <b>Fehler ignorieren</b>  fortfahren. Das Ignorieren des Fehlers wird nicht empfohlen!";
$GLOBALS['strAlreadyInstalled']			= $phpAds_productname." ist bereits auf diesem System installiert. Zur Konfiguration nutzen Sie das <a href='settings-index.php'>Konfigurationsmen�</a>"; 
$GLOBALS['strCouldNotConnectToDB']		= "Verbindung zur Datenbank war nicht m�glich. Bitte vorgenommene Einstellung pr�fen.";
$GLOBALS['strCreateTableTestFailed']		= "Der von Ihnen angegebene Nutzer hat nicht die notwendigen Zugriffsrechte, um die Datenbankstruktur anlegen zu k�nnen. Wenden Sie sich an den Systemverwalter.";
$GLOBALS['strUpdateTableTestFailed']		= " Der von Ihnen angegebene Nutzer hat nicht die notwendigen Zugriffsrechte, um die Datenbank zu aktualisieren. Wenden Sie sich an den Systemverwalter.";
$GLOBALS['strTablePrefixInvalid']		= "Ung�ltiges Vorzeichen (Pr�fix) im Tabellennamen";
$GLOBALS['strTableInUse']			= "Die genannte Datenbank wird bereits von ".$phpAds_productname.", genutzt. Verwenden Sie einen anderen Pr�fix oder lesen Sie im Handbuch die Hinweise f�r ein Upgrade.";
$GLOBALS['strTableWrongType']		= "Der gew�hlte Tabellentype wird bei der Installation von ".$phpAds_dbmsname." nicht unterst�tzt";
$GLOBALS['strMayNotFunction']			= "Folgende Probleme sind zu beheben, um fortzufahren";
$GLOBALS['strFixProblemsBefore']		= "Folgende Teile m�ssen korrigiert werden, bevor der Installationsproze� von ".$phpAds_productname." fortgesetzt werden kann. Informationen �ber Fehlermeldungen finden sich im Handbuch.";
$GLOBALS['strFixProblemsAfter']			= "Sollten Sie die oben aufgef�hrten Fehler nicht selbst heben k�nnen, nehmen Sie Kontakt mit der Systemadministration Ihres Servers auf. Diese wird Ihnen weiterhelfen k�nnen.";
$GLOBALS['strIgnoreWarnings']			= "Ignoriere Warnungen";
$GLOBALS['strWarningDBavailable']		= "Die eingesetzte PHP-Version unterst�tzt nicht die Verbindung zum ".$phpAds_dbmsname." Datenbankserver. Die PHP- ".$phpAds_dbmsname."-Erweiterung wird ben�tigt.";
$GLOBALS['strWarningPHPversion']		= $phpAds_productname." ben�tigt PHP 4.0 oder h�her, um korrekt genutzt werden zu k�nnen. Sie nutzten {php_version}.";
$GLOBALS['strWarningRegisterGlobals']		= "Die PHP-Konfigurationsvaribable <i>register_globals</i> mu� gesetzt werden.";
$GLOBALS['strWarningMagicQuotesGPC']		= " Die PHP-Konfigurationsvaribable <i> magic_quotes_gpc</i> mu� gesetzt werden.";
$GLOBALS['strWarningMagicQuotesRuntime']	= " Die PHP-Konfigurationsvaribable <i> magic_quotes_runtime</i> mu� deaktiviert werden.";
$GLOBALS['strWarningFileUploads']		= " Die PHP-Konfigurationsvaribable <i> file_uploads</i> mu� gesetzt werden.";
$GLOBALS['strWarningTrackVars']			= " Die PHP-Konfigurationsvaribable <i> track_vars</i> mu� gesetzt werden.";
$GLOBALS['strWarningPREG']				= "Die verwendete PHP-Version unterst�tzt nicht PERL-kompatible Ausdr�cke. Um fortfahren zu k�nnen wird die PHP-Erweiterung <i>PREG</i> ben�tigt.";
$GLOBALS['strConfigLockedDetected']		= $phpAds_productname." hat erkannt, da� die Datei <b>config.inc.php</b> schreibgesch�tzt ist.<br> Die Installation kann aber ohne Schreibberechtigung nicht fortgesetzt werden. <br>Weitere Informationen finden sich im Handbuch."; 

$GLOBALS['strCantUpdateDB']  			= "Ein Update der Datenbank ist derzeit nicht m�glich. Wenn Sie die Installation fortsetzen, werden alle existierende Banner, Statistiken und Inserenten gel�scht. ";
$GLOBALS['strIgnoreErrors']			= "Fehler ignorieren";
$GLOBALS['strRetryUpdate']			= "Wiederhole Update";
$GLOBALS['strTableNames']			= "Tabellenname";
$GLOBALS['strTablesPrefix']			= "Pr�fix zum Tabellenname";
$GLOBALS['strTablesType']			= "Tabellentype";
$GLOBALS['strInstallWelcome']			= "Willkommen bei ".$phpAds_productname;
$GLOBALS['strInstallMessage']			= "Bevor ".$phpAds_productname." genutzt werden kann, m�ssen die Einstellungen konfiguriert  <br> sowie die Datenbank geschaffen (create) werden. Dr�cken Sie <b>Weiter</b> , um fortzufahren.";
$GLOBALS['strInstallSuccess']			= "<b>die Installation von ".$phpAds_productname." war erfolgreich.</b><br><br>Damit ".$phpAds_productname." korrekt arbeitet, mu� sichergestellt sein, da� das Wartungsmodul (maintenance.php) st�ndlich aktiviert wird. N�here Informationen finden sich im Handbuch. <br><br>
F�r weitere Einstellungen auf der Konfigurationsseite dr�cken Sie  <b>Weiter</b>. 
<BR>
Der Schreibschutz der Datei <i>config.inc.php</i> sollte aus Sicherheitsgr�nden wieder gesetzt werden.";
$GLOBALS['strUpdateSuccess']		= "<b>Das Update von ".$phpAds_productname." war erfolgreich.</b><br><br>
Damit ".$phpAds_productname." korrekt arbeitet, mu� sichergestellt sein, da� das Wartungsmodul (maintenance.php) st�ndlich aktiviert wird. N�here Informationen finden sich im Handbuch. <br><br>
F�r weitere Einstellungen auf der Konfigurationsseite dr�cken Sie  <b>Weiter</b>. 
<BR>
Der Schreibschutz der Datei <i>config.inc.php</i> sollte aus Sicherheitsgr�nden wieder gesetzt werden.";

$GLOBALS['strInstallNotSuccessful']		= "<b>Die Installation von ".$phpAds_productname." war nicht erfolgreich</b><br><br>
Teile des Installationsprozesses wurden nicht beendet. Das Problem ist m�glicherweise nur tempor�r. In diesem Fall dr�cken Sie <b> Weiter</b> und beginnen Sie den Installationsproze� von neuem. N�heres zu Fehlermeldungen und -behebung findet sich im Handbuch.";
$GLOBALS['strErrorOccured']			= "Der folgende Fehler ist aufgetreten:";
$GLOBALS['strErrorInstallDatabase']		= "Die Datenbankstruktur konnte nicht angelegt werden.";
$GLOBALS['strErrorUpgrade'] = 'The existing installation\'s database could not be upgraded.';
$GLOBALS['strErrorInstallConfig']		= "Die Konfigurationsdatei oder die Datenbank konnten nicht aktualisiert werden.";
$GLOBALS['strErrorInstallDbConnect']		= "Eine Verbindung zur Datenbank war nicht m�glich.";

$GLOBALS['strUrlPrefix']			= "URL Pr�fix";

$GLOBALS['strProceed']				= "Weiter &gt;";
$GLOBALS['strInvalidUserPwd']			= "Fehlerhafter Benutzername oder Passwort";

$GLOBALS['strUpgrade']				= "Prorammerg�nzung (Upgrade)";
$GLOBALS['strSystemUpToDate']		= "Das System ist up to date. Eine Erg�nzung (Upgrade) ist nicht notwendig. <br>
Dr�cken Sie <b>Weiter</b>, um zur Startseite zu gelangen.";
$GLOBALS['strSystemNeedsUpgrade']		= "Die Datenbankstruktur und die Konfigurationsdateien sollten aktualisiert werden. Dr�cken Sie <b>Weiter</b> f�r den Start des Aktualisierungslauf.
 <br><br>Abh�ngig von der derzeitig genutzten Version und der Anzahl der vorhandenen Statistiken kann dieser Proze� Ihre Datenbank stark belasten. Das Upgrade kann einige Minuten dauern.";
$GLOBALS['strSystemUpgradeBusy']		= "Aktualisierung des Systems l�uft. Bitte warten ...";
$GLOBALS['strSystemRebuildingCache']		= "Cache wird neu erstellt. Bitte warten ...";
$GLOBALS['strServiceUnavalable']		= "Dieser Service ist zur Zeit nicht erreichbar. System wird aktualisiert...";

$GLOBALS['strConfigNotWritable']		= "F�r die Datei <i>config.inc.php</i>  besteht Schreibschutz";



/*********************************************************/
/* Configuration translations                            */
/*********************************************************/

// Global
$GLOBALS['strChooseSection']			= "Bereichsauswahl";
$GLOBALS['strDayFullNames'] 			= array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag");
$GLOBALS['strEditConfigNotPossible']   		= "�nderungen der Systemeinstellung nicht m�glich. F�r die Konfigurationsdatei  <i>config.inc.php</i> besteht Schreibschutz. ".
										  "F�r �nderungen mu� der Schreibschutz aufgehoben werden.";
$GLOBALS['strEditConfigPossible']		= "Unbefugte System�nderungen sind m�glich. Die Zugriffsrechte der Konfigurationsdatei <i>config.inc.php</i> sind auf Schreibbrechtigung gesetzt. ".
										  "Zur Sicherung des System sollte der Schreibschutz gesetzt werden. N�here Informationen im Handbuch.";



// Database
$GLOBALS['strDatabaseSettings']			= "Datenbankeinstellungen";
$GLOBALS['strDatabaseServer']			= "Datenbank Server";
$GLOBALS['strDbLocal']				= "Verbindung zum lokalen Server mittels Sockets"; // Pg only
$GLOBALS['strDbHost']				= "Datenbank Hostname";
$GLOBALS['strDbPort']				= "Datenbank Portnummer";
$GLOBALS['strDbUser']				= "Datenbank Benutzername";
$GLOBALS['strDbPassword']			= "Datenbank Passwort";
$GLOBALS['strDbName']			= "Datenbank Name";

$GLOBALS['strDatabaseOptimalisations']		= " Datenbankoptimierungen";
$GLOBALS['strPersistentConnections']		= "Dauerhafte Verbindung zur Datenbank";
$GLOBALS['strInsertDelayed']			= "Datenbank wird zeitlich versetzt beschrieben";
$GLOBALS['strCompatibilityMode']		= "Kompatibilit�tsmodus (Datenbank)";
$GLOBALS['strCantConnectToDb']		= "Verbindung zur Datenbank nicht m�glich";


// Invocation and Delivery
$GLOBALS['strInvocationAndDelivery']		= "Einstellungen f�r Bannercode und -auslieferung";

$GLOBALS['strAllowedInvocationTypes']		= "Zugelassene Bannercodes (Mehrfachauswahl m�glich)";
$GLOBALS['strAllowRemoteInvocation']		= "Normaler Bannercode (Remote)";
$GLOBALS['strAllowRemoteJavascript']		= "Bannercode f�r Javascript ";
$GLOBALS['strAllowRemoteFrames']		= "Bannercode f�r Frames (iframe/ilayer)";
$GLOBALS['strAllowRemoteXMLRPC']		= "Bannercode bei Nutzung von XML-RPC";
$GLOBALS['strAllowLocalmode']			= "Lokaler Modus";
$GLOBALS['strAllowInterstitial']			= "Interstitial oder Floating DHTML";
$GLOBALS['strAllowPopups']			= "PopUp";

$GLOBALS['strUseAcl']				= "Einschr�nkungen w�hrend der Bannerauslieferung werden ber�cksichtigt";

$GLOBALS['strDeliverySettings']			= "Einstellungen f�r Bannerauslieferung";
$GLOBALS['strCacheType']			= "Cache-Type f�r Bannerauslieferung";
$GLOBALS['strCacheFiles']			= "Dateien";
$GLOBALS['strCacheDatabase']			= "Datenbank";
$GLOBALS['strCacheShmop']			= "Shared memory/Shmop";
$GLOBALS['strCacheSysvshm']			= "Shared memory/Sysvshm";
$GLOBALS['strExperimental']			= "Experimental";
$GLOBALS['strKeywordRetrieval']		= "Schl�sselwortselektion";
$GLOBALS['strBannerRetrieval']			= "Modus f�r Bannerselektion";
$GLOBALS['strRetrieveRandom']			= "Zufallsbasierte Bannerselektion (Voreinstellung)";
$GLOBALS['strRetrieveNormalSeq']		= "Sequentielle Bannerselektion";
$GLOBALS['strWeightSeq']			= "Gewichtungsabh�ngige Bannerselektion ";
$GLOBALS['strFullSeq']				= " Streng sequentielle Bannerselektion ";
$GLOBALS['strUseConditionalKeys']		= "Logische Operatoren sind bei Direktselektion zul�ssig ";
$GLOBALS['strUseMultipleKeys']			= "Mehrere Schl�sselw�rter sind f�r die Direktselektion zugelassen ";

$GLOBALS['strZonesSettings']			= "Selektion �ber Zonen";
$GLOBALS['strZoneCache']			= "Einrichten von Zwischenspeichern (Cache) f�r Zonen. Beschleunigt die Bannerauslieferung";
$GLOBALS['strZoneCacheLimit']			= "Aktualisierungsintervall der Zwischenspeicher (Cache) in Sekunden";
$GLOBALS['strZoneCacheLimitErr']		= "Aktualisierungsintervall mu� ein positiver ganzzahliger Wert sein";

$GLOBALS['strP3PSettings']			= "P3P Privacy Policies";
$GLOBALS['strUseP3P']				= "Verwendung von P3P Policies";
$GLOBALS['strP3PCompactPolicy']		= "P3P Compact Policies";
$GLOBALS['strP3PPolicyLocation']		= "P3P Policies Location"; 



// Banner Settings
$GLOBALS['strBannerSettings']			= "Bannereinstellungen";

$GLOBALS['strAllowedBannerTypes']		= "Zugelassene Bannertypen (Mehrfachnennung m�glich)";
$GLOBALS['strTypeSqlAllow']			= "Banner in Datenbank speichern (SQL)";
$GLOBALS['strTypeWebAllow']			= "Banner auf Webserver (lokal)";
$GLOBALS['strTypeUrlAllow']			= "Banner �ber URL verwalten";
$GLOBALS['strTypeHtmlAllow']			= "HTML-Banners";
$GLOBALS['strTypeTxtAllow']			= "Textanzeigen";

$GLOBALS['strTypeWebSettings']		= "Bannerkonfiguration auf Webserver (lokal)";
$GLOBALS['strTypeWebMode']			= "Speichermethode";
$GLOBALS['strTypeWebModeLocal']		= "Lokales Verzeichnis";
$GLOBALS['strTypeWebModeFtp']		= "Externer FTP-Server";
$GLOBALS['strTypeWebDir']			= "Webserver-Verzeichnis";  
$GLOBALS['strTypeWebFtp']			= "FTP-Bannerserver";
$GLOBALS['strTypeWebUrl']			= "(�ffentliche) URL"; 
$GLOBALS['strTypeWebSslUrl']			= "(�ffentliche) URL (SSL)";
$GLOBALS['strTypeFTPHost']			= "FTP-Host";
$GLOBALS['strTypeFTPDirectory']		= "FTP-Verzeichnis";
$GLOBALS['strTypeFTPUsername']		= "FTP-Benutzername";
$GLOBALS['strTypeFTPPassword']		= "FTP-Passwort";
$GLOBALS['strTypeFTPErrorDir']		= "FTP-Verzeichnis existiert nicht";
$GLOBALS['strTypeFTPErrorConnect']		= "Verbindung zum FTP Server nicht m�glich. Benutzername oder Passwort waren fehlerhaft";
$GLOBALS['strTypeFTPErrorHost']			= "Hostname f�r FTP-Server ist fehlerhaft";
$GLOBALS['strTypeDirError']				= "Das lokale Verzeichnis existiert nicht";



$GLOBALS['strDefaultBanners']			= "Ersatzbanner <i>(kein regul�res Banner steht zur Verf�gung)</i>";
$GLOBALS['strDefaultBannerUrl']		= "Bild-URL f�r Ersatzbanner";
$GLOBALS['strDefaultBannerTarget']		= "Ziel-URL f�r Ersatzbanner";

$GLOBALS['strTypeHtmlSettings']		= "Optionen f�r HTML-Banner";

$GLOBALS['strTypeHtmlAuto']			= "HTML-Code zum Aufzeichnen der AdClicks modifizieren";
$GLOBALS['strTypeHtmlPhp']			= "Ausf�hrbarer PHP-Code ist in HTML-Banner zugelassen ";



// Host information and Geotargeting
$GLOBALS['strHostAndGeo']			= "Geotargeting (Hostinformation und Standortbestimmung) der Besucher";

$GLOBALS['strRemoteHost']			= "Host des Besuchers";
$GLOBALS['strReverseLookup']			= "Es wird versucht, den Name des Hosts f�r den Besucher zu ermitteln, wenn er nicht mitgeliefert wird";
$GLOBALS['strProxyLookup']				= "Es wird versucht, die  IP-Adresse des Besuchers zu ermitteln, wenn er einen Proxy-Server nutzt";

$GLOBALS['strGeotargeting']			= "Geotargeting (Standortbestimmung) ";
$GLOBALS['strGeotrackingType']			= "Datenbanktypen f�r Geotargeting ";
$GLOBALS['strGeotrackingLocation'] 		= "Standort der Datenbank f�r Geotargeting";
$GLOBALS['strGeotrackingLocationError'] = "Keine Datenbank f�r Geotargeting an der genannten Adresse gefunden";
$GLOBALS['strGeoStoreCookie']			= "Speichern des Ergebnisses in einem Cookie zur sp�teren Nutzung";



// Statistics Settings
$GLOBALS['strStatisticsSettings']			= "Statistikeinstellungen";

$GLOBALS['strStatisticsFormat']			= "Statistikformat";
$GLOBALS['strCompactStats']				= " Statistikformat ";
$GLOBALS['strLogAdviews']				= "Jede Bannerauslieferung wird als ein AdView protokolliert";
$GLOBALS['strLogAdclicks']				= "Jeder Klick auf ein Banner wird als ein AdClick protokolliert";
$GLOBALS['strLogSource']				= "Die Parameter der Quelle werden  bei der Bannerauslieferung protokolliert";
$GLOBALS['strGeoLogStats']				= "Das Land des Besuchers wird protokolliert";
$GLOBALS['strLogHostnameOrIP']			= "Hostname oder IP-Adresse des Besuchers wird protokolliert";
$GLOBALS['strLogIPOnly']				= "Ausschlie�lich die IP-Adresse des Besuchers wird protokolliert, auch wenn der Hostname erkannt ist";
$GLOBALS['strLogIP']					= "Die IP-Adresse des Besuchers wird protokolliert";
$GLOBALS['strLogBeacon']				= " Ein Beacon (Minibild) wird verwendet, um sicherzustellen, da� nur vollst�ndige Bannerauslieferungen protokolliert werden ";

$GLOBALS['strRemoteHosts']				= "Host des Besuchers";
$GLOBALS['strIgnoreHosts']				= "AdViews und AdClicks f�r Besucher mit folgenden IP-Adressen oder Hostnamen bleiben in den Statistiken unber�cksichtigt";
$GLOBALS['strBlockAdviews']				= "Reloadsperre (Zeitraum in Sek.)";
$GLOBALS['strBlockAdclicks']			= " Reclicksperre (Zeitraum in Sek.) ";

$GLOBALS['strPreventLogging']			= "Protokollieren verhindern";
$GLOBALS['strEmailWarnings']			= "Warnungen per eMail";
$GLOBALS['strAdminEmailHeaders']		= "Kopfzeile f�r alle eMails, die versandt werden";
$GLOBALS['strWarnLimit']				= "Warnung per eMail bei Unterschreiten der definierten Untergrenze";
$GLOBALS['strWarnLimitErr']				= "Warnlimit mu� eine positive Ganzzahl sein";
$GLOBALS['strWarnAdmin']				= "Warnung per eMail an den Administrator, wenn eine Kampagne ausgelaufen ist";
$GLOBALS['strWarnClient']				= "Warnung per eMail an den Inserenten, wenn eine Kampagne ausgelaufen ist";
$GLOBALS['strQmailPatch']				= "Kopfzeile auch f�r qMail lesbar machen";

$GLOBALS['strAutoCleanTables']			= "Datenbank l�schen";
$GLOBALS['strAutoCleanStats']			= "Statistiken l�schen";
$GLOBALS['strAutoCleanUserlog']		= "Benutzerprotokoll l�schen"; 
$GLOBALS['strAutoCleanStatsWeeks']		= "Zeitraum in Wochen, nachdem Statistiken gel�scht werden <br><i>(jedoch mindestens 3 Wochen)</i>";
$GLOBALS['strAutoCleanUserlogWeeks']		= "Zeitraum in Wochen, nachdem Statistiken gel�scht werden <br><i>(3 Wochen mindestens)</i>";
$GLOBALS['strAutoCleanErr']			= "Der Zeitraum, nach dem die Daten gel�scht werden sollen, mu� mindestens 3 Wochen betragen";
$GLOBALS['strAutoCleanVacuum']		= "VACUUM ANALYZE Tabellen jede Nacht"; // only Pg


// Administrator settings
$GLOBALS['strAdministratorSettings']		= "Einstellungen f�r Administrator";

$GLOBALS['strLoginCredentials']			= "Erkennungspr�fung";
$GLOBALS['strAdminUsername']			= "Benutzername (Admin)";
$GLOBALS['strInvalidUsername']			= "Benutzername fehlerhaft";

$GLOBALS['strBasicInformation']			= "Basisinformation";
$GLOBALS['strAdminFullName']			= "Name, Vorname";
$GLOBALS['strAdminEmail']			= "E-Mail";
$GLOBALS['strCompanyName']			= "Firma";

$GLOBALS['strAdminCheckUpdates']		= "Pr�fen, ob neue Programmversionen vorhanden sind";
$GLOBALS['strAdminCheckEveryLogin']		= "Bei jedem Login";
$GLOBALS['strAdminCheckDaily']		= "T�glich";
$GLOBALS['strAdminCheckWeekly']		= "W�chentlich";
$GLOBALS['strAdminCheckMonthly']		= "Monatlich";
$GLOBALS['strAdminCheckNever']		= "Nie"; 

$GLOBALS['strAdminNovice']			= "L�schvorg�nge im Admin-Bereich nur mit Sicherheitsbest�tigung";
$GLOBALS['strUserlogEmail']			= "Alle ausgehende eMails protokollieren ";
$GLOBALS['strUserlogPriority']			= "St�ndliche Rekalkulation der Priorit�ten wird protokolliert";
$GLOBALS['strUserlogAutoClean']		= "Protokollieren des S�uberns der Datenbank";


// User interface settings
$GLOBALS['strGuiSettings']			= "Konfiguration Benutzerbereich (Inhaber des AdServers)";

$GLOBALS['strGeneralSettings']				= "Einstellungen f�r das Gesamtprogramm";
$GLOBALS['strAppName']				= "Name oder Bezeichnung der Anwendung";
$GLOBALS['strMyHeader']				= "Kopfzeile im Admin-Bereich";
$GLOBALS['strMyHeaderError']		= "Die Datei f�r die Kopfzeile wurde an angegebenen Adresse nicht vorgefunden";
$GLOBALS['strMyFooter']				= "Fu�zeile im Admin-Bereich";
$GLOBALS['strMyFooterError']		= "Die Datei f�r die Fu�zeile wurde an angegebenen Adresse nicht vorgefunden";
$GLOBALS['strGzipContentCompression']		= "Komprimieren mit GZIP";

$GLOBALS['strClientInterface']			= "Inserentenbereich";
$GLOBALS['strClientWelcomeEnabled']		= "Begr��ungstext f�r Inserenten verwenden";
$GLOBALS['strClientWelcomeText']		= "Begr��ungstext<br><i>(HTML Tags sind zugelassen)</i>";



// Interface defaults
$GLOBALS['strInterfaceDefaults']		= "Einstellung der Voreinstellungen";

$GLOBALS['strInventory']			= "Bestandsverzeichnis";
$GLOBALS['strShowCampaignInfo']		= "Anzeigen zus�tzlicher Informationen auf der Seite <i>�bersicht Kampagnen</i>"; 
$GLOBALS['strShowBannerInfo']			= "Anzeigen zus�tzlicher Bannerinformationen auf der Seite <i>�bersicht Banner</i> ";
$GLOBALS['strShowCampaignPreview']		= "Vorschau aller Banner auf der Seite  <i>�bersicht Banner </i>";
$GLOBALS['strShowBannerHTML']			= "Anzeige des Banners anstelle des HTML-Codes bei Vorschau von HTML-Bannern ";
$GLOBALS['strShowBannerPreview']		= "Bannervorschau oben auf allen Seiten mit dem Bezug zum Banner ";
$GLOBALS['strHideInactive']			= "Verbergen inaktive Teile auf den �bersichtsseiten";
$GLOBALS['strGUIShowMatchingBanners']		= "Anzeige des zugeh�renden Banner auf der Seite <i>Verkn�pfte Banner</i>";
$GLOBALS['strGUIShowParentCampaigns']		= "Anzeige der zugeh�renden Kampagne auf der Seite <i>Vekn�pfte Banner</i>";
$GLOBALS['strGUILinkCompactLimit']		= "Verbergen nicht verkn�pfter Banner auf der Seite <i>Verkn�pfte Banner</i>, sofern es mehr sind als ";

$GLOBALS['strStatisticsDefaults'] 		= "Statistiken";
$GLOBALS['strBeginOfWeek']			= "Wochenbeginn";
$GLOBALS['strPercentageDecimals']		= "Dezimalstellen bei Prozentangaben";

$GLOBALS['strWeightDefaults']			= "Gewichtung (Voreinstellung)";
$GLOBALS['strDefaultBannerWeight']		= "Gewichtung Banner (Voreinstellung)";
$GLOBALS['strDefaultCampaignWeight']		= "Gewichtung Kampagne (Voreinstellung)";
$GLOBALS['strDefaultBannerWErr']		= "Voreinstellung f�r Bannergewichtung mu� eine positive Ganzzahl sein";
$GLOBALS['strDefaultCampaignWErr']		= " Voreinstellung f�r Kampagne mu� eine positive Ganzzahl sein";




// Not used at the moment
$GLOBALS['strTableBorderColor']		= "Table Border Color";
$GLOBALS['strTableBackColor']			= "Table Back Color";
$GLOBALS['strTableBackColorAlt']		= "Table Back Color (Alternative)";
$GLOBALS['strMainBackColor']			= "Main Back Color";
$GLOBALS['strOverrideGD']			= "Override GD Imageformat";
$GLOBALS['strTimeZone']			= "Time Zone";

?>