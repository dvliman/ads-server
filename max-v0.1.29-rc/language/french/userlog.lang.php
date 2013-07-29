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

$GLOBALS['strDeliveryEngine']			= 'Moteur de distribution';
$GLOBALS['strMaintenance']			= 'Maintenance';
$GLOBALS['strAdministrator']			= 'Administrateur';


$GLOBALS['strUserlog'] = array (
	phpAds_actionAdvertiserReportMailed	=> 'Rapport pour l\'annonceur {id} envoy� par e-mail',
	phpAds_actionPublisherReportMailed 	=> 'Rapport pour l\'�diteur {id} envoy� par e-mail',
	phpAds_actionWarningMailed		=> 'Avertissement de d�sactivation pour la campagne {id} envoy� par e-mail',
	phpAds_actionDeactivationMailed		=> 'Notification de d�sactivation pour la campagne {id} envoy� par e-mail',
	phpAds_actionPriorityCalculation	=> 'Priorit�s recalcul�es',
	phpAds_actionPriorityAutoTargeting	=> 'Objectifs de campagnes recalcul�s',
	phpAds_actionDeactiveCampaign		=> 'Campagne {id} d�sactiv�e',
	phpAds_actionActiveCampaign		=> 'Campagne {id} activ�e'
);

?>