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
$Id: lib-log.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


/*********************************************************/
/* Check if host has to be ignored                       */
/*********************************************************/

function phpads_logCheckHost()
{
	global $phpAds_config;
	global $HTTP_SERVER_VARS;
	
	if (count($phpAds_config['ignore_hosts']))
	{
		$hosts = "#(".implode ('|',$phpAds_config['ignore_hosts']).")$#i";
		
		if ($hosts != '')
		{
			$hosts = str_replace (".", '\.', $hosts);
			$hosts = str_replace ("*", '[^.]+', $hosts);
			
			if (preg_match($hosts, $HTTP_SERVER_VARS['REMOTE_ADDR']))
				return false;
			
			if (preg_match($hosts, $HTTP_SERVER_VARS['REMOTE_HOST']))
				return false;
		}
	}
	
	return true; //$HTTP_SERVER_VARS['REMOTE_HOST'];
}



/*********************************************************/
/* Log an impression                                     */
/*********************************************************/

function phpAds_logImpression ($userid, $bannerid, $zoneid, $source)
{
	global $HTTP_SERVER_VARS, $phpAds_config, $phpAds_geo;
	
	//decrypt source
	$source = phpAds_decrypt($source);

	// Check if host is on list of hosts to ignore
	if ($host = phpads_logCheckHost())
	{
		$log_source = $phpAds_config['log_source'] ? $source : '';
		
		$log_country = $phpAds_config['geotracking_stats'] && $phpAds_geo && $phpAds_geo['country'] ? $phpAds_geo['country'] : '';
		$log_host    = $phpAds_config['log_hostname'] ? $HTTP_SERVER_VARS['REMOTE_HOST'] : '';
		$log_host    = $phpAds_config['log_iponly'] ? $HTTP_SERVER_VARS['REMOTE_ADDR'] : $log_host;

		if (phpAds_dbConnect(phpAds_rawDb)) {
			phpAds_dbQuery(
				"INSERT ".($phpAds_config['insert_delayed'] ? 'DELAYED' : '')." INTO ".$phpAds_config['tbl_adviews'].
				"(userid".
				",bannerid".
				",zoneid".
				",host".
				",source".
				",country)".
				" VALUES ".
				"('".$userid."'".
				",".$bannerid.
				",".$zoneid.
				",'".$log_host."'".
				",'".$source."'".
				",'".$log_country."')"
			, phpAds_rawDb);
			phpAds_dbClose(phpAds_rawDb);
		};
		
	}
}

/*********************************************************/
/* Log a click                                          */
/*********************************************************/

function phpAds_logClick($userid, $bannerid, $zoneid, $source)
{
	global $HTTP_SERVER_VARS, $phpAds_config, $phpAds_geo;
	
	//decrypt source
	$source = phpAds_decrypt($source);

	// Check if host is on list of hosts to ignore
	if ($host = phpads_logCheckHost())
	{
		$log_source = $phpAds_config['log_source'] ? $source : '';
		
		$log_country = $phpAds_config['geotracking_stats'] && $phpAds_geo && $phpAds_geo['country'] ? $phpAds_geo['country'] : '';
		$log_host    = $phpAds_config['log_hostname'] ? $HTTP_SERVER_VARS['REMOTE_HOST'] : '';
		$log_host    = $phpAds_config['log_iponly'] ? $HTTP_SERVER_VARS['REMOTE_ADDR'] : $log_host;

		if (phpAds_dbConnect(phpAds_rawDb)) {
			phpAds_dbQuery(
				"INSERT ".($phpAds_config['insert_delayed'] ? 'DELAYED' : '')." INTO ".$phpAds_config['tbl_adclicks'].
				"(userid".
				",bannerid".
				",zoneid".
				",host".
				",source".
				",country)".
				" VALUES ".
				"('".$userid."'".
				",".$bannerid.
				",".$zoneid.
				",'".$log_host."'".
				",'".$source."'".
				",'".$log_country."')"
			, phpAds_rawDb);
			phpAds_dbClose(phpAds_rawDb);
		}
	}
}

/*********************************************************/
/* Log a conversion                                      */
/*********************************************************/

function phpAds_logConversion($userid, $trackerid)
{
	global $HTTP_SERVER_VARS, $phpAds_config, $phpAds_geo;
	// Check if host is on list of hosts to ignore
	if ($host = phpads_logCheckHost()) {
		$log_country = $phpAds_config['geotracking_stats'] && $phpAds_geo && $phpAds_geo['country'] ? $phpAds_geo['country'] : '';
		$log_host    = $phpAds_config['log_hostname'] ? $HTTP_SERVER_VARS['REMOTE_HOST'] : '';
		$log_host    = $phpAds_config['log_iponly'] ? $HTTP_SERVER_VARS['REMOTE_ADDR'] : $log_host;
		if (phpAds_dbConnect(phpAds_rawDb)) {
			phpAds_dbQuery(
				"INSERT ".($phpAds_config['insert_delayed'] ? 'DELAYED' : '')." INTO ".$phpAds_config['tbl_adconversions'].
				"(userid".
				",trackerid".
				",dbserver_ip".				
				",host".
				",country)".
				" VALUES ".
				"('".$userid."'".
				",".$trackerid.
				",'".$phpAds_config['rawDbhost']."'".
				",'".$log_host."'".
				",'".$log_country."')"
			, phpAds_rawDb);
			// return local_conversionid and dbserver_ip
			$conversionInfo = array('local_conversionid' => phpAds_dbInsertID(phpAds_rawDb),
			                        'dbserver_ip' => $phpAds_config['rawDbhost']);
			phpAds_dbClose(phpAds_rawDb);	
			return $conversionInfo;
		}
	}
	return false;	
}

function phpAds_logVariableValue($variableId, $value, $localConversionId, $dbServerIp)
{
    global $phpAds_config;
    $delayed = $phpAds_config['insert_delayed'] ? ' DELAYED' : '';
    $value = $value != null ? "'$value'" : 'NULL';
    $query = "
        INSERT $delayed INTO {$phpAds_config['tbl_variablevalues']}
            (variableid,value,local_conversionid,dbserver_ip)
        VALUES
            ($variableId,$value,'$localConversionId','$dbServerIp')
    ";
    if (phpAds_dbConnect(phpAds_rawDb)) {
        phpAds_dbQuery($query, phpAds_rawDb);
        phpAds_dbClose(phpAds_rawDb);
    }
}
?>