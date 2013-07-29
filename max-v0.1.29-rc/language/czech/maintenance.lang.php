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
$Id: maintenance.lang.php 1167 2004-12-17 17:07:28Z andrew $
*/

// Main strings
$GLOBALS['strChooseSection']			= "Vyberte sekci";


// Priority
$GLOBALS['strRecalculatePriority']		= "Pøepoèítat prioritu";
$GLOBALS['strHighPriorityCampaigns']		= "Kampanì s vysokou prioritou";
$GLOBALS['strAdViewsAssigned']			= "Pøidìlìnıch AdViews";
$GLOBALS['strLowPriorityCampaigns']		= "Kampanì s nízkou prioritou";
$GLOBALS['strPredictedAdViews']			= "Pøedpovìzenıch AdViews";
$GLOBALS['strPriorityDaysRunning']		= "V tuto chvíli jsou k dispozici statistiky za {days} dní z èeho ".$phpAds_productname." mùe vytvoøit denní pøedpovìï. ";
$GLOBALS['strPriorityBasedLastWeek']		= "Pøedpovìï je zaloena na údajích z tohoto a pøedchozího tıdne. ";
$GLOBALS['strPriorityBasedLastDays']		= "Pøedpovìï je zaloena na údajích z pøedchozích nìkolika dnù. ";
$GLOBALS['strPriorityBasedYesterday']		= "Pøedpovìï je zaloena na údajích ze vèerejška. ";
$GLOBALS['strPriorityNoData']			= "Není k dispozici dostatek údajù pro vytvoøení dùvìryhodné pøedpovìdi poètu impresí pro dnešní den. Pøidìlení priorit bude prùbìnì upravováno na základì prùbìnıch údajù. ";
$GLOBALS['strPriorityEnoughAdViews']		= "Mìlo by bıt k dispozici dostatek AdViews pro plné splnìní kampaní s vysokou prioritou. ";
$GLOBALS['strPriorityNotEnoughAdViews']		= "Není jisté e bude k dispozici dostatek AdViews pro plné splnìní kampaní s vysokou prioritou. ";


// Banner cache
$GLOBALS['strRebuildBannerCache']		= "Aktualizovat cache bannerù";
$GLOBALS['strBannerCacheExplaination']		= "
	Cache bannerù obsahuje kopii HTML kódu kterı se pouívá pro zobrazení banneru. Pouitím chache bannerù je moné docílit zrychlení
	doruèování bannerù protoe se HTML kód nemusí generovat pokadé kdy má bıt banner doruèen. Protoe cache bannerù obsahuje pevné 
	okdazy na URL kde je umístìno ".$phpAds_productname." a jeho bannery, cache musí bıt aktualizována pokadé, kdy dojde k pøesunu
	".$phpAds_productname." do jiného umístìní na webserveru.
";


// Cache
$GLOBALS['strCache']			= "Cache doruèování";
$GLOBALS['strAge']				= "Stáøí";
$GLOBALS['strRebuildDeliveryCache']			= "Aktualizovat cache doruèování";
$GLOBALS['strDeliveryCacheExplaination']		= "
	Cache doruèováné je pouívána pro urychlení doruèování bannerù. Cache obsahuje kopii všech bannerù
	které jsou pøipojené k zónì co ušetøí nìkolik databázovıch dotazù a bannery jsou pøímo doruèovány uivateli. Cache
	je normálnì znovu vytváøena pøi kadé zmìnì zóny nebo bannerù zóny a pokud je to moné je cache aktualizována. Z tohoto
	dùvodu se cache automaticky aktualizuje kadou hodinu, ale je moné ji aktualizovat i ruènì.
";
$GLOBALS['strDeliveryCacheSharedMem']		= "
	V tuto chvíli se pro ukládání cache doruèování vyuívá sdílená pamì.
";
$GLOBALS['strDeliveryCacheDatabase']		= "
	V tuto chvíli se pro ukládání cache doruèování vyuívá databáze.
";
$GLOBALS['strDeliveryCacheFiles']		= "
	V tuto chvíli se pro ukládání cache doruèování vyuívá vícero souborù na disku.
";


// Storage
$GLOBALS['strStorage']				= "Ukládání";
$GLOBALS['strMoveToDirectory']			= "Pøesunout obrázky uloené v databázi do adresáøe";
$GLOBALS['strStorageExplaination']		= "
	Obrázky lokálních bannerù jsou uloeny v databázi nebo v adresáøi. Pokud uloíte soubory do adresáøe 
	zátì databáze vıraznì poklesne a zvıší se rychlost doruèování.
";


// Storage
$GLOBALS['strStatisticsExplaination']		= "
	Zapnul jste formát <i>kompaktních statistik</i>, ale vaše staré statistiky jsou stále v detailním formátu. 
	Pøejete si pøevést vaše detailní statistiky do kompaktního formátu?
";


// Product Updates
$GLOBALS['strSearchingUpdates']			= "Hledám aktualizace. Prosím èekejte...";
$GLOBALS['strAvailableUpdates']			= "Dostupné aktualizace";
$GLOBALS['strDownloadZip']			= "Download (.zip)";
$GLOBALS['strDownloadGZip']			= "Download (.tar.gz)";

$GLOBALS['strUpdateAlert']			= "Je k dispozici nová verze ".$phpAds_productname." .                 \\n\\nPøejete si více informací o tété \\naktualizaci?";
$GLOBALS['strUpdateAlertSecurity']		= "Je k dispozici nová verze ".$phpAds_productname." .                 \\n\\nDùraznì doporuèujeme provést aktualizaci \\nco nejdøíve, nebo tato verze obsahuje \\njednu nebo více bezpeènostních oprav.";

$GLOBALS['strUpdateServerDown']			= "
    Z neznámého dùvodu nebylo moné získat <br>
	informace o aktualizacích. Prosím zkuste to znovu pozdìji.
";

$GLOBALS['strNoNewVersionAvailable']		= "
	Vaše verze ".$phpAds_productname." je aktuální. V tuto chvíli nejsou k dispozici ádné aktualizace.
";

$GLOBALS['strNewVersionAvailable']		= "
	<b>Novìjší verze ".$phpAds_productname." je k dispozici.</b><br> Doporuèujeme nainstalovat tuto aktualizaci,
	protoe mùe obsahovat opravy nìkterıch chyb a obsahovat nové funkce. Pro více informací o tom jak provést
	aktualizaci si prosím pøeètìte dokumentaci která je v níe uvedenıch souborech.
";

$GLOBALS['strSecurityUpdate']			= "
	<b>Dùraznì doporuèujeme nainstalovat tuto aktualizaci co nejdøíve, protoe obsahuje nìkolik oprav
	bezpeènostních chyb.</b> Verze ".$phpAds_productname." kterou pouíváte mùe bıt citlivá ná rùzné 
	druhy útokù a zøejmì není bezpeèná. Pro více informací o tom jak provést aktualizaci si prosím 
	pøeètìte dokumentaci která je v níe uvedenıch souborech.
";

$GLOBALS['strNotAbleToCheck']			= "
	<b>Protoe XML doplnìk není instalován na vašem serveru, ".$phpAds_productname." není 
    schopen ovìøit zda jsou k dispozici aktualizace.</b>
";

$GLOBALS['strForUpdatesLookOnWebsite']	= "
	Pokud chcete vìdìt jestli je k dispozici novìjší verze tak navštivte naše stránky.
";

$GLOBALS['strClickToVisitWebsite']		= "Kliknìte zde pro naše webové stránky";
$GLOBALS['strCurrentlyUsing'] 			= "V tuto chvíli pouíváte";
$GLOBALS['strRunningOn']				= "bìící na";
$GLOBALS['strAndPlain']					= "a";


// Stats conversion
$GLOBALS['strConverting']			= "Probíhá pøevod";
$GLOBALS['strConvertingStats']			= "Pøevod statistik...";
$GLOBALS['strConvertStats']			= "Pøeveï statistiky";
$GLOBALS['strConvertAdViews']			= "Pøevedenıch AdViews,";
$GLOBALS['strConvertAdClicks']			= "Pøevedenıch AdClicks...";
$GLOBALS['strConvertNothing']			= "Nic k pøevodu...";
$GLOBALS['strConvertFinished']			= "Ukonèeno...";

$GLOBALS['strConvertExplaination']		= "
	V tuto chvíli pouíváte kompaktní formát statistik, ale stále máte nìkteré statsitiky <br>
	v datailním formátu. Dokud nebudou deatilní statistiky pøevedny do kompaktního formátu <br>
	nebudou zobrazovány pøi prohlíení této stránky.  <br>
	Pøed pøevodem statistiky si zazálohujte databázi!  <br>
	Chcete pøevést deatilní statistiky do kompaktního formátu? <br>
";

$GLOBALS['strConvertingExplaination']		= "
	Všechny zbıvající detailní statistiky jsou pøevádìny do kompaktního formátu. <br>
	V závislosti na poètu impresí uloenıch v detailním formátu tato akce mùe trvat  <br>
	a nìkolik minut. Prosím vyèkejte na ukonèení pøevodu ne navšívíte jiné stráky. <br>
	Níe máte seznam všech úprav provedenıch na databázi. <br>
";

$GLOBALS['strConvertFinishedExplaination']  	= "
	Pøevod zbıvajících detailních statistik byl úspìšnı a data by nyní mìla bıt <br>
	znovu pouitelná. Níe máte seznam všech úprav provedenıch na databázi. <br>
";


?>
