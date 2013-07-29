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
$Id: lib-sessions.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/



/*********************************************************/
/* Fetch sessiondata from the database                   */
/*********************************************************/

function phpAds_SessionDataFetch()
{
	global $phpAds_config;
	global $HTTP_COOKIE_VARS, $Session;
	
	if (isset($HTTP_COOKIE_VARS['sessionID']) && $HTTP_COOKIE_VARS['sessionID'] != '')
	{
		$result = phpAds_dbQuery("SELECT sessiondata FROM ".$phpAds_config['tbl_session']." WHERE sessionid='".$HTTP_COOKIE_VARS['sessionID']."'" .
					 	         " AND UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(lastused) < 3600");
		
		if ($row = phpAds_dbFetchArray($result))
		{
			$Session = unserialize(stripslashes($row['sessiondata']));
			
			// Reset LastUsed, prevent from timing out
			phpAds_dbQuery("UPDATE ".$phpAds_config['tbl_session']." SET lastused = NOW() WHERE sessionid = '".$HTTP_COOKIE_VARS['sessionID']."'");
		}
	}
	else
	{
		$HTTP_COOKIE_VARS['sessionID'] = '';
		return (False);
	}
}



/*********************************************************/
/* Create a new sessionid                                */
/*********************************************************/

function phpAds_SessionStart()
{
	global $HTTP_COOKIE_VARS, $Session;
	
	if (!isset($HTTP_COOKIE_VARS['sessionID']) || $HTTP_COOKIE_VARS['sessionID'] == '')
	{
		// Start a new session
		$Session = array();
		$HTTP_COOKIE_VARS['sessionID'] = uniqid('phpads', 1);
		
		phpAds_setCookie ('sessionID', $HTTP_COOKIE_VARS['sessionID']);
		phpAds_flushCookie ();
	}
	
	return $HTTP_COOKIE_VARS['sessionID'];
}



/*********************************************************/
/* Register the data in the session array                */
/*********************************************************/

function phpAds_SessionDataRegister($key, $value='')
{
	global $Session;
	
	if (!defined('phpAds_installing'))
		phpAds_SessionStart();
	
	if (is_array($key) && $value=='')
	{
		for (reset($key);$name=key($key);next($key))
		{
			$Session[$name] = $key[$name];
		}
	}
	else
		$Session[$key] = $value;
	
	phpAds_SessionDataStore();
	
	// This function has been disabled because of incompatibility
	// problem with ZendOptimizer 1.00. Call sessionDataStore
	// manually if have modified the session array.
	// register_shutdown_function("phpAds_SessionDataStore");
}



/*********************************************************/
/* Store the session array in the database               */
/*********************************************************/

function phpAds_SessionDataStore()
{
	global $phpAds_config;
	global $HTTP_COOKIE_VARS, $Session;
	
	if (isset($HTTP_COOKIE_VARS['sessionID']) && $HTTP_COOKIE_VARS['sessionID'] != '')
	{
		phpAds_dbQuery("REPLACE INTO ".$phpAds_config['tbl_session']." VALUES ('".$HTTP_COOKIE_VARS['sessionID']."', '" .
					   addslashes(serialize($Session)) . "', null )");
	}
	
	// Randomly purge old sessions
	srand((double)microtime()*1000000);
	if(rand(1, 100) == 42)	
		phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_session']." WHERE UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(lastused) > 43200");
}



/*********************************************************/
/* Destroy the current session                           */
/*********************************************************/

function phpAds_SessionDataDestroy()
{
	global $phpAds_config;
	global $HTTP_COOKIE_VARS, $Session;
	
	// Remove the session data from the database
	phpAds_dbQuery("DELETE FROM ".$phpAds_config['tbl_session']." WHERE sessionid='".$HTTP_COOKIE_VARS['sessionID']."'");
	
	// Kill the cookie containing the session ID
	phpAds_setCookie ('sessionID', '');
	phpAds_flushCookie ();
	
	// Clear all local session data and the session ID
	$Session = "";
	unset($Session);
	
	$HTTP_COOKIE_VARS['sessionID'] = "";
	unset($HTTP_COOKIE_VARS['sessionID']);
}

?>