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
$Id: lib-config.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/

$phpAds_settings_write_cache = array();
$phpAds_settings_update_cache = array();

$phpAds_configFilepath = phpAds_path.'/config.inc.php';

/*********************************************************/
/* Public: Determine if the config file is writable      */
/*********************************************************/

function phpAds_isConfigWritable()
{
	global $phpAds_configFilepath;
	return (@fclose(@fopen($phpAds_configFilepath, 'a')));
}

/*********************************************************/
/* Public: Edit a setting                                */
/*********************************************************/

function phpAds_SettingsWriteAdd($key, $value)
{
	global $phpAds_settings_write_cache;
	$phpAds_settings_write_cache[$key] = $value;
	return true;
}

/*********************************************************/
/* Public: Store all edited settings                     */
/*********************************************************/

function phpAds_SettingsWriteFlush()
{
	global $phpAds_config;
	global $phpAds_settings_information, $phpAds_settings_write_cache;
	$sql = array();
	$config_inc = array();
	while (list($k, $v) = each($phpAds_settings_write_cache)) {
		$k_sql  = $phpAds_settings_information[$k]['sql'];
		$k_type = $phpAds_settings_information[$k]['type'];
		if ($k_sql) {
			if ($k_type == 'boolean') {
				$v = $v ? 't' : 'f';
			}
			$sql[] = $k." = '".$v."'";
		} else {
			if ($k_type == 'boolean') {
				$v = $v ? true : false;
			}  else if ($k_type != 'array') {
				$v = stripslashes($v);
			}
			$config_inc[] = array($k, $v, $k_type);
		}
	}
	if (count($sql)) {
		if (phpAds_isUser(phpAds_Agency)) {
			$agencyid = phpAds_getUserID();
		} else {
			$agencyid = 0;
		}
		$query = "UPDATE ".$phpAds_config['tbl_config']." SET ".join(", ", $sql)." WHERE agencyid=".$agencyid;
		$res = @phpAds_dbQuery($query);
		if (@phpAds_dbAffectedRows() < 1) {
			$query = "INSERT INTO ".$phpAds_config['tbl_config']." SET ".join(", ", $sql).",agencyid=".$agencyid;
			@phpAds_dbQuery($query);
		}
	}
	if (count($config_inc)) {
		if (!phpAds_ConfigFilePrepare()) {
			return false;
		}
		while(list(, $v) = each($config_inc)) {
			phpAds_ConfigFileSet($v[0], $v[1], $v[2]);
		}
		return phpAds_ConfigFileFlush();
	}
	return true;
}

/*********************************************************/
/* Public: Clear the config file                         */
/*********************************************************/

function phpAds_ConfigFileClear()
{
	global $phpAds_configFilepath;
	$config		= @fopen($phpAds_configFilepath,'w');
	$template   = @fopen(phpAds_path.'/libraries/defaults/config.template.php','r');
	if ($config && $template) {
		// Write the contents of the template to the config file
		@fwrite ($config, @fread($template, filesize(phpAds_path.'/libraries/defaults/config.template.php')));
		@fclose($template);
		@fclose($config);
	}
}

/*********************************************************/
/* Public: Import settings from the config file          */
/*********************************************************/

function phpAds_ConfigFileUpdateFlush()
{
	global $phpAds_settings_update_cache;
	global $phpAds_settings_information;	
	for (reset($phpAds_settings_update_cache); $key = key($phpAds_settings_update_cache); next($phpAds_settings_update_cache)) {
		phpAds_SettingsWriteAdd ($key, $phpAds_settings_update_cache[$key]);
	}
	// Before we start writing all the settings
	// start with a clean config file to make
	// sure we always have the latest version
	phpAds_ConfigFileClear();	
	// Now write all the settings back to the
	// clean config file
	return phpAds_SettingsWriteFlush();
}

function phpAds_ConfigFileUpdateExport()
{
	global $phpAds_config;
	global $phpAds_settings_update_cache;	
	for (reset($phpAds_settings_update_cache); $key = key($phpAds_settings_update_cache); next($phpAds_settings_update_cache)) {
		// Overwrite existing values
		$phpAds_config[$key] = $phpAds_settings_update_cache[$key];
	}
}

/*********************************************************/
/* Private: Read the config file and start editing       */
/*********************************************************/

function phpAds_ConfigFilePrepare()
{
	global $phpAds_configBuffer, $phpAds_configFilepath;	
	if (phpAds_isConfigWritable ()) {
		if ($confighandle = @fopen($phpAds_configFilepath,'r')) {
			$phpAds_configBuffer = @fread($confighandle, filesize($phpAds_configFilepath));
			@fclose ($confighandle);
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/*********************************************************/
/* Private: Edit a setting                               */
/*********************************************************/

function phpAds_ConfigFileSet($key, $value, $type)
{
	global $phpAds_configBuffer;
	// Prepare value
	if ($type == 'array' && is_array($value)) {
		reset ($value);
		while (list ($akey, $aval) = each ($value))  {
		    if (is_string ($aval) && $aval != '') {
				$value[$akey] = "'$aval'";
		    }
		}
		$value = "array (".implode (',', $value).")";
	} else if ($type == 'string') {
		$value = "'$value'";
	} elseif ($type == 'boolean') {
		$value = ($value ? 'true' : 'false');
	}
	$phpAds_configBuffer = preg_replace('/(phpAds_config\[\''.$key.'\'\]\s*=)\s*[^\n]*;/', "\$1 $value;", $phpAds_configBuffer);
}

/*********************************************************/
/* Private: Write edited config file                     */
/*********************************************************/

function phpAds_ConfigFileFlush()
{
	global $phpAds_configBuffer, $phpAds_configFilepath;
	if ($phpAds_configBuffer != '' && phpAds_isConfigWritable) {
		if ($confighandle = @fopen($phpAds_configFilepath,'w')) {
			$result = @fwrite ($confighandle, $phpAds_configBuffer);
			@fclose ($confighandle);
			return $result;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

?>