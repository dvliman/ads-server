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
$Id: userlog.lang.php 1167 2004-12-17 17:07:28Z andrew $
*/


// Set translation strings

$GLOBALS['strDeliveryEngine']				= "Doruèovaci robot";
$GLOBALS['strMaintenance']					= "Maintenance";
$GLOBALS['strAdministrator']				= "Administrator";


$GLOBALS['strUserlog'] = array (
	phpAds_actionAdvertiserReportMailed 	=> "Report pro inzerenta {id} odesláno emailem",
	phpAds_actionActiveCampaign				=> "Kampaò {id} aktivována",
	phpAds_actionAutoClean					=> "Automatické èištìní databáze",
	phpAds_actionBatchStatistics			=> "Statistika setøídìna",
	phpAds_actionDeactivationMailed			=> "Upozornìní o deaktivaci kampanì {id} odesláno emailem",
	phpAds_actionDeactiveCampaign			=> "Kampaò {id} deaktivována",
	phpAds_actionInventoryCalculation		=> "Vytváøení plánu doruèování",
	phpAds_actionPriorityCalculation		=> "Pøepoèítávání priorit",
	phpAds_actionPublisherReportMailed 		=> "Report pro vydavatele {id} odeslán emailem",
	phpAds_actionWarningMailed				=> "Varovaní o deaktivaci kampanì {id} odesláno emailem"
);

?>