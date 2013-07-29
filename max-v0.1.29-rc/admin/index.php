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
$Id: index.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client + phpAds_Affiliate);



/*********************************************************/
/* Main code                                             */
/*********************************************************/

if (phpAds_isUser(phpAds_Admin))
{
	Header("Location: ".$phpAds_config['admin_url_prefix']."/admin/advertiser-index.php");
	exit;
}

if (phpAds_isUser(phpAds_Agency))
{
	Header("Location: ".$phpAds_config['admin_url_prefix']."/admin/advertiser-index.php");
	exit;
}

if (phpAds_isUser(phpAds_Client))
{
	Header("Location: ".$phpAds_config['admin_url_prefix']."/admin/stats-advertiser-history.php?clientid=".phpAds_getUserID());
	exit;
}

if (phpAds_isUser(phpAds_Affiliate))
{
	Header("Location: ".$phpAds_config['admin_url_prefix']."/admin/stats-affiliate-zones.php?affiliateid=".phpAds_getUserID());
	exit;
}

?>