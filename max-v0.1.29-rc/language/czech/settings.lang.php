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
$GLOBALS['strDayFullNames'] 			= array("Nedìle","Pondìlí","Úterı","Støeda","Ètvrtek","Pátek","Sobota");
$GLOBALS['strEditConfigNotPossible']   		= "Upozornìní : nelze mìnit systémové hodnoty";
$GLOBALS['strEditConfigPossible']		= "Upozornìní : lze mìnit systémové hodnoty";



// Database
$GLOBALS['strDatabaseSettings']			= "Nastavení databáze";
$GLOBALS['strDatabaseServer']			= "Nastavení databázového serveru";
$GLOBALS['strDbLocal']				= "Pøipojení na socket"; // Pg only
$GLOBALS['strDbHost']				= "Server";
$GLOBALS['strDbPort']				= "Port";
$GLOBALS['strDbUser']				= "Uivatelské jméno";
$GLOBALS['strDbPassword']			= "Heslo";
$GLOBALS['strDbName']				= "Název tabulky";

$GLOBALS['strDatabaseOptimalisations']		= "Další nastavení";
$GLOBALS['strPersistentConnections']		= "Pouøít trvalé pøipojení";
$GLOBALS['strInsertDelayed']			= "Pouít zpodìné inserty";
$GLOBALS['strCompatibilityMode']		= "Pouít kompatibilní mód";
$GLOBALS['strCantConnectToDb']			= "Can't connect to database";



// Invocation and Delivery
$GLOBALS['strInvocationAndDelivery']		= "Vızvy a doruèování";

$GLOBALS['strAllowedInvocationTypes']		= "Povolit typ doruèování";
$GLOBALS['strAllowRemoteInvocation']		= "Vzdálené volání";
$GLOBALS['strAllowRemoteInvocationNoCookies']		= "Vzdálené volání - bez Cookies";
$GLOBALS['strAllowRemoteJavascript']		= "Vzdálené volání - Javascript";
$GLOBALS['strAllowRemoteFrames']		= "Vzdálené volání - Frames";
$GLOBALS['strAllowRemoteXMLRPC']		= "Vzdálené volání - XML-RPC";
$GLOBALS['strAllowLocalmode']			= "Místní volání";
$GLOBALS['strAllowInterstitial']		= "Vısuvné bannery";
$GLOBALS['strAllowPopups']			= "Pop-Up";

$GLOBALS['strUseAcl']				= "Vyhodnocovat omezení doruèování v prùbìhu doruèování";

$GLOBALS['strDeliverySettings']			= "Nastavení doruèování";
$GLOBALS['strCacheType']				= "Typ doruèovací cache";
$GLOBALS['strCacheFiles']				= "Soubory";
$GLOBALS['strCacheDatabase']			= "Database";
$GLOBALS['strCacheShmop']				= "Sdílená pamì/Shmop";
$GLOBALS['strCacheSysvshm']				= "Sdílená pamì/Sysvshm";
$GLOBALS['strExperimental']				= "Experimentální";
$GLOBALS['strKeywordRetrieval']			= "Vıbìr klíèovıch slove";
$GLOBALS['strBannerRetrieval']			= "Vıbìr bannerù";
$GLOBALS['strRetrieveRandom']			= "Náhodnı vıbìr bannerù (vıchozí)";
$GLOBALS['strRetrieveNormalSeq']		= "Narmální 1:1 vımìna";
$GLOBALS['strWeightSeq']				= "Vımìna bannerù podle váhy";
$GLOBALS['strFullSeq']					= "Plnì souvislá vımìna bannerù";
$GLOBALS['strUseKeywords']				= "Pouít klíèová slova pro vıbìr banneru";
$GLOBALS['strUseConditionalKeys']		= "Povolit logické operátory pøi pouití pøímé volby";
$GLOBALS['strUseMultipleKeys']			= "Povolit vícero klíèovıch slov pøi pouití pøímé volby";

$GLOBALS['strZonesSettings']			= "Doruèovací zóna";
$GLOBALS['strZoneCache']			= "Caschovat zóny, pro zvıšení rychlosti";
$GLOBALS['strZoneCacheLimit']			= "Èas mezi updatem cache (v sekundách)";
$GLOBALS['strZoneCacheLimitErr']		= "Èas musí bıt udán v kladnıch èíselnıch hodnotách";

$GLOBALS['strP3PSettings']			= "P3P pravidla politiky";
$GLOBALS['strUseP3P']				= "Pouití P3P politiky";
$GLOBALS['strP3PCompactPolicy']			= "P3P jednoduché politika";
$GLOBALS['strP3PPolicyLocation']		= "P3P politika umístìní"; 



// Banner Settings
$GLOBALS['strBannerSettings']			= "Nastavení banneru";

$GLOBALS['strAllowedBannerTypes']		= "Povolit typy bannerù";
$GLOBALS['strTypeSqlAllow']			= "Lokální banner (SQL)";
$GLOBALS['strTypeWebAllow']			= "Lokální banner (Webserver)";
$GLOBALS['strTypeUrlAllow']			= "Externí banner";
$GLOBALS['strTypeHtmlAllow']			= "HTML bannery";
$GLOBALS['strTypeTxtAllow']			= "Textovou reklamu";

$GLOBALS['strTypeWebSettings']			= "Lokální banner (Webserver) nastavení";
$GLOBALS['strTypeWebMode']			= "Typ ukládání";
$GLOBALS['strTypeWebModeLocal']			= "Místní adresáø";
$GLOBALS['strTypeWebModeFtp']			= "Exerní FTP server";
$GLOBALS['strTypeWebDir']			= "Místné adresáø";
$GLOBALS['strTypeWebFtp']			= "FTP mód";
$GLOBALS['strTypeWebUrl']			= "Veøejná URL";
$GLOBALS['strTypeWebSslUrl']			= "Veøejná URL (SSL)";
$GLOBALS['strTypeFTPHost']			= "FTP server";
$GLOBALS['strTypeFTPDirectory']			= "Adresáø";
$GLOBALS['strTypeFTPUsername']			= "Uiv. jméno";
$GLOBALS['strTypeFTPPassword']			= "Heslo";
$GLOBALS['strTypeFTPErrorDir']			= "Adresáø na serveru neexistuje";
$GLOBALS['strTypeFTPErrorConnect']		= "Nemohu se pøipojit na vzdáleny FTP server, zkontroluj pøihlašovací údaje";
$GLOBALS['strTypeFTPErrorHost']			= "Název serveru FTP není správnı";
$GLOBALS['strTypeDirError']				= "Lokální adresáø neexistuje";



$GLOBALS['strDefaultBanners']			= "Vıchozí banner";
$GLOBALS['strDefaultBannerUrl']			= "URL vıchozího banneru";
$GLOBALS['strDefaultBannerTarget']		= "Vıchozí smìrování URL";

$GLOBALS['strTypeHtmlSettings']			= "Parametry HTML banneru";
$GLOBALS['strTypeHtmlAuto']			= "Automaticky upravovat HTML bannery, aby bylo moné sledovat kliknutí";
$GLOBALS['strTypeHtmlPhp']			= "Povolit spouštìní PHP vırazù z HTML bannerù";



// Host information and Geotargeting
$GLOBALS['strHostAndGeo']				= "Informace o hostech a geocíle";

$GLOBALS['strRemoteHost']				= "Vzdálenı host";
$GLOBALS['strReverseLookup']			= "Pokusit se urèit název hostitele, pokud není poskytnuto serverem";
$GLOBALS['strProxyLookup']				= "Pokusit se urcit pravou IP adresu návštìvníka, kterı pouívá proxy server";
$GLOBALS['strObfuscate']					= "Zmást zdroj pokud doruèuje reklamu";

$GLOBALS['strGeotargeting']				= "Geocílení";
$GLOBALS['strGeotrackingType']			= "Typ databáze geocíle";
$GLOBALS['strGeotrackingLocation'] 		= "Místo databáze geocílení";
$GLOBALS['strGeotrackingLocationError'] = "Geobaze neexistuje ...";
$GLOBALS['strGeoStoreCookie']			= "Uloit cookie s vısledkem pro pøíštì";



// Statistics Settings
$GLOBALS['strStatisticsSettings']		= "Nastavení statistik";

$GLOBALS['strStatisticsFormat']			= "Formát statistik";
$GLOBALS['strCompactStats']				= "Formát statistik";
$GLOBALS['strLogAdViews']				= "Logovat banner vdy pokud je doruèen";
$GLOBALS['strLogAdClicks']				= "Logovat AdClick vdy kdy návštìvník klikne na banner";
$GLOBALS['strLogAdConversions']				= "Logovat AdConversion vdy, pokud návštìvník shlédne stránku s AdConversion èidlem";
$GLOBALS['strLogSource']				= "Logovat parametr zdroje pøedanı pøi volání";
$GLOBALS['strGeoLogStats']				= "Logovat zemi návšzìvníka ve statistikách";
$GLOBALS['strLogHostnameOrIP']			= "Logovat hostitele nebo IP návštìvníka";
$GLOBALS['strLogIPOnly']				= "Logovat pouze IP adresu návštìvníka i kdy je znám název hostitele";
$GLOBALS['strLogIP']					= "Logovat IP adresu návštìvníka";
$GLOBALS['strLogBeacon']				= "Pouít malı obrázek k logování AdViews, aby bylo zjištìno, e jsou logovány pouze doruèené bannery";

$GLOBALS['strRemoteHosts']				= "Vzdálebı host";
$GLOBALS['strIgnoreHosts']				= "Neukládat statistiky pro návštìvníky pouívající jednu z následujících IP adres nebo názvù hostitelù";
$GLOBALS['strBlockAdViews']				= "Nelogovat AdViews, pokud návštìvník nevidìl stejnı banner bìhem (sekund)";
$GLOBALS['strBlockAdClicks']			= "Nelogovat AdClicks, pokud návštìvník kliknul na stejnı banner bìhem (sekund)";
$GLOBALS['strBlockAdConversions']			= "Nelogovat AdConversions, pokud návštìvník nevidìl pøíslušné stránky AdConv. (sekund)";


$GLOBALS['strPreventLogging']			= "Zamezit logování";
$GLOBALS['strEmailWarnings']			= "E-mailová upozornení";
$GLOBALS['strAdminEmailHeaders']		= "Pøidej následující hlavièku ke kadé odesílané zprávì ".$phpAds_productname;
$GLOBALS['strWarnLimit']				= "Poslat upozornìní, kdy je poèet zbıvajících impresí niší ne zde uvedenı";
$GLOBALS['strWarnLimitErr']				= "Limit musí bıt dán èíslem kladnım";
$GLOBALS['strWarnAdmin']				= "Upozornit administrátora kdykoliv je kampaò témìø vyèerpána";
$GLOBALS['strWarnClient']				= "Upozornit vydavatele kdykoliv je kampaò témìø vyèerpána";
$GLOBALS['strWarnAgency']				= "Upozornit manaera spoleènosti kdykoliv je kampaò témìø vyèerpána";
$GLOBALS['strQmailPatch']				= "Zapnout qmail patch";

$GLOBALS['strAutoCleanTables']			= "Èištìní databáze";
$GLOBALS['strAutoCleanStats']			= "Èištìní statistiky";
$GLOBALS['strAutoCleanUserlog']			= "Èiètìní uivatelského logu";
$GLOBALS['strAutoCleanStatsWeeks']		= "Maimální stáøí statistiky <br>(3 tıdny min.)";
$GLOBALS['strAutoCleanUserlogWeeks']	= "Maimální stáøí uiv. logu <br>(3 tıd. min.)";
$GLOBALS['strAutoCleanErr']				= "Maximální stáøí musí bıt vìtší ne 3 tıdny";
$GLOBALS['strAutoCleanVacuum']			= "VACUUM ANALYZE tabulek kazdou noc"; // only Pg


// Administrator settings
$GLOBALS['strAdministratorSettings']		= "Nastavení administrátora";

$GLOBALS['strLoginCredentials']			= "Pøihlašovací údaje";
$GLOBALS['strAdminUsername']			= "Administrátorské jméno";
$GLOBALS['strInvalidUsername']			= "Špatné uiv. jméno";

$GLOBALS['strBasicInformation']			= "Základní informace";
$GLOBALS['strAdminFullName']			= "Jméno Administrátora";
$GLOBALS['strAdminEmail']			= "E-mail administrátora";
$GLOBALS['strCompanyName']			= "Název spoleènosti";

$GLOBALS['strAdminCheckUpdates']		= "Zjišování novıch verzí";
$GLOBALS['strAdminCheckEveryLogin']		= "Pøi logování";
$GLOBALS['strAdminCheckDaily']			= "Dennì";
$GLOBALS['strAdminCheckWeekly']			= "Tıdnì";
$GLOBALS['strAdminCheckMonthly']		= "Mìsíènì";
$GLOBALS['strAdminCheckNever']			= "Nikdy";

$GLOBALS['strAdminNovice']			= "Potvrzovat Mazání";
$GLOBALS['strUserlogEmail']			= "Logovat veškerou odchozí poštu";
$GLOBALS['strUserlogPriority']			= "Logovat hodinové kalkulace priorit";
$GLOBALS['strUserlogAutoClean']			= "Logovat automatické èistìní databáze";


// User interface settings
$GLOBALS['strGuiSettings']			= "Nastavení uivatelského rozhraní";

$GLOBALS['strGeneralSettings']			= "Základní nastavení";
$GLOBALS['strAppName']				= "Název aplikace";
$GLOBALS['strMyHeader']				= "Umístìní souboru s hlavièkou";
$GLOBALS['strMyHeaderError']		= "Soubor hlavièky neecistuje";
$GLOBALS['strMyFooter']				= "Umístìní souboru s paièkou";
$GLOBALS['strMyFooterError']		= "Soubor patièky neexistuje";
$GLOBALS['strGzipContentCompression']		= "Pouít GZIP kompresi";

$GLOBALS['strClientInterface']			= "Rozhraní inzerenta";
$GLOBALS['strClientWelcomeEnabled']		= "Zapnout uvítací zprávu";
$GLOBALS['strClientWelcomeText']		= "Uvítací text<br>(HTML tagy povoleny)";



// Interface defaults
$GLOBALS['strInterfaceDefaults']		= "Vıchozí parametry rozhraní";

$GLOBALS['strInventory']			= "Inventáø";
$GLOBALS['strShowCampaignInfo']			= "Zobrazit informace o kampani na stránce <i>Pøehled kampanì</i>";
$GLOBALS['strShowBannerInfo']			= "Zobrazit extra informace o banneru na stránce <i>Pøehled banneru</i>";
$GLOBALS['strShowCampaignPreview']		= "Zobrazit náhled všech bannerù na stránce <i>Pøehled banneru</i>";
$GLOBALS['strShowBannerHTML']			= "Zobrazit banner místo HTML kódu na stránce pro náhled abnneru";
$GLOBALS['strShowBannerPreview']		= "Zobrazit náhled banneru na konci stránek pracující s bannery";
$GLOBALS['strHideInactive']			= "Skrıt neaktivní poloky ze šech pøehledovıch stránek";
$GLOBALS['strGUIShowMatchingBanners']		= "Zobrazit odpovídající bannery na stránce <i>Pøipojenı banner</i>";
$GLOBALS['strGUIShowParentCampaigns']		= "Zobrazit nadøazenou kampan na stránce <i>Pøipojenı banner</i>";
$GLOBALS['strGUILinkCompactLimit']		= "Skrıt nepøipojené kampanì nebo bannery na stránce <i>Pøipojenı banner</i>, kdy je vìtší ne";

$GLOBALS['strStatisticsDefaults'] 		= "Statistika";
$GLOBALS['strBeginOfWeek']			= "Zaèátek tıdne";
$GLOBALS['strPercentageDecimals']		= "Desetiná øárka";

$GLOBALS['strWeightDefaults']			= "Vıchozí váha";
$GLOBALS['strDefaultBannerWeight']		= "Vıchozí váha banneru";
$GLOBALS['strDefaultCampaignWeight']		= "Vıchozí váha kampanì";
$GLOBALS['strDefaultBannerWErr']		= "Musí bıt zadáno kladné èíslo";
$GLOBALS['strDefaultCampaignWErr']		= "Musí bıt zadáno kladné èíslo";



// Not used at the moment
$GLOBALS['strTableBorderColor']			= "Table Border Color";
$GLOBALS['strTableBackColor']			= "Table Back Color";
$GLOBALS['strTableBackColorAlt']		= "Table Back Color (Alternativní)";
$GLOBALS['strMainBackColor']			= "Main Back Color";
$GLOBALS['strOverrideGD']			= "Override GD Imageformat";
$GLOBALS['strTimeZone']				= "Èasové pásmo";

?>