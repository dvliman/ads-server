<?php

/*
+---------------------------------------------------------------------------+
| Max Media Manager v0.1                                                    |
| =================                                                         |
|                                                                           |
| Copyright (c) 2003-2005 m3 Media Services Limited                         |
| For contact details, see: http://www.m3.net/                              |
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
$Id: html.php 375 2004-06-14 13:24:39Z scott $
*/

    function MAX_displayStatsHeader($pageName, $listorder, $orderdirection, $entityIds=null, $anonymous=false)
    {
        global $phpAds_TextAlignRight;
        $column1 = _getHtmlStatsHeaderColumn($GLOBALS['strName'], 'name', $pageName, $entityIds, $listorder, $orderdirection);
        $column2 = _getHtmlStatsHeaderColumn($GLOBALS['strID'], 'id', $pageName, $entityIds, $listorder, $orderdirection, ($anonymous == false));
        $column3 = _getHtmlStatsHeaderColumn($GLOBALS['strViews'], 'views', $pageName, $entityIds, $listorder, $orderdirection);
        $column4 = _getHtmlStatsHeaderColumn($GLOBALS['strClicks'], 'clicks', $pageName, $entityIds, $listorder, $orderdirection);
        $column5 = _getHtmlStatsHeaderColumn($GLOBALS['strCTRShort'], 'ctr', $pageName, $entityIds, $listorder, $orderdirection);
        $column6 = _getHtmlStatsHeaderColumn($GLOBALS['strConversions'], 'conversions', $pageName, $entityIds, $listorder, $orderdirection);
        $column7 = _getHtmlStatsHeaderColumn($GLOBALS['strCNVRShort'], 'cnvr', $pageName, $entityIds, $listorder, $orderdirection);
        echo "
        <tr height='1'>
            <td><img src='images/spacer.gif' width='200' height='1' border='0' alt='' title=''></td>
            <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
            <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
            <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
            <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
            <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
            <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
        </tr>
        <tr height='25'>
            <td width='30%'>$column1</td>
            <td align='$phpAds_TextAlignRight'>$column2</td>
            <td align='$phpAds_TextAlignRight'>$column3</td>
            <td align='$phpAds_TextAlignRight'>$column4</td>
            <td align='$phpAds_TextAlignRight'>$column5</td>
            <td align='$phpAds_TextAlignRight'>$column6</td>
            <td align='$phpAds_TextAlignRight'>$column7</td>
        </tr>
        <tr height='1'><td colspan='7' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    }
    function MAX_displayStatsHistoryHeader($pageName, $listorder, $orderdirection, $entityIds=null)
    {
        global $phpAds_TextAlignRight;
        $column1 = _getHtmlStatsHeaderColumn($GLOBALS['strDays'], 'name', $pageName, $entityIds, $listorder, $orderdirection);
        $column2 = _getHtmlStatsHeaderColumn($GLOBALS['strViews'], 'views', $pageName, $entityIds, $listorder, $orderdirection);
        $column3 = _getHtmlStatsHeaderColumn($GLOBALS['strClicks'], 'clicks', $pageName, $entityIds, $listorder, $orderdirection);
        $column4 = _getHtmlStatsHeaderColumn($GLOBALS['strCTRShort'], 'ctr', $pageName, $entityIds, $listorder, $orderdirection);
        $column5 = _getHtmlStatsHeaderColumn($GLOBALS['strConversions'], 'conversions', $pageName, $entityIds, $listorder, $orderdirection);
        $column6 = _getHtmlStatsHeaderColumn($GLOBALS['strCNVRShort'], 'cnvr', $pageName, $entityIds, $listorder, $orderdirection);
        echo "
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
            <tr height='1'>
                <td><img src='images/spacer.gif' width='200' height='1' border='0' alt='' title=''></td>
                <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
                <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
                <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
                <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
                <td><img src='images/spacer.gif' width='80' height='1' border='0' alt='' title=''></td>
            </tr>
            <tr height='25'>
                <td width='30%'>$column1</td>
                <td align='$phpAds_TextAlignRight'>$column2</td>
                <td align='$phpAds_TextAlignRight'>$column3</td>
                <td align='$phpAds_TextAlignRight'>$column4</td>
                <td align='$phpAds_TextAlignRight'>$column5</td>
                <td align='$phpAds_TextAlignRight'>$column6</td>
            </tr>
            <tr height='1'><td colspan='7' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>
        ";
    }
    
    function MAX_displayNoStatsMessage()
    {
        echo "
        <br><br><div class='errormessage'><img class='errormessage' src='images/info.gif' width='16' height='16' border='0' align='absmiddle'>{$GLOBALS['strNoStats']}</div>";
    }
    function _getHtmlStatsHeaderColumn($title, $name, $pageName, $entityIds, $listorder, $orderdirection, $showColumn = true)
    {
        $str = '';
        $entity = _getEntityString($entityIds);
        if ($listorder == $name) {
            if (($orderdirection == '') || ($orderdirection == 'down')) {
                $str = "<a href='$pageName?{$entity}orderdirection=up'><img src='images/caret-ds.gif' border='0' alt='' title=''></a>";
            } else {
                $str = "<a href='$pageName?{$entity}orderdirection=down'><img src='images/caret-u.gif' border='0' alt='' title=''></a>";
            }
        }
        return $showColumn ? "<b><a href='$pageName?{$entity}listorder=$name'>$title</a>$str</b>" : '';
    }
    
    function _getEntityString($entityIds)
    {
        $entity = '';
        if (!empty($entityIds)) {
            $entityArr = array();
            foreach ($entityIds as $entityId => $entityValue) {
                $entityArr[] = "$entityId=$entityValue";
            }
            $entity = implode('&',$entityArr) . '&';
        }
        
        return $entity;
    }
    
    function MAX_displayDateSelectionForm($period, $aDates, $pageName, &$tabindex, $hiddenValues = null, $selectName = 'period')
    {
        $year = date('Y');
        
        
        echo "
        <form action='$pageName'>
        <select name='".$selectName."' onChange='this.form.submit();' tabindex='" . $tabindex++ . "'>
            <option value='today'".($period == 'today' ? ' selected' : '').">{$GLOBALS['strCollectedToday']}</option>
            <option value='yesterday'".($period == 'yesterday' ? ' selected' : '').">{$GLOBALS['strCollectedYesterday']}</option>
            <option value='last7days'".($period == 'last7days' ? ' selected' : '').">{$GLOBALS['strCollectedLast7Days']}</option>
            <option value='thisweek'".($period == 'thisweek' ? ' selected' : '').">{$GLOBALS['strCollectedThisWeek']}</option>
            <option value='lastweek'".($period == 'lastweek' ? ' selected' : '').">{$GLOBALS['strCollectedLastWeek']}</option>
            <option value='thismonth'".($period == 'thismonth' ? ' selected' : '').">{$GLOBALS['strCollectedThisMonth']}</option>
            <option value='lastmonth'".($period == 'lastmonth' ? ' selected' : '').">{$GLOBALS['strCollectedLastMonth']}</option>
            <option value='allstats'".($period == 'allstats' ? ' selected' : '').">{$GLOBALS['strCollectedAllStats']}</option>
        </select>";
        _displayHiddenValues($hiddenValues);
        echo "
        &nbsp;&nbsp;&nbsp;&nbsp;{$aDates['day_begin']} - {$aDates['day_end']}
        </form>
        ";
    }
    function _displayHiddenValues($hiddenValues)
    {
        if (!empty($hiddenValues) && is_array($hiddenValues)) {
            foreach ($hiddenValues as $name => $value) {
                echo "
        <input type='hidden' name='$name' value='$value'>";
            }
        }
    }
    function MAX_displayPeriodSelectionForm($period, $pageName, &$tabindex, $hiddenValues = null)
    {
        global $phpAds_TextDirection;
        
        echo "
        <form action='$pageName'>
        <select name='period' onChange='this.form.submit();' tabindex='". $tabindex++ ."'>
            <option value='daily'".($period == 'daily' ? ' selected' : '').">{$GLOBALS['strDailyHistory']}</option>
            <option value='w'".($period == 'weekly' ? ' selected' : '').">{$GLOBALS['strWeeklyHistory']}</option>
            <option value='m'".($period == 'monthly' ? ' selected' : '').">{$GLOBALS['strMonthlyHistory']}</option>
        </select>
        &nbsp;&nbsp;
        <input type='image' src='images/$phpAds_TextDirection/go_blue.gif' border='0' name='submit'>
        &nbsp;";
        _displayHiddenValues($hiddenValues);
        echo "
        </form>
        ";
    }
    function MAX_displayHistoryStatsDaily($aHistoryStats, $aTotalHistoryStats, $pageName, $hiddenValues = null)
    {
        $i = 0;
        $entity = _getEntityString($hiddenValues);
        foreach ($aHistoryStats as $day => $stats) {
            $bgColor = ($i++ % 2 == 0) ? '#F6F6F6' : '#FFFFFF';
            $views = phpAds_formatNumber($stats['views']);
            $clicks = phpAds_formatNumber($stats['clicks']);
            $conversions = phpAds_formatNumber($stats['conversions']);
            $ctr = phpAds_buildRatioPercentage($stats['clicks'], $stats['views']);
            $cnvr = phpAds_buildRatioPercentage($stats['conversions'], $stats['clicks']);
            echo "
            <tr height='25' bgcolor='$bgColor'>
                <td>&nbsp;<img src='images/icon-date.gif' align='absmiddle' alt=''>&nbsp;<a href='$pageName?{$entity}'>$day</a></td>
                <td align='right'>$views</td>
                <td align='right'>$clicks</td>
                <td align='right'>$ctr</td>
                <td align='right'>$conversions</td>
                <td align='right'>$cnvr</td>
            </tr>
            <tr><td height='1' colspan='6' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>
            ";
        }
        echo "
        </table>";
    }  
    
    function MAX_displayPublisherZoneStats($aStats, $pageName, $anonymous, $aNodes, $expand, $listorder, $orderdirection, $hideinactive, $showPublisher, $entityIds)
    {
        global $phpAds_TextAlignLeft, $phpAds_TextAlignRight, $phpAds_TextDirection;
        
        // Get the icons for all levels (publisher/zone)
        $icons = MAX_getEntityIcons();
        $entity = _getEntityString($entityIds);
        $publishersHidden = 0;
        
        if (!empty($aStats['children'])) {
            echo "
            <br><br>
            <table border='0' width='100%' cellpadding='0' cellspacing='0'>";
            MAX_displayStatsHeader($pageName, $listorder, $orderdirection, $entityIds);
            
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
                    <td>";
                        if (!empty($publisher['children'])) {
                            if ($publisherExpanded)
                                echo "&nbsp;<a href='$pageName?{$entity}collapse=p$publisherId'><img src='images/triangle-d.gif' align='absmiddle' border='0'></a>&nbsp;";
                            else
                                echo "&nbsp;<a href='$pageName?{$entity}expand=p$publisherId'><img src='images/$phpAds_TextDirection/triangle-l.gif' align='absmiddle' border='0'></a>&nbsp;";
                        }
                        else
                            echo "&nbsp;<img src='images/spacer.gif' height='16' width='16'>&nbsp;";
                            
                        echo "
                        <img src='{$icons['publisher'][$publisher['active']][$publisher['type']]}' align='absmiddle'>&nbsp;
                        <a href='stats-affiliate-history.php?affiliateid=$publisherId'>{$publisher['name']}</a>
                    </td>";
                        if ($anonymous) {
                            echo "
                    <td align='$phpAds_TextAlignRight'>&nbsp;</td>";
                        } else {
                            echo "
                    <td align='$phpAds_TextAlignRight'>$publisherId</td>";
                        }
                        echo "
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
            </tr>";
            if (!$anonymous) {
                echo "
            <tr>
                <td colspan='4' align='$phpAds_TextAlignLeft' nowrap>";
            
                if ($hideinactive == true) {
                    echo "&nbsp;&nbsp;<img src='images/icon-activate.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?{$entity}hideinactive=0'>{$GLOBALS['strShowAll']}</a>&nbsp;&nbsp;|&nbsp;&nbsp;$publishersHidden {$GLOBALS['strInactivePublishersHidden']}";
                } else {
                    echo "&nbsp;&nbsp;<img src='images/icon-hideinactivate.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?{$entity}hideinactive=1'>{$GLOBALS['strHideInactivePublishers']}</a>";
                }
                
                echo "
                </td>
                <td colspan='3' align='$phpAds_TextAlignRight' nowrap><img src='images/triangle-d.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?{$entity}expand=all' accesskey='$keyExpandAll'>{$GLOBALS['strExpandAll']}</a>&nbsp;&nbsp;|&nbsp;&nbsp;<img src='images/$phpAds_TextDirection/triangle-l.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?{$entity}expand=none' accesskey='$keyCollapseAll'>{$GLOBALS['strCollapseAll']}</a>&nbsp;&nbsp;</td>
            </tr>
            <tr height='25'>";
                if ($showPublisher == 't') {
                    echo "
                <td colspan='7' align='$phpAds_TextAlignLeft' nowrap>&nbsp;&nbsp;<img src='images/icon-affiliate.gif' align='absmiddle'><a href='$pageName?{$entity}showpublisher=f'> Hide parent publisher</a></td>";
                } else {
                    echo "
                <td colspan='7' align='$phpAds_TextAlignLeft' nowrap>&nbsp;&nbsp;<img src='images/icon-affiliate.gif' align='absmiddle'><a href='$pageName?{$entity}showpublisher=t'> Show parent publisher</a></td>";
                }
                echo "
            </tr>";
            }
            echo "
            </table>
            <br><br>";
        } else {
            MAX_displayNoStatsMessage();
        }
    }
    function MAX_displayZoneStats($aStats, $pageName, $anonymous, $aNodes, $expand, $listorder, $orderdirection, $hideinactive, $showPublisher, $entityIds)
    {
        global $phpAds_TextAlignLeft, $phpAds_TextAlignRight, $phpAds_TextDirection;
        
        // Get the icons for all levels (publisher/zone)
        $icons = MAX_getEntityIcons();
        $entity = _getEntityString($entityIds);
        $publishersHidden = 0;
        
        if (!empty($aStats['children'])) {
            echo "
            <br><br>
            <table border='0' width='100%' cellpadding='0' cellspacing='0'>";
            MAX_displayStatsHeader($pageName, $listorder, $orderdirection, $entityIds, $anonymous);
            
            // Variable to determine if the row should be grey or white...
            $i=0;
            
            // Loop through publishers
            if (!empty($aStats['children'])) {
                MAX_sortArray($aStats['children'], $listorder, $orderdirection == 'up');
                foreach($aStats['children'] as $zoneId => $zone) {
                    $zoneViews = phpAds_formatNumber($zone['views']);
                    $zoneClicks = phpAds_formatNumber($zone['clicks']);
                    $zoneConversions = phpAds_formatNumber($zone['conversions']);
                    $zoneCtr = phpAds_buildRatioPercentage($zone['clicks'], $zone['views']);
                    $zoneSr = phpAds_buildRatioPercentage($zone['conversions'], $zone['clicks']);            
                    
                    if (!$hideinactive || $zone['active'] == 't') {
                        $bgcolor = ($i++ % 2 == 0) ? " bgcolor='#F6F6F6'" : '';
                        echo "
                <tr height='25'$bgcolor>
                    <td>&nbsp;<img src='images/spacer.gif' height='16' width='16'>&nbsp;
                        <img src='{$icons['zone'][$zone['active']][$zone['type']]}' align='absmiddle'>&nbsp;";
                        if ($anonymous) {
                            echo "
                        Hidden zone {$zone['id']}";
                        } else {
                            echo "
                        <a href='stats-zone-history.php?affiliateid={$zone['parentid']}'>{$zone['name']}</a>";
                        }
                        echo "
                    </td>";
                        if ($anonymous) {
                            echo "
                    <td align='$phpAds_TextAlignRight'>&nbsp;</td>";
                        } else {
                            echo "
                    <td align='$phpAds_TextAlignRight'>$zoneId</td>";
                        }
                        echo "
                    <td align='$phpAds_TextAlignRight'>$zoneViews</td>
                    <td align='$phpAds_TextAlignRight'>$zoneClicks</td>
                    <td align='$phpAds_TextAlignRight'>$zoneCtr</td>
                    <td align='$phpAds_TextAlignRight'>$zoneConversions</td>
                    <td align='$phpAds_TextAlignRight'>$zoneSr</td>
                </tr>
                <tr height='1'><td colspan='7' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
                    } else {
                        $publishersHidden++;
                    }
                }
            }
            
            // Total
            echo "
            <tr height='25'$bgcolor>
                <td>&nbsp;&nbsp;<b>{$GLOBALS['strTotal']}</b></td>
                <td>&nbsp;</td>
                <td align='$phpAds_TextAlignRight'>".phpAds_formatNumber($aStats['views'])."</td>
                <td align='$phpAds_TextAlignRight'>".phpAds_formatNumber($aStats['clicks'])."</td>
                <td align='$phpAds_TextAlignRight'>".phpAds_buildCTR($aStats['views'], $aStats['clicks'])."</td>
                <td align='$phpAds_TextAlignRight'>".phpAds_formatNumber($aStats['conversions'])."</td>
                <td align='$phpAds_TextAlignRight'>".phpAds_buildCTR($aStats['clicks'], $aStats['conversions'])."</td>
            </tr>
            <tr height='1'>
                <td colspan='7' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td>
            </tr>";
            if (!$anonymous) {
                echo "
            <tr>
                <td colspan='4' align='$phpAds_TextAlignLeft' nowrap>";
            
                if ($hideinactive == true) {
                    echo "&nbsp;&nbsp;<img src='images/icon-activate.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?{$entity}hideinactive=0'>{$GLOBALS['strShowAll']}</a>&nbsp;&nbsp;|&nbsp;&nbsp;$publishersHidden {$GLOBALS['strInactivePublishersHidden']}";
                } else {
                    echo "&nbsp;&nbsp;<img src='images/icon-hideinactivate.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?{$entity}hideinactive=1'>{$GLOBALS['strHideInactivePublishers']}</a>";
                }
                
                echo "
                </td>
                <td colspan='3' align='$phpAds_TextAlignRight' nowrap><img src='images/triangle-d.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?{$entity}expand=all'>{$GLOBALS['strExpandAll']}</a>&nbsp;&nbsp;|&nbsp;&nbsp;<img src='images/$phpAds_TextDirection/triangle-l.gif' align='absmiddle' border='0'>&nbsp;<a href='$pageName?{$entity}expand=none'>{$GLOBALS['strCollapseAll']}</a>&nbsp;&nbsp;</td>
            </tr>
            <tr height='25'>";
                if ($showPublisher == 't') {
                    echo "
                <td colspan='7' align='$phpAds_TextAlignLeft' nowrap>&nbsp;&nbsp;<img src='images/icon-affiliate.gif' align='absmiddle'><a href='$pageName?{$entity}showpublisher=f'> Hide parent publisher</a></td>";
                } else {
                    echo "
                <td colspan='7' align='$phpAds_TextAlignLeft' nowrap>&nbsp;&nbsp;<img src='images/icon-affiliate.gif' align='absmiddle'><a href='$pageName?{$entity}showpublisher=t'> Show parent publisher</a></td>";
                }
                echo "
            </tr>";
            }
            echo "
            </table>
            <br><br>";
        } else {
            MAX_displayNoStatsMessage();
        }
    }

    
?>