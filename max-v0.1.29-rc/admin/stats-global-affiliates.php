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
$Id: stats-global-affiliates.php 3145 2005-05-20 13:15:01Z andrew $
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
    phpAds_PageHeader('2.4');
    phpAds_ShowSections(array('2.1', '2.4', '2.2'));

    // Get the statistics for publishers
    $agencyId = phpAds_isUser(phpAds_Admin) ? '' : phpAds_getAgencyID();
    $aDates = MAX_getDatesByPeriod($period);
    $aStats = MAX_getCachePublisherZoneStatsByAgencyIdDate($agencyId, $aDates['day_begin'], $aDates['day_end'], $listorder, $orderdirection);
    
    // Initialise some parameters
    $pageName = basename($_SERVER['PHP_SELF']);
    $tabindex = 1;
    $publishersHidden = 0;
    
    // Get the icons for all levels (publisher/zone)
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
        
        // Loop through publishers
        if (!empty($aStats['children'])) {
            MAX_sortArray($aStats['children'], $listorder, $orderdirection == 'up');
            foreach($aStats['children'] as $publisherId => $publisher) {
                $publisherViews = phpAds_formatNumber($publisher['views']);
                $publisherClicks = phpAds_formatNumber($publisher['clicks']);
                $publisherConversions = phpAds_formatNumber($publisher['conversions']);
                $publisherCtr = phpAds_buildRatioPercentage($publisher['clicks'], $publisher['views']);
                $publisherSr = phpAds_buildRatioPercentage($publisher['conversions'], $publisher['clicks']);            
                $publisherExpanded = MAX_isExpanded($publisherId, $expand, $aNodes, 'p');
                
                if (!$hideinactive || $publisher['active'] == 't') {
                    $bgcolor = ($i++ % 2 == 0) ? " bgcolor='#F6F6F6'" : '';
                    echo "
            <tr height='25'$bgcolor>
                <td width='30%'>";
                    if (!empty($publisher['children'])) {
                        if ($publisherExpanded)
                            echo "&nbsp;<a href='$pageName?collapse=p$publisherId'><img src='images/triangle-d.gif' align='absmiddle' border='0'></a>&nbsp;";
                        else
                            echo "&nbsp;<a href='$pageName?expand=p$publisherId'><img src='images/$phpAds_TextDirection/triangle-l.gif' align='absmiddle' border='0'></a>&nbsp;";
                    }
                    else
                        echo "&nbsp;<img src='images/spacer.gif' height='16' width='16'>&nbsp;";
                        
                    echo "
                    <img src='{$icons['publisher'][$publisher['active']][$publisher['type']]}' align='absmiddle'>&nbsp;
                    <a href='stats-affiliate-history.php?affiliateid=$publisherId'>{$publisher['name']}</a>
                </td>
                <td align='$phpAds_TextAlignRight'>$publisherId</td>
                <td align='$phpAds_TextAlignRight'>$publisherViews</td>
                <td align='$phpAds_TextAlignRight'>$publisherClicks</td>
                <td align='$phpAds_TextAlignRight'>$publisherCtr</td>
                <td align='$phpAds_TextAlignRight'>$publisherConversions</td>
                <td align='$phpAds_TextAlignRight'>$publisherSr</td>
            </tr>";
    
                    if (!empty($publisher['children']) && $publisherExpanded) {
                        echo "
            <tr height='1'>
                <td$bgcolor><img src='images/spacer.gif' width='1' height='1'></td>
                <td colspan='7' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td>
            </tr>";
                        
                        
                        // Loop through zones
                        MAX_sortArray($publisher['children'], $listorder, $orderdirection == 'up');
                        foreach ($publisher['children'] as $zoneId => $zone) {
                            $zoneViews = phpAds_formatNumber($zone['views']);
                            $zoneClicks = phpAds_formatNumber($zone['clicks']);
                            $zoneConversions = phpAds_formatNumber($zone['conversions']);
                            $zoneCtr = phpAds_buildRatioPercentage($zone['clicks'], $zone['views']);
                            $zoneSr = phpAds_buildRatioPercentage($zone['conversions'], $zone['clicks']);            
                
                            echo "
            <tr height='25'$bgcolor>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <img src='images/spacer.gif' height='16' width='16' align='absmiddle'>&nbsp;
                    <img src='{$icons['zone'][$zone['active']][$zone['type']]}' align='absmiddle'>&nbsp;
                    <a href='stats-zone-history.php?affiliateid=$publisherId&zoneid=$zoneId'>{$zone['name']}</a>
                </td>
                <td align='$phpAds_TextAlignRight'>$zoneId</td>
                <td align='$phpAds_TextAlignRight'>$zoneViews</td>
                <td align='$phpAds_TextAlignRight'>$zoneClicks</td>
                <td align='$phpAds_TextAlignRight'>$zoneCtr</td>
                <td align='$phpAds_TextAlignRight'>$zoneConversions</td>
                <td align='$phpAds_TextAlignRight'>$zoneSr</td>
            </tr>";
                        }
                    }        
                    echo "
                <tr height='1'><td colspan='7' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
                } else {
                    $publishersHidden++;
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
            echo "&nbsp;&nbsp;<img src='images/icon-activate.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?period=$period&amp;hideinactive=0'>$strShowAll</a>&nbsp;&nbsp;|&nbsp;&nbsp;$publishersHidden $strInactivePublishersHidden";
        } else {
            echo "&nbsp;&nbsp;<img src='images/icon-hideinactivate.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?period=$period&amp;hideinactive=1'>$strHideInactivePublishers</a>";
        }
        
        echo "
            </td>
            <td colspan='3' align='$phpAds_TextAlignRight' nowrap><img src='images/triangle-d.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?period=$period&amp;expand=all' accesskey='$keyExpandAll'>$strExpandAll</a>&nbsp;&nbsp;|&nbsp;&nbsp;<img src='images/$phpAds_TextDirection/triangle-l.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?period=$period&amp;expand=none' accesskey='$keyCollapseAll'>$strCollapseAll</a>&nbsp;&nbsp;</td>
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