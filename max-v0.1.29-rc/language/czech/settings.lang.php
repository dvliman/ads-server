<?php

/*
+---------------------------------------------------------------------------+
| Max Media Manager v0.1                                                    |
| =================                                                         |
|                                                                           |
| Copyright (c) 2003-2005 m3 Media Services Limited                         |
| For contact details, see: http://www.m3.net/                              |
|                                                                           |
| Copyright (c) 2000-2003 the phpAdsNew developers                          |
| For contact details, see: http://www.phpadsnew.com/                       |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id: settings.lang.php 1167 2004-12-17 17:07:28Z andrew $
*/



// Installer translation strings
$GLOBALS['strInstall']				= "Install";
$GLOBALS['strChooseInstallLanguage']		= "Choose language for the installation procedure";
$GLOBALS['strLanguageSelection']		= "Language Selection";
$GLOBALS['strDatabaseSettings']			= "Database Settings";
$GLOBALS['strAdminSettings']			= "Administrator Settings";
$GLOBALS['strAdvancedSettings']			= "Advanced Settings";
$GLOBALS['strOtherSettings']			= "Other settings";

$GLOBALS['strWarning']				= "Warning";
$GLOBALS['strFatalError']			= "A fatal error occurred";
$GLOBALS['strUpdateError']			= "An error occured while updating";
$GLOBALS['strUpdateDatabaseError']	= "Due to unknown reasons the update of the database structure wasn't succesful. The recommended way to proceed is to click <b>Retry updating</b> to try to correct these potential problems. If you are sure these errors won't affect the functionality of ".$phpAds_productname." you can click <b>Ignore errors</b> to continue. Ignoring these errors may cause serious problems and is not recommended!";
$GLOBALS['strAlreadyInstalled']			= $phpAds_productname." is already installed on this system. If you want to configure it go to <a href='settings-index.php'>settings interface</a>";
$GLOBALS['strCouldNotConnectToDB']		= "Could not connect to database, please recheck the settings you specified";
$GLOBALS['strCreateTableTestFailed']		= "The user you specified doesn't have permission to create or update the database structure, please contact the database administrator.";
$GLOBALS['strUpdateTableTestFailed']		= "The user you specified doesn't have permission to update the database structure, please contact the database administrator.";
$GLOBALS['strTablePrefixInvalid']		= "Table prefix contains invalid characters";
$GLOBALS['strTableInUse']			= "The database which you specified is already used for ".$phpAds_productname.", please use a different table prefix, or read the UPGRADE.txt file for upgrading instructions.";
$GLOBALS['strTableWrongType']		= "The table type you selected isn't supported by your installation of ".$phpAds_dbmsname;
$GLOBALS['strMayNotFunction']			= "Before you continue, please correct these potential problems:";
$GLOBALS['strFixProblemsBefore']		= "The following item(s) need to be corrected before you can install ".$phpAds_productname.". If you have any questions about this error message, please read the <i>Administrator guide</i>, which is part of the package you downloaded.";
$GLOBALS['strFixProblemsAfter']			= "If you are not able to correct the problems listed above, please contact the administrator of the server you are trying to install ".$phpAds_productname." on. The administrator of the server may be able to help you.";
$GLOBALS['strIgnoreWarnings']			= "Ignore warnings";
$GLOBALS['strWarningDBavailable']		= "The version of PHP you are using doesn't have support for connecting to a ".$phpAds_dbmsname." database server. You need to enable the PHP ".$phpAds_dbmsname." extension before you can proceed.";
$GLOBALS['strWarningPHPversion']		= $phpAds_productname." requires PHP 4.0 or higher to function correctly. You are currently using {php_version}.";
$GLOBALS['strWarningRegisterGlobals']		= "The PHP configuration variable register_globals needs to be turned on.";
$GLOBALS['strWarningMagicQuotesGPC']		= "The PHP configuration variable magic_quotes_gpc needs to be turned on.";
$GLOBALS['strWarningMagicQuotesRuntime']	= "The PHP configuration variable magic_quotes_runtime needs to be turned off.";
$GLOBALS['strWarningFileUploads']		= "The PHP configuration variable file_uploads needs to be turned on.";
$GLOBALS['strWarningTrackVars']			= "The PHP configuration variable track_vars needs to be turned on.";
$GLOBALS['strWarningPREG']				= "The version of PHP you are using doesn't have support for PERL compatible regular expressions. You need to enable the PREG extension before you can proceed.";
$GLOBALS['strConfigLockedDetected']		= $phpAds_productname." has detected that your <b>config.inc.php</b> file is not writeable by the server. You can't proceed until you change permissions on the file. Read the supplied documentation if you don't know how to do that.";
$GLOBALS['strCantUpdateDB']  			= "It is currently not possible to update the database. If you decide to proceed, all existing banners, statistics and advertisers will be deleted.";
$GLOBALS['strIgnoreErrors']			= "Ignore errors";
$GLOBALS['strRetryUpdate']			= "Retry updating";
$GLOBALS['strTableNames']			= "Table Names";
$GLOBALS['strTablesPrefix']			= "Table names prefix";
$GLOBALS['strTablesType']			= "Table type";

$GLOBALS['strInstallWelcome']			= "Welcome to ".$phpAds_productname;
$GLOBALS['strInstallMessage']			= "Before you can use ".$phpAds_productname." it needs to be configured and <br> the database needs to be created. Click <b>Proceed</b> to continue.";
$GLOBALS['strInstallSuccess']			= "<b>The installation of ".$phpAds_productname." is now complete.</b><br><br>In order for ".$phpAds_productname." to function correctly you also need
						   to make sure the maintenance file is run every hour. More information about this subject can be found in the documentation.
						   <br><br>Click <b>Proceed</b> to go the configuration page, where you can 
						   set up more settings. Please do not forget to lock the config.inc.php file when you are finished to prevent security
						   breaches.";
$GLOBALS['strUpdateSuccess']			= "<b>The upgrade of ".$phpAds_productname." was succesful.</b><br><br>In order for ".$phpAds_productname." to function correctly you also need
						   to make sure the maintenance file is run every hour (previously this was every day). More information about this subject can be found in the documentation.
						   <br><br>Click <b>Proceed</b> to go to the administration interface. Please do not forget to lock the config.inc.php file 
						   to prevent security breaches.";
$GLOBALS['strInstallNotSuccessful']		= "<b>The installation of ".$phpAds_productname." was not succesful</b><br><br>Some portions of the install process could not be completed.
						   It is possible these problems are only temporarily, in that case you can simply click <b>Proceed</b> and return to the
						   first step of the install process. If you want to know more on what the error message below means, and how to solve it, 
						   please consult the supplied documentation.";
$GLOBALS['strErrorOccured']			= "The following error occured:";
$GLOBALS['strErrorInstallDatabase']		= "The database structure could not be created.";
$GLOBALS['strErrorUpgrade'] = 'The existing installation\'s database could not be upgraded.';
$GLOBALS['strErrorInstallConfig']		= "The configuration file or database could not be updated.";
$GLOBALS['strErrorInstallDbConnect']		= "It was not possible to open a connection to the database.";

$GLOBALS['strUrlPrefix']			= "Delivery Engine URL Prefix";
$GLOBALS['strSslUrlPrefix']			= "Delivery Engine SSL URL Prefix";

$GLOBALS['strProceed']				= "Proceed &gt;";
$GLOBALS['strInvalidUserPwd']			= "Invalid username or password";

$GLOBALS['strUpgrade']				= "Upgrade";
$GLOBALS['strSystemUpToDate']			= "Your system is already up to date, no upgrade is needed at the moment. <br>Click on <b>Proceed</b> to go to home page.";
$GLOBALS['strSystemNeedsUpgrade']		= "The database structure and configuration file need to be upgraded in order to function correctly. Click <b>Proceed</b> to start the upgrade process. <br><br>Depending on which version you are upgrading from and how many statistics are already stored in the database, this process can cause high load on your database server. Please be patient, the upgrade can take up to a couple of minutes.";
$GLOBALS['strSystemUpgradeBusy']		= "System upgrade in progress, please wait...";
$GLOBALS['strSystemRebuildingCache']		= "Rebuilding cache, please wait...";
$GLOBALS['strServiceUnavalable']		= "The service is temporarily unavailable. System upgrade in progress";

$GLOBALS['strConfigNotWritable']		= "Your config.inc.php file is not writable";





/*********************************************************/
/* Configuration translations                            */
/*********************************************************/

// Global
$GLOBALS['strChooseSection']			= "Vyber sekci";
$GLOBALS['strDayFullNames'] 			= array("Ned�le","Pond�l�","�ter�","St�eda","�tvrtek","P�tek","Sobota");
$GLOBALS['strEditConfigNotPossible']   		= "Upozorn�n� : nelze m�nit syst�mov� hodnoty";
$GLOBALS['strEditConfigPossible']		= "Upozorn�n� : lze m�nit syst�mov� hodnoty";



// Database
$GLOBALS['strDatabaseSettings']			= "Nastaven� datab�ze";
$GLOBALS['strDatabaseServer']			= "Nastaven� datab�zov�ho serveru";
$GLOBALS['strDbLocal']				= "P�ipojen� na socket"; // Pg only
$GLOBALS['strDbHost']				= "Server";
$GLOBALS['strDbPort']				= "Port";
$GLOBALS['strDbUser']				= "U�ivatelsk� jm�no";
$GLOBALS['strDbPassword']			= "Heslo";
$GLOBALS['strDbName']				= "N�zev tabulky";

$GLOBALS['strDatabaseOptimalisations']		= "Dal�� nastaven�";
$GLOBALS['strPersistentConnections']		= "Pou��t trval� p�ipojen�";
$GLOBALS['strInsertDelayed']			= "Pou��t zpo�d�n� inserty";
$GLOBALS['strCompatibilityMode']		= "Pou��t kompatibiln� m�d";
$GLOBALS['strCantConnectToDb']			= "Can't connect to database";



// Invocation and Delivery
$GLOBALS['strInvocationAndDelivery']		= "V�zvy a doru�ov�n�";

$GLOBALS['strAllowedInvocationTypes']		= "Povolit typ doru�ov�n�";
$GLOBALS['strAllowRemoteInvocation']		= "Vzd�len� vol�n�";
$GLOBALS['strAllowRemoteInvocationNoCookies']		= "Vzd�len� vol�n� - bez Cookies";
$GLOBALS['strAllowRemoteJavascript']		= "Vzd�len� vol�n� - Javascript";
$GLOBALS['strAllowRemoteFrames']		= "Vzd�len� vol�n� - Frames";
$GLOBALS['strAllowRemoteXMLRPC']		= "Vzd�len� vol�n� - XML-RPC";
$GLOBALS['strAllowLocalmode']			= "M�stn� vol�n�";
$GLOBALS['strAllowInterstitial']		= "V�suvn� bannery";
$GLOBALS['strAllowPopups']			= "Pop-Up";

$GLOBALS['strUseAcl']				= "Vyhodnocovat omezen� doru�ov�n� v pr�b�hu doru�ov�n�";

$GLOBALS['strDeliverySettings']			= "Nastaven� doru�ov�n�";
$GLOBALS['strCacheType']				= "Typ doru�ovac� cache";
$GLOBALS['strCacheFiles']				= "Soubory";
$GLOBALS['strCacheDatabase']			= "Database";
$GLOBALS['strCacheShmop']				= "Sd�len� pam�/Shmop";
$GLOBALS['strCacheSysvshm']				= "Sd�len� pam�/Sysvshm";
$GLOBALS['strExperimental']				= "Experiment�ln�";
$GLOBALS['strKeywordRetrieval']			= "V�b�r kl��ov�ch slove";
$GLOBALS['strBannerRetrieval']			= "V�b�r banner�";
$GLOBALS['strRetrieveRandom']			= "N�hodn� v�b�r banner� (v�choz�)";
$GLOBALS['strRetrieveNormalSeq']		= "Narm�ln� 1:1 v�m�na";
$GLOBALS['strWeightSeq']				= "V�m�na banner� podle v�hy";
$GLOBALS['strFullSeq']					= "Pln� souvisl� v�m�na banner�";
$GLOBALS['strUseKeywords']				= "Pou��t kl��ov� slova pro v�b�r banneru";
$GLOBALS['strUseConditionalKeys']		= "Povolit logick� oper�tory p�i pou�it� p��m� volby";
$GLOBALS['strUseMultipleKeys']			= "Povolit v�cero kl��ov�ch slov p�i pou�it� p��m� volby";

$GLOBALS['strZonesSettings']			= "Doru�ovac� z�na";
$GLOBALS['strZoneCache']			= "Caschovat z�ny, pro zv��en� rychlosti";
$GLOBALS['strZoneCacheLimit']			= "�as mezi updatem cache (v sekund�ch)";
$GLOBALS['strZoneCacheLimitErr']		= "�as mus� b�t ud�n v kladn�ch ��seln�ch hodnot�ch";

$GLOBALS['strP3PSettings']			= "P3P pravidla politiky";
$GLOBALS['strUseP3P']				= "Pou�it� P3P politiky";
$GLOBALS['strP3PCompactPolicy']			= "P3P jednoduch� politika";
$GLOBALS['strP3PPolicyLocation']		= "P3P politika um�st�n�"; 



// Banner Settings
$GLOBALS['strBannerSettings']			= "Nastaven� banneru";

$GLOBALS['strAllowedBannerTypes']		= "Povolit typy banner�";
$GLOBALS['strTypeSqlAllow']			= "Lok�ln� banner (SQL)";
$GLOBALS['strTypeWebAllow']			= "Lok�ln� banner (Webserver)";
$GLOBALS['strTypeUrlAllow']			= "Extern� banner";
$GLOBALS['strTypeHtmlAllow']			= "HTML bannery";
$GLOBALS['strTypeTxtAllow']			= "Textovou reklamu";

$GLOBALS['strTypeWebSettings']			= "Lok�ln� banner (Webserver) nastaven�";
$GLOBALS['strTypeWebMode']			= "Typ ukl�d�n�";
$GLOBALS['strTypeWebModeLocal']			= "M�stn� adres��";
$GLOBALS['strTypeWebModeFtp']			= "Exern� FTP server";
$GLOBALS['strTypeWebDir']			= "M�stn� adres��";
$GLOBALS['strTypeWebFtp']			= "FTP m�d";
$GLOBALS['strTypeWebUrl']			= "Ve�ejn� URL";
$GLOBALS['strTypeWebSslUrl']			= "Ve�ejn� URL (SSL)";
$GLOBALS['strTypeFTPHost']			= "FTP server";
$GLOBALS['strTypeFTPDirectory']			= "Adres��";
$GLOBALS['strTypeFTPUsername']			= "U�iv. jm�no";
$GLOBALS['strTypeFTPPassword']			= "Heslo";
$GLOBALS['strTypeFTPErrorDir']			= "Adres�� na serveru neexistuje";
$GLOBALS['strTypeFTPErrorConnect']		= "Nemohu se p�ipojit na vzd�leny FTP server, zkontroluj p�ihla�ovac� �daje";
$GLOBALS['strTypeFTPErrorHost']			= "N�zev serveru FTP nen� spr�vn�";
$GLOBALS['strTypeDirError']				= "Lok�ln� adres�� neexistuje";



$GLOBALS['strDefaultBanners']			= "V�choz� banner";
$GLOBALS['strDefaultBannerUrl']			= "URL v�choz�ho banneru";
$GLOBALS['strDefaultBannerTarget']		= "V�choz� sm�rov�n� URL";

$GLOBALS['strTypeHtmlSettings']			= "Parametry HTML banneru";
$GLOBALS['strTypeHtmlAuto']			= "Automaticky upravovat HTML bannery, aby bylo mo�n� sledovat kliknut�";
$GLOBALS['strTypeHtmlPhp']			= "Povolit spou�t�n� PHP v�raz� z HTML banner�";



// Host information and Geotargeting
$GLOBALS['strHostAndGeo']				= "Informace o hostech a geoc�le";

$GLOBALS['strRemoteHost']				= "Vzd�len� host";
$GLOBALS['strReverseLookup']			= "Pokusit se ur�it n�zev hostitele, pokud nen� poskytnuto serverem";
$GLOBALS['strProxyLookup']				= "Pokusit se urcit pravou IP adresu n�v�t�vn�ka, kter� pou��v� proxy server";
$GLOBALS['strObfuscate']					= "Zm�st zdroj pokud doru�uje reklamu";

$GLOBALS['strGeotargeting']				= "Geoc�len�";
$GLOBALS['strGeotrackingType']			= "Typ datab�ze geoc�le";
$GLOBALS['strGeotrackingLocation'] 		= "M�sto datab�ze geoc�len�";
$GLOBALS['strGeotrackingLocationError'] = "Geobaze neexistuje ...";
$GLOBALS['strGeoStoreCookie']			= "Ulo�it cookie s v�sledkem pro p��t�";



// Statistics Settings
$GLOBALS['strStatisticsSettings']		= "Nastaven� statistik";

$GLOBALS['strStatisticsFormat']			= "Form�t statistik";
$GLOBALS['strCompactStats']				= "Form�t statistik";
$GLOBALS['strLogAdViews']				= "Logovat banner v�dy pokud je doru�en";
$GLOBALS['strLogAdClicks']				= "Logovat AdClick v�dy kdy� n�v�t�vn�k klikne na banner";
$GLOBALS['strLogAdConversions']				= "Logovat AdConversion v�dy, pokud n�v�t�vn�k shl�dne str�nku s AdConversion �idlem";
$GLOBALS['strLogSource']				= "Logovat parametr zdroje p�edan� p�i vol�n�";
$GLOBALS['strGeoLogStats']				= "Logovat zemi n�v�z�vn�ka ve statistik�ch";
$GLOBALS['strLogHostnameOrIP']			= "Logovat hostitele nebo IP n�v�t�vn�ka";
$GLOBALS['strLogIPOnly']				= "Logovat pouze IP adresu n�v�t�vn�ka i kdy� je zn�m n�zev hostitele";
$GLOBALS['strLogIP']					= "Logovat IP adresu n�v�t�vn�ka";
$GLOBALS['strLogBeacon']				= "Pou��t mal� obr�zek k logov�n� AdViews, aby bylo zji�t�no, �e jsou logov�ny pouze doru�en� bannery";

$GLOBALS['strRemoteHosts']				= "Vzd�leb� host";
$GLOBALS['strIgnoreHosts']				= "Neukl�dat statistiky pro n�v�t�vn�ky pou��vaj�c� jednu z n�sleduj�c�ch IP adres nebo n�zv� hostitel�";
$GLOBALS['strBlockAdViews']				= "Nelogovat AdViews, pokud n�v�t�vn�k nevid�l stejn� banner b�hem (sekund)";
$GLOBALS['strBlockAdClicks']			= "Nelogovat AdClicks, pokud n�v�t�vn�k kliknul na stejn� banner b�hem (sekund)";
$GLOBALS['strBlockAdConversions']			= "Nelogovat AdConversions, pokud n�v�t�vn�k nevid�l p��slu�n� str�nky AdConv. (sekund)";


$GLOBALS['strPreventLogging']			= "Zamezit logov�n�";
$GLOBALS['strEmailWarnings']			= "E-mailov� upozornen�";
$GLOBALS['strAdminEmailHeaders']		= "P�idej n�sleduj�c� hlavi�ku ke ka�d� odes�lan� zpr�v� ".$phpAds_productname;
$GLOBALS['strWarnLimit']				= "Poslat upozorn�n�, kdy� je po�et zb�vaj�c�ch impres� ni��� ne� zde uveden�";
$GLOBALS['strWarnLimitErr']				= "Limit mus� b�t d�n ��slem kladn�m";
$GLOBALS['strWarnAdmin']				= "Upozornit administr�tora kdykoliv je kampa� t�m�� vy�erp�na";
$GLOBALS['strWarnClient']				= "Upozornit vydavatele kdykoliv je kampa� t�m�� vy�erp�na";
$GLOBALS['strWarnAgency']				= "Upozornit mana�era spole�nosti kdykoliv je kampa� t�m�� vy�erp�na";
$GLOBALS['strQmailPatch']				= "Zapnout qmail patch";

$GLOBALS['strAutoCleanTables']			= "�i�t�n� datab�ze";
$GLOBALS['strAutoCleanStats']			= "�i�t�n� statistiky";
$GLOBALS['strAutoCleanUserlog']			= "�i�t�n� u�ivatelsk�ho logu";
$GLOBALS['strAutoCleanStatsWeeks']		= "Maim�ln� st��� statistiky <br>(3 t�dny min.)";
$GLOBALS['strAutoCleanUserlogWeeks']	= "Maim�ln� st��� u�iv. logu <br>(3 t�d. min.)";
$GLOBALS['strAutoCleanErr']				= "Maxim�ln� st��� mus� b�t v�t�� ne� 3 t�dny";
$GLOBALS['strAutoCleanVacuum']			= "VACUUM ANALYZE tabulek kazdou noc"; // only Pg


// Administrator settings
$GLOBALS['strAdministratorSettings']		= "Nastaven� administr�tora";

$GLOBALS['strLoginCredentials']			= "P�ihla�ovac� �daje";
$GLOBALS['strAdminUsername']			= "Administr�torsk� jm�no";
$GLOBALS['strInvalidUsername']			= "�patn� u�iv. jm�no";

$GLOBALS['strBasicInformation']			= "Z�kladn� informace";
$GLOBALS['strAdminFullName']			= "Jm�no Administr�tora";
$GLOBALS['strAdminEmail']			= "E-mail administr�tora";
$GLOBALS['strCompanyName']			= "N�zev spole�nosti";

$GLOBALS['strAdminCheckUpdates']		= "Zji��ov�n� nov�ch verz�";
$GLOBALS['strAdminCheckEveryLogin']		= "P�i logov�n�";
$GLOBALS['strAdminCheckDaily']			= "Denn�";
$GLOBALS['strAdminCheckWeekly']			= "T�dn�";
$GLOBALS['strAdminCheckMonthly']		= "M�s��n�";
$GLOBALS['strAdminCheckNever']			= "Nikdy";

$GLOBALS['strAdminNovice']			= "Potvrzovat Maz�n�";
$GLOBALS['strUserlogEmail']			= "Logovat ve�kerou odchoz� po�tu";
$GLOBALS['strUserlogPriority']			= "Logovat hodinov� kalkulace priorit";
$GLOBALS['strUserlogAutoClean']			= "Logovat automatick� �ist�n� datab�ze";


// User interface settings
$GLOBALS['strGuiSettings']			= "Nastaven� u�ivatelsk�ho rozhran�";

$GLOBALS['strGeneralSettings']			= "Z�kladn� nastaven�";
$GLOBALS['strAppName']				= "N�zev aplikace";
$GLOBALS['strMyHeader']				= "Um�st�n� souboru s hlavi�kou";
$GLOBALS['strMyHeaderError']		= "Soubor hlavi�ky neecistuje";
$GLOBALS['strMyFooter']				= "Um�st�n� souboru s pai�kou";
$GLOBALS['strMyFooterError']		= "Soubor pati�ky neexistuje";
$GLOBALS['strGzipContentCompression']		= "Pou��t GZIP kompresi";

$GLOBALS['strClientInterface']			= "Rozhran� inzerenta";
$GLOBALS['strClientWelcomeEnabled']		= "Zapnout uv�tac� zpr�vu";
$GLOBALS['strClientWelcomeText']		= "Uv�tac� text<br>(HTML tagy povoleny)";



// Interface defaults
$GLOBALS['strInterfaceDefaults']		= "V�choz� parametry rozhran�";

$GLOBALS['strInventory']			= "Invent��";
$GLOBALS['strShowCampaignInfo']			= "Zobrazit informace o kampani na str�nce <i>P�ehled kampan�</i>";
$GLOBALS['strShowBannerInfo']			= "Zobrazit extra informace o banneru na str�nce <i>P�ehled banneru</i>";
$GLOBALS['strShowCampaignPreview']		= "Zobrazit n�hled v�ech banner� na str�nce <i>P�ehled banneru</i>";
$GLOBALS['strShowBannerHTML']			= "Zobrazit banner m�sto HTML k�du na str�nce pro n�hled abnneru";
$GLOBALS['strShowBannerPreview']		= "Zobrazit n�hled banneru na konci str�nek pracuj�c� s bannery";
$GLOBALS['strHideInactive']			= "Skr�t neaktivn� polo�ky ze �ech p�ehledov�ch str�nek";
$GLOBALS['strGUIShowMatchingBanners']		= "Zobrazit odpov�daj�c� bannery na str�nce <i>P�ipojen� banner</i>";
$GLOBALS['strGUIShowParentCampaigns']		= "Zobrazit nad�azenou kampan na str�nce <i>P�ipojen� banner</i>";
$GLOBALS['strGUILinkCompactLimit']		= "Skr�t nep�ipojen� kampan� nebo bannery na str�nce <i>P�ipojen� banner</i>, kdy� je v�t�� ne�";

$GLOBALS['strStatisticsDefaults'] 		= "Statistika";
$GLOBALS['strBeginOfWeek']			= "Za��tek t�dne";
$GLOBALS['strPercentageDecimals']		= "Desetin� ��rka";

$GLOBALS['strWeightDefaults']			= "V�choz� v�ha";
$GLOBALS['strDefaultBannerWeight']		= "V�choz� v�ha banneru";
$GLOBALS['strDefaultCampaignWeight']		= "V�choz� v�ha kampan�";
$GLOBALS['strDefaultBannerWErr']		= "Mus� b�t zad�no kladn� ��slo";
$GLOBALS['strDefaultCampaignWErr']		= "Mus� b�t zad�no kladn� ��slo";



// Not used at the moment
$GLOBALS['strTableBorderColor']			= "Table Border Color";
$GLOBALS['strTableBackColor']			= "Table Back Color";
$GLOBALS['strTableBackColorAlt']		= "Table Back Color (Alternativn�)";
$GLOBALS['strMainBackColor']			= "Main Back Color";
$GLOBALS['strOverrideGD']			= "Override GD Imageformat";
$GLOBALS['strTimeZone']				= "�asov� p�smo";

?>