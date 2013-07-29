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
$Id: lib-updates.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Set define to prevent duplicate include
define ('LIBUPDATES_INCLUDED', true);


// Include required files
require (phpAds_path.'/libraries/lib-xmlrpc.inc.php');



/*********************************************************/
/* XML-RPC server settings                               */
/*********************************************************/

$phpAds_updatesServer = array(
	'host'	 => 'max.awarez.net',
	'script' => '/update/xmlrpc.php',
	'port'	 => 80
);



/*********************************************************/
/* Check for updates via XML-RPC                         */
/*********************************************************/

function phpAds_checkForUpdates($already_seen = 0)
{
	global $phpAds_config, $phpAds_updatesServer;
	global $xmlrpcerruser;

	// Create client object
	$client = new xmlrpc_client($phpAds_updatesServer['script'],
		$phpAds_updatesServer['host'], $phpAds_updatesServer['port']);
	
	// Create XML-RPC request message
	$msg = new xmlrpcmsg("updateAdsNew.check", array(
		new xmlrpcval($phpAds_config['config_version'], "string"),
		new xmlrpcval($already_seen, "string")
	));

	// Send XML-RPC request message
	if($response = $client->send($msg))
	{
		// XML-RPC server found, now checking for errors
		if (!$response->faultCode())
		{
			$ret = array(0, phpAds_xmlrpcDecode($response->value()));
			
			phpAds_dbQuery("
				UPDATE
					".$phpAds_config['tbl_config']."
				SET
					updates_last_seen = '".$ret[1]['config_version']."',
					updates_timestamp = ".time()."
			");
		}
		else
			$ret = array($response->faultCode(), $response->faultString());
		
		return $ret;
	}
	
	return array(-1, 'No response from the server');
}

?>