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
$Id: campaign-banners.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require_once("config.php");
require_once("lib-statistics.inc.php");
require_once("lib-expiration.inc.php");
require_once("lib-gd.inc.php");
require_once('../libraries/common.php');

// Register input variables
phpAds_registerGlobal('expand', 'collapse', 'hideinactive', 'listorder', 'orderdirection');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);

if (phpAds_isUser(phpAds_Agency)) {
    $query = "SELECT clientid FROM ".$phpAds_config['tbl_clients']." WHERE clientid=".$clientid." AND agencyid=".phpAds_getUserID();
    $res = phpAds_dbQuery($query) or phpAds_sqlDie();
    if (phpAds_dbNumRows($res) == 0) {
        phpAds_PageHeader("2");
        phpAds_Die ($strAccessDenied, $strNotAdmin);
    }
}

if (!MAX_checkAdvertiser($clientid) or !MAX_checkCampaign($clientid, $campaignid)) {
    phpAds_PageHeader("2");
    phpAds_Die ($strAccessDenied, $strNotAdmin);
}

/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if (isset($Session['prefs']['advertiser-campaigns.php'][$clientid]['listorder'])) {
    $navorder = $Session['prefs']['advertiser-campaigns.php'][$clientid]['listorder'];
} else {
    $navorder = '';
}

if (isset($Session['prefs']['advertiser-campaigns.php'][$clientid]['orderdirection'])) {
    $navdirection = $Session['prefs']['advertiser-campaigns.php'][$clientid]['orderdirection'];
} else {
    $navdirection = '';
}

// Get other campaigns
$res = phpAds_dbQuery("
    SELECT
        *
    FROM
        ".$phpAds_config['tbl_campaigns']."
    WHERE
        clientid = ".$clientid."
    ".phpAds_getCampaignListOrder ($navorder, $navdirection)."
");

while ($row = phpAds_dbFetchArray($res)) {
    phpAds_PageContext (
        phpAds_buildName ($row['campaignid'], $row['campaignname']),
        "campaign-banners.php?clientid=".$clientid."&campaignid=".$row['campaignid'],
        $campaignid == $row['campaignid']
    );
}

if (!phpAds_isUser(phpAds_Client)) phpAds_PageShortcut($strClientProperties, 'advertiser-edit.php?clientid='.$clientid, 'images/icon-advertiser.gif');
phpAds_PageShortcut($strCampaignHistory, 'stats-campaign-history.php?clientid='.$clientid.'&campaignid='.$campaignid, 'images/icon-statistics.gif');



$extra  = "\t\t\t\t<form action='campaign-modify.php'>\n";
$extra .= "\t\t\t\t<input type='hidden' name='clientid' value='$clientid'>\n";
$extra .= "\t\t\t\t<input type='hidden' name='campaignid' value='$campaignid'>\n";
$extra .= "\t\t\t\t<input type='hidden' name='returnurl' value='campaign-banners.php'>\n";
$extra .= "\t\t\t\t<br><br>\n";
$extra .= "\t\t\t\t<b>$strModifyCampaign</b><br>\n";
$extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
$extra .= "<img src='images/icon-move-campaign.gif' align='absmiddle'>&nbsp;<a href='campaign-modify.php?clientid=".$clientid."&campaignid=".$campaignid."&duplicate=true&returnurl=".$_SERVER['PHP_SELF']."'>$strDuplicate</a><br>";
$extra .= "\t\t\t\t<img src='images/break.gif' height='1' width='160' vspace='4' alt=''><br>\n";
$extra .= "\t\t\t\t<img src='images/icon-move-campaign.gif' align='absmiddle' alt=''>&nbsp;$strMoveTo<br>\n";
$extra .= "\t\t\t\t<img src='images/spacer.gif' height='1' width='160' vspace='2' alt=''><br>\n";
$extra .= "\t\t\t\t&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n";
$extra .= "\t\t\t\t<select name='moveto' style='width: 110;'>\n";

if (phpAds_isUser(phpAds_Admin)) {
    $query = "SELECT clientid,clientname".
        " FROM ".$phpAds_config['tbl_clients'].
        " WHERE clientid!=".$clientid;
} elseif (phpAds_isUser(phpAds_Agency)) {
    $query = "SELECT clientid,clientname".
        " FROM ".$phpAds_config['tbl_clients'].
        " WHERE clientid!=".$clientid.
    " AND agencyid=".phpAds_getUserID();
} elseif (phpAds_isUser(phpAds_Client)) {
    $query = "SELECT clientid,clientname".
        " FROM ".$phpAds_config['tbl_clients'].
        " WHERE clientid!=".$clientid.
    " AND agencyid=".phpAds_getAgencyID();
}

$res = phpAds_dbQuery($query)
    or phpAds_sqlDie();

while ($row = phpAds_dbFetchArray($res)) {
    $extra .= "\t\t\t\t\t<option value='".$row['clientid']."'>".phpAds_buildName($row['clientid'], $row['clientname'])."</option>\n";
}

$extra .= "\t\t\t\t</select>&nbsp;\n";
$extra .= "\t\t\t\t<input type='image' src='images/".$phpAds_TextDirection."/go_blue.gif'><br>\n";
$extra .= "\t\t\t\t<img src='images/break.gif' height='1' width='160' vspace='4' alt=''><br>\n";
$extra .= "\t\t\t\t<img src='images/icon-recycle.gif' align='absmiddle' alt=''>&nbsp;<a href='campaign-delete.php?clientid=".$clientid."&campaignid=".$campaignid."&returnurl=advertiser-index.php'".phpAds_DelConfirm($strConfirmDeleteCampaign).">$strDelete</a><br>\n";
$extra .= "\t\t\t\t</form>";


if (phpAds_isUser(phpAds_Admin) or phpAds_isUser(phpAds_Agency)) {
    phpAds_PageHeader("4.1.3.3", $extra);
    echo "<img src='images/icon-advertiser.gif' align='absmiddle' alt=''>&nbsp;".phpAds_getParentClientName($campaignid);
    echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif' alt=''>&nbsp;";
    echo "<img src='images/icon-campaign.gif' align='absmiddle' alt=''>&nbsp;<b>".phpAds_getCampaignName($campaignid)."</b><br><br><br>";
    phpAds_ShowSections(array("4.1.3.2", "4.1.3.3", "4.1.3.4", "4.1.3.5"));
}
elseif (phpAds_isUser(phpAds_Client)) {
    phpAds_PageHeader("2.1.1");
    echo "<img src='images/icon-advertiser.gif' align='absmiddle' alt=''>&nbsp;".phpAds_getParentClientName($campaignid);
    echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif' alt=''>&nbsp;";
    echo "<img src='images/icon-campaign.gif' align='absmiddle' alt=''>&nbsp;<b>".phpAds_getCampaignName($campaignid)."</b><br><br><br>";
    phpAds_ShowSections(array("2.1.1"));
}


/*********************************************************/
/* Get preferences                                       */
/*********************************************************/

if (!isset($hideinactive)) {
    if (isset($Session['prefs']['campaign-banners.php'][$campaignid]['hideinactive'])) {
        $hideinactive = $Session['prefs']['campaign-banners.php'][$campaignid]['hideinactive'];
    } else {
        $hideinactive = ($phpAds_config['gui_hide_inactive'] == 't');
    }
}

if (!isset($listorder)) {
    if (isset($Session['prefs']['campaign-banners.php'][$campaignid]['listorder'])) {
        $listorder = $Session['prefs']['campaign-banners.php'][$campaignid]['listorder'];
    } else {
        $listorder = '';
    }
}

if (!isset($orderdirection)) {
    if (isset($Session['prefs']['campaign-banners.php'][$campaignid]['orderdirection'])) {
        $orderdirection = $Session['prefs']['campaign-banners.php'][$campaignid]['orderdirection'];
    } else {
        $orderdirection = '';
    }
}

if (isset($Session['prefs']['campaign-banners.php'][$campaignid]['nodes'])) {
    $node_array = explode (",", $Session['prefs']['campaign-banners.php'][$campaignid]['nodes']);
} else {
    $node_array = array();
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/

$res = phpAds_dbQuery("
    SELECT
        *
    FROM
        ".$phpAds_config['tbl_banners']."
    WHERE
        campaignid = '$campaignid'
    ".phpAds_getBannerListOrder($listorder, $orderdirection)."
") or phpAds_sqlDie();

$countActive = 0;

while ($row = phpAds_dbFetchArray($res)) {
    $banners[$row['bannerid']] = $row;
    $banners[$row['bannerid']]['expand'] = 0;
    if ($row['active'] == 't') {
        $countActive++;
    }
}

// Add ID found in expand to expanded nodes
if (isset($expand) && $expand != '') {
    switch ($expand) {
        case 'all':
            $node_array = array();
            if (isset($banners)) {
                for (reset($banners); $key=key($banners); next($banners)) {
                    $node_array[] = $key;
                }
            }
            break;
						
        case 'none':
            $node_array = array();
            break;
						
        default:
            $node_array[] = $expand;
            break;
    }
}

$node_array_size = sizeof($node_array);
for ($i=0; $i < $node_array_size; $i++) {
    if (isset($collapse) && $collapse == $node_array[$i]) {
        unset ($node_array[$i]);
    } else {
        if (isset($banners[$node_array[$i]])) {
            $banners[$node_array[$i]]['expand'] = 1;
        }
    }
}

// Figure out which banners are inactive, 
$bannersHidden = 0;
if (isset($banners) && is_array($banners) && count($banners) > 0) {
    reset ($banners);
    while (list ($key, $banner) = each ($banners)) {
        if (($hideinactive == true) && ($banner['active'] == 'f')) {            
            $bannersHidden++;
            unset($banners[$key]);
        }
    }
}
if ((!phpAds_isUser(phpAds_Client)) || phpAds_isAllowed(phpAds_ModifyBanner)) {
    echo "\t\t\t\t<img src='images/icon-banner-new.gif' align='absmiddle' alt=''>&nbsp;";
    echo "<a href='banner-edit.php?clientid=".$clientid."&campaignid=".$campaignid."' accesskey='".$keyAddNew."'>".$strAddBanner_Key."</a>&nbsp;&nbsp;&nbsp;&nbsp;\n";
    phpAds_ShowBreak();
}

echo "\t\t\t\t<br><br>\n";
echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";	

echo "<tr height='25'>";
echo "<td height='25' width='40%'><b>&nbsp;&nbsp;<a href='campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&listorder=name'>".$GLOBALS['strName']."</a>";

if (($listorder == "name") || ($listorder == "")) {
    if  (($orderdirection == "") || ($orderdirection == "down")) {
        echo ' <a href="campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=up">';
        echo '<img src="images/caret-ds.gif" border="0" alt="" title="">';
    } else{
        echo ' <a href="campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=down">';
        echo '<img src="images/caret-u.gif" border="0" alt="" title="">';
    }
    echo '</a>';
}

echo '</b></td>';
echo '<td height="25"><b><a href="campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&listorder=id">'.$GLOBALS['strID'].'</a>';

if ($listorder == "id") {
    if  (($orderdirection == "") || ($orderdirection == "down")) {
        echo ' <a href="campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=up">';
        echo '<img src="images/caret-ds.gif" border="0" alt="" title="">';
    } else {
        echo ' <a href="campaign-banners.php?clientid='.$clientid.'&campaignid='.$campaignid.'&orderdirection=down">';
        echo '<img src="images/caret-u.gif" border="0" alt="" title="">';
    }
    echo '</a>';
}

echo '</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>';
echo "<td height='25'>&nbsp;</td>";
echo "<td height='25'>&nbsp;</td>";
echo "<td height='25'>&nbsp;</td>";
echo "</tr>";

echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";


if (!isset($banners) || !is_array($banners) || count($banners) == 0) {
    echo "<tr height='25' bgcolor='#F6F6F6'><td height='25' colspan='5'>";
    echo "&nbsp;&nbsp;".$strNoBanners;
    echo "</td></tr>";
} else {
    $i=0;
    for (reset($banners);$bkey=key($banners);next($banners)) {
        if ($i > 0) {
            echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%' alt=''></td></tr>";
        }
		
        // Icon & name
        $name = $strUntitled;
        if (isset($banners[$bkey]['alt']) && $banners[$bkey]['alt'] != '') {
            $name = $banners[$bkey]['alt'];
        }
        if (isset($banners[$bkey]['description']) && $banners[$bkey]['description'] != '') {
            $name = $banners[$bkey]['description'];
        }
		
        $name = phpAds_breakString ($name, '30');
		
        echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"")."><td height='25'>";
        echo "&nbsp;";
				
        if (!$phpAds_config['gui_show_campaign_preview']) {
            if ($banners[$bkey]['expand'] == '1') {
                echo "<a href='campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&collapse=".$banners[$bkey]['bannerid']."'><img src='images/triangle-d.gif' align='absmiddle' border='0' alt=''></a>&nbsp;";
            } else {
                echo "<a href='campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&expand=".$banners[$bkey]['bannerid']."'><img src='images/".$phpAds_TextDirection."/triangle-l.gif' align='absmiddle' border='0' alt=''></a>&nbsp;";
            }
        } else {
            echo "&nbsp;";
        }
		
        if ($banners[$bkey]['active'] == 't') {
            if ($banners[$bkey]['storagetype'] == 'html') {
                echo "<img src='images/icon-banner-html.gif' align='absmiddle' alt=''>";
            } elseif ($banners[$bkey]['storagetype'] == 'txt') {
                echo "<img src='images/icon-banner-text.gif' align='absmiddle' alt=''>";
            } elseif ($banners[$bkey]['storagetype'] == 'url') {
                echo "<img src='images/icon-banner-url.gif' align='absmiddle' alt=''>";
            } else {
                echo "<img src='images/icon-banner-stored.gif' align='absmiddle' alt=''>";
            }
        } else {
            if ($banners[$bkey]['storagetype'] == 'html') {
                echo "<img src='images/icon-banner-html-d.gif' align='absmiddle'>";
            } elseif ($banners[$bkey]['storagetype'] == 'txt') {
                echo "<img src='images/icon-banner-text-d.gif' align='absmiddle'>";
            } elseif ($banners[$bkey]['storagetype'] == 'url') {
                echo "<img src='images/icon-banner-url-d.gif' align='absmiddle'>";
            } else {
                echo "<img src='images/icon-banner-stored-d.gif' align='absmiddle'>";
            }
        }
		
        echo "&nbsp;<a href='banner-edit.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$bkey."'>".$name."</td>";
                  
        echo "</td>";
		
        // ID
        echo "<td height='25'>".$bkey."</td>";
		
        echo "<td height='25' align='".$phpAds_TextAlignRight."'>";
        if ((!phpAds_isUser(phpAds_Client)) || phpAds_isAllowed(phpAds_ModifyBanner)) {
            // Button 1
            echo "<a href='banner-acl.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$banners[$bkey]['bannerid']."'><img src='images/icon-acl.gif' border='0' align='absmiddle' alt='$strACL'>&nbsp;$strACL</a>&nbsp;&nbsp;&nbsp;&nbsp;";
        }
        echo "</td>";
        
        echo "<td height='25' align='".$phpAds_TextAlignRight."'>";
        if (!phpAds_isUser(phpAds_Client)) {
            // Button 2
            if ($banners[$bkey]["active"] == "t") {
                echo "<a href='banner-activate.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$banners[$bkey]["bannerid"]."&value=".$banners[$bkey]["active"]."'><img src='images/icon-deactivate.gif' align='absmiddle' border='0'>&nbsp;";
                echo $strDeActivate;
            } else {
                echo "<a href='banner-activate.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$banners[$bkey]["bannerid"]."&value=".$banners[$bkey]["active"]."'><img src='images/icon-activate.gif' align='absmiddle' border='0'>&nbsp;";
                echo $strActivate;
            }
        } else {
            if ($banners[$bkey]['active'] == 't') {
                if (phpAds_isAllowed(phpAds_DisableBanner)) {
                    if (phpAds_isAllowed(phpAds_ActivateBanner)) {
                        echo "<a href='banner-activate.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$banners[$bkey]["bannerid"]."&value=".$banners[$bkey]["active"]."'><img src='images/icon-deactivate.gif' align='absmiddle' border='0'>&nbsp;";
                    } else {
                        echo "<a href='banner-activate.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$banners[$bkey]["bannerid"]."&value=".$banners[$bkey]["active"]."' ".phpAds_DelConfirm($strConfirmDeactivate)."><img src='images/icon-deactivate.gif' align='absmiddle' border='0'>&nbsp;";
                    }
                    echo $strDeActivate;
                }
            }
            if ($banners[$bkey]['active'] == 'f') {
                if (phpAds_isAllowed(phpAds_ActivateBanner)) {
                    echo "<a href='banner-activate.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$banners[$bkey]["bannerid"]."&value=".$banners[$bkey]["active"]."'><img src='images/icon-activate.gif' align='absmiddle' border='0'>&nbsp;";
                    echo $strActivate;
                }
            }
        }
		echo "</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>";

        echo "<td height='25' align='".$phpAds_TextAlignRight."'>";
        if (!phpAds_isUser(phpAds_Client)) {
            // Button 3
            echo "<a href='banner-delete.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$banners[$bkey]['bannerid']."&returnurl=campaign-banners.php'".phpAds_DelConfirm($strConfirmDeleteBanner)."><img src='images/icon-recycle.gif' border='0' align='absmiddle' alt='$strDelete'>&nbsp;$strDelete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
        } else {
            echo "&nbsp;";
        }
        echo "</td></tr>";
		
        // Extra banner info
        if ($phpAds_config['gui_show_banner_info']) {
            echo "<tr height='1'>";
            echo "<td ".($i%2==0?"bgcolor='#F6F6F6'":"")."><img src='images/spacer.gif' width='1' height='1'></td>";
            echo "<td colspan='4' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td>";
            echo "</tr>";
			
            echo "<tr ".($i%2==0?"bgcolor='#F6F6F6'":"")."><td colspan='1'>&nbsp;</td><td colspan='4'>";
            echo "<table width='100%' cellpadding='0' cellspacing='0' border='0'>";
			
            echo "<tr height='25'><td colspan='2'>".($banners[$bkey]['url'] != '' ? $banners[$bkey]['url'] : '-')."</td></tr>";
			
            if ($phpAds_config['use_keywords']) {
                echo "<tr height='15'><td colspan='2'>".$strKeyword.": ".($banners[$bkey]['keyword'] != '' ? $banners[$bkey]['keyword'] : '-')."</td></tr>";
            }
			
            if ($banners[$bkey]['storagetype'] == 'txt') {
                echo "<tr height='25'><td width='50%'>".$strSize.": -</td>";
            } else {
                echo "<tr height='25'><td width='50%'>".$strSize.": ".$banners[$bkey]['width']." x ".$banners[$bkey]['height']."</td>";
            }
			
            echo "<td width='50%'>".$strWeight.": ".$banners[$bkey]['weight']."</td></tr>";
			
            echo "</table><br></td></tr>";
        }
		
        // Banner preview
        if ($banners[$bkey]['expand'] == 1 || $phpAds_config['gui_show_campaign_preview']) {
            if (!$phpAds_config['gui_show_banner_info']) {
                echo "<tr ".($i%2==0?"bgcolor='#F6F6F6'":"")."><td colspan='1'>&nbsp;</td><td colspan='4'>";
            }
			
            echo "<tr ".($i%2==0?"bgcolor='#F6F6F6'":"")."><td colspan='5'>";
            echo "<table width='100%' cellpadding='0' cellspacing='0' border='0'><tr>";
            echo "<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
            echo "<td width='100%'>".phpAds_buildBannerCode ($banners[$bkey]['bannerid'], true)."</td>";
            echo "</tr></table><br><br>";
            echo "</td></tr>";
        }
		
        $i++;
    }
}

// Display the items to:
//  - Show all banners, or hide the inactive banners
//  - Display how many inactive banners have been hidden, if applicable
//  - Expand all banner entries
//  - Collapse all banner entries
echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
echo "<tr height='25'><td colspan='2' height='25' nowrap>";
if ($hideinactive == true) {
    echo "&nbsp;&nbsp;<img src='images/icon-activate.gif' align='absmiddle' border='0'>";
    echo "&nbsp;<a href='campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&hideinactive=0'>".$strShowAll."</a>";
    echo "&nbsp;&nbsp;|&nbsp;&nbsp;".$bannersHidden." ".$strInactiveBannersHidden;
} else {
    echo "&nbsp;&nbsp;<img src='images/icon-hideinactivate.gif' align='absmiddle' border='0'>";
    echo "&nbsp;<a href='campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&hideinactive=1'>".$strHideInactiveBanners."</a>";
}
echo "</td>";

if (!$phpAds_config['gui_show_campaign_preview']) {
    echo "<td colspan='3' align='".$phpAds_TextAlignRight."' nowrap>";
    echo "<img src='images/triangle-d.gif' align='absmiddle' border='0' alt=''>";
    echo "&nbsp;<a href='campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&expand=all' accesskey='".$keyExpandAll."'>".$strExpandAll."</a>";
    echo "&nbsp;&nbsp;|&nbsp;&nbsp;";
    echo "<img src='images/".$phpAds_TextDirection."/triangle-l.gif' align='absmiddle' border='0' alt=''>";
    echo "&nbsp;<a href='campaign-banners.php?clientid=".$clientid."&campaignid=".$campaignid."&expand=none' accesskey='".$keyCollapseAll."'>".$strCollapseAll."</a>";
    echo "</td>";
} else {
    echo "<td colspan='2'>&nbsp;</td>";	
}
echo "</tr>";
	
// Display the items to:
//  - Delete all banners, if banners exist
//  - Activate all banners, if banners exist and some banners are inactive
//  - Deactivate all banners, if banners exist and some banners are active
if (isset($banners) && count($banners)) {
    echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break-el.gif' height='1' width='100%' alt=''></td></tr>";
    echo "<tr height='25'>";
    echo "<td colspan='5' height='25' align='".$phpAds_TextAlignRight."'>";
    if (!phpAds_isUser(phpAds_Client)) echo "<img src='images/icon-recycle.gif' border='0' align='absmiddle' alt=''>&nbsp;<a href='banner-delete.php?clientid=".$clientid."&campaignid=".$campaignid."&returnurl=campaign-banners.php'".phpAds_DelConfirm($strConfirmDeleteAllBanners).">$strDeleteAllBanners</a>&nbsp;&nbsp;&nbsp;&nbsp;";
    if ($countActive < count($banners)) {
        if (!phpAds_isUser(phpAds_Client)) {
            echo "<img src='images/icon-activate.gif' border='0' align='absmiddle' alt=''>&nbsp;<a href='banner-activate.php?clientid=".$clientid."&campaignid=".$campaignid."&value=f'>$strActivateAllBanners</a>&nbsp;&nbsp;&nbsp;&nbsp;";
        }
    }
    if ($countActive > 0) {
        if (!phpAds_isUser(phpAds_Client)) {
            echo "<img src='images/icon-deactivate.gif' border='0' align='absmiddle' alt=''>&nbsp;<a href='banner-activate.php?clientid=".$clientid."&campaignid=".$campaignid."&value=t'>$strDeactivateAllBanners</a>&nbsp;&nbsp;";
        }
    }
    echo "</td>";
    echo "</tr>";
}

echo "</table>";
echo "<br><br>";
echo "<br><br>";

echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
echo "<tr><td height='25' colspan='2'><b>$strCreditStats</b></td></tr>";
echo "<tr><td height='1' colspan='2' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";

list($desc,$enddate,$daysleft) = phpAds_getDaysLeft($campaignid);
$adclicksleft = phpAds_getAdClicksLeft($campaignid);
$adviewsleft  = phpAds_getAdViewsLeft($campaignid);

echo "<tr><td height='25'>$strViewCredits: <b>$adviewsleft</b></td>";
echo "<td height='25'>$strClickCredits: <b>$adclicksleft</b></td></tr>";
echo "<tr><td height='1' colspan='2' bgcolor='#888888'><img src='images/break-el.gif' height='1' width='100%' alt=''></td></tr>";
echo "<tr><td height='25' colspan='2'>$desc</td></tr>";

echo "<tr><td height='1' colspan='2' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%' alt=''></td></tr>";
echo "</table>";
echo "<br><br>";



/*********************************************************/
/* Store preferences                                     */
/*********************************************************/

$Session['prefs']['campaign-banners.php'][$campaignid]['hideinactive'] = $hideinactive;
$Session['prefs']['campaign-banners.php'][$campaignid]['listorder'] = $listorder;
$Session['prefs']['campaign-banners.php'][$campaignid]['orderdirection'] = $orderdirection;
$Session['prefs']['campaign-banners.php'][$campaignid]['nodes'] = implode (",", $node_array);

phpAds_SessionDataStore();



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>