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
$Id: stats-global-advertiser.php 3145 2005-05-20 13:15:01Z andrew $
*/

    // Include required files
    include_once 'config.php';
    include_once 'lib-statistics.inc.php';
    include_once '../libraries/common.php';
    include_once '../libraries/db.php';
    include_once '../libraries/html.php';
    include_once '../libraries/stats.php';
    
    // Security check
    phpAds_checkAccess(phpAds_Admin + phpAds_Agency);

    // Get input variables
    $period         = MAX_getStoredValue('period', 'today');
    $hideinactive   = MAX_getStoredValue('hideinactive', ($phpAds_config['gui_hide_inactive'] == 't'));
    $listorder      = MAX_getStoredValue('listorder', 'name');
    $orderdirection = MAX_getStoredValue('orderdirection', 'up');
    $aNodes         = MAX_getStoredArray('nodes', array());
    $expand         = MAX_getValue('expand', '');
    $collapse       = MAX_getValue('collapse');

    // Adjust which nodes are opened closed...
    MAX_adjustNodes($aNodes, $expand, $collapse);
    
    // Display navigation
    phpAds_PageHeader('2.1');
    phpAds_ShowSections(array('2.1', '2.4', '2.2'));

    // Get the statistics for advertisers
    $agencyId = phpAds_isUser(phpAds_Admin) ? '' : phpAds_getAgencyID();
    $aDates = MAX_getDatesByPeriod($period);
    $aStats = MAX_getCacheAdvertiserCampaignBannerStatsByAgencyIdDate($agencyId, $aDates['day_begin'], $aDates['day_end'], $listorder, $orderdirection);
    
    // Initialise some parameters
    $pageName = basename($_SERVER['PHP_SELF']);
    $tabindex = 1;
    $advertisersHidden = 0;
    
    // Get the icons for all levels (advertiser/campaign/banner)
    $icons = MAX_getEntityIcons();
    
    // Display date filter form
    MAX_displayDateSelectionForm($period, $aDates, $pageName, $tabindex);
    
    phpAds_ShowBreak();
    
    if (!empty($aStats['children'])) {
        echo "
        <br><br>
        <table border='0' width='100%' cellpadding='0' cellspacing='0'>";
        MAX_displayStatsHeader($pageName, $listorder, $orderdirection);
        
        // Variable to determine if the row should be grey or white...
        $i=0;
        
        // Loop through advertisers
        if (!empty($aStats['children'])) {
            MAX_sortArray($aStats['children'], $listorder, $orderdirection == 'up');
            foreach($aStats['children'] as $advertiserId => $advertiser) {
                $advertiserViews = phpAds_formatNumber($advertiser['views']);
                $advertiserClicks = phpAds_formatNumber($advertiser['clicks']);
                $advertiserConversions = phpAds_formatNumber($advertiser['conversions']);
                $advertiserCtr = phpAds_buildRatioPercentage($advertiser['clicks'], $advertiser['views']);
                $advertiserSr = phpAds_buildRatioPercentage($advertiser['conversions'], $advertiser['clicks']);            
                $advertiserExpanded = MAX_isExpanded($advertiserId, $expand, $aNodes, 'a');
                
                if (!$hideinactive || $advertiser['active'] == 't') {
                    $bgcolor = ($i++ % 2 == 0) ? " bgcolor='#F6F6F6'" : '';
                    echo "
            <tr height='25'$bgcolor>
                <td>";
                    if (!empty($advertiser['children'])) {
                        if ($advertiserExpanded)
                            echo "&nbsp;<a href='$pageName?collapse=a$advertiserId'><img src='images/triangle-d.gif' align='absmiddle' border='0'></a>&nbsp;";
                        else
                            echo "&nbsp;<a href='$pageName?expand=a$advertiserId'><img src='images/$phpAds_TextDirection/triangle-l.gif' align='absmiddle' border='0'></a>&nbsp;";
                    }
                    else
                        echo "&nbsp;<img src='images/spacer.gif' height='16' width='16'>&nbsp;";
                        
                    echo "
                    <img src='{$icons['advertiser'][$advertiser['active']][$advertiser['type']]}' align='absmiddle'>&nbsp;
                    <a href='stats-advertiser-history.php?clientid=$advertiserId'>{$advertiser['name']}</a>
                </td>
                <td align='$phpAds_TextAlignRight'>$advertiserId</td>
                <td align='$phpAds_TextAlignRight'>$advertiserViews</td>
                <td align='$phpAds_TextAlignRight'>$advertiserClicks</td>
                <td align='$phpAds_TextAlignRight'>$advertiserCtr</td>
                <td align='$phpAds_TextAlignRight'>$advertiserConversions</td>
                <td align='$phpAds_TextAlignRight'>$advertiserSr</td>
            </tr>";
    
                    if (!empty($advertiser['children']) && $advertiserExpanded) {
                        echo "
            <tr height='1'>
                <td$bgcolor><img src='images/spacer.gif' width='1' height='1'></td>
                <td colspan='7' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td>
            </tr>";
                        
                        
                        // Loop through campaigns
                        MAX_sortArray($advertiser['children'], $listorder, $orderdirection == 'up');
                        foreach ($advertiser['children'] as $campaignId => $campaign) {
                            $campaignViews = phpAds_formatNumber($campaign['views']);
                            $campaignClicks = phpAds_formatNumber($campaign['clicks']);
                            $campaignConversions = phpAds_formatNumber($campaign['conversions']);
                            $campaignCtr = phpAds_buildRatioPercentage($campaign['clicks'], $campaign['views']);
                            $campaignSr = phpAds_buildRatioPercentage($campaign['conversions'], $campaign['clicks']);            
                            $campaignExpanded = MAX_isExpanded($campaignId, $expand, $aNodes, 'c');
                
                            echo "
            <tr height='25'$bgcolor>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                            if (!empty($campaign['children'])) {
                                if ($campaignExpanded)
                                    echo "
                    <a href='$pageName?collapse=c$campaignId'><img src='images/triangle-d.gif' align='absmiddle' border='0'></a>&nbsp;";
                                else
                                    echo "
                    <a href='$pageName?expand=c$campaignId'><img src='images/$phpAds_TextDirection/triangle-l.gif' align='absmiddle' border='0'></a>&nbsp;";
                            }
                            else
                                echo "
                    <img src='images/spacer.gif' height='16' width='16' align='absmiddle'>&nbsp;";
                            
                            
                            echo "
                    <img src='{$icons['campaign'][$campaign['active']][$campaign['type']]}' align='absmiddle'>&nbsp;";
                            
                            echo "
                    <a href='stats-campaign-history.php?clientid=$advertiserId&campaignid=$campaignId'>{$campaign['name']}</a>
                </td>
                <td align='$phpAds_TextAlignRight'>$campaignId</td>
                <td align='$phpAds_TextAlignRight'>$campaignViews</td>
                <td align='$phpAds_TextAlignRight'>$campaignClicks</td>
                <td align='$phpAds_TextAlignRight'>$campaignCtr</td>
                <td align='$phpAds_TextAlignRight'>$campaignConversions</td>
                <td align='$phpAds_TextAlignRight'>$campaignSr</td>
            </tr>";
                            if (!empty($campaign['children']) && $campaignExpanded) {
                                
                                
                                // Loop through banners
                                MAX_sortArray($campaign['children'], $listorder, $orderdirection == 'up');
                                foreach ($campaign['children'] as $bannerId => $banner) {
                                    $bannerViews = phpAds_formatNumber($banner['views']);
                                    $bannerClicks = phpAds_formatNumber($banner['clicks']);
                                    $bannerConversions = phpAds_formatNumber($banner['conversions']);
                                    $bannerCtr = phpAds_buildRatioPercentage($banner['clicks'], $banner['views']);
                                    $bannerSr = phpAds_buildRatioPercentage($banner['conversions'], $banner['clicks']);            
                                    
                                    echo "
            <tr height='1'>
                <td$bgcolor><img src='images/spacer.gif' width='1' height='1'></td>
                <td colspan='6' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td>
            </tr>
            <tr height='25'$bgcolor>
                <td height='25'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                                    $bannerActive = ($banner['active'] == 't' && $campaign['active'] == 't') ? 't' : 'f';
                                    $bannerType = ($banner['type'] == 'html' || $banner['type'] == 'url' || $banner['type'] == 'txt') ? $banner['type'] : '';
                                    echo "<img src='{$icons['banner'][$bannerActive][$bannerType]}' align='absmiddle'>&nbsp;";
                                    echo "&nbsp;<a href='stats-banner-history.php?clientid=$advertiserId&campaignid=$campaignId&bannerid=$bannerId'>{$banner['name']}</a></td>
                <td align='$phpAds_TextAlignRight'>$bannerId</td>
                <td align='$phpAds_TextAlignRight'>$bannerViews</td>
                <td align='$phpAds_TextAlignRight'>$bannerClicks</td>
                <td align='$phpAds_TextAlignRight'>$bannerCtr</td>
                <td align='$phpAds_TextAlignRight'>$bannerConversions</td>
                <td align='$phpAds_TextAlignRight'>$bannerSr</td>
            </tr>";
                                }
                            }
                        }
                    }        
                    echo "
                <tr height='1'><td colspan='7' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
                } else {
                    $advertisersHidden++;
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
            echo "&nbsp;&nbsp;<img src='images/icon-activate.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?period=$period&amp;hideinactive=0'>$strShowAll</a>&nbsp;&nbsp;|&nbsp;&nbsp;$advertisersHidden $strInactiveAdvertisersHidden";
        } else {
            echo "&nbsp;&nbsp;<img src='images/icon-hideinactivate.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?period=$period&amp;hideinactive=1'>$strHideInactiveAdvertisers</a>";
        }
        
        echo "
            </td>
            <td colspan='3' align='$phpAds_TextAlignRight' nowrap><img src='images/triangle-d.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?period=$period&amp;expand=all' accesskey='$keyExpandAll'>$strExpandAll</a>&nbsp;&nbsp;|&nbsp;&nbsp;<img src='images/$phpAds_TextDirection/triangle-l.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?period=$period&amp;expand=none' accesskey='$keyCollapseAll'>$strCollapseAll</a>&nbsp;&nbsp;</td>
        </tr>
        </table>
        <br><br>";
    } else {
        echo "
        <br><br><div class='errormessage'><img class='errormessage' src='images/info.gif' width='16' height='16' border='0' align='absmiddle'>$strNoStats</div>";
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