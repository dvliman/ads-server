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
// Main strings
$GLOBALS['strChooseSection']			= "Auswahlbereich";


// Priority
$GLOBALS['strRecalculatePriority']		= "Rekalkulieren der Priorit�ten";
$GLOBALS['strHighPriorityCampaigns']		= "Kampagnen mit hoher Priorit�t";
$GLOBALS['strAdViewsAssigned']		= "Festgelegte AdViews";
$GLOBALS['strLowPriorityCampaigns']		= " Kampagnen mit geringer Priorit�t ";
$GLOBALS['strPredictedAdViews']		= "Prognostizierte AdViews";
$GLOBALS['strPriorityDaysRunning']		= "Die Prognose f�r die t�gliche Bannerauslieferung basiert auf Statistiken von {days} Tagen. ";
$GLOBALS['strPriorityBasedLastWeek']		= "Die Prognose basiert auf den Daten dieser und der vergangenen Woche. ";
$GLOBALS['strPriorityBasedLastDays']		= "Die Prognose basiert auf den Daten der letzten Tage. ";
$GLOBALS['strPriorityBasedYesterday']		= "Die Prognose basiert auf den Daten von gestern. ";
$GLOBALS['strPriorityNoData']			= "F�r eine zuverl�ssige Prognose �ber die heute m�gliche Anzahl von AdViews stehen f�r nicht ausreichend Daten zur Verf�gung.  Die Festlegung der Priorit�ten wird daher nur auf in Echtzeit erstellte Statistiken gest�tzt sein. ";
$GLOBALS['strPriorityEnoughAdViews']		= "Es werden ausreichend AdViews zur Verf�gung stehen, um die Kampagnen mit hoher Priorit�t bedienen zu k�nnen. ";
$GLOBALS['strPriorityNotEnoughAdViews']		= "Es ist ungewi�, ob ausreichend AdViews zur Verf�gung stehen werden, um die Anforderungen durch Kampagnen mit hoher Priorit�t befriedigen zu k�nnen.";


// Banner cache
$GLOBALS['strRebuildBannerCache']		= "Bannercache erneuern";
$GLOBALS['strBannerCacheExplaination']		= "
	Im Bannercache werden Kopien der HTML-Codes, die f�r die Bannerdarstellung notwendig sind, vorgehalten. Durch den Bannercache wird die Auslieferung beschleunigt,
	denn der HTML-Code mu� nicht bei jeder Auslieferung neu generiert werden. Weil im 
Bannercache die URL als Direktadressierung, verkn�pft mit dem Standort von ".$phpAds_productname." nebst dem Banner vorliegt, mu� der Bannercache aktualisiert werden, wenn sein
	Standort verschoben wird.";


// Cache
$GLOBALS['strCache']			= "Cache f�r Bannerauslieferung";
$GLOBALS['strAge']				= "Alter";
$GLOBALS['strRebuildDeliveryCache']			= "Cache wird erneuert";
$GLOBALS['strDeliveryCacheExplaination']		= "
	Der Cache f�r Bannerauslieferung wird zur Beschleunigung der Bannerauslieferung ben�tigt. Im Cache sind Kopien von jedem Banner, der mit der Zone verbunden (verlinkt) ist. Dadurch, das die aktuellen Banner im Cache vorgehalten sind,  wird eine Reihe von Datenbankabfragen gespart. Der Cache wird jedesmal bei �nderungen der Zone oder dem verkn�pften Banner erneuert. Um dennoch �beralterung vorzubeugen, wird der Cache st�ndlich automatisch erneuert. Der Vorgang kann zus�tzlich manuell angesto�en werden.";
$GLOBALS['strDeliveryCacheSharedMem']		= "
	Der gemeinsam genutzte Speicher wird vom Cache f�r Bannerauslieferung benutzt.";
$GLOBALS['strDeliveryCacheDatabase']		= "
	Die Datenbank wird zur Zeit vom Cache f�r Bannerauslieferung benutzt.";
$GLOBALS['strDeliveryCacheFiles']		= "
	Der Cache f�r Bannerauslieferung wird zur Zeit in mehrere Dateien gespeichert.";


// Storage
$GLOBALS['strStorage']				= "Speicherung";
$GLOBALS['strMoveToDirectory']		= "Bilder aus der Datenbank in ein Verzeichnis verschieben ";
$GLOBALS['strStorageExplaination']		= "
	Bilddateien f�r lokale Banner werden in der Datenbank oder in einem lokalen Verzeichnis gespeichert. 
	Das Speichern in einem lokalen Verzeichnis anstelle in der Datenbank vermindert die Ladezeit.";


// Storage
$GLOBALS['strStatisticsExplaination']		= "
	Sie haben als Darstellung <i>kompakte Statistiken</i> gew�hlt, �ltere Statistiken sind im detaillierten Format. 
	Sollen diese (�lteren) detaillierten Statistiken in das kompakte Format konvertiert werden?";


// Product Updates
$GLOBALS['strSearchingUpdates']		= "Sehe nach neuen Updates. Bitte warten...";
$GLOBALS['strAvailableUpdates']			= "Vorhandene Updates";
$GLOBALS['strDownloadZip']			= "Download (.zip)";
$GLOBALS['strDownloadGZip']			= "Download (.tar.gz)";

$GLOBALS['strUpdateAlert']		= "Eine neue Version von ".$phpAds_productname." ist verf�gbar.                 \\n\\nWerden weitere Informationen dazu gew�nscht?";
$GLOBALS['strUpdateAlertSecurity']	= "Eine neue Version von ".$phpAds_productname." ist verf�gbar.                 \\n\\n
Eine kurzfristige Aktualisierung  Ihres Systems \\n
wird empfohlen, da in der neuen Version eine oder \\n
mehrere Sicherheitselemente �berarbeitet wurden.";

$GLOBALS['strUpdateServerDown']			= "
    Aus unbekannten Gr�nden ist es nicht m�glich, nach Informationen <br>
	zu neuen Updates zu pr�fen. Versuchen Sie es sp�ter noch einmal.
";

$GLOBALS['strNoNewVersionAvailable']		= "
	Ihre Version von ".$phpAds_productname." ist aktuell. Ein Update ist nicht erforderlich.
";

$GLOBALS['strNewVersionAvailable']		= "
	<b>Eine neue Version von ".$phpAds_productname." ist verf�gbar.</b><br> 
	Eine Aktualisierung wird empfohlen, da einige vorhandenen Probleme behoben und neue Leistungsmerkmale integriert wurden. Weitergehende Information finden sich in der beigef�gten Dokumentation.";

$GLOBALS['strSecurityUpdate']			= "
	<b>Die schnellstm�gliche Durchf�hrung des Updates wird empfohlen, da eine Reihe von Sicherheitsproblemen behoben wurden.</b> 
Ihre Version von ".$phpAds_productname." ist gegen illegale Angriffe m�glicherweise nicht ausreichen gesichert. Weitergehende Information finden sich in der beigef�gten Dokumentation.";


$GLOBALS['strNotAbleToCheck']			= "
	Auf Ihrem Server ist die XML-Erweiterung nicht verf�gbar. ".$phpAds_productname." kann nicht pr�fen, ob eine neuere Version vorliegt.";

$GLOBALS['strForUpdatesLookOnWebsite']	= "
	Informationen �ber neue Versionen .befinden sich auf unserer Webseite.";

$GLOBALS['strClickToVisitWebsite']		= "	Zu unserer Webseite ";

$GLOBALS['strCurrentlyUsing'] 			= "Sie nutzen derzeit";
$GLOBALS['strRunningOn']				= "laufend auf";
$GLOBALS['strAndPlain']				= "und";



// Stats conversion
$GLOBALS['strConverting']			= "Konvertierung";
$GLOBALS['strConvertingStats']			= "Statistiken werden konvertiert...";
$GLOBALS['strConvertStats']			= "Statistiken konvertieren";
$GLOBALS['strConvertAdViews']			= "AdViews sind konvertiert,";
$GLOBALS['strConvertAdClicks']			= "AdClicks sind konvertiert...";
$GLOBALS['strConvertNothing']			= "Nichts zu konvertieren...";
$GLOBALS['strConvertFinished']			= "Fertig...";

$GLOBALS['strConvertExplaination']		= "
	F�r die statistische Auswertung verwenden Sie kompakte Darstellung. Es liegen <br>
	noch �ltere Statistiken in detailliertem Format vor. Solange diese detaillierten Statistiken <br>
	nicht in das kompakte Format konvertiert sind, k�nnen sie auf dieser Seite nicht angezeigt<br>
	werden. Eine Sicherung der Datenbank vor dem Konvertierungslauf ist empfohlen!  <br>
	Wollen Sie die detaillierten Statistiken in das kompakte Format umwandeln? <br>
";

$GLOBALS['strConvertingExplaination']		= "
	Alle verbliebene Statistiken im detaillierten Format werden in das kompakte umgewandelt. <br>
	Die Dauer des Vorganges ist abh�ngig von der Anzahl protokollierten Vorg�nge. Es k�nnte <br>
	einige Minuten dauern. Bitte warten Sie bis zum Ende des Konvertierungslauf, bevor Sie <br>
	andere Seiten. Unten wird ein Protokoll der vorgenommenen Datenbankmodifikationen angezeigt. <br>
";

$GLOBALS['strConvertFinishedExplaination']  	= "
	Der Konvertierungslauf war erfolgreich.  Die Daten stehen nun wieder zur
	Verf�gung. Nachfolgend ist ein Protokoll aller vorgenommenen Datenbankmodifikationen.<br>
";

?>