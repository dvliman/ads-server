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
$Id: userlog.lang.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Set translation strings

$GLOBALS['strDeliveryEngine']			= "Bezorger";
$GLOBALS['strMaintenance']			= "Onderhoud";
$GLOBALS['strAdministrator']			= "Beheerder";


$GLOBALS['strUserlog'] = array (
	phpAds_actionAdvertiserReportMailed 	=> "Rapport voor adverteerder {id} verzonden per e-mail",
	phpAds_actionPublisherReportMailed 	=> "Rapport voor uitgever {id} verzonden per e-mail",
	phpAds_actionWarningMailed		=> "Deactivatie waarschuwing voor campagne {id} verzonden per e-mail",
	phpAds_actionDeactivationMailed		=> "Deactivatie bevestiging voor campagne {id} verzonden per e-mail",
	phpAds_actionPriorityCalculation	=> "Prioriteit berekend",
	phpAds_actionPriorityAutoTargeting	=> "Campagne doelstellingen berekend",
	phpAds_actionDeactiveCampaign		=> "Campagne {id} gedeactiveerd",
	phpAds_actionActiveCampaign		=> "Campagne {id} geactiveerd"
);

?>