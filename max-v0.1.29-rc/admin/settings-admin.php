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
$Id: settings-admin.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
include ("lib-settings.inc.php");
include ("lib-languages.inc.php");


// Register input variables
phpAds_registerGlobal ('admin', 'pwold', 'pw', 'pw2', 'admin_fullname', 'admin_email', 'company_name', 'language', 
					   'updates_frequency', 'admin_novice', 'userlog_email', 'userlog_priority', 'userlog_autoclean');


// Security check
phpAds_checkAccess(phpAds_Admin);


$errormessage = array();
$sql = array();

if (isset($HTTP_POST_VARS['submit']) && $HTTP_POST_VARS['submit'] == 'true')
{
	if (isset($admin))
	{
		if (!strlen($admin))
			$errormessage[0][] = $strInvalidUsername;
		elseif (phpAds_dbNumRows(phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_clients']." WHERE LOWER(clientusername) = '".strtolower($admin)."'")))
			$errormessage[0][] = $strDuplicateClientName;
		elseif (phpAds_dbNumRows(phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_affiliates']." WHERE LOWER(username) = '".strtolower($admin)."'")))
			$errormessage[0][] = $strDuplicateClientName;
		else
			phpAds_SettingsWriteAdd('admin', $admin);
	}
	
	if (isset($pwold) && strlen($pwold) ||
		isset($pw) && strlen($pw) ||
		isset($pw2) && strlen($pw2))
	{
		if (md5($pwold) != $phpAds_config['admin_pw'])
			$errormessage[0][] = $strPasswordWrong;
		elseif (!strlen($pw)  || strstr("\\", $pw))
			$errormessage[0][] = $strInvalidPassword;
		elseif (strcmp($pw, $pw2))
			$errormessage[0][] = $strNotSamePasswords;
		else
		{
			$admin_pw = $pw;
			phpAds_SettingsWriteAdd('admin_pw', md5($admin_pw));
		}
	}
	
	if (isset($admin_fullname))
		phpAds_SettingsWriteAdd('admin_fullname', $admin_fullname);
	if (isset($admin_email))
		phpAds_SettingsWriteAdd('admin_email', $admin_email);
	if (isset($company_name))
		phpAds_SettingsWriteAdd('company_name', $company_name);
	
	
	if (isset($language))
		phpAds_SettingsWriteAdd('language', $language);
	if (isset($updates_frequency))
		phpAds_SettingsWriteAdd('updates_frequency', $updates_frequency);
	
	phpAds_SettingsWriteAdd('admin_novice', isset($admin_novice));
	
	
	phpAds_SettingsWriteAdd('userlog_email', isset($userlog_email));
	phpAds_SettingsWriteAdd('userlog_priority', isset($userlog_priority));
	phpAds_SettingsWriteAdd('userlog_autoclean', isset($userlog_autoclean));

	if (!count($errormessage))
	{
		phpAds_SettingsWriteFlush();
			header("Location: settings-interface.php");
			exit;
		
	}
}



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PrepareHelp();
if (isset($message))
	phpAds_ShowMessage($message);
phpAds_PageHeader("5.1");
if (phpAds_isUser(phpAds_Admin))
{
	phpAds_ShowSections(array("5.1", "5.3", "5.4", "5.2","5.5"));
}
elseif (phpAds_isUser(phpAds_Agency))
{
	phpAds_ShowSections(array("5.1"));
}
phpAds_SettingsSelection("admin");



/*********************************************************/
/* Cache settings fields and get help HTML Code          */
/*********************************************************/

$unique_users = array();

$res = phpAds_dbQuery(
	"SELECT LOWER(clientusername) as used".
	" FROM ".$phpAds_config['tbl_clients'].
	" WHERE clientusername != ''"
);

while ($row = phpAds_dbFetchArray($res))
	$unique_users[] = $row['used'];

$res = phpAds_dbQuery("SELECT LOWER(username) as used FROM ".$phpAds_config['tbl_affiliates']." WHERE username != ''");
while ($row = phpAds_dbFetchArray($res))
	$unique_users[] = $row['used'];




$settings = array (

array (
	'text' 	  => $strLoginCredentials,
	'items'	  => array (
		array (
			'type' 	  => 'text', 
			'name' 	  => 'admin',
			'text' 	  => $strAdminUsername,
			'check'	  => 'unique',
			'unique'  => $unique_users
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'password', 
			'name' 	  => 'pwold',
			'text' 	  => $strOldPassword
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'password', 
			'name' 	  => 'pw',
			'text' 	  => $strNewPassword,
			'depends' => 'pwold!=""'
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'password', 
			'name' 	  => 'pw2',
			'text' 	  => $strRepeatPassword,
			'depends' => 'pwold!=""',
			'check'	  => 'compare:pw'
		)
	)
),
array (
	'text' 	  => $strBasicInformation,
	'items'	  => array (
		array (
			'type' 	  => 'text', 
			'name' 	  => 'admin_fullname',
			'text' 	  => $strAdminFullName,
			'size'	  => 35
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'admin_email',
			'text' 	  => $strAdminEmail,
			'size'	  => 35,
			'check'	  => 'email'
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'text', 
			'name' 	  => 'company_name',
			'text' 	  => $strCompanyName,
			'size'	  => 35
		)
	)
),
array (
	'text' 	  => $strPreferences,
	'items'	  => array (
		array (
			'type' 	  => 'select', 
			'name' 	  => 'language',
			'text' 	  => $strLanguage,
			'items'   => phpAds_AvailableLanguages()
		),
		array (
			'type'    => 'break'
		),
		array (
			'type' 	  => 'select', 
			'name' 	  => 'updates_frequency',
			'text' 	  => $strAdminCheckUpdates,
			'items'   => array (
				'0'  => $strAdminCheckEveryLogin,
				'1'  => $strAdminCheckDaily,
				'7'  => $strAdminCheckWeekly,
				'30' => $strAdminCheckMonthly,
				'-1' => $strAdminCheckNever
			)
		),
		array (
			'type'    => 'break'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'admin_novice',
			'text'	  => $strAdminNovice
		),
		array (
			'type'    => 'break'
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'userlog_email',
			'text'	  => $strUserlogEmail
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'userlog_priority',
			'text'	  => $strUserlogPriority
		),
		array (
			'type'    => 'checkbox',
			'name'    => 'userlog_autoclean',
			'text'	  => $strUserlogAutoClean
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