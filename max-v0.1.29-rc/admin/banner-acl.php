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
$Id: banner-acl.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-zones.inc.php");
require ("lib-banner.inc.php");


// Include needed resources
require (phpAds_path."/libraries/resources/res-iso639.inc.php");
require (phpAds_path."/libraries/resources/res-iso3166.inc.php");
require (phpAds_path."/libraries/resources/res-usstates.inc.php");
require (phpAds_path."/libraries/resources/res-useragent.inc.php");
require (phpAds_path."/libraries/resources/res-continent.inc.php");


// Register input variables
phpAds_registerGlobal ('submit', 'action', 'acl', 'type', 'time', 'cap', 'session_capping');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);

if (phpAds_isUser(phpAds_Agency))
{
	$query = "SELECT ".
		$phpAds_config['tbl_banners'].".bannerid as bannerid".
		" FROM ".$phpAds_config['tbl_clients'].
		",".$phpAds_config['tbl_campaigns'].
		",".$phpAds_config['tbl_banners'].
		" WHERE ".$phpAds_config['tbl_campaigns'].".clientid=".$clientid.
		" AND ".$phpAds_config['tbl_banners'].".campaignid=".$campaignid.
		" AND ".$phpAds_config['tbl_banners'].".bannerid=".$bannerid.
		" AND ".$phpAds_config['tbl_banners'].".campaignid=".$phpAds_config['tbl_campaigns'].".campaignid".
		" AND ".$phpAds_config['tbl_campaigns'].".clientid=".$phpAds_config['tbl_clients'].".clientid".
		" AND ".$phpAds_config['tbl_clients'].".agencyid=".phpAds_getUserID();
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("2");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}

// Define variable types
$type_list['weekday']   = $strWeekDay;
$type_list['time']		= $strTime;
$type_list['date']		= $strDate;
$type_list['clientip']	= $strClientIP;
$type_list['domain']	= $strDomain;
$type_list['language']	= $strLanguage;

// Get geotargeting info
if ($phpAds_config['geotracking_type'] != '')
{
	$phpAds_geoPlugin = phpAds_path."/libraries/geotargeting/geo-".$phpAds_config['geotracking_type'].".inc.php";
	
	if (@file_exists($phpAds_geoPlugin))
	{
		@include_once ($phpAds_geoPlugin);
		
		eval ('$'.'info = phpAds_'.$phpAds_geoPluginID.'_getInfo();');
		
		if ($info['country']) $type_list['country']	= $strCountry;
		if ($info['region']) $type_list['region'] = $strUSState;
		if ($info['continent']) $type_list['continent']	= $strContinent;
	}
}

$type_list['browser']   = $strBrowser;
$type_list['os']		= $strOS;
$type_list['useragent']	= $strUserAgent;
$type_list['referer']	= $strReferer;
$type_list['source']	= $strSource;



$comparison_default = array (
	'==' => $strEqualTo,
	'!=' => $strDifferentFrom,
);

$comparison_date = array (
	'==' => $strEqualTo,
	'!=' => $strDifferentFrom,
	'>'  => $strLaterThan,
	'>=' => $strLaterThanOrEqual,
	'<'	 => $strEarlierThan,
	'<=' => $strEarlierThanOrEqual
);

$comparison_referer = array (
	'==' => $strContains,
	'!=' => $strNotContains,
);

$logical_default = array (
	'or'  => $strOR,
	'and' => $strAND
);




/*********************************************************/
/* Process submitted form                                */
/*********************************************************/

if (isset($action))
{
    if (phpAds_isUser(phpAds_Client) && !phpAds_isAllowed(phpAds_ModifyBanner)) {
        phpAds_PageHeader("1");
        phpAds_Die ($strAccessDenied, $strNotAdmin);
    }
    
	if (!isset($acl))
		$acl = array();
	
	if (isset($action['down']))
	{
		// Move limitation down
		$source = key($action['down']);
		$destination = $source + 1;
		
		$tmp = $acl[$source];
		$acl[$source] = $acl[$destination];
		$acl[$destination] = $tmp;
	}
	
	if (isset($action['up']))
	{
		// Move limitation up
		$source = key($action['up']);
		$destination = $source - 1;
		
		$tmp = $acl[$source];
		$acl[$source] = $acl[$destination];
		$acl[$destination] = $tmp;
	}
	
	if (isset($action['del']))
	{
		// Delete limitation
		$first = key($action['del']);
		$last  = count($acl) - 1;
		$tmp   = array();
		
		for ($i=0; $i < $first; $i++)
			$tmp[$i] = $acl[$i];
		
		for ($i=$first; $i < $last; $i++)
			$tmp[$i] = $acl[$i + 1];
		
		$acl = $tmp;
	}
	
	if (isset($action['new']))
	{
		// Create new limitation
		$last = count($acl);
		
		$acl[$last]['logical']    = 'and';
		$acl[$last]['type'] 	  = $type;
		$acl[$last]['comparison'] = '==';
		
		
		if ($type == 'time' || $type == 'weekday' || $type == 'browser' || $type == 'os' ||
			$type == 'country' || $type == 'continent' || $type == 'region' || $type == 'language')
		{
			$acl[$last]['data'] = array();
		}
		elseif ($type == 'date')
		{
			$acl[$last]['data'] = array('day' => '-', 'month' => '-', 'year' => '-');
		}
		else
		{
			$acl[$last]['data'] = '';
		}
	}
}
elseif (isset($submit))
{
    if (phpAds_isUser(phpAds_Client) && !phpAds_isAllowed(phpAds_ModifyBanner)) {
        phpAds_PageHeader("1");
        phpAds_Die ($strAccessDenied, $strNotAdmin);
    }
    
    if ($phpAds_config['acl'])
	{
		
		// First delete existing limitations
		phpAds_dbQuery ("
			DELETE FROM 
				".$phpAds_config['tbl_acls']." 
			WHERE 
				bannerid='".$bannerid."'
		");
		
		
		// Store limitations
		if (isset($acl) && count($acl))
		{
			reset($acl);
			while (list ($key,) = each ($acl))
			{
				if (isset($acl[$key]['data']))
				{
					if ($acl[$key]['type'] == 'time' || $acl[$key]['type'] == 'weekday' || 
						$acl[$key]['type'] == 'country' || $acl[$key]['type'] == 'continent' || 
						$acl[$key]['type'] == 'region')
					{
						$acl[$key]['data'] = implode (',', $acl[$key]['data']);
					}
					elseif ($acl[$key]['type'] == 'language')
					{
						$acl[$key]['data'] = '('.implode (')|(', $acl[$key]['data']).')';
					}
					elseif ($acl[$key]['type'] == 'browser')
					{
						// Collect regular expressions
						$regs = array();
						
						reset ($acl[$key]['data']);
						while (list($k,$v) = each ($acl[$key]['data']))
						{
							reset ($phpAds_Browser);
							while (list($tk,$tv) = each ($phpAds_Browser))
								if ($tk == $v) $regs[] = $tv;
						}
						
						// Use addslashes because these values are not yet slashes by registerGlobal
						$acl[$key]['data'] = addslashes('('.implode (')|(', $regs).')');
					}
					elseif ($acl[$key]['type'] == 'os')
					{
						// Collect regular expressions
						$regs = array();
						
						reset ($acl[$key]['data']);
						while (list($k,$v) = each ($acl[$key]['data']))
						{
							reset ($phpAds_OS);
							while (list($tk,$tv) = each ($phpAds_OS))
								if ($tk == $v) $regs[] = $tv;
						}
						
						// Use addslashes because these values are not yet slashes by registerGlobal
						$acl[$key]['data'] = addslashes('('.implode (')|(', $regs).')');
					}
					elseif ($acl[$key]['type'] == 'date')
					{
						$acl[$key]['data'] = sprintf('%04d%02d%02d', 
							$acl[$key]['data']['year'], $acl[$key]['data']['month'], $acl[$key]['data']['day']
						);
					}
				}
				else
					$acl[$key]['data'] = '';
				
				
				phpAds_dbQuery ("
					INSERT INTO
						".$phpAds_config['tbl_acls']."
					SET
						bannerid  	   = '".$bannerid."',
						logical   	   = '".$acl[$key]['logical']."',
						type  	  	   = '".$acl[$key]['type']."',
						data  	  	   = '".$acl[$key]['data']."',
						comparison     = '".$acl[$key]['comparison']."',
						executionorder = '".$key."'
				");
			}
		}
		
		// Precompile limitation
		phpAds_compileLimitation ($bannerid);
	}
	
	
	// Set time limit
	if (isset($time))
	{
		$block = 0;
		if ($time['second'] != '-') $block += (int)$time['second'];
		if ($time['minute'] != '-') $block += (int)$time['minute'] * 60;
		if ($time['hour'] != '-') 	$block += (int)$time['hour'] * 3600;
	}
	else
		$block = 0;
	
	
	// Set capping and session capping
	if (isset($cap) && $cap != '-')
		$cap = (int)$cap;
	else
		$cap = 0;


	if (isset($session_capping) && $session_capping != '-')
		$session_capping = (int)$session_capping;
	else
		$session_capping = 0;
	
    if (phpAds_isUser(phpAds_Client) && !phpAds_isAllowed(phpAds_ActivateBanner)) {
		    $deactivate_sql = ", active = 'f' ";
	}
		
	$res = phpAds_dbQuery("
		UPDATE
			".$phpAds_config['tbl_banners']."
		SET
			block='".$block."', capping='".$cap."', session_capping='".$session_capping."' $deactivate_sql
		WHERE
			bannerid='".$bannerid."'
	") or phpAds_sqlDie();
	
	
	// Rebuild cache
	if (!defined('LIBVIEWCACHE_INCLUDED')) 
		include (phpAds_path.'/libraries/deliverycache/cache-'.$phpAds_config['delivery_caching'].'.inc.php');
	
	phpAds_cacheDelete();
	
	Header ('Location: banner-append.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid);
	exit;
}



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if (isset($Session['prefs']['campaign-banners.php'][$campaignid]['listorder']))
	$navorder = $Session['prefs']['campaign-banners.php'][$campaignid]['listorder'];
else
	$navorder = '';

if (isset($Session['prefs']['campaign-banners.php'][$campaignid]['orderdirection']))
	$navdirection = $Session['prefs']['campaign-banners.php'][$campaignid]['orderdirection'];
else
	$navdirection = '';

if (phpAds_isUser(phpAds_Client) && !phpAds_isAllowed(phpAds_ModifyBanner)) {
    $input_disable = ' DISABLED ';
}
else { $input_disable = ''; }

// Get other banners
$res = phpAds_dbQuery("
	SELECT
		*
	FROM
		".$phpAds_config['tbl_banners']."
	WHERE
		campaignid = '$campaignid'
	".phpAds_getBannerListOrder($navorder, $navdirection)."
");

while ($row = phpAds_dbFetchArray($res))
{
	phpAds_PageContext (
		phpAds_buildBannerName ($row['bannerid'], $row['description'], $row['alt']),
		"banner-acl.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$row['bannerid'],
		$bannerid == $row['bannerid']
	);
}

if (!phpAds_isUser(phpAds_Client)) {
    phpAds_PageShortcut($strClientProperties, 'advertiser-edit.php?clientid='.$clientid, 'images/icon-advertiser.gif');
    phpAds_PageShortcut($strCampaignProperties, 'campaign-edit.php?clientid='.$clientid.'&campaignid='.$campaignid, 'images/icon-campaign.gif');
}
phpAds_PageShortcut($strBannerHistory, 'stats-banner-history.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid, 'images/icon-statistics.gif');



$extra  = "<form action='banner-modify.php'>";
$extra .= "<input type='hidden' name='clientid' value='$clientid'>";
$extra .= "<input type='hidden' name='campaignid' value='$campaignid'>";
$extra .= "<input type='hidden' name='bannerid' value='$bannerid'>";
$extra .= "<input type='hidden' name='returnurl' value='banner-acl.php'>";
$extra .= "<br><br>";
$extra .= "<b>$strModifyBanner</b><br>";
$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
$extra .= "<img src='images/icon-duplicate-banner.gif' align='absmiddle'>&nbsp;<a href='banner-modify.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$bannerid."&duplicate=true&returnurl=banner-acl.php'>$strDuplicate</a><br>";
$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
$extra .= "<img src='images/icon-move-banner.gif' align='absmiddle'>&nbsp;$strMoveTo<br>";
$extra .= "<img src='images/spacer.gif' height='1' width='160' vspace='2'><br>";
$extra .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$extra .= "<select name='moveto' style='width: 110;'>";

if (phpAds_isUser(phpAds_Admin))
{
	$query = "SELECT campaignid,campaignname".
		" FROM ".$phpAds_config['tbl_campaigns'].
		" WHERE campaignid!=".$campaignid;
} elseif (phpAds_isUser(phpAds_Agency))
{
	$query = "SELECT m.campaignid AS campaignid".
		",m.campaignname AS campaignname".
		" FROM ".$phpAds_config['tbl_campaigns']." AS m".
		",".$phpAds_config['tbl_clients']." AS c".
		" WHERE m.clientid=c.clientid".
		" AND m.campaignid!=".$campaignid.
		" AND c.agencyid=".phpAds_getAgencyID();
} elseif (phpAds_isUser(phpAds_Client)) {
	$query = "SELECT m.campaignid AS campaignid".
		",m.campaignname AS campaignname".
		" FROM ".$phpAds_config['tbl_campaigns']." AS m".
		" WHERE m.clientid=".phpAds_getUserID();    
}
$res = phpAds_dbQuery($query)
	or phpAds_sqlDie();

while ($row = phpAds_dbFetchArray($res))
	$extra .= "<option value='".$row['campaignid']."'>".phpAds_buildName($row['campaignid'], $row['campaignname'])."</option>";

$extra .= "</select>&nbsp;<input type='image' name='moveto' src='images/".$phpAds_TextDirection."/go_blue.gif'><br>";
$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
$extra .= "<img src='images/icon-duplicate-acl.gif' align='absmiddle'>&nbsp;$strApplyLimitationsTo<br>";
$extra .= "<img src='images/spacer.gif' height='1' width='160' vspace='2'><br>";
$extra .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$extra .= "<select name='applyto' style='width: 110;'>";

$res = phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_banners']." WHERE bannerid != '".$bannerid."' AND campaignid = '".$campaignid."'") or phpAds_sqlDie();
while ($row = phpAds_dbFetchArray($res))
	$extra .= "<option value='".$row['bannerid']."'>".phpAds_buildBannerName ($row['bannerid'], $row['description'], $row['alt'])."</option>";

$extra .= "</select>&nbsp;<input type='image' name='applyto' src='images/".$phpAds_TextDirection."/go_blue.gif'><br>";
$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
if (!phpAds_isUser(phpAds_Client)) $extra .= "<img src='images/icon-recycle.gif' align='absmiddle'>&nbsp;<a href='banner-delete.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$bannerid."&returnurl=campaign-banners.php'".phpAds_DelConfirm($strConfirmDeleteBanner).">$strDelete</a><br>";
$extra .= "</form>";

if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency)) {
    phpAds_PageHeader("4.1.3.3.3", $extra);
	echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;".phpAds_getParentClientName($campaignid);
	echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
	echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;".phpAds_getCampaignName($campaignid);
	echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
	echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;<b>".phpAds_getBannerName($bannerid)."</b><br><br>";
	echo phpAds_buildBannerCode($bannerid)."<br><br><br><br>";
	phpAds_ShowSections(array("4.1.3.3.2", "4.1.3.3.3", "4.1.3.3.6", "4.1.3.3.4"));
} elseif (phpAds_isUser(phpAds_Client)) {
    if (phpAds_isAllowed(phpAds_ModifyBanner)) {
        phpAds_PageHeader("2.1.1.2", $extra);
    } else {
        phpAds_PageHeader("2.1.1.2");
    }
	echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;".phpAds_getParentClientName($campaignid);
	echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
	echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;".phpAds_getCampaignName($campaignid);
	echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
	echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;<b>".phpAds_getBannerName($bannerid)."</b><br><br>";
	echo phpAds_buildBannerCode($bannerid)."<br><br><br><br>";
	phpAds_ShowSections(array("2.1.1.1", "2.1.1.2", "2.1.1.3"));
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

if (!isset($acl) && $phpAds_config['acl'])
{
	// Fetch all ACLs from the database
	$res = phpAds_dbQuery("
		SELECT
			*
		FROM
			".$phpAds_config['tbl_acls']."
		WHERE
			bannerid = '".$bannerid."'
		ORDER BY
			executionorder
	") or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray ($res))
	{
		$acl[$row['executionorder']]['logical'] 	= $row['logical'];
		$acl[$row['executionorder']]['type'] 		= $row['type'];
		$acl[$row['executionorder']]['comparison'] 	= $row['comparison'];
		
		// Misc lists
		if ($row['type'] == 'time' || $row['type'] == 'weekday' || 
			$row['type'] == 'country' || $row['type'] == 'continent' || 
			$row['type'] == 'region')
		{
			$acl[$row['executionorder']]['data'] = explode (',', $row['data']);
		}
		
		// Languages
		elseif ($row['type'] == 'language')
		{
			if (ereg("^\(.*\)$", $row['data']))
				$row['data'] = substr($row['data'], 1, strlen($row['data']) - 2);
			
			$acl[$row['executionorder']]['data'] = explode (')|(', $row['data']);
		}
		
		// Browsers
		elseif ($row['type'] == 'browser')
		{
			if (ereg("^\(.*\)$", $row['data']))
				$row['data'] = substr($row['data'], 1, strlen($row['data']) - 2);
			
			$keys = explode (')|(', $row['data']);
			
			while (list($k,$v) = each($keys))
			{
				reset ($phpAds_Browser);
				while (list($tk,$tv) = each ($phpAds_Browser))
					if ($tv == $v) $acl[$row['executionorder']]['data'][] = $tk;
			}
		}
		
		// Operating systems
		elseif ($row['type'] == 'os')
		{
			if (ereg("^\(.*\)$", $row['data']))
				$row['data'] = substr($row['data'], 1, strlen($row['data']) - 2);
			
			$keys = explode (')|(', $row['data']);
			
			while (list($k,$v) = each($keys))
			{
				reset ($phpAds_OS);
				while (list($tk,$tv) = each ($phpAds_OS))
					if ($tv == $v) $acl[$row['executionorder']]['data'][] = $tk;
			}
		}
		
		elseif ($row['type'] == 'date')
		{
			if ($row['data'] == '00000000')
			{
				$acl[$row['executionorder']]['data'] = array(
					'day' => '-',
					'month' => '-',
					'year' => '-'
				);
			}
			else
			{
				$acl[$row['executionorder']]['data'] = array(
					'day'   => substr($row['data'], 6, 2),
					'month' => substr($row['data'], 4, 2),
					'year'  => substr($row['data'], 0, 4)
				);
			}
		}
		
		// Others
		else
			$acl[$row['executionorder']]['data'] = addslashes($row['data']);
	}
}



if (!isset($time) || !isset($cap) || !isset($session_capping))
{
	$res = phpAds_dbQuery("
		SELECT
			block,
			capping,
			session_capping
		FROM
			".$phpAds_config['tbl_banners']."
		WHERE
			bannerid = '".$bannerid."'
	");
	
	if ($row = phpAds_dbFetchArray ($res))
	{
		if (!isset($time))
		{
			$seconds = $row['block'];
			
			$time['hour'] = ($seconds - ($seconds % 3600)) / 3600;
			$seconds = $seconds % 3600;
			
			$time['minute'] = ($seconds - ($seconds % 60)) / 60;
			$seconds = $seconds % 60;
			
			$time['second'] = $seconds;
		}
		
		if (!isset($cap))
			$cap = $row['capping'];

		if (!isset($session_capping))
			$session_capping = $row['session_capping'];

	}
}

if ($time['hour'] == 0 && $time['minute'] == 0 && $time['second'] == 0) $time['second'] = '-'; 
if ($time['hour'] == 0 && $time['minute'] == 0) $time['minute'] = '-'; 
if ($time['hour'] == 0) $time['hour'] = '-';
if ($cap == 0) $cap = '-';
if ($session_capping == 0) $session_capping = '-';

$tabindex = 1;



// Begin form
echo "<form action='banner-acl.php' method='post'>";
echo "<input type='hidden' name='clientid' value='".$clientid."'>";
echo "<input type='hidden' name='campaignid' value='".$campaignid."'>";
echo "<input type='hidden' name='bannerid' value='".$bannerid."'>";

// Workaround: Include dummy submit image to
// prevent first del button to be used when
// pressing enter in a text field
echo "<input type='image' name='dummy' src='images/spacer.gif' border='0' width='1' height='1' $input_disable><br><br>";


if ($phpAds_config['acl'])
{
	echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
	echo "<tr><td height='25' colspan='4' bgcolor='#FFFFFF'><b>".$strDeliveryLimitations."</b></td></tr>";
	echo "<tr><td height='1' colspan='4' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	
	// Display all ACLs
	if (isset($acl) && count($acl))
	{
		echo "<tr><td height='25' colspan='4' bgcolor='#F6F6F6'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$strOnlyDisplayWhen."</td></tr>";
		echo "<tr><td colspan='4'><img src='images/break-el.gif' width='100%' height='1'></td></tr>";
		
		$current_i = 0;
		$previous_i = 0;
		$previous_type = '';
		
		reset($acl);
		while (list ($key,) = each ($acl))
		{
			if ($acl[$key]['logical'] == 'or' && $current_i > 0)
			{
				echo "<tr><td colspan='4'><img src='images/break-el.gif' width='100%' height='1'></td></tr>";
				$previous_i++;
			}
			else
				if ($previous_type != '') echo "<tr><td colspan='4'><img src='images/break-el.gif' width='100%' height='1'></td></tr>";
			
			
			$bgcolor = $previous_i % 2 == 0 ? "#F6F6F6" : "#FFFFFF";
			
			
			echo "<tr height='35' bgcolor='$bgcolor'>";
			echo "<td width='100'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			if ($key == 0)
				echo "<input type='hidden' name='acl[".$key."][logical]' value='".$acl[$key]['logical']."'>&nbsp;";
			else
			{
				echo "<select name='acl[".$key."][logical]' tabindex='".($tabindex++)."' $input_disable>";
				
				reset($logical_default);
				while (list ($logical_type, $logical_name) = each ($logical_default))
				{
					echo "<option value=";
					printf("'%s'%s>", $logical_type, $logical_type == $acl[$key]['logical'] ? ' selected' : '');
					echo $logical_name."\n";
				}
				
				echo "</select>";
			}
			
			echo "</td><td width='130'>";
			echo "<img src='images/icon-acl.gif' align='absmiddle'>&nbsp;".$type_list[$acl[$key]['type']];
			echo "<input type='hidden' name='acl[".$key."][type]' value='".$acl[$key]['type']."'>";
			echo "</td><td >";
			echo "<select name='acl[".$key."][comparison]' tabindex='".($tabindex++)."' $input_disable>";
			
			if ($acl[$key]['type'] == 'date')
				$comparison_list = $comparison_date;
			elseif ($acl[$key]['type'] == 'referer')
				$comparison_list = $comparison_referer;
			else
				$comparison_list = $comparison_default;
			
			reset($comparison_list);
			while (list ($comparison_type, $comparison_name) = each ($comparison_list))
			{
				echo "<option value=";
				printf("'%s'%s>", $comparison_type, $comparison_type == $acl[$key]['comparison'] ? ' selected' : '');
				echo $comparison_name."\n";
			}
			echo "</select></td>";
			
			
			// Show buttons
			echo "<td align='".$phpAds_TextAlignRight."'>";
			echo "<input type='image' name='action[del][".$key."]' src='images/icon-recycle.gif' border='0' align='absmiddle' alt='$strDelete' $input_disable>";
			echo "&nbsp;&nbsp;";
			echo "<img src='images/break-el.gif' width='1' height='35'>";
			echo "&nbsp;&nbsp;";
			
			if ($key && $key < count($acl))
				echo "<input type='image' name='action[up][".$key."]' src='images/triangle-u.gif' border='0' alt='$strUp' align='absmiddle' $input_disable>";
			else
				echo "<img src='images/triangle-u-d.gif' alt='$strUp' align='absmiddle'>";
			
			if ($key < count($acl) - 1)
				echo "<input type='image' name='action[down][".$key."]' src='images/triangle-d.gif' border='0' alt='$strDown' align='absmiddle' $input_disable>";
			else
				echo "<img src='images/triangle-d-d.gif' alt='$strDown' align='absmiddle'>";
			
			echo "&nbsp;&nbsp;</td></tr>";
			echo "<tr bgcolor='$bgcolor'><td>&nbsp;</td><td>&nbsp;</td><td colspan='2'>";
			
			if ($acl[$key]['type'] == 'weekday')
			{
				if (!isset($acl[$key]['data']))
					$acl[$key]['data'] = array();
				
				echo "<table width='275' cellpadding='0' cellspacing='0' border='0'>";
				for ($i = 0; $i < 7; $i++)
				{
					if ($i % 4 == 0) echo "<tr>";
					echo "<td><input type='checkbox' name='acl[".$key."][data][]' value='$i'".(in_array ($i, $acl[$key]['data']) ? ' CHECKED' : '')." tabindex='".($tabindex++)."' $input_disable>&nbsp;".$strDayShortCuts[$i]."&nbsp;&nbsp;</td>";
					if (($i + 1) % 4 == 0) echo "</tr>";
				}
				if (($i + 1) % 4 != 0) echo "</tr>";
				echo "</table>";
			}
			elseif ($acl[$key]['type'] == 'time')
			{
				if (!isset($acl[$key]['data']))
					$acl[$key]['data'] = array();
				
				echo "<table width='275' cellpadding='0' cellspacing='0' border='0'>";
				for ($i = 0; $i < 24; $i++)
				{
					if ($i % 4 == 0) echo "<tr>";
					echo "<td><input type='checkbox' name='acl[".$key."][data][]' value='$i'".(in_array ($i, $acl[$key]['data']) ? ' CHECKED' : '')." tabindex='".($tabindex++)."' $input_disable>&nbsp;".$i.":00&nbsp;&nbsp;</td>";
					if (($i + 1) % 4 == 0) echo "</tr>";
				}
				if (($i + 1) % 4 != 0) echo "</tr>";
				echo "</table>";
			}
			elseif ($acl[$key]['type'] == 'date')
			{
				if (!isset($acl[$key]['data']))
					$acl[$key]['data'] = array('day' => '-', 'month' => '-', 'year' => '-');
				
				
				echo "<select name='acl[".$key."][data][day]' tabindex='".($tabindex++)."' $input_disable>";
				echo "<option value='-'".($acl[$key]['data']['day']=='-' ? ' selected' : '').">-</option>";
				for ($i=1;$i<=31;$i++)
					echo "<option value='$i'".($acl[$key]['data']['day']==$i ? ' selected' : '').">$i</option>";
				echo "</select>&nbsp;";
				
				echo "<select name='acl[".$key."][data][month]' tabindex='".($tabindex++)."' $input_disable>";
				echo "<option value='-'".($acl[$key]['data']['month']=='-' ? ' selected' : '').">-</option>";
				for ($i=1;$i<=12;$i++)
					echo "<option value='$i'".($acl[$key]['data']['month']==$i ? ' selected' : '').">".$strMonth[$i-1]."</option>";
				echo "</select>&nbsp;";
				
				if ($acl[$key]['data']['year'] != '-')
					$s = $acl[$key]['data']['year'] < date('Y') ? $acl[$key]['data']['year'] : date('Y');
				else
					$s = date('Y');
				
				echo "<select name='acl[".$key."][data][year]' tabindex='".($tabindex++)."' $input_disable>";
				echo "<option value='-'".($acl[$key]['data']['year']=='-' ? ' selected' : '').">-</option>";
				for ($i=$s;$i<=($s+4);$i++)
					echo "<option value='$i'".($acl[$key]['data']['year']==$i ? ' selected' : '').">$i</option>";
				echo "</select>\n";
			}
			elseif ($acl[$key]['type'] == 'language')
			{
				if (!isset($acl[$key]['data']))
					$acl[$key]['data'] = array();
				
				echo "<div class='box'>";
				
				while (list($iso,$fullname) = each ($phpAds_ISO639))
				{
					echo "<div class='boxrow' onMouseOver='boxrow_over(this);' onMouseOut='boxrow_leave(this);' onClick='o=findObj(\"check_".$key."_".$iso."\"); o.checked = !o.checked;'>";
					echo "<input onClick='boxrow_nonbubble();' tabindex='".($tabindex++)."' ";
					echo "type='checkbox' id='check_".$key."_".$iso."' name='acl[".$key."][data][]' value='$iso'".(in_array ($iso, $acl[$key]['data']) ? ' checked' : '')." align='middle' $input_disable>".$fullname;
					echo "</div>";
				}
				
				echo "</div>";
			}
			elseif ($acl[$key]['type'] == 'country')
			{
				if (!isset($acl[$key]['data']))
					$acl[$key]['data'] = array();
				
				
				echo "<div class='box'>";
				
				while (list($iso,$fullname) = each ($phpAds_ISO3166))
				{
					echo "<div class='boxrow' onMouseOver='boxrow_over(this);' onMouseOut='boxrow_leave(this);' onClick='o=findObj(\"c_".$key."_".$iso."\"); o.checked = !o.checked;'>";
					echo "<input onClick='boxrow_nonbubble();' tabindex='".($tabindex++)."' ";
					echo "type='checkbox' id='c_".$key."_".$iso."' name='acl[".$key."][data][]' value='$iso'".(in_array ($iso, $acl[$key]['data']) ? ' checked' : '')." $input_disable>".$fullname;
					echo "</div>";
				}
				
				echo "</div>";
			}
			elseif ($acl[$key]['type'] == 'continent')
			{
				if (!isset($acl[$key]['data']))
					$acl[$key]['data'] = array();
				
				echo "<div class='box'>";
				
				while (list($iso,$fullname) = each ($phpAds_cont_name))
				{
					echo "<div class='boxrow' onMouseOver='boxrow_over(this);' onMouseOut='boxrow_leave(this);' onClick='o=findObj(\"check_".$key."_".$iso."\"); o.checked = !o.checked;'>";
					echo "<input onClick='boxrow_nonbubble();' tabindex='".($tabindex++)."' ";
					echo "type='checkbox' id='check_".$key."_".$iso."' name='acl[".$key."][data][]' value='$iso'".(in_array ($iso, $acl[$key]['data']) ? ' checked' : '')." align='middle' $input_disable>".$fullname;
					echo "</div>";
				}
				
				echo "</div>";
			}
			elseif ($acl[$key]['type'] == 'region')
			{
				if (!isset($acl[$key]['data']))
					$acl[$key]['data'] = array();
				
				echo "<div class='box'>";
				
				while (list($postalcode,$fullname) = each ($phpAds_USStates))
				{
					echo "<div class='boxrow' onMouseOver='boxrow_over(this);' onMouseOut='boxrow_leave(this);' onClick='o=findObj(\"check_".$key."_".$postalcode."\"); o.checked = !o.checked;'>";
					echo "<input onClick='boxrow_nonbubble();' tabindex='".($tabindex++)."' ";
					echo "type='checkbox' id='check_".$key."_".$postalcode."' name='acl[".$key."][data][]' value='$postalcode'".(in_array ($postalcode, $acl[$key]['data']) ? ' checked' : '')." align='middle' $input_disable>".$fullname;
					echo "</div>";
				}
				
				echo "</div>";
			}
			elseif ($acl[$key]['type'] == 'browser')
			{
				if (!isset($acl[$key]['data']))
					$acl[$key]['data'] = array();
				
				echo "<table width='350' cellpadding='0' cellspacing='0' border='0'>";
				$i = 0;
				
				reset ($phpAds_Browser);
				while (list($ukey, $uvalue) = each ($phpAds_Browser))
				{
					if ($i % 3 == 0) echo "<tr>";
					echo "<td><input type='checkbox' name='acl[".$key."][data][]' value='".$ukey."'".(in_array ($ukey, $acl[$key]['data']) ? ' CHECKED' : '')." tabindex='".($tabindex++)."' $input_disable>&nbsp;".$ukey."&nbsp;&nbsp;</td>";
					if (($i + 1) % 3 == 0) echo "</tr>";
					$i++;
				}
				if (($i + 1) % 3 != 0) echo "</tr>";
				echo "</table>";
			}
			elseif ($acl[$key]['type'] == 'os')
			{
				if (!isset($acl[$key]['data']))
					$acl[$key]['data'] = array();
				
				echo "<table width='350' cellpadding='0' cellspacing='0' border='0'>";
				$i = 0;
				
				reset ($phpAds_OS);
				while (list($ukey, $uvalue) = each ($phpAds_OS))
				{
					if ($i % 3 == 0) echo "<tr>";
					echo "<td><input type='checkbox' name='acl[".$key."][data][]' value='".$ukey."'".(in_array ($ukey, $acl[$key]['data']) ? ' CHECKED' : '')." tabindex='".($tabindex++)."' $input_disable>&nbsp;".$ukey."&nbsp;&nbsp;</td>";
					if (($i + 1) % 3 == 0) echo "</tr>";
					$i++;
				}
				if (($i + 1) % 3 != 0) echo "</tr>";
				echo "</table>";
			}
			else
				echo "<input type='text' size='40' name='acl[".$key."][data]' value='".phpAds_htmlQuotes(stripslashes(isset($acl[$key]['data']) ? $acl[$key]['data'] : ""))."' tabindex='".($tabindex++)."' $input_disable>";
			
			echo "<br><br></td></tr>";
			
			
			$previous_type = $acl[$key]['type'];
			$current_i++;
		}
		
		// Show Footer
		if (!isset($acl[$key]['type']) || $acl[$key]['type'] != $previous_type && $previous_type != '')
			echo "<tr><td height='1' colspan='4' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	}
	else
	{
		echo "<tr><td height='24' colspan='4' bgcolor='#F6F6F6'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$strNoLimitations."</td></tr>";
		echo "<tr><td height='1' colspan='4' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	}
	
	
	echo "<tr><td height='30' colspan='2'>";
	
	if (isset($acl) && count($acl) > 1 && !phpAds_isUser(phpAds_Client))
	{
		echo "<img src='images/icon-recycle.gif' border='0' align='absmiddle'>&nbsp;";
		echo "<a href='banner-acl.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$bannerid."&action[clear]=true'>";
		echo $strRemoveAllLimitations."</a>&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	
	echo "</td><td height='30' colspan='2' align='".$phpAds_TextAlignRight."'>";
	echo "<img src='images/icon-acl-add.gif' align='absmiddle'>&nbsp;";
	echo "<select name='type' accesskey='".$keyAddNew."' tabindex='".($tabindex++)."' $input_disable>";
	
	reset($type_list);
	while (list ($type_id, $type_name) = each ($type_list))
	{
		echo "<option value=";
		printf("'%s'%s>", $type_id, $type_id == 'weekday' ? ' selected' : '');
		echo $type_name."\n";
	}
	
	echo "</select>";
	echo "&nbsp;";
	echo "<input type='image' name='action[new]' src='images/".$phpAds_TextDirection."/go_blue.gif' border='0' align='absmiddle' alt='$strSave' $input_disable>";
	echo "</td></tr>";
	
	echo "</table>";
	echo "<br><br><br>";
}


echo "<table border='0' width='100%' cellpadding='0' cellspacing='0' bgcolor='#FFFFFF'>";
echo "<tr><td height='25' colspan='3' bgcolor='#FFFFFF'><b>".$strDeliveryCapping."</b></td></tr>";
echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";

echo "<tr><td width='30'>&nbsp;</td>";
echo "<td width='200'>".$strTimeCapping."</td>";
echo "<td valign='top'>";
echo "<input id='timehour' class='flat' type='text' size='3' name='time[hour]' value='".$time['hour']."' onKeyUp=\"phpAds_formLimitUpdate(this);\" tabindex='".($tabindex++)."' $input_disable> ".$strHours." &nbsp;&nbsp;";
echo "<input id='timeminute' class='flat' type='text' size='3' name='time[minute]' value='".$time['minute']."' onKeyUp=\"phpAds_formLimitUpdate(this);\" tabindex='".($tabindex++)."' $input_disable> ".$strMinutes." &nbsp;&nbsp;";
echo "<input id='timesecond' class='flat' type='text' size='3' name='time[second]' value='".$time['second']."' onBlur=\"phpAds_formLimitBlur(this);\" onKeyUp=\"phpAds_formLimitUpdate(this);\" tabindex='".($tabindex++)."' $input_disable> ".$strSeconds." &nbsp;&nbsp;";
echo "</td></tr>";
echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";

echo "<tr><td width='30'>&nbsp;</td>";
echo "<td width='200'>".$strImpressionCapping."</td>";
echo "<td valign='top'>";
echo "<input class='flat' type='text' size='3' name='cap' value='".$cap."' onBlur=\"phpAds_formCapBlur(this);\" tabindex='".($tabindex++)."' $input_disable> ".$strTimes;
echo "</td></tr>";

echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";

echo "<tr><td width='30'>&nbsp;</td>";
echo "<td width='200'>".$strImpressionCappingSession."</td>";
echo "<td valign='top'>";
echo "<input class='flat' type='text' size='3' name='session_capping' value='".$session_capping."' onBlur=\"phpAds_formCapBlur(this);\" tabindex='".($tabindex++)."' $input_disable> ".$strTimes;
echo "</td></tr>";

echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";

echo "</table>";


echo "<br><br><br>";
if (!phpAds_isUser(phpAds_Client) || phpAds_isAllowed(phpAds_ModifyBanner)) {
    $aBanner = MAX_getBannerByBannerId($bannerid);
    if (phpAds_isUser(phpAds_Client) && !phpAds_isAllowed(phpAds_ActivateBanner) && ($aBanner['active'] == 't')) {
        $warn_deactivate = phpAds_DelConfirm($strConfirmDeactivate);
    } else { $warn_deactivate = ''; }
    
    echo "<input type='submit' name='submit' value='$strSaveChanges' tabindex='".($tabindex++)."' $warn_deactivate>";
}
echo "</form><br><br>";



/*********************************************************/
/* Form requirements                                     */
/*********************************************************/

?>

<script language='JavaScript'>
<!--
	
	function phpAds_formCapBlur (i)
	{
		if (i.value == '' || i.value == '0') i.value = '-'
	}
	
	function phpAds_formLimitBlur (i)
	{
		f = i.form;
		
		if (f.timehour.value == '') f.timehour.value = '0';
		if (f.timeminute.value == '') f.timeminute.value = '0';
		if (f.timesecond.value == '') f.timesecond.value = '0';
		
		phpAds_formLimitUpdate (i);
	}
			
	function phpAds_formLimitUpdate (i)
	{
		f = i.form;
		
		// Set -
		if (f.timeminute.value == '-' && f.timehour.value != '-') f.timeminute.value = '0';
		if (f.timesecond.value == '-' && f.timeminute.value != '-') f.timesecond.value = '0';
		
		// Set 0
		if (f.timehour.value == '0') f.timehour.value = '-';
		if (f.timehour.value == '-' && f.timeminute.value == '0') f.timeminute.value = '-';
		if (f.timeminute.value == '-' && f.timesecond.value == '0') f.timesecond.value = '-';
	}
	
//-->
</script>

<?php



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>