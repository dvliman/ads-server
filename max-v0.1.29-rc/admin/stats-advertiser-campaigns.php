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
$Id: stats-advertiser-campaigns.php 3145 2005-05-20 13:15:01Z andrew $
*/

    // Include required files
    include_once 'config.php';
    include_once 'lib-statistics.inc.php';
    include_once '../libraries/common.php';
    include_once '../libraries/db.php';
    include_once '../libraries/html.php';
    include_once '../libraries/stats.php';
    
    // Security check
    phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);

    // Get this page name...
    $pageName = basename($_SERVER['PHP_SELF']);

    // Get input variables
    $advertiserId   = MAX_getValue('clientid');
    $period         = MAX_getStoredValue('period', 'today');
    $hideinactive   = MAX_getStoredValue('hideinactive', ($phpAds_config['gui_hide_inactive'] == 't'));
    $listorder      = MAX_getStoredValue('listorder', 'name');
    $orderdirection = MAX_getStoredValue('orderdirection', 'up');
    $aNodes         = MAX_getStoredArray('nodes', array());
    $expand         = MAX_getValue('expand');
    $collapse       = MAX_getValue('collapse');

    // Make sure that the client has access to the advertiser
    if (!MAX_checkAdvertiser($advertiserId)) {
		phpAds_PageHeader('2');
		phpAds_Die ($strAccessDenied, $strNotAdmin);
    }

    // Adjust which nodes are opened closed...
    MAX_adjustNodes($aNodes, $expand, $collapse);

    // TODO:  Rewrite this phpAdsNewCode...
    // Display navigation
    $userId = phpAds_getUserID();
    if (phpAds_isUser(phpAds_Admin)) {
    	$query = "SELECT clientid,clientname FROM {$phpAds_config['tbl_clients']}";
    } elseif (phpAds_isUser(phpAds_Agency)) {
    	$query = "SELECT clientid,clientname FROM {$phpAds_config['tbl_clients']} WHERE agencyid=$userId";
    } elseif (phpAds_isUser(phpAds_Client)) {
    	$query = "SELECT clientid,clientname FROM {$phpAds_config['tbl_clients']} WHERE clientid=$userId";
    }
    $res = phpAds_dbQuery($query)
    	or phpAds_sqlDie();
    	
    while ($row = phpAds_dbFetchArray($res)) {
    	phpAds_PageContext (
    		phpAds_buildName ($row['clientid'], $row['clientname']),
    		"stats-advertiser-campaigns.php?clientid=".$row['clientid'],
    		$clientid == $row['clientid']
    	);
    }
    
    if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency)) {
        phpAds_PageShortcut($strClientProperties, "advertiser-edit.php?clientid=$clientid", 'images/icon-advertiser.gif');
        phpAds_PageHeader('2.1.2');
        $advertiserName = phpAds_getClientName($advertiserId);
        echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;<b>$advertiserName</b><br><br><br>";
        phpAds_ShowSections(array('2.1.1', '2.1.2'));
    } elseif (phpAds_isUser(phpAds_Client)) {
        phpAds_PageShortcut($strCampaignOverview, 'advertiser-campaigns.php?clientid='.$clientid, 'images/icon-campaign.gif');
	    phpAds_PageHeader('1.2');
        if ($phpAds_config['client_welcome']) {
    		echo '<br><br>';
    		// Show welcome message
    		if (!empty($phpAds_client_welcome_msg))
    			echo $phpAds_client_welcome_msg;
    		else
    			include('templates/welcome-advertiser.html');
    		echo '<br><br>';
    	}
    	phpAds_ShowSections(array('1.1', '1.2'));
    }
    
    // Get the statistics for advertisers
    $aDates = MAX_getDatesByPeriod($period);
    $aStats = MAX_getCacheCampaignBannerStatsByAdvertiserIdDate($advertiserId, $aDates['day_begin'], $aDates['day_end'], $listorder, $orderdirection);
    
    // Initialise some parameters
    $tabindex = 1;
    $campaignsHidden = 0;
    
    // Get the icons for all levels (advertiser/campaign/banner)
    $icons = MAX_getEntityIcons();
    
    // Display date filter form
    $entityIds = array('clientid'=>$advertiserId);
    MAX_displayDateSelectionForm($period, $aDates, $pageName, $tabindex, $entityIds);
    
    phpAds_ShowBreak();
    
    if (!empty($aStats['children'])) {
        echo "
        <br><br>
        <table border='0' width='100%' cellpadding='0' cellspacing='0'>";
        MAX_displayStatsHeader($pageName, $listorder, $orderdirection, $entityIds);
        
        // Variable to determine if the row should be grey or white...
        $i=0;
        
        // Loop through advertisers
        if (!empty($aStats['children'])) {
            MAX_sortArray($aStats['children'], $listorder, $orderdirection == 'up');
            foreach($aStats['children'] as $campaignId => $campaign) {
                $campaignViews = phpAds_formatNumber($campaign['views']);
                $campaignClicks = phpAds_formatNumber($campaign['clicks']);
                $campaignConversions = phpAds_formatNumber($campaign['conversions']);
                $campaignCtr = phpAds_buildRatioPercentage($campaign['clicks'], $campaign['views']);
                $campaignSr = phpAds_buildRatioPercentage($campaign['conversions'], $campaign['clicks']);            
                $campaignExpanded = MAX_isExpanded($campaignId, $expand, $aNodes, 'c');
                
                if (!$hideinactive || $campaign['active'] == 't') {
                    $bgcolor = ($i++ % 2 == 0) ? " bgcolor='#F6F6F6'" : '';
                    echo "
            <tr height='25'$bgcolor>
                <td>";
                    if (!empty($campaign['children']) and !phpAds_isUser(phpAds_Client)) {
                        if ($campaignExpanded)
                            echo "&nbsp;<a href='$pageName?clientid=$advertiserId&collapse=c$campaignId'><img src='images/triangle-d.gif' align='absmiddle' border='0'></a>&nbsp;";
                        else
                            echo "&nbsp;<a href='$pageName?clientid=$advertiserId&expand=c$campaignId'><img src='images/$phpAds_TextDirection/triangle-l.gif' align='absmiddle' border='0'></a>&nbsp;";
                    }
                    else
                        echo "&nbsp;<img src='images/spacer.gif' height='16' width='16'>&nbsp;";
                        
                    echo "
                    <img src='{$icons['campaign'][$campaign['active']][$campaign['type']]}' align='absmiddle'>&nbsp;";
                    if (phpAds_isUser(phpAds_Client) and ($campaign['anonymous'] == 't')) { echo "{$campaign['name']}"; }
                    else { echo "<a href='stats-campaign-history.php?clientid=$advertiserId&campaignid=$campaignId'>{$campaign['name']}</a>"; }
                    echo "
                </td>
                <td align='$phpAds_TextAlignRight'>$campaignId</td>
                <td align='$phpAds_TextAlignRight'>$campaignViews</td>
                <td align='$phpAds_TextAlignRight'>$campaignClicks</td>
                <td align='$phpAds_TextAlignRight'>$campaignCtr</td>
                <td align='$phpAds_TextAlignRight'>$campaignConversions</td>
                <td align='$phpAds_TextAlignRight'>$campaignSr</td>
            </tr>";
    
                    if (!empty($campaign['children']) && $campaignExpanded) {
                        echo "
            <tr height='1'>
                <td$bgcolor><img src='images/spacer.gif' width='1' height='1'></td>
                <td colspan='7' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td>
            </tr>";
                        
                        
                        // Loop through campaigns
                        MAX_sortArray($campaign['children'], $listorder, $orderdirection == 'up');
                        foreach ($campaign['children'] as $bannerId => $banner) {
                            $bannerViews = phpAds_formatNumber($banner['views']);
                            $bannerClicks = phpAds_formatNumber($banner['clicks']);
                            $bannerConversions = phpAds_formatNumber($banner['conversions']);
                            $bannerCtr = phpAds_buildRatioPercentage($banner['clicks'], $banner['views']);
                            $bannerSr = phpAds_buildRatioPercentage($banner['conversions'], $banner['clicks']);            
                            echo "
            <tr height='25'$bgcolor>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <img src='images/spacer.gif' height='16' width='16' align='absmiddle'>&nbsp;
                    <img src='{$icons['banner'][$banner['active']][$banner['type']]}' align='absmiddle'>&nbsp;
                    <a href='stats-banner-history.php?clientid=$advertiserId&campaignid=$campaignId&bannerid=$bannerId'>{$banner['name']}</a>
                </td>
                <td align='$phpAds_TextAlignRight'>$bannerId</td>
                <td align='$phpAds_TextAlignRight'>$bannerViews</td>
                <td align='$phpAds_TextAlignRight'>$bannerClicks</td>
                <td align='$phpAds_TextAlignRight'>$bannerCtr</td>
                <td align='$phpAds_TextAlignRight'>$bannerConversions</td>
                <td align='$phpAds_TextAlignRight'>$bannerSr</td>
            </tr>";
                        }
                    }        
                    echo "
                <tr height='1'><td colspan='7' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
                } else {
                    $campaignsHidden++;
                }
            }
        }
        
        // Total
        echo "
        <tr height='25'$bgcolor>
            <td>&nbsp;&nbsp;<b>$strTotal</b></td>
            <td>&nbsp;</td>
            <td align='$phpAds_TextAlignRight'>".phpAds_formatNumber($aStats['views'])."</td>
            <td align='$phpAds_TextAlignRight'>".phpAds_formatNumber($aStats['clicks'])."</td>
            <td align='$phpAds_TextAlignRight'>".phpAds_buildCTR($aStats['views'], $aStats['clicks'])."</td>
            <td align='$phpAds_TextAlignRight'>".phpAds_formatNumber($aStats['conversions'])."</td>
            <td align='$phpAds_TextAlignRight'>".phpAds_buildCTR($aStats['clicks'], $aStats['conversions'])."</td>
        </tr>
        <tr height='1'>
            <td colspan='7' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td>
        </tr>
        <tr>
            <td colspan='4' align='$phpAds_TextAlignLeft' nowrap>";
        
        if ($hideinactive == true) {
            echo "&nbsp;&nbsp;<img src='images/icon-activate.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?clientid=$advertiserId&period=$period&amp;hideinactive=0'>$strShowAll</a>&nbsp;&nbsp;|&nbsp;&nbsp;$campaignsHidden $strInactiveCampaignsHidden";
        } else {
            echo "&nbsp;&nbsp;<img src='images/icon-hideinactivate.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?clientid=$advertiserId&period=$period&amp;hideinactive=1'>$strHideInactiveCampaigns</a>";
        }
        
        echo "
            </td>";
        if (!phpAds_isUser(phpAds_Client)) { echo "<td colspan='3' align='$phpAds_TextAlignRight' nowrap><img src='images/triangle-d.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?clientid=$advertiserId&period=$period&amp;expand=all' accesskey='$keyExpandAll'>$strExpandAll</a>&nbsp;&nbsp;|&nbsp;&nbsp;<img src='images/$phpAds_TextDirection/triangle-l.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?clientid=$advertiserId&period=$period&amp;expand=none' accesskey='$keyCollapseAll'>$strCollapseAll</a>&nbsp;&nbsp;</td>"; }
        echo "
        </tr>
        </table>
        <br><br>";
    } else {
        MAX_displayNoStatsMessage();
    }
    
    // Store preferences
    $Session['prefs'][$pageName]['hideinactive'] = $hideinactive;
    $Session['prefs'][$pageName]['listorder'] = $listorder;
    $Session['prefs'][$pageName]['nodes'] = implode (",", $aNodes);
    $Session['prefs'][$pageName]['orderdirection'] = $orderdirection;
    $Session['prefs'][$pageName]['period'] = $period;
    phpAds_SessionDataStore();
    
    // Display page footer
    phpAds_PageFooter();
    

?>
