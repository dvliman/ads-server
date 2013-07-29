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
$Id: lib-warnings.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


// Set define to prevent duplicate include
define ('LIBWARNING_INCLUDED', true);


/*********************************************************/
/* Mail warning - preset is reached						 */
/*********************************************************/

function phpAds_warningMail ($campaign)
{
	global $phpAds_config;
	global $strViewsClicksConversionsLow, $strMailHeader, $strWarnClientTxt;
	global $strMailNothingLeft, $strMailFooter;
	
	
	if ($phpAds_config['warn_admin'] || $phpAds_config['warn_client'])
	{
		// Get the client which belongs to this campaign
		$clientresult = phpAds_dbQuery(
			"SELECT *".
			" FROM ".$phpAds_config['tbl_clients'].
			" WHERE clientid=".$campaign['clientid']
		);
		if ($client = phpAds_dbFetchArray($clientresult))
		{
			// Load config from the database
			if (!defined('LIBDBCONFIG_INCLUDED'))
			{
				include (phpAds_path.'/libraries/lib-dbconfig.inc.php');
				phpAds_LoadDbConfig();
			}
			
			
			// Load client language strings
			@include (phpAds_path.'/language/english/default.lang.php');
			if ($client['language'] != '') $phpAds_config['language'] = $client['language'];
			if ($phpAds_config['language'] != 'english' && file_exists(phpAds_path.'/language/'.$phpAds_config['language'].'/default.lang.php'))
				@include (phpAds_path.'/language/'.$phpAds_config['language'].'/default.lang.php');
			
			
			// Build email
			$Subject = $strViewsClicksConversionsLow.": ".$campaign['campaignname'];
			
			$Body    = "$strMailHeader\n";
			$Body 	.= "$strWarnClientTxt\n";
			$Body 	.= "$strMailNothingLeft\n\n";
			$Body   .= "$strMailFooter";
			
			$Body    = str_replace ("{clientname}", $campaign['campaignname'], $Body);
			$Body	 = str_replace ("{contact}", $client["contact"], $Body);
			$Body    = str_replace ("{adminfullname}", $phpAds_config['admin_fullname'], $Body);
			$Body    = str_replace ("{limit}", $phpAds_config['warn_limit'], $Body);
			
			
			// Send email
			if ($phpAds_config['warn_admin'])
				phpAds_sendMail ($phpAds_config['admin_email'], $phpAds_config['admin_fullname'], $Subject, $Body);
			
			if ($phpAds_config['warn_client'] && $client["email"] != '')
			{
				phpAds_sendMail ($client['email'], $client['contact'], $Subject, $Body);
				
				if ($phpAds_config['userlog_email']) 
					phpAds_userlogAdd (phpAds_actionWarningMailed, $campaign['campaignid'], $Subject."\n\n".$Body);
			}
		}
	}
}



/*********************************************************/
/* Mail warning - preset is reached						 */
/*********************************************************/

function phpAds_deactivateMail ($campaign)
{
	global $phpAds_config;
	global $strMailSubjectDeleted, $strMailHeader, $strMailClientDeactivated;
	global $strNoMoreClicks, $strNoMoreViews, $strBeforeActivate, $strAfterExpire;
	global $strBanner, $strMailNothingLeft, $strMailFooter, $strUntitled;
	
	
	$clientresult = phpAds_dbQuery(
		"SELECT *".
		" FROM ".$phpAds_config['tbl_clients'].
		" WHERE clientid=".$campaign['clientid']
	);
	if ($client = phpAds_dbFetchArray($clientresult))
	{
		if ($client["email"] != '' && $client["reportdeactivate"] == 't')
		{
			// Load config from the database
			if (!defined('LIBDBCONFIG_INCLUDED'))
			{
				include (phpAds_path.'/libraries/lib-dbconfig.inc.php');
				phpAds_LoadDbConfig();
			}
			
			
			// Load client language strings
			@include (phpAds_path.'/language/english/default.lang.php');
			if ($client['language'] != '') $phpAds_config['language'] = $client['language'];
			if ($phpAds_config['language'] != 'english' && file_exists(phpAds_path.'/language/'.$phpAds_config['language'].'/default.lang.php'))
				@include (phpAds_path.'/language/'.$phpAds_config['language'].'/default.lang.php');
			
			
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
				" FROM ".$phpAds_config['tbl_banners'].
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
			$Body  = str_replace ("{adminfullname}", $phpAds_config['admin_fullname'], $Body);
			
			
			// Send email
			phpAds_sendMail ($client['email'], $client['contact'], $Subject, $Body);
			
			if ($phpAds_config['userlog_email']) 
				phpAds_userlogAdd (phpAds_actionDeactivationMailed, $campaign['campaignid'], $Subject."\n\n".$Body);
		}
	}
}

?>