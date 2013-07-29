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
$Id: stats-campaign-history.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-expiration.inc.php");

// Register input variables
phpAds_registerGlobal (
	 'hideinactive'
	,'limit'
	,'listorder'
	,'orderdirection'
	,'period'
	,'start'
	,'campaignid'
);
if (isset($Session['prefs']['stats-campaign-history.php']['listorder']) && !isset($listorder))
	$listorder = $Session['prefs']['stats-campaign-history.php']['listorder'];
if (isset($Session['prefs']['stats-campaign-history.php']['orderdirection']) && !isset($orderdirection))
	$orderdirection = $Session['prefs']['stats-campaign-history.php']['orderdirection'];
if (isset($Session['prefs']['stats-campaign-history.php']['hide']) && !isset($hideinactive))
	$hideinactive = $Session['prefs']['stats-campaign-history.php']['hide'];
if (isset($Session['prefs']['stats-campaign-history.php']['limit']) && !isset($limit))
	$limit = $Session['prefs']['stats-campaign-history.php']['limit'];
if (isset($Session['prefs']['stats-campaign-history.php']['period']) && !isset($period))
	$period = $Session['prefs']['stats-campaign-history.php']['period'];
if (isset($Session['prefs']['stats-campaign-history.php']['start']) && !isset($start))
	$start = $Session['prefs']['stats-campaign-history.php']['start'];

// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);

// Check so that user doesnt access page through URL
if (phpAds_isUser(phpAds_Client))
{
	$clientid = phpAds_getUserID();
	
	if (isset($campaignid) && $campaignid != '')
	{
		$query = "SELECT c.clientid".
			" FROM ".$phpAds_config['tbl_clients']." AS c".
			",".$phpAds_config['tbl_campaigns']." AS m".
			" WHERE c.clientid=m.clientid".
			" AND c.clientid=".$clientid.
			" AND m.campaignid=".$campaignid.
			" AND agencyid=".phpAds_getAgencyID();
	}
	else
	{
		$query = "SELECT c.clientid".
			" FROM ".$phpAds_config['tbl_clients']." AS c".
			" WHERE c.clientid=".$clientid.
			" AND agencyid=".phpAds_getAgencyID();
	}
	
	$res = phpAds_dbQuery($query) or phpAds_sqlDie();
	
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("2");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}
elseif (phpAds_isUser(phpAds_Agency))
{
	if (isset($campaignid) && $campaignid != '')
	{
		$query = "SELECT c.clientid".
			" FROM ".$phpAds_config['tbl_clients']." AS c".
			",".$phpAds_config['tbl_campaigns']." AS m".
			" WHERE c.clientid=m.clientid".
			" AND c.clientid=".$clientid.
			" AND m.campaignid=".$campaignid.
			" AND c.agencyid=".phpAds_getUserID();
	}
	else
	{
		$query = "SELECT c.clientid".
			" FROM ".$phpAds_config['tbl_clients']." AS c".
			" WHERE c.clientid=".$clientid.
			" AND c.agencyid=".phpAds_getUserID();
	}
	$res = phpAds_dbQuery($query) or phpAds_sqlDie();
	if (phpAds_dbNumRows($res) == 0)
	{
		phpAds_PageHeader("2");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}

/*********************************************************/
/* HTML framework                                        */
/*********************************************************/


if (phpAds_isUser(phpAds_Client))
{
	if (phpAds_getUserID() == phpAds_getCampaignParentClientID($campaignid)) {
	    //campaign-banners.php?clientid=$clientid&campaignid=$campaignid" => $strBannerOverview
		phpAds_PageShortcut($strBannerOverview, "campaign-banners.php?clientid=$clientid&campaignid=$campaignid", 'images/icon-campaign.gif');
	    $res = phpAds_dbQuery(
			"SELECT *".
			" FROM ".$phpAds_config['tbl_campaigns'].
			" WHERE clientid = ".phpAds_getUserID().
			phpAds_getCampaignListOrder ($navorder, $navdirection)
		) or phpAds_sqlDie();
		
		while ($row = phpAds_dbFetchArray($res))
		{
			phpAds_PageContext (
				phpAds_buildName ($row['campaignid'], $row['campaignname']),
				"stats-campaign-history.php?clientid=".$clientid."&campaignid=".$row['campaignid'],
				$campaignid == $row['campaignid']
			);
			
		}
		
		phpAds_PageHeader("1.2.1");
			echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;<b>".phpAds_getCampaignName($campaignid)."</b><br><br><br>";
			
			if (phpAds_isAllowed(phpAds_ViewTargetingStats)) 
				phpAds_ShowSections(array("1.2.1", "1.2.2", "1.2.3", "1.2.4"));
			else 
				phpAds_ShowSections(array("1.2.1", "1.2.2", "1.2.3"));
			
	}
	else
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}
elseif (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
{
	if (phpAds_isUser(phpAds_Admin))
	{
		$query = "SELECT campaignid,campaignname".
			" FROM ".$phpAds_config['tbl_campaigns'].
			" WHERE clientid=".$clientid.
			phpAds_getCampaignListOrder ($navorder, $navdirection);
	}
	elseif (phpAds_isUser(phpAds_Agency))
	{
		$query = "SELECT m.campaignid,m.campaignname".
			" FROM ".$phpAds_config['tbl_campaigns']." AS m".
			",".$phpAds_config['tbl_clients']." AS c".
			" WHERE m.clientid=c.clientid".
			" AND m.clientid=".$clientid.
			" AND c.agencyid=".phpAds_getUserID().
			phpAds_getCampaignListOrder ($navorder, $navdirection);
	}
	$res = phpAds_dbQuery($query)
		or phpAds_sqlDie();
	
	while ($row = phpAds_dbFetchArray($res))
	{
		phpAds_PageContext (
			phpAds_buildName ($row['campaignid'], $row['campaignname']),
			"stats-campaign-history.php?clientid=".$clientid."&campaignid=".$row['campaignid'],
			$campaignid == $row['campaignid']
		);
	}
	
	phpAds_PageShortcut($strClientProperties, 'advertiser-edit.php?clientid='.$clientid, 'images/icon-advertiser.gif');
	phpAds_PageShortcut($strCampaignProperties, 'campaign-edit.php?clientid='.$clientid.'&campaignid='.$campaignid, 'images/icon-campaign.gif');
	phpAds_PageHeader("2.1.2.1");
		echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;".phpAds_getParentClientName($campaignid);
		echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;<b>".phpAds_getCampaignName($campaignid)."</b><br><br><br>";
		phpAds_ShowSections(array("2.1.2.1", "2.1.2.2", "2.1.2.3", "2.1.2.4"));
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

$idresult = phpAds_dbQuery ("SELECT bannerid".
	" FROM ".$phpAds_config['tbl_banners'].
	" WHERE campaignid=".$campaignid
) or phpAds_sqlDie();

if (phpAds_dbNumRows($idresult) > 0)
{
	while ($row = phpAds_dbFetchArray($idresult))
	{
		$bannerids[] = $phpAds_config['tbl_adstats'].".bannerid=".$row['bannerid'];
	}
	
	$lib_history_where     = "(".implode(' OR ', $bannerids).")";
	$lib_history_params    = array ('clientid' => $clientid, 'campaignid' => $campaignid);
	$lib_history_hourlyurl = "stats-campaign-daily.php";

    //  crude hack to make sure array sort is not included more than once, D. Turner
    //  this function is required in the file included next, lib-history.inc.php, which 
    //  sometimes is executed by other files where the following function is already in the stack
    if (!function_exists('phpAds_sortArray')) {
        function phpAds_sortArray(&$array, $column=0, $ascending=TRUE)
        {
        	
        	for ($i=0; $i<sizeof($array); $i++)
        		if (isset($array[$i]['children']) && is_array($array[$i]['children']))
        			phpAds_sortArray($array[$i]['children'], $column, $ascending);
        	
        	phpAds_qsort($array, $column, $ascending);
        
        }
        
        function phpAds_qsort(&$array, $column=0, $ascending=true, $first=0, $last=0)
        {
        	if ($last == 0)
        		$last = count($array) - 1;
        	
        	if ($last > $first)
        	{
        		$alpha = $first;
        		$omega = $last;
        		$mid = floor(($alpha+$omega)/2);
        		$guess = $array[$mid][$column];
        		
        		while ($alpha <= $omega)
        		{
        			if ($ascending)
        			{
        				while ( ($array[$alpha][$column] < $guess) && ($alpha < $last) )
        					$alpha++;
        				while ( ($array[$omega][$column] > $guess) && ($omega > $first) )
        					$omega--;
        			}
        			else
        			{
        				while ( ($array[$alpha][$column] > $guess) && ($alpha < $last) )
        					$alpha++;
        				while ( ($array[$omega][$column] < $guess) && ($omega > $first) )
        					$omega--;
        			}
        			
        			if ($alpha <= $omega)
        			{
        				$temp = $array[$alpha];
        				$array[$alpha] = $array[$omega];
        				$array[$omega] = $temp;
        
        				$alpha++;
        				$omega--;
        			}
        		}
        		
        		if ($first < $omega)
        			phpAds_qsort($array, $column, $ascending, $first, $omega);
        		if ($alpha < $last)
        			phpAds_qsort($array, $column, $ascending, $alpha, $last);
        	}
        }

    }
	include ("lib-history.inc.php");
}
else
{
	echo "<br><img src='images/info.gif' align='absmiddle'>&nbsp;";
	echo "<b>".$strNoStats."</b>";
	phpAds_ShowBreak();
}

/*********************************************************/
/* Store preferences                                     */
/*********************************************************/
$Session['prefs']['stats-campaign-history.php']['listorder'] 		= $listorder;
$Session['prefs']['stats-campaign-history.php']['orderdirection'] 	= $orderdirection;
$Session['prefs']['stats-campaign-history.php']['hide'] 			= $hideinactive;
$Session['prefs']['stats-campaign-history.php']['limit'] 			= $limit;
$Session['prefs']['stats-campaign-history.php']['start'] 			= $start;
$Session['prefs']['stats-campaign-history.php']['period'] 			= $period;
phpAds_SessionDataStore();


/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>