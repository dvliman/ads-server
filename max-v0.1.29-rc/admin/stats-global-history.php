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
$Id: stats-global-history.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");


// Register input variables
phpAds_registerGlobal ('period', 'start', 'limit');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
{
	phpAds_PageHeader("2.2");
	phpAds_ShowSections(array("2.1", "2.4", "2.2"));
}
else
{
	phpAds_PageHeader("1");
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

$lib_history_hourlyurl = "stats-global-daily.php";

include ("lib-history.inc.php");



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>
