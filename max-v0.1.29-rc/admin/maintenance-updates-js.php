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
$Id: maintenance-updates-js.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-maintenance.inc.php");

$update_check = false;


/*********************************************************/
/* Main code                                             */
/*********************************************************/

// Check for product updates when the admin logs in
if (phpAds_isUser(phpAds_Admin))
{
	// Check accordingly to user preferences
	switch ($phpAds_config['updates_frequency'])
	{
		case -1:	$update_check = false; break;
		case 0: 	$update_check = true; break;
		default: 	$update_check = ($phpAds_config['updates_timestamp'] +
						$phpAds_config['updates_frequency']*60*60*24) <= time();
					break;
	}
	
	if ($update_check)
	{
		include('lib-updates.inc.php');
		$update_check = phpAds_checkForUpdates($phpAds_config['updates_last_seen']);
		
		if ($update_check[0])
			$update_check = false;
	}
	
	phpAds_SessionDataRegister('update_check', $update_check);
	phpAds_SessionDataStore();
	
	
	// Add Product Update redirector
	if ($update_check)
	{
		Header("Content-Type: application/x-javascript");
		
		if ($Session['update_check'][1]['security_fix'])
			echo "\t\t\talert('".$strUpdateAlertSecurity."');\n";
		else
			echo "\t\t\tif (confirm('".$strUpdateAlert."'))\n\t";
		
		echo "\t\tdocument.location.replace('maintenance-updates.php');\n";
	}
}

?>