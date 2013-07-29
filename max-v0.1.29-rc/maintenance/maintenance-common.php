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
$Id: maintenance-common.php 3145 2005-05-20 13:15:01Z andrew $
*/

function debug($msg)
{
    global $conf;
    if ($conf['DEBUG']) {
        error_log($msg);
    }
}

/*********************************************************/
/* Add an entry to the userlog                           */
/*********************************************************/

function phpAds_userlogAdd($action, $object, $details = '')
{
	global $conf;
	$res = phpAds_dbQuery("
		INSERT INTO
			" . $conf['table']['userlog'] . "
		SET
			timestamp = ".time().",
			usertype = '". phpAds_userMaintenance ."',
			userid = 0,
			action = '".$action."',
			object = '".$object."',
			details = '" . addslashes($details) . "'
	");
}

// MySQL DB Resource
$phpAds_db_link = '';

// Disable delayed inserts when not using MyISAM tables
if ($conf['table_type'] != 'MYISAM')
	$conf['insert_delayed'] = false;

/*********************************************************/
/* Open a connection to the database			         */
/*********************************************************/

function phpAds_dbConnect($db = phpAds_adminDb)
{
	global $conf;
	global $phpAds_db_link, $phpAds_rawDb_link;

	switch($db) {
		case phpAds_rawDb:
				$dbport     = isset($conf['rawDbport']) ? $conf['rawDbport'] : 3306;
				$dbhost     = $dbport != 3306 ? $conf['rawDbhost'].':'.$dbport : $conf['rawDbhost'];
				$dbuser     = $conf['rawDbuser'];
				$dbpassword = $conf['rawDbpassword'];
				$dbname     = $conf['rawDbname'];

				if ($conf['persistent_connections'])
					$phpAds_rawDb_link = @mysql_pconnect($dbhost, $dbuser, $dbpassword);
				else
					$phpAds_rawDb_link = @mysql_connect($dbhost, $dbuser, $dbpassword);

				if ($conf['compatibility_mode'])
					return $phpAds_rawDb_link;

				if (@mysql_select_db($dbname, $phpAds_rawDb_link))
					return $phpAds_rawDb_link;
				break;

		case phpAds_adminDb:
		default:
				$dbport     = isset($conf['dbport']) ? $conf['dbport'] : 3306;
				$dbhost     = $dbport != 3306 ? $conf['dbhost'].':'.$dbport : $conf['dbhost'];
				$dbuser     = $conf['dbuser'];
				$dbpassword = $conf['dbpassword'];
				$dbname     = $conf['dbname'];
				
				if ($conf['persistent_connections']) {
					$phpAds_db_link = @mysql_pconnect($dbhost, $dbuser, $dbpassword);
				} else {
					$phpAds_db_link = @mysql_connect($dbhost, $dbuser, $dbpassword);
				}
					
				if (@mysql_select_db($dbname, $phpAds_db_link)) {
					return $phpAds_db_link;
				}
				break;
	}
}

/*********************************************************/
/* Close the connection to the database			         */
/*********************************************************/

function phpAds_dbClose($db = phpAds_adminDb)
{
	// Never close the database connection, because
	// it may interfere with other scripts which
	// share the same connection.

	global $phpAds_db_link, $phpAds_rawDb_link;
	switch($db) {
		case phpAds_rawDb: 
				mysql_close($phpAds_rawDb_link);
				break;
		case phpAds_adminDb:
		default:
				break;
	}
}

/*********************************************************/
/* Execute a query								         */
/*********************************************************/

function phpAds_dbQuery($query, $db = phpAds_adminDb)
{
    global $phpAds_last_query;
	global $phpAds_db_link, $phpAds_rawDb_link;
	
    $phpAds_last_query = $query;
    return mysql_query($query, $db == phpAds_adminDb ? $phpAds_db_link : $phpAds_rawDb_link);
}

/*********************************************************/
/* Get the number of rows returned                       */
/*********************************************************/

function phpAds_dbNumRows($res)
{
	return mysql_num_rows($res);
}

/*********************************************************/
/* Get next row as an array with keys                    */
/*********************************************************/

function phpAds_dbFetchArray($res)
{
	return mysql_fetch_array($res, MYSQL_ASSOC);
}

/*********************************************************/
/* Get next row as an array                              */
/*********************************************************/

function phpAds_dbFetchRow($res)
{
	return mysql_fetch_row($res);
}

/*********************************************************/
/* Get a specific row and column                         */
/*********************************************************/

function phpAds_dbResult($res, $row, $column)
{
	return @mysql_result($res, $row, $column);
}

/*********************************************************/
/* Free the result from memory                           */
/*********************************************************/

function phpAds_dbFreeResult($res)
{
	return @mysql_free_result($res);
}

/*********************************************************/
/* Return the number of affected rows                    */
/*********************************************************/

function phpAds_dbAffectedRows($db = phpAds_adminDb)
{
	global $phpAds_db_link, $phpAds_rawDb_link;
	return @mysql_affected_rows($db == phpAds_adminDb ? $phpAds_db_link : $phpAds_rawDb_link);
}

/*********************************************************/
/* Go to the specified row                               */
/*********************************************************/

function phpAds_dbSeekRow($res, $row)
{
	return @mysql_data_seek($res, $row);
}

/*********************************************************/
/* Get the ID of the last inserted row                   */
/*********************************************************/

function phpAds_dbInsertID($db = phpAds_adminDb)
{
	global $phpAds_db_link, $phpAds_rawDb_link;
	return @mysql_insert_id($db == phpAds_adminDb ? $phpAds_db_link : $phpAds_rawDb_link);
}

/*********************************************************/
/* Get the error message if something went wrong         */
/*********************************************************/

function phpAds_dbError($db = phpAds_adminDb)
{
	global $phpAds_db_link, $phpAds_rawDb_link;
	return @mysql_error($db == phpAds_adminDb ? $phpAds_db_link : $phpAds_rawDb_link);
}

function phpAds_dbErrorNo($db = phpAds_adminDb)
{
	global $phpAds_db_link, $phpAds_rawDb_link;
	return @mysql_errno($db == phpAds_adminDb ? $phpAds_db_link : $phpAds_rawDb_link);
}

// Current phpAds version
$phpAds_version = 0.1;
$phpAds_version_readable = "v0.1.16-beta";
$phpAds_productname = "Max Media Manager";
$phpAds_producturl = "max.awarez.net";
$phpAds_dbmsname = "MySQL";

$GLOBALS['phpAds_settings_information'] = array(
    'acl' =>                                array ('type' => 'boolean', 'sql' => false),
    'admin' =>                              array ('type' => 'string', 'sql' => true),
    'admin_email' =>                        array ('type' => 'string', 'sql' => true),
    'admin_email_headers' =>                array ('type' => 'string', 'sql' => true),
    'admin_fullname' =>                     array ('type' => 'string', 'sql' => true),
    'admin_instant_update' =>               array ('type' => 'boolean', 'sql' => false),
    'admin_novice' =>                       array ('type' => 'boolean', 'sql' => true),
    'admin_pw' =>                           array ('type' => 'string', 'sql' => true),
    'allow_invocation_frame' =>             array ('type' => 'boolean', 'sql' => true),
    'allow_invocation_js' =>                array ('type' => 'boolean', 'sql' => true),
    'allow_invocation_interstitial' =>      array ('type' => 'boolean', 'sql' => true),
    'allow_invocation_local' =>             array ('type' => 'boolean', 'sql' => true),
    'allow_invocation_plain' =>             array ('type' => 'boolean', 'sql' => true),
    'allow_invocation_plain_nocookies' =>   array ('type' => 'boolean', 'sql' => true),
    'allow_invocation_popup' =>             array ('type' => 'boolean', 'sql' => true),
    'allow_invocation_xmlrpc' =>            array ('type' => 'boolean', 'sql' => true),
    'autotarget_factor' =>                  array ('type' => 'double', 'sql' => true),
    'auto_clean_tables' =>                  array ('type' => 'boolean', 'sql' => true),
    'auto_clean_tables_interval' =>         array ('type' => 'integer', 'sql' => true),
    'auto_clean_userlog' =>                 array ('type' => 'boolean', 'sql' => true),
    'auto_clean_userlog_interval' =>        array ('type' => 'integer', 'sql' => true),
    'begin_of_week' =>                      array ('type' => 'integer', 'sql' => true),
    'block_adclicks' =>                     array ('type' => 'integer', 'sql' => false),
    'block_adconversions' =>                array ('type' => 'integer', 'sql' => false),
    'block_adviews' =>                      array ('type' => 'integer', 'sql' => false),
    'client_welcome' =>                     array ('type' => 'boolean', 'sql' => true),
    'client_welcome_msg' =>                 array ('type' => 'string', 'sql' => true),
    'compact_stats' =>                      array ('type' => 'boolean', 'sql' => true),
    'company_name' =>                       array ('type' => 'string', 'sql' => true),
    'compatibility_mode' =>                 array ('type' => 'boolean', 'sql' => false),
    'config_version' =>                     array ('type' => 'string', 'sql' => true),
    'content_gzip_compression' =>           array ('type' => 'boolean', 'sql' => true),
    'con_key' =>                            array ('type' => 'boolean', 'sql' => false),
    'dbhost' =>                             array ('type' => 'string', 	'sql' => false),
    'dbname' =>                             array ('type' => 'string', 	'sql' => false),
    'dbpassword' =>                         array ('type' => 'string', 	'sql' => false),
    'dbport' =>                             array ('type' => 'integer', 'sql' => false),
    'dbuser' =>                             array ('type' => 'string', 	'sql' => false),
    'default_banner_url' =>                 array ('type' => 'string', 	'sql' => false),
    'default_banner_target' =>              array ('type' => 'string', 	'sql' => false),
    'default_banner_weight' =>              array ('type' => 'integer', 'sql' => true),
    'delivery_caching' =>                   array ('type' => 'string', 	'sql' => false),
    'default_campaign_weight' =>            array ('type' => 'integer', 'sql' => true),
    'default_conversion_clickwindow' =>     array ('type' => 'integer', 'sql' => false),
    'default_conversion_viewwindow' =>      array ('type' => 'integer', 'sql' => false),
    'geotracking_type' =>                   array ('type' => 'string',  'sql' => false),
    'geotracking_location' =>               array ('type' => 'string',  'sql' => false),
    'geotracking_stats' =>                  array ('type' => 'boolean', 'sql' => false),
    'geotracking_cookie' =>                 array ('type' => 'boolean', 'sql' => false),
    'gui_hide_inactive' =>                  array ('type' => 'boolean', 'sql' => true),
    'gui_link_compact_limit' =>             array ('type' => 'integer', 'sql' => true),
    'gui_show_banner_html' =>               array ('type' => 'boolean', 'sql' => true),
    'gui_show_banner_info' =>               array ('type' => 'boolean', 'sql' => true),
    'gui_show_banner_preview' =>            array ('type' => 'boolean', 'sql' => true),
    'gui_show_campaign_info' =>             array ('type' => 'boolean', 'sql' => true),
    'gui_show_campaign_preview' =>          array ('type' => 'boolean', 'sql' => true),
    'gui_show_matching' =>                  array ('type' => 'boolean', 'sql' => true),
    'gui_show_parents' =>                   array ('type' => 'boolean', 'sql' => true),
    'ignore_hosts' =>                       array ('type' => 'array',	'sql' => false),
    'insert_delayed' =>                     array ('type' => 'boolean', 'sql' => false),
    'instant_update_cache' =>               array ('type' => 'boolean', 'sql' => false),
    'instant_update_priority' =>            array ('type' => 'boolean', 'sql' => false),
    'language' =>                           array ('type' => 'string', 'sql' => true),
    'log_adviews' =>                        array ('type' => 'boolean', 'sql' => false),
    'log_adclicks' =>                       array ('type' => 'boolean', 'sql' => false),
    'log_adconversions' =>                  array ('type' => 'boolean', 'sql' => false),
    'log_beacon' =>                         array ('type' => 'boolean', 'sql' => false),
    'log_hostname' =>                       array ('type' => 'boolean', 'sql' => false),
    'log_iponly' =>                         array ('type' => 'boolean', 'sql' => false),
    'log_source' =>                         array ('type' => 'boolean', 'sql' => false),
    'maintenance_timestamp' =>              array ('type' => 'integer', 'sql' => true),
    'mult_key' =>                           array ('type' => 'boolean', 'sql' => false),
    'my_header' =>                          array ('type' => 'string', 'sql' => true),
    'my_footer' =>                          array ('type' => 'string', 'sql' => true),
    'name' =>                               array ('type' => 'string', 'sql' => true),
    'override_gd_imageformat' =>            array ('type' => 'string', 'sql' => true),
    'p3p_compact_policy' =>                 array ('type' => 'string', 	'sql' => false),
    'p3p_policies' =>                       array ('type' => 'boolean', 'sql' => false),
    'p3p_policy_location' =>                array ('type' => 'string', 	'sql' => false),
    'percentage_decimals' =>                array ('type' => 'integer', 'sql' => true),
    'persistent_connections' =>             array ('type' => 'boolean', 'sql' => false),
    'proxy_lookup' =>                       array ('type' => 'boolean', 'sql' => false),
    'obfuscate' =>                          array ('type' => 'boolean', 'sql' => false),
    'qmail_patch' =>                        array ('type' => 'boolean', 'sql' => true),
    'reverse_lookup' =>                     array ('type' => 'boolean', 'sql' => false),
    'table_prefix' =>                       array ('type' => 'string', 	'sql' => false),
    'table_type' =>                         array ('type' => 'string', 	'sql' => false),
    'tbl_acls' =>                           array ('type' => 'string', 	'sql' => false),
    'tbl_adclicks' =>                       array ('type' => 'string', 	'sql' => false),
    'tbl_adconversions' =>                  array ('type' => 'string', 	'sql' => false),
    'tbl_adstats' =>                        array ('type' => 'string', 	'sql' => false),
    'tbl_adviews' =>                        array ('type' => 'string', 	'sql' => false),
    'tbl_affiliates' =>                     array ('type' => 'string', 	'sql' => false),
    'tbl_agency' =>                         array ('type' => 'string', 	'sql' => false),
    'tbl_application_variable' =>           array ('type' => 'string', 	'sql' => false),
    'tbl_banners' =>                        array ('type' => 'string', 	'sql' => false),
    'tbl_cache' =>                          array ('type' => 'string', 	'sql' => false),
    'tbl_campaigns' =>                      array ('type' => 'string', 	'sql' => false),
    'tbl_campaigns_trackers' =>             array ('type' => 'string', 	'sql' => false),
    'tbl_clients' =>                        array ('type' => 'string', 	'sql' => false),
    'tbl_config' =>                         array ('type' => 'string', 	'sql' => false),
    'tbl_conversionlog' =>                  array ('type' => 'string', 	'sql' => false),
    'tbl_images' =>                         array ('type' => 'string', 	'sql' => false),
    'tbl_session' =>                        array ('type' => 'string', 	'sql' => false),
    'tbl_targetstats' =>                    array ('type' => 'string', 	'sql' => false),
    'tbl_trackers' =>                       array ('type' => 'string', 	'sql' => false),
    'tbl_userlog' =>                        array ('type' => 'string', 	'sql' => false),
    'tbl_variables' =>                      array ('type' => 'string', 	'sql' => false),
    'tbl_variablevalues' =>                 array ('type' => 'string', 	'sql' => false),
    'tbl_zones' =>                          array ('type' => 'string', 	'sql' => false),
    'type_html_allow' =>                    array ('type' => 'boolean', 'sql' => true),
    'type_html_auto' =>                     array ('type' => 'boolean', 'sql' => false),
    'type_html_php' =>                      array ('type' => 'boolean', 'sql' => false),
    'type_sql_allow' =>                     array ('type' => 'boolean', 'sql' => true),
    'type_txt_allow' =>                     array ('type' => 'boolean', 'sql' => true),
    'type_url_allow' =>                     array ('type' => 'boolean', 'sql' => true),
    'type_web_allow' =>                     array ('type' => 'boolean', 'sql' => true),
    'type_web_dir' =>                       array ('type' => 'string', 'sql' => true),
    'type_web_ftp' =>                       array ('type' => 'string', 'sql' => true),
    'type_web_mode' =>                      array ('type' => 'integer', 'sql' => true),
    'type_web_url' =>                       array ('type' => 'string', 'sql' => false),
    'type_web_ssl_url' =>                   array ('type' => 'string', 'sql' => false),
    'ui_enabled' =>                         array ('type' => 'boolean', 'sql' => false),
    'ui_forcessl' =>                        array ('type' => 'boolean', 'sql' => false),
    'updates_frequency' =>                  array ('type' => 'integer', 'sql' => true),
    'updates_last_seen' =>                  array ('type' => 'string', 'sql' => true),
    'updates_timestamp' =>                  array ('type' => 'integer', 'sql' => true),
    'url_prefix' =>                         array ('type' => 'string', 	'sql' => false),
    'ssl_url_prefix' =>                     array ('type' => 'string', 	'sql' => false),
    'admin_url_prefix' =>                   array ('type' => 'string', 	'sql' => false),
    'use_keywords' =>                       array ('type' => 'boolean', 'sql' => false),
    'userlog_email' =>                      array ('type' => 'boolean', 'sql' => true),
    'userlog_priority' =>                   array ('type' => 'boolean', 'sql' => true),
    'userlog_autoclean' =>                  array ('type' => 'boolean', 'sql' => true),
    'warn_admin' =>                         array ('type' => 'boolean', 'sql' => true),
    'warn_agency' =>                        array ('type' => 'boolean', 'sql' => true),	
    'warn_client' =>                        array ('type' => 'boolean', 'sql' => true),
    'warn_limit' =>                         array ('type' => 'integer', 'sql' => true)
);


/*********************************************************/
/* Load configuration from database                      */
/*********************************************************/

function phpAds_LoadDbConfig($agencyid = 0)
{
	global $conf, $phpAds_settings_information;
	
	if ((!empty($GLOBALS['phpAds_db_link']) || phpAds_dbConnect()) && isset($conf['table']['config']))
	{
		$query = "SELECT *".
			" FROM ".$conf['table']['config'].
			" WHERE agencyid=".$agencyid;
			
		if ($res = phpAds_dbQuery($query))
		{
			if ($row = phpAds_dbFetchArray($res, 0))
			{
				while (list($k, $v) = each($phpAds_settings_information))
				{
					if (!$v['sql'] || !isset($row[$k]))
						continue;
					
					switch($v['type'])
					{
						case 'boolean': $row[$k] = $row[$k] == 't'; break;
						case 'integer': $row[$k] = (int)$row[$k]; break;
						case 'array': $row[$k] = unserialize($row[$k]); break;
						case 'float': $row[$k] = (float)$row[$k]; break;
					}
					
					$conf[$k] = $row[$k];
				}
				
				reset($phpAds_settings_information);
				
				return true;
			}
		}
	}
	
	return false;
}

/*********************************************************/
/* Send an email                                         */
/*********************************************************/

function phpAds_sendMail($email, $readable, $subject, $contents)
{
	global $conf, $phpAds_CharSet;
	
	// Build To header
	if (!get_cfg_var('SMTP'))
		$param_to = '"'.$readable.'" <'.$email.'>';
	else
		$param_to = $email;
	
	// Build additional headers
	$param_headers = "Content-Transfer-Encoding: 8bit\r\n";
	
	if (isset($phpAds_CharSet))
		$param_headers .= "Content-Type: text/plain; charset=".$phpAds_CharSet."\r\n"; 
	
	if (get_cfg_var('SMTP'))
		$param_headers .= 'To: "'.$readable.'" <'.$email.">\r\n";
	
	$param_headers .= 'From: "'.$conf['admin_fullname'].'" <'.$conf['admin_email'].'>'."\r\n";
	
	if ($conf['admin_email_headers'] != '')
		$param_headers .= "\r\n".$conf['admin_email_headers'];
	
	// Use only \n as header separator when qmail is used
	if ($conf['qmail_patch'])
		$param_headers = str_replace("\r", '', $param_headers);
	
	// Add \r to linebreaks in the contents for MS Exchange compatibility
	$contents = str_replace("\n", "\r\n", $contents);
	
	return (@mail($param_to, $subject, $contents, $param_headers));
}

/*********************************************************/
/* Mail warning - preset is reached						 */
/*********************************************************/

function phpAds_deactivateMail($campaign)
{
	global $conf;
	global $strMailSubjectDeleted, $strMailHeader, $strMailClientDeactivated;
	global $strNoMoreClicks, $strNoMoreViews, $strBeforeActivate, $strAfterExpire;
	global $strBanner, $strMailNothingLeft, $strMailFooter, $strUntitled;

    //  strings
/*
$strMailSubjectDeleted = "Deactivated banners";
$strMailHeader = "Dear {contact},\n";
$strMailClientDeactivated = "The following banners have been disabled because";
$strNoMoreClicks = "there are no AdClicks remaining";
$strNoMoreViews = "there are no AdViews remaining";
$strBeforeActivate = "the activation date has not yet been reached";
$strAfterExpire = "the expiration date has been reached";
$strBanner = "Banner";
$strMailNothingLeft = "If you would like to continue advertising on our website, please feel free to contact us.\nWe'd be glad to hear from you.";
$strMailFooter = "Regards,\n   {adminfullname}";
$strUntitled = "Untitled";
*/

	$clientresult = phpAds_dbQuery(
		"SELECT *".
		" FROM ".$conf['table']['clients'].
		" WHERE clientid=".$campaign['clientid']
	);
	if ($client = phpAds_dbFetchArray($clientresult))
	{
		if ($client["email"] != '' && $client["reportdeactivate"] == 't')
		{
			// Build email
			$Subject = $strMailSubjectDeleted.": ".$campaign['campaignname'];
			
			$Body  = $strMailHeader."\n";
			$Body .= $strMailClientDeactivated;
			if ($campaign['clicks'] == 0) 			$Body .= ", $strNoMoreClicks";
			if ($campaign['views'] == 0) 			$Body .= ", $strNoMoreViews";
			if (time() < $campaign["activate_st"])	$Body .= ", $strBeforeActivate";
			if (time() > $campaign["expire_st"] && $campaign["expire_st"] != 0)
			$Body .= ", $strAfterExpire";
			$Body .= ".\n\n";
			$res_banners = phpAds_dbQuery(
				"SELECT".
				" bannerid".
				",url".
				",description".
				",alt".
				" FROM ".$conf['table']['banners'].
				" WHERE campaignid=".$campaign['campaignid']
			);
			
			if (phpAds_dbNumRows($res_banners) > 0)
			{
				$Body .= "-------------------------------------------------------\n";
				
				while($row_banners = phpAds_dbFetchArray($res_banners))
				{
					$name = "[id".$row_banners['bannerid']."] ";
					
					if ($row_banners['description'] != "")
						$name .= $row_banners['description'];
					elseif ($row_banners['alt'] != "")
						$name .= $row_banners['alt'];
					else
						$name .= $strUntitled;
					
					$Body .= $strBanner."  ".$name."\n";
					$Body .= "linked to: ".$row_banners['url']."\n";
					$Body .= "-------------------------------------------------------\n";
				}
			}
			
			$Body .= "\n";
			$Body .= "$strMailNothingLeft\n\n";
			$Body .= "$strMailFooter";
			
			$Body  = str_replace ("{clientname}", $client["clientname"], $Body);
			$Body  = str_replace ("{contact}", $client["contact"], $Body);
			$Body  = str_replace ("{adminfullname}", $conf['admin_fullname'], $Body);
			// Send email
			phpAds_sendMail($client['email'], $client['contact'], $Subject, $Body);
			
			if ($conf['userlog_email']) 
				phpAds_userlogAdd(phpAds_actionDeactivationMailed, $campaign['campaignid'], $Subject."\n\n".$Body);
		}
	}
}

/*********************************************************/
/* Old lib-statistics code, general html transform fns   */
/*********************************************************/

// Define defaults
$clientCache = array();
$campaignCache = array();
$bannerCache = array();
$zoneCache = array();
$affiliateCache = array();

/*********************************************************/
/* Limit a string to a number of characters              */
/*********************************************************/

function phpAds_breakString($str, $maxLen, $append = "...")
{
	return strlen($str) > $maxLen 
		? rtrim(substr($str, 0, $maxLen - strlen($append))).$append 
		: $str;
}

/*********************************************************/
/* Build the client name from ID and name                */
/*********************************************************/

function phpAds_buildName($id, $name)
{
	return ("<span dir='".$GLOBALS['phpAds_TextDirection']."'>[id$id]</span> ".$name);
}

/*********************************************************/
/* Get list order status                                 */
/*********************************************************/

// Manage Orderdirection
function phpAds_getOrderDirection($ThisOrderDirection)
{
	$sqlOrderDirection = '';
	
	switch ($ThisOrderDirection)
	{
		case 'down':
			$sqlOrderDirection .= ' DESC';
			break;
		case 'up':
			$sqlOrderDirection .= ' ASC';
			break;
		default:
			$sqlOrderDirection .= ' ASC';
	}
	return $sqlOrderDirection;
}

// Order for $conf['table']['banners']
function phpAds_getBannerListOrder($ListOrder, $OrderDirection)
{
	$sqlTableOrder = '';
	switch ($ListOrder)
	{
		case 'name':
			$sqlTableOrder = ' ORDER BY description';
			break;
		case 'id':
			$sqlTableOrder = ' ORDER BY bannerid';
			break;
		case 'views':
			$sqlTableOrder = ' ORDER BY views';
			break;
		case 'clicks':
			$sqlTableOrder = ' ORDER BY clicks';
			break;
		case 'conversions':
			$sqlTableOrder = ' ORDER BY conversions';
			break;
		case 'ctr':
			$sqlTableOrder = ' ORDER BY ctr';
			break;
		case 'cnvr':
			$sqlTableOrder = ' ORDER BY cnvr';
			break;
		default:
			$sqlTableOrder = ' ORDER BY description,bannerid';
	}
	if 	($sqlTableOrder != '') {
		$sqlTableOrder .= phpAds_getOrderDirection($OrderDirection);
	}
	return ($sqlTableOrder);
}

function phpAds_getHourListOrder($ListOrder, $OrderDirection)
{
	$sqlTableOrder = '';
	switch ($ListOrder)
	{
		case 'name':
			$sqlTableOrder = ' ORDER BY hour';
			break;
		case 'id':
			$sqlTableOrder = ' ORDER BY hour';
			break;
		case 'views':
			$sqlTableOrder = ' ORDER BY views';
			break;
		case 'clicks':
			$sqlTableOrder = ' ORDER BY clicks';
			break;
		case 'conversions':
			$sqlTableOrder = ' ORDER BY conversions';
			break;
		case 'ctr':
			$sqlTableOrder = ' ORDER BY ctr';
			break;
		case 'cnvr':
			$sqlTableOrder = ' ORDER BY cnvr';
			break;
		default:
			$sqlTableOrder = ' ORDER BY hour';
	}
	if 	($sqlTableOrder != '') {
		$sqlTableOrder .= phpAds_getOrderDirection($OrderDirection);
	}
	return ($sqlTableOrder);
}

/*********************************************************/
/* Build the banner name from ID, Description and Alt    */
/*********************************************************/

function phpAds_buildBannerName($bannerid, $description = '', $alt = '', $limit = 30, $use_html = true)
{
	global $strUntitled;
	$name = '';
	
	if ($description != "")
		$name .= $description;
	elseif ($alt != "")
		$name .= $alt;
	else
		$name .= $strUntitled;
	
	
	if (strlen($name) > $limit)
		$name = phpAds_breakString ($name, $limit);
	
	if ($bannerid != '')
		$name = $use_html ? "<span dir='".$GLOBALS['phpAds_TextDirection']."'>[id$bannerid]</span> ".$name : "[id$bannerid] ".$name;
	
	return ($name);
}

/*********************************************************/
/* Replace variables in HTML or external banner          */
/*********************************************************/

function phpAds_replaceVariablesInBannerCode($htmlcode)
{
	global $conf;
	
	// Parse for variables
	$htmlcode = str_replace ('{timestamp}',	time(), $htmlcode);
	$htmlcode = str_replace ('%7Btimestamp%7D',	time(), $htmlcode);
	
	while (preg_match ('#(%7B|\{)random((%3A|:)([0-9]+)){0,1}(%7D|})#i', $htmlcode, $matches))
	{
		if ($matches[4])
			$randomdigits = $matches[4];
		else
			$randomdigits = 8;
		
		if (isset($lastdigits) && $lastdigits == $randomdigits)
			$randomnumber = $lastrandom;
		else
		{
			$randomnumber = '';
			
			for ($r=0; $r<$randomdigits; $r=$r+9)
				$randomnumber .= (string)mt_rand (111111111, 999999999);
			
			$randomnumber  = substr($randomnumber, 0 - $randomdigits);
		}
		
		$htmlcode = str_replace ($matches[0], $randomnumber, $htmlcode);
		
		$lastdigits = $randomdigits;
		$lastrandom = $randomnumber;
	}
	
	
	// Parse PHP code
	if ($conf['type_html_php'])
	{
		if (preg_match ("#(\<\?php(.*)\?\>)#i", $htmlcode, $parser_regs))
		{
			// Extract PHP script
			$parser_php 	= $parser_regs[2];
			$parser_result 	= '';
			
			// Replace output function
			$parser_php = preg_replace ("#echo([^;]*);#i", '$parser_result .=\\1;', $parser_php);
			$parser_php = preg_replace ("#print([^;]*);#i", '$parser_result .=\\1;', $parser_php);
			$parser_php = preg_replace ("#printf([^;]*);#i", '$parser_result .= sprintf\\1;', $parser_php);
			
			// Split the PHP script into lines
			$parser_lines = explode (";", $parser_php);
			for ($parser_i = 0; $parser_i < sizeof($parser_lines); $parser_i++)
			{
				if (trim ($parser_lines[$parser_i]) != '')
					eval (trim ($parser_lines[$parser_i]).';');
			}
			
			// Replace the script with the result
			$htmlcode = str_replace ($parser_regs[1], $parser_result, $htmlcode);
		}
	}
	
	return ($htmlcode);
}

/*********************************************************/
/* Build Click-Thru ratio                                */
/*********************************************************/

function phpAds_buildRatio($numerator, $denominator)
{
	return ($denominator == 0 ? 0 : $numerator/$denominator);
}

/*********************************************************/
/* Get overview statistics						         */
/*********************************************************/

function phpAds_totalStats($column, $bannerid, $timeconstraint="")
{
    global $conf;
    
    $where = "";
	
    if (!empty($bannerid)) 
    	$where = "WHERE bannerid = '$bannerid'";
    
	if (!empty($timeconstraint))
	{
		if (!empty($bannerid))
			$where .= " AND ";
		else
			$where = "WHERE ";
		
		if ($timeconstraint == "month")
		{
			$begin = date('Ymd', mktime(0, 0, 0, date('m'), 1, date('Y')));
			$end   = date('Ymd', mktime(0, 0, 0, date('m') + 1, 1, date('Y')));
			$where .= "day >= $begin AND day < $end";
		}
		elseif ($timeconstraint == "week")
		{
			$begin = date('Ymd', mktime(0, 0, 0, date('m'), date('d') - 6, date('Y')));
			$end   = date('Ymd', mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')));
			$where .= "day >= $begin AND day < $end";
		}
		else
		{
			$begin = date('Ymd', mktime(0, 0, 0, date('m'), date('d'), date('Y')));
		    
			$where .= "day = $begin";
		}
	}
	
    $res = phpAds_dbQuery("SELECT SUM($column) as qnt FROM ".$conf['table']['adstats']." $where") or phpAds_sqlDie();
    
	if (phpAds_dbNumRows ($res))
    { 
        $row = phpAds_dbFetchArray($res);
		return ($row['qnt']);
	}
	else
		return (0);
}

function phpAds_totalClicks($bannerid="", $timeconstraint="")
{
	global $conf;
	
    return phpAds_totalStats("clicks", $bannerid, $timeconstraint);
}

function phpAds_totalConversions($bannerid="", $timeconstraint="")
{
	global $conf;
	
    return phpAds_totalStats("conversions", $bannerid, $timeconstraint);
}

function phpAds_totalViews($bannerid="", $timeconstraint="")
{
	global $conf;
	
    return phpAds_totalStats("views", $bannerid, $timeconstraint);
}

/*********************************************************/
/* Calculates timestamp taking DST into account          */
/*********************************************************/

function phpAds_makeTimestamp($start, $offset = 0)
{
	if (!$offset)
		return $start;
	
	return $start + $offset + (date('I', $start) - date('I', $start + $offset)) * 60;
}

/*********************************************************/
/* Obtain/release the maintenance priority lock          */
/*********************************************************/

/**
 * Obtains an advisory database lock for the maintenance priority process.
 *
 * @return boolean True if lock was obtained, false otherwise.
 */
function obtainPriorityLock()
{
    $query = "SELECT GET_LOCK('priority', 1) AS 'lock'";
    $res = phpAds_dbQuery($query);
    $row = phpAds_dbResult($res, 0, 'lock');
    if ($row[0] == 1) {
        // Lock obtained successfully
        return true;
    }
    return false;
}

/**
 * Releases the advisory database lock for the maintenance priority process.
 */
function releasePriorityLock()
{
   $query = "SELECT RELEASE_LOCK('priority')";
   $res = phpAds_dbQuery($query);
}

?>