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
$Id: lib-permissions.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("lib-sessions.inc.php");


// Define usertypes bitwise, so 1, 2, 4, 8, 16, etc.
define ("phpAds_Admin", 1);
define ("phpAds_Client", 2);
define ("phpAds_Affiliate", 4);
define ("phpAds_Agency", 8);


// Define client permissions bitwise, so 1, 2, 4, 8, 16, etc.
define ("phpAds_ModifyInfo", 1);
define ("phpAds_ModifyBanner", 2);
define ("phpAds_AddBanner", 4);
define ("phpAds_DisableBanner", 8);
define ("phpAds_ActivateBanner", 16);
define ("phpAds_ViewTargetingStats", 32);

// Define affiliate permissions bitwise, so 1, 2, 4, 8, 16, etc.
define ("phpAds_LinkBanners", 2);
define ("phpAds_AddZone", 4);
define ("phpAds_DeleteZone", 8);
define ("phpAds_EditZone", 16);


/*********************************************************/
/* Start or continue current session                     */
/*********************************************************/

function phpAds_Start()
{
	global $phpAds_config;
	global $Session;
	global $phpAds_productname;
	
	if (!defined('phpAds_installing'))
		phpAds_SessionDataFetch();
	
	if (!phpAds_isLoggedIn() || phpAds_SuppliedCredentials())
	{
		// Load preliminary language settings
		@include (phpAds_path.'/language/english/default.lang.php');
		if ($phpAds_config['language'] != 'english' && file_exists(phpAds_path.'/language/'.$phpAds_config['language'].'/default.lang.php'))
			@include (phpAds_path.'/language/'.$phpAds_config['language'].'/default.lang.php');
		
		phpAds_SessionDataRegister(phpAds_Login());
	}
	
	// Overwrite certain preset preferences
	if (isset($Session['language']) && $Session['language'] != '' && $Session['language'] != $phpAds_config['language'])
	{
		$phpAds_config['language'] = $Session['language'];
	}
}



/*********************************************************/
/* Stop current session                                  */
/*********************************************************/

function phpAds_Logout()
{
	global $phpAds_config;
	
	phpAds_SessionDataDestroy();
	
	// Return to the login screen
	header ("Location: index.php");
}



/*********************************************************/
/* Check if user has permission to view this page        */
/*********************************************************/

function phpAds_checkAccess ($allowed)
{
	global $Session;
	global $strNotAdmin, $strAccessDenied;
	
	if (!($allowed & $Session['usertype']))
	{
		// No permission to access this page!
		phpAds_PageHeader(0);
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}



/*********************************************************/
/* Check if user is of a certain usertype                */
/*********************************************************/

function phpAds_isUser ($allowed)
{
	global $Session;
	
	if (isset($Session['usertype']))
		return ($allowed & (int)$Session['usertype']);
	else
		return false;
}



/*********************************************************/
/* Check if user has clearance to do a certain task      */
/*********************************************************/

function phpAds_isAllowed ($allowed)
{
	global $Session;
	return ($allowed & (int)$Session['permissions']);
}



/*********************************************************/
/* Get the ID of the current user                        */
/*********************************************************/

function phpAds_getUserID ()
{
	global $Session;
	return ($Session['userid']);
}

/*********************************************************/
/* Get the ID of the current user                        */
/*********************************************************/

function phpAds_getAgencyID ()
{
	global $Session;
	return ($Session['agencyid']);
}



/*********************************************************/
/* Private functions                                     */
/*********************************************************/

function phpAds_Login()
{
	global $phpAds_config;
	global $strPasswordWrong, $strEnableCookies, $strEnterBoth;
	
	global $HTTP_COOKIE_VARS;
	global $HTTP_POST_VARS;
	
	if (phpAds_SuppliedCredentials())
	{
		// Trim spaces from input
		$username  = trim($HTTP_POST_VARS['phpAds_username']);
		$password  = trim($HTTP_POST_VARS['phpAds_password']);
		$md5digest = trim($HTTP_POST_VARS['phpAds_md5']);
		
		// Add slashes to input if needed
		if (!ini_get ('magic_quotes_gpc'))
		{
			$username  = addslashes($username);
			$password  = addslashes($password);
			$md5digest = addslashes($md5digest);
		}
		
		// Convert plain text password to md5 digest
		if ($md5digest == '' && $password != '')
		{
			$md5digest = md5($password);
		}
		
		// Exit if not both username and password are given
		if ($md5digest == '' ||	$md5digest == md5('') || $username  == '')
		{
			$HTTP_COOKIE_VARS['sessionID'] = phpAds_SessionStart();
			phpAds_LoginScreen($strEnterBoth, $HTTP_COOKIE_VARS['sessionID']);
		}
		
		// Exit if cookies are disabled
		if ($HTTP_COOKIE_VARS['sessionID'] != $HTTP_POST_VARS['phpAds_cookiecheck'])
		{
			$HTTP_COOKIE_VARS['sessionID'] = phpAds_SessionStart();
			phpAds_LoginScreen($strEnableCookies, $HTTP_COOKIE_VARS['sessionID']);
		}
		
		
		
		if (phpAds_isAdmin($username, $md5digest))
		{
			// User is Administrator
			return (array ("usertype" 		=> phpAds_Admin,
						   "loggedin" 		=> "t",
						   "agencyid"		=> 0,
						   "username" 		=> $username)
			       );
		}
		else
		{
			// Check agency table
			
			$res = phpAds_dbQuery("
				SELECT
					agencyid,
					permissions,
					language
				FROM
					".$phpAds_config['tbl_agency']."
				WHERE
					username = '".$username."'
					AND password = '".$md5digest."'
			") or phpAds_sqlDie();
			
			
			if (phpAds_dbNumRows($res) > 0)
			{
				// User found with correct password
				$row = phpAds_dbFetchArray($res);
				
				return (array ("usertype" 		=> phpAds_Agency,
							   "loggedin" 		=> "t",
							   "username" 		=> $username,
							   "userid" 		=> $row['agencyid'],
							   "agencyid"		=> $row['agencyid'],
							   "permissions" 	=> $row['permissions'],
							   "language" 		=> $row['language'])
				       );
			}
			else
			{
				// Check client table
				
				$res = phpAds_dbQuery("
					SELECT
						clientid,
						agencyid,
						permissions,
						language
					FROM
						".$phpAds_config['tbl_clients']."
					WHERE
						clientusername = '".$username."'
						AND clientpassword = '".$md5digest."'
				") or phpAds_sqlDie();
				
				
				if (phpAds_dbNumRows($res) > 0)
				{
					// User found with correct password
					$row = phpAds_dbFetchArray($res);
					
					return (array ("usertype" 		=> phpAds_Client,
								   "loggedin" 		=> "t",
								   "username" 		=> $username,
								   "userid" 		=> $row['clientid'],
								   "agencyid"		=> $row['agencyid'],
								   "permissions" 	=> $row['permissions'],
								   "language" 		=> $row['language'])
					       );
				}
				else
				{
					$res = phpAds_dbQuery("
						SELECT
							affiliateid,
							agencyid,
							permissions,
							language
						FROM
							".$phpAds_config['tbl_affiliates']."
						WHERE
							username = '".$username."'
							AND password = '".$md5digest."'
						");
					
					if ($res && phpAds_dbNumRows($res) > 0)
					{
						// User found with correct password
						$row = phpAds_dbFetchArray($res);
						
						return (array ("usertype" 		=> phpAds_Affiliate,
									   "loggedin" 		=> "t",
									   "username" 		=> $username,
									   "userid" 		=> $row['affiliateid'],
									   "agencyid"		=> $row['agencyid'],
									   "permissions" 	=> $row['permissions'],
									   "language" 		=> $row['language'])
						       );
					}
					else
					{
						// Password is not correct or user is not known
						
						// Set the session ID now, some server do not support setting a cookie during a redirect
						$HTTP_COOKIE_VARS['sessionID'] = phpAds_SessionStart();
						phpAds_LoginScreen($strPasswordWrong, $HTTP_COOKIE_VARS['sessionID']);
					}
				}
			}
		}
	}
	else
	{
		// User has not supplied credentials yet
		
		if (defined('phpAds_installing'))
		{
			// We are trying to install, grant access...
			return (array ("usertype" 		=> phpAds_Admin,
						   "loggedin" 		=> "t",
						   "agencyid"		=> 0,
						   "username" 		=> 'admin')
			       );
		}
		
		// Set the session ID now, some server do not support setting a cookie during a redirect
		$HTTP_COOKIE_VARS['sessionID'] = phpAds_SessionStart();
		phpAds_LoginScreen('', $HTTP_COOKIE_VARS['sessionID']);
	}
}


function phpAds_IsLoggedIn()
{
	global $Session;
	return (isset($Session['loggedin']) ? ($Session['loggedin'] == "t") : false);
}

function phpAds_SuppliedCredentials()
{
	global $HTTP_POST_VARS;
	
	return (isset($HTTP_POST_VARS['phpAds_username']) &&
		    isset($HTTP_POST_VARS['phpAds_password']) &&
			isset($HTTP_POST_VARS['phpAds_md5']));
}



function phpAds_isAdmin($username, $md5)
{
	global $phpAds_config;
	
	return (
		($username == $phpAds_config['admin'] && $md5 == $phpAds_config['admin_pw']) ||
		($username == $phpAds_config['admin'] && $md5 == md5($phpAds_config['admin_pw']) && defined('phpAds_updating'))
	);
}



function phpAds_LoginScreen($message='', $sessionID=0)
{
	global $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS;
	global $phpAds_config, $phpAds_productname;
	global $strUsername, $strPassword, $strLogin, $strWelcomeTo, $strEnterUsername, $strNoAdminInteface;
	
	phpAds_PageHeader(phpAds_Login);
	
	if ($phpAds_config['ui_enabled'] == true)
	{
		echo "<br>";
		phpAds_ShowBreak();
		echo "<br>";
		
		echo "<form name='login' method='post' onSubmit='return login_md5(this);' action='".basename($HTTP_SERVER_VARS['PHP_SELF']);
		echo (isset($HTTP_SERVER_VARS['QUERY_STRING']) && $HTTP_SERVER_VARS['QUERY_STRING'] != '' ? '?'.$HTTP_SERVER_VARS['QUERY_STRING'] : '')."'>";
		echo "<input type='hidden' name='phpAds_cookiecheck' value='".$HTTP_COOKIE_VARS['sessionID']."'>";
		echo "<input type='hidden' name='phpAds_md5' value=''>";
		echo "<table width='100%' cellpadding='0' cellspacing='0' border='0'><tr>";
		echo "<td width='80' valign='bottom'><img src='images/login-welcome.gif'>&nbsp;&nbsp;</td>";
		echo "<td width='100%' valign='bottom'>";
		echo "<span class='tab-s'>".$strWelcomeTo." ".(isset($phpAds_config['name']) && $phpAds_config['name'] != '' ? $phpAds_config['name'] : $phpAds_productname)."</span><br>";
		echo "<span class='install'>".$strEnterUsername."</span><br>";
		
		if ($message != "")
		{
			echo "<div class='errormessage' style='width: 400px;'><img class='errormessage' src='images/errormessage.gif' align='absmiddle'>";
			echo "<span class='tab-r'>$message</span></div>";
		}
		else
			echo "<img src='images/break-el.gif' width='400' height='1' vspace='8'>";	
		
		echo "</td></tr><tr><td>&nbsp;</td><td>";
		echo "<table cellpadding='0' cellspacing='0' border='0'>";
		
		echo "<tr height='24'><td>".$strUsername.":&nbsp;</td><td><input class='flat' type='text' name='phpAds_username'></td></tr>";
		echo "<tr height='24'><td>".$strPassword.":&nbsp;</td><td><input class='flat' type='password' name='phpAds_password'></td></tr>";
		echo "<tr height='24'><td>&nbsp;</td><td><input type='submit' value='".$strLogin."'></td></tr>";
		echo "</table>";
		
		echo "</td></tr></table>";
		echo "</form>";
		
		phpAds_ShowBreak();
		
		echo "<script language='JavaScript' src='md5.js'></script>";
		echo "<script language='JavaScript'>";
		?>
<!--
		function login_md5(o) {
			if (o.phpAds_password.value != '')
			{
				o.phpAds_md5.value = MD5(o.phpAds_password.value);
				o.phpAds_password.value = '';
			}
			
			return true;
		}
				
		login_focus();
//-->
		<?php
		echo "</script>";
	}
	else
	{
		phpAds_ShowBreak();
		echo "<br><img src='images/info.gif' align='absmiddle'>&nbsp;";
		echo $strNoAdminInteface;
	}
	
	
	phpAds_PageFooter();
	exit;
}


?>