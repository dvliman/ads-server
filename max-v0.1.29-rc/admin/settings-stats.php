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
$Id: settings-stats.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
include ("lib-settings.inc.php");


// Register input variables
phpAds_registerGlobal (
	 'admin_email_headers'
	,'auto_clean_tables'
	,'auto_clean_tables_interval'
	,'auto_clean_userlog'
	,'auto_clean_userlog_interval'
	,'block_adviews'
	,'block_adclicks'
	,'block_adconversions'
	,'compact_stats'
	,'default_conversion_clickwindow'
	,'default_conversion_viewwindow'
	,'geotracking_stats'
	,'ignore_hosts'
	,'log_adviews'
	,'log_adclicks'
	,'log_adconversions'
	,'log_beacon'
	,'log_hostname'
	,'log_iponly'
	,'log_source'
	,'qmail_patch'
	,'warn_admin'
	,'warn_client'
	,'warn_agency'
	,'warn_limit'
);


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);


$errormessage = array();
$sql = array();

if (isset($HTTP_POST_VARS['submit']) && $HTTP_POST_VARS['submit'] == 'true')
{
	if (isset($compact_stats))
		phpAds_SettingsWriteAdd('compact_stats', ($compact_stats == '1'));
	
	phpAds_SettingsWriteAdd('log_adviews', isset($log_adviews));
	phpAds_SettingsWriteAdd('log_adclicks', isset($log_adclicks));
	phpAds_SettingsWriteAdd('log_adconversions', isset($log_adconversions));
	
	phpAds_SettingsWriteAdd('log_source', isset($log_source));
	phpAds_SettingsWriteAdd('geotracking_stats', isset($geotracking_stats));
	phpAds_SettingsWriteAdd('log_hostname', isset($log_hostname));
	phpAds_SettingsWriteAdd('log_iponly', isset($log_iponly));
	
	phpAds_SettingsWriteAdd('log_beacon', isset($log_beacon));
	
	
	if (isset($ignore_hosts))
	{
		if (trim($ignore_hosts) != '')
		{
			$ignore_hosts = explode("\n",
				trim(ereg_replace("[[:blank:]\n\r]+", "\n",
				stripslashes($ignore_hosts))));
			
			phpAds_SettingsWriteAdd('ignore_hosts', $ignore_hosts);
		}
		else
			phpAds_settingsWriteAdd('ignore_hosts', array());
	}
	
	if (isset($block_adviews))
		phpAds_SettingsWriteAdd('block_adviews', $block_adviews);
	if (isset($block_adclicks))
		phpAds_SettingsWriteAdd('block_adclicks', $block_adclicks);
	if (isset($block_adconversions))
		phpAds_SettingsWriteAdd('block_adconversions', $block_adconversions);
	if (isset($default_conversion_clickwindow))
		phpAds_SettingsWriteAdd('default_conversion_clickwindow', $default_conversion_clickwindow);
	if (isset($default_conversion_viewwindow))
		phpAds_SettingsWriteAdd('default_conversion_viewwindow', $default_conversion_viewwindow);
	
	
	
	phpAds_SettingsWriteAdd('warn_admin', isset($warn_admin));
	phpAds_SettingsWriteAdd('warn_client', isset($warn_client));
	phpAds_SettingsWriteAdd('warn_agency', isset($warn_agency));
	
	if (isset($warn_limit))
	{
		if (!is_numeric($warn_limit) || $warn_limit <= 0)
			$errormessage[2][] = $strWarnLimitErr;
		else
			phpAds_SettingsWriteAdd('warn_limit', $warn_limit);
	}
	
	if (isset($admin_email_headers))
	{
		$admin_email_headers = trim(ereg_replace("\r?\n", "\\r\\n", $admin_email_headers));
		phpAds_SettingsWriteAdd('admin_email_headers', $admin_email_headers);
	}
	
	phpAds_SettingsWriteAdd('qmail_patch', isset($qmail_patch));
	
	
	
	phpAds_SettingsWriteAdd('auto_clean_tables', isset($auto_clean_tables));
	phpAds_SettingsWriteAdd('auto_clean_userlog', isset($auto_clean_userlog));
	
	if (isset($auto_clean_tables_interval))
	{
		if (!is_numeric($auto_clean_tables_interval) || $auto_clean_tables_interval <= 2)
			$errormessage[3][] = $strAutoCleanErr;
		else
			phpAds_SettingsWriteAdd('auto_clean_tables_interval', $auto_clean_tables_interval);
	}
	
	if (isset($auto_clean_userlog_interval))
	{
		if (!is_numeric($auto_clean_userlog_interval) || $auto_clean_userlog_interval <= 2)
			$errormessage[3][] = $strAutoCleanErr;
		else
			phpAds_SettingsWriteAdd('auto_clean_userlog_interval', $auto_clean_userlog_interval);
	}
	
	if (!count($errormessage))
	{
		phpAds_SettingsWriteFlush();
		header("Location: settings-banner.php");
		exit;
	}
}



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PrepareHelp();
phpAds_PageHeader("5.1");
if (phpAds_isUser(phpAds_Admin))
{
	phpAds_ShowSections(array("5.1", "5.3", "5.4", "5.2","5.5"));
}
elseif (phpAds_isUser(phpAds_Agency))
{
	phpAds_ShowSections(array("5.1"));
}
phpAds_SettingsSelection("stats");



/*********************************************************/
/* Cache settings fields and get help HTML Code          */
/*********************************************************/

// Change ignore_hosts into a string, so the function handles it good
$phpAds_config['ignore_hosts'] = join("\n", $phpAds_config['ignore_hosts']);



$settings = array (

array (
	'text' 	  => $strStatisticsFormat,
	'items'	  => array (
		array (
			'type' 	  => 'select', 
			'name' 	  => 'compact_stats',
			'text' 	  => $strCompactStats,
			'items'   => array($strVerbose, $strCompact)
		),
		array (
			'type'    => 'break'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'log_adviews',
			'text'	  => $strLogAdViews,
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'log_adclicks',
			'text'	  => $strLogAdClicks,
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'log_adconversions',
			'text'	  => $strLogAdConversions,
		),
		array (
			'type'    => 'break',
			'size'	  => 'large'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'log_source',
			'text'	  => $strLogSource,
			'depends' => '(log_adclicks==true || log_adviews==true) && compact_stats==0'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'geotracking_stats',
			'text'	  => $strGeoLogStats,
			'visible' => $phpAds_config['geotracking_type'] != '',
			'depends' => '(log_adclicks==true || log_adviews==true) && compact_stats==0'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'log_hostname',
			'text'	  => $strLogHostnameOrIP,
			'visible' => isset($HTTP_SERVER_VARS['REMOTE_HOST']) || $phpAds_config['reverse_lookup'],
			'depends' => '(log_adclicks==true || log_adviews==true) && compact_stats==0'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'log_iponly',
			'text'	  => $strLogIPOnly,
			'indent'  => true,
			'visible' => isset($HTTP_SERVER_VARS['REMOTE_HOST']) || $phpAds_config['reverse_lookup'],
			'depends' => '(log_adclicks==true || log_adviews==true) && compact_stats==0 && log_hostname==true'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'log_hostname',
			'text'	  => $strLogIP,
			'visible' => !isset($HTTP_SERVER_VARS['REMOTE_HOST']) && !$phpAds_config['reverse_lookup'],
			'depends' => '(log_adclicks==true || log_adviews==true) && compact_stats==0'
		),
		array (
			'type'    => 'break',
			'size'	  => 'large'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'log_beacon',
			'text'	  => $strLogBeacon
		)
	)
),
array (
	'text' 	  => $strPreventLogging,
	'items'	  => array (
		array (
			'type' 	  => 'textarea', 
			'name' 	  => 'ignore_hosts',
			'text' 	  => $strIgnoreHosts
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'block_adviews',
			'text' 	  => $strBlockAdViews,
			'size'    => 12,
			'depends' => 'log_adviews==true',
			'check'	  => 'number+',
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'block_adclicks',
			'text' 	  => $strBlockAdClicks,
			'size'    => 12,
			'depends' => 'log_adclicks==true',
			'check'	  => 'number+',
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'block_adconversions',
			'text' 	  => $strBlockAdConversions,
			'size'    => 12,
			'depends' => 'log_adconversions==true',
			'check'	  => 'number+',
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'default_conversion_clickwindow',
			'text' 	  => $strConversionClickWindow,
			'size'    => 12,
			'depends' => 'log_adconversions==true',
			'check'	  => 'number+',
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'default_conversion_viewwindow',
			'text' 	  => $strConversionViewWindow,
			'size'    => 12,
			'depends' => 'log_adconversions==true',
			'check'	  => 'number+',
		)
	)
),
array (
	'text' 	  => $strEmailWarnings,
	'items'	  => array (
		array (
			'type'    => 'checkbox',
			'name'    => 'warn_admin',
			'text'	  => $strWarnAdmin
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'warn_agency',
			'text'	  => $strWarnAgency
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'warn_client',
			'text'	  => $strWarnClient
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'warn_limit',
			'text' 	  => $strWarnLimit,
			'size'    => 12,
			'depends' => 'warn_client==true || warn_admin==true || warn_agency==true',
			//'check'	  => '',
			'req'	  => true
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'textarea', 
			'name' 	  => 'admin_email_headers',
			'text' 	  => $strAdminEmailHeaders
		),
		array (
			'type'    => 'break'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'qmail_patch',
			'text'	  => $strQmailPatch
		)
	)
),
array (
	'text' 	  => $strAutoCleanTables,
	'items'	  => array (
		array (
			'type'    => 'checkbox',
			'name'    => 'auto_clean_tables',
			'text'	  => $strAutoCleanStats
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'auto_clean_tables_interval',
			'text' 	  => $strAutoCleanStatsWeeks,
			'size'    => 25,
			'depends' => 'auto_clean_tables==true',
			'check'	  => 'number+3',
			'req'	  => true
		),
		array (
			'type'    => 'break',
			'size'	  => 'large'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'auto_clean_userlog',
			'text'	  => $strAutoCleanUserlog
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'auto_clean_userlog_interval',
			'text' 	  => $strAutoCleanUserlogWeeks,
			'size'    => 25,
			'depends' => 'auto_clean_userlog==true',
			'check'	  => 'number+3',
			'req'	  => true
		)
	)
));




/*********************************************************/
/* Main code                                             */
/*********************************************************/

phpAds_ShowSettings($settings, $errormessage);



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>