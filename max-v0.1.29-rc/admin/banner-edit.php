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
$Id: banner-edit.php 3368 2005-06-13 09:53:50Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-storage.inc.php");
require ("lib-swf.inc.php");
require ("lib-banner.inc.php");
require ("lib-zones.inc.php");
include_once ('../libraries/db.php');


// Register input variables
phpAds_registerGlobal (
     'alink'
    ,'alink_chosen'
    ,'alt'
    ,'asource'
    ,'atar'
    ,'autohtml'
    ,'adserver'
    ,'banner'
    ,'bannertext'
    ,'campaignid'
    ,'checkswf'
    ,'clientid'
    ,'description'
    ,'height'
    ,'imageurl'
    ,'keyword'
    ,'message'
    ,'replaceimage'
    ,'replacealtimage'
    ,'status'
    ,'storagetype'
    ,'submit'
    ,'target'
    ,'upload'
    ,'url'
    ,'weight'
    ,'width'
);


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Client);

if (phpAds_isUser(phpAds_Agency))
{
    if (isset($bannerid) && ($bannerid != ''))
    {
        $query = "SELECT b.bannerid as bannerid".
            " FROM ".$phpAds_config['tbl_clients']." AS c".
            ",".$phpAds_config['tbl_campaigns']." AS m".
            ",".$phpAds_config['tbl_banners']." AS b".
            " WHERE c.clientid=".$clientid.
            " AND m.campaignid=".$campaignid.
            " AND b.bannerid=".$bannerid.
            " AND b.campaignid=m.campaignid".
            " AND m.clientid=c.clientid".
            " AND c.agencyid=".phpAds_getUserID();
    }
    else 
    {
        $query = "SELECT m.campaignid as campaignid".
            " FROM ".$phpAds_config['tbl_clients']." AS c".
            ",".$phpAds_config['tbl_campaigns']." AS m".
            " WHERE c.clientid=".$clientid.
            " AND m.campaignid=".$campaignid.
            " AND m.clientid=c.clientid".
            " AND c.agencyid=".phpAds_getUserID();
    }
    $res = phpAds_dbQuery($query)
        or phpAds_sqlDie();
    if (phpAds_dbNumRows($res) == 0)
    {
        phpAds_PageHeader("2");
        phpAds_Die ($strAccessDenied, $strNotAdmin);
    }
}


/*********************************************************/
/* Client interface security                             */
/*********************************************************/

if (phpAds_isUser(phpAds_Client))
{
    if (isset($bannerid) && ($bannerid != '')) {
        $result = phpAds_dbQuery("
            SELECT
                campaignid
            FROM
                ".$phpAds_config['tbl_banners']."
            WHERE
                bannerid = '$bannerid'
            ") or phpAds_sqlDie();
        $row = phpAds_dbFetchArray($result);
        
        if ($row["campaignid"] == '' || phpAds_getUserID() != phpAds_getCampaignParentClientID ($row["campaignid"]))
        {
            phpAds_PageHeader("1");
            phpAds_Die ($strAccessDenied, $strNotAdmin);
        }
        else
        {
            $campaignid = $row["campaignid"];
        }
    }
    else {
        if (!phpAds_isAllowed(phpAds_ModifyBanner)) {
            phpAds_PageHeader("1");
            phpAds_Die ($strAccessDenied, $strNotAdmin);
        }
    }
}




/*********************************************************/
/* Process submitted form                                */
/*********************************************************/

if (isset($submit))
{
    if (phpAds_isUser(phpAds_Client) && !phpAds_isAllowed(phpAds_ModifyBanner)) {
        phpAds_PageHeader("1");
        phpAds_Die ($strAccessDenied, $strNotAdmin);
    }
    // Get the existing banner details (if it is not a new banner)
    if (!empty($bannerid)) {
        $aBanner = MAX_getBannerByBannerId($bannerid);
    }
    
    $aVariables = array();
    $aVariables['campaignid']      = $campaignid;
    $aVariables['storagetype']     = $storagetype;
    $aVariables['target']          = isset($target) ? $target : '';
    $aVariables['height']          = isset($height) ? $height : 0;
    $aVariables['width']           = isset($width)  ? $width : 0;
    $aVariables['weight']          = !empty($weight) ? $weight : 0;
    $aVariables['autohtml']        = isset($autohtml) ? 't' : 'f';
    $aVariables['adserver']        = !empty($adserver) ? $adserver : '';
    $aVariables['alt']             = !empty($alt) ? phpAds_htmlQuotes($alt) : '';
    $aVariables['bannertext']      = !empty($bannertext) ? phpAds_htmlQuotes($bannertext) : '';
    $aVariables['htmltemplate']    = !empty($banner) ? $banner : '';
    $aVariables['description']     = !empty($description) ? $description : '';
    $aVariables['imageurl']        = (!empty($imageurl) && $imageurl != 'http://') ? $imageurl : '';
    $aVariables['url']             = (!empty($url) && $url != 'http://') ? $url : '';
    $aVariables['status']          = !empty($status) ? $status : '';
    $aVariables['htmlcache']       = !empty($aVariables['htmltemplate']) ? (phpAds_getBannerCache($aVariables)) : '';
    $aVariables['filename']        = !empty($aBanner['filename']) ? $aBanner['filename'] : '';
    $aVariables['contenttype']     = !empty($aBanner['contenttype']) ? $aBanner['contenttype'] : '';
    $aVariables['contenttype']     = ($storagetype == 'url') ? _getFileContentType($aVariables['imageurl']) : $aVariables['contenttype'];
    $aVariables['alt_filename']    = !empty($aBanner['alt_filename']) ? $aBanner['alt_filename'] : '';
    $aVariables['alt_contenttype'] = !empty($aBanner['alt_contenttype']) ? $aBanner['alt_contenttype'] : '';
    
    if (phpAds_isUser(phpAds_Client)) {
        if (!phpAds_isAllowed(phpAds_ActivateBanner)) {
            $aVariables['active']      = 'f';
        }
    } else {
        $aVariables['keyword']         = !empty($keyword) ? implode(' ', split('[ ,]+', trim($keyword))) : '';
    }
    
    $editSwf = false;
    
    // Deal with any files that are uploaded.
    if (!empty($_FILES['upload']) && $replaceimage == 't') {
        $aFile = _handleUploadedFile('upload', $storagetype);
        if (!empty($aFile)) {
            $aVariables['filename']      = $aFile['filename'];
            $aVariables['contenttype']   = $aFile['contenttype'];
            $aVariables['width']         = $aFile['width'];
            $aVariables['height']        = $aFile['height'];
            $aVariables['pluginversion'] = $aFile['pluginversion'];
            $editSwf                     = $aFile['editswf'];
        }
    }
    if (!empty($_FILES['uploadalt']) && $replacealtimage == 't') {
        $aFile = _handleUploadedFile('uploadalt', $storagetype, true);
        if (!empty($aFile)) {
            $aVariables['alt_filename']    = $aFile['filename'];
            $aVariables['alt_contenttype'] = $aFile['contenttype'];
        }
    }
    
    // Delete any old banners...
    if (!empty($aBanner['filename']) && $aBanner['filename'] != $aVariables['filename'])
        phpAds_ImageDelete($aBanner['storagetype'], $aBanner['filename']);
    if (!empty($aBanner['alt_filename']) && $aBanner['alt_filename'] != $aVariables['alt_filename'])
        phpAds_ImageDelete($aBanner['storagetype'], $aBanner['alt_filename']);
    
    // File the data
    if (!empty($bannerid)) {
        MAX_setBannerByBannerId($bannerid, $aVariables);
    } else {
        $bannerid = MAX_addBanner($aVariables);
    }
    
    
    include_once('lib-instant-update.inc.php');
    instant_update($bannerid);
    
    // Determine what the next page is
    if ($editSwf) {
        $nextPage = "banner-swf.php?clientid=$clientid&campaignid=$campaignid&bannerid=$bannerid";
    } else {
        $nextPage = "banner-acl.php?clientid=$clientid&campaignid=$campaignid&bannerid=$bannerid";
    }
    
    // Go to the next page
    Header("Location: $nextPage");
    exit;
}



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if ($bannerid != '')
{
    // Fetch the data from the database
    
    $res = phpAds_dbQuery("
        SELECT
            *
        FROM
            ".$phpAds_config['tbl_banners']."
        WHERE
            bannerid = '$bannerid'
    ") or phpAds_sqlDie();
    $row = phpAds_dbFetchArray($res);
    
    
    
    if (isset($Session['prefs']['campaign-banners.php'][$campaignid]['listorder']))
        $navorder = $Session['prefs']['campaign-banners.php'][$campaignid]['listorder'];
    else
        $navorder = '';
    
    if (isset($Session['prefs']['campaign-banners.php'][$campaignid]['orderdirection']))
        $navdirection = $Session['prefs']['campaign-banners.php'][$campaignid]['orderdirection'];
    else
        $navdirection = '';
    
    
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
    
    while ($others = phpAds_dbFetchArray($res))
    {
        phpAds_PageContext (
            phpAds_buildBannerName ($others['bannerid'], $others['description'], $others['alt']),
            "banner-edit.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$others['bannerid'],
            $bannerid == $others['bannerid']
        );
    }

    $extra  = "<form action='banner-modify.php'>";
    $extra .= "<input type='hidden' name='bannerid' value='$bannerid'>";
    $extra .= "<input type='hidden' name='clientid' value='$clientid'>";
    $extra .= "<input type='hidden' name='campaignid' value='$campaignid'>";
    $extra .= "<input type='hidden' name='returnurl' value='banner-edit.php'>";
    $extra .= "<br><br>";
    $extra .= "<b>$strModifyBanner</b><br>";
    $extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
    $extra .= "<img src='images/icon-duplicate-banner.gif' align='absmiddle'>&nbsp;<a href='banner-modify.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$bannerid."&duplicate=true&returnurl=banner-edit.php'>$strDuplicate</a><br>";
    $extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
    $extra .= "<img src='images/icon-move-banner.gif' align='absmiddle'>&nbsp;$strMoveTo<br>";
    $extra .= "<img src='images/spacer.gif' height='1' width='160' vspace='2'><br>";
    $extra .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    $extra .= "<select name='moveto' style='width: 110;'>";   
    
    if (phpAds_isUser(phpAds_Admin)) {
        $query = "SELECT campaignid,campaignname FROM ".$phpAds_config['tbl_campaigns']." WHERE campaignid !=".$campaignid;
    } elseif (phpAds_isUser(phpAds_Agency)) {
        $query = "SELECT campaignid,campaignname".
            " FROM ".$phpAds_config['tbl_campaigns'].
            ",".$phpAds_config['tbl_clients'].
            " WHERE ".$phpAds_config['tbl_clients'].".clientid=".$phpAds_config['tbl_campaigns'].".clientid".
            " AND agencyid=".phpAds_getAgencyID().
            " AND campaignid !=".$campaignid;
    } elseif (phpAds_isUser(phpAds_Client)) {
        $query = "SELECT campaignid,campaignname".
            " FROM ".$phpAds_config['tbl_campaigns'].
            " WHERE clientid=".phpAds_getUserID().
            " AND campaignid !=".$campaignid;
    }
    
    $res = phpAds_dbQuery($query)
        or phpAds_sqlDie();
    while ($others = phpAds_dbFetchArray($res))
        $extra .= "<option value='".$others['campaignid']."'>".phpAds_buildName($others['campaignid'], $others['campaignname'])."</option>";
    
    $extra .= "</select>&nbsp;<input type='image' name='moveto' src='images/".$phpAds_TextDirection."/go_blue.gif'><br>";
    $extra .= "<img src='images/break.gif' height='1' width='160' vspace='4'><br>";
    if (!phpAds_isClient) $extra .= "<img src='images/icon-recycle.gif' align='absmiddle'>&nbsp;<a href='banner-delete.php?clientid=".$clientid."&campaignid=".$campaignid."&bannerid=".$bannerid."&returnurl=campaign-banners.php'".phpAds_DelConfirm($strConfirmDeleteBanner).">$strDelete</a><br>";
    $extra .= "</form>";
    
    if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
    {
        phpAds_PageShortcut($strClientProperties, 'advertiser-edit.php?clientid='.$clientid, 'images/icon-advertiser.gif');
        phpAds_PageShortcut($strCampaignProperties, 'campaign-edit.php?clientid='.$clientid.'&campaignid='.$campaignid, 'images/icon-campaign.gif');
        phpAds_PageShortcut($strBannerHistory, 'stats-banner-history.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid, 'images/icon-statistics.gif');
        
        phpAds_PageHeader("4.1.3.3.2", $extra);
        echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;".phpAds_getParentClientName($campaignid);
        echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
        echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;".phpAds_getCampaignName($campaignid);
        echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
        echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;<b>".phpAds_getBannerName($bannerid)."</b><br><br>";
        echo phpAds_buildBannerCode($bannerid)."<br><br><br><br>";
        phpAds_ShowSections(array("4.1.3.3.2", "4.1.3.3.3", "4.1.3.3.6", "4.1.3.3.4"));
    }
    else
    {
        phpAds_PageShortcut($strBannerHistory, 'stats-banner-history.php?clientid='.$clientid.'&campaignid='.$campaignid.'&bannerid='.$bannerid, 'images/icon-statistics.gif');

        if (phpAds_isAllowed(phpAds_ModifyBanner)) {
            phpAds_PageHeader("2.1.1.1", $extra);
        } else {
            phpAds_PageHeader("2.1.1.1");
        }
        echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;".phpAds_getCampaignName($campaignid);
        echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
        echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;<b>".phpAds_getBannerName($bannerid)."</b><br><br>";
        echo phpAds_buildBannerCode($bannerid)."<br><br><br><br>";
        phpAds_ShowSections(array("2.1.1.1", "2.1.1.2", "2.1.1.3"));
    }
    
    // Set basic values
    $storagetype        = $row['storagetype'];
    $hardcoded_links   = array();
    $hardcoded_targets = array();
    $hardcoded_sources = array();    
}
else
{
    phpAds_PageHeader("4.1.3.3.1", $extra);
        echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;".phpAds_getParentClientName($campaignid);
        echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
        echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;".phpAds_getCampaignName($campaignid);
        echo "&nbsp;<img src='images/".$phpAds_TextDirection."/caret-rs.gif'>&nbsp;";
        echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;<b>".$strUntitled."</b><br><br><br>";
        phpAds_ShowSections(array("4.1.3.3.1"));
    
    // Set default values for new banner
    $row['alt']          = '';
    $row['status']          = '';
    $row['bannertext']      = '';
    $row['url']          = "http://";
    $row['target']          = '';
    $row['imageurl']      = "http://";
    $row['width']          = '';
    $row['height']          = '';
    $row['htmltemplate'] = '';
    $row['keyword']      = '';
    $row['description']  = '';
    
    $hardcoded_links = array();
    $hardcoded_targets = array();
}



/*********************************************************/
/* Main code                                             */
/*********************************************************/


// Determine which bannertypes to show
$show_sql        = $phpAds_config['type_sql_allow'];
$show_web        = $phpAds_config['type_web_allow'];
$show_url        = $phpAds_config['type_url_allow'];
$show_html       = $phpAds_config['type_html_allow'];
$show_txt       = $phpAds_config['type_txt_allow'];

if (isset($storagetype) && $storagetype == "sql")        $show_sql     = true;
if (isset($storagetype) && $storagetype == "web")      $show_web     = true;
if (isset($storagetype) && $storagetype == "url")      $show_url     = true;
if (isset($storagetype) && $storagetype == "html")     $show_html    = true;
if (isset($storagetype) && $storagetype == "txt")      $show_txt     = true;

// If adding a new banner or used storing type is disabled
// determine which bannertype to show as default

if (!isset($storagetype))
{
    if ($show_txt)     $storagetype = "txt"; 
    if ($show_html)    $storagetype = "html"; 
    if ($show_url)     $storagetype = "url"; 
    if ($show_web)     $storagetype = "web"; 
    if ($show_sql)     $storagetype = "sql"; 
}

if (phpAds_isUser(phpAds_Client) && !phpAds_isAllowed(phpAds_ModifyBanner)) {
    $input_disable = ' DISABLED ';
} else {
    $input_disable = ''; 
}

$tabindex = 1;

if (!isset($bannerid) || $bannerid == '')
{
    echo "<form action='banner-edit.php' method='POST' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='clientid' value='".$clientid."'>";
    echo "<input type='hidden' name='campaignid' value='".$campaignid."'>";
    echo "<input type='hidden' name='bannerid' value='".$bannerid."'>";
    
    echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
    echo "<tr><td height='25' colspan='3'><b>".$strChooseBanner."</b></td></tr>";
    echo "<tr><td height='25'>";
    echo "<select name='storagetype' onChange='this.form.submit();' accesskey='".$keyList."' tabindex='".($tabindex++)."'>";
    
    if ($show_sql)     echo "<option value='sql'".($storagetype == "sql" ? ' selected' : '').">".$strMySQLBanner."</option>";
    if ($show_web)        echo "<option value='web'".($storagetype == "web" ? ' selected' : '').">".$strWebBanner."</option>";
    if ($show_url)        echo "<option value='url'".($storagetype == "url" ? ' selected' : '').">".$strURLBanner."</option>";
    if ($show_html)    echo "<option value='html'".($storagetype == "html" ? ' selected' : '').">".$strHTMLBanner."</option>";
    if ($show_txt)     echo "<option value='txt'".($storagetype == "txt" ? ' selected' : '').">".$strTextBanner."</option>";
    
    echo "</select>";
    echo "</td></tr></table>";
    phpAds_ShowBreak();
    echo "</form>";
    
}


?>

<script language='JavaScript'>
<!--
    
    function selectFile(o)
    {
        var filename = o.value.toLowerCase();
        var swflayer = findObj ('swflayer');
        var editbanner = findObj ('editbanner');
        
        // Show SWF Layer
        if (swflayer)
        {
            if (filename.indexOf('swf') + 3 == filename.length)
                swflayer.style.display = '';
            else
                swflayer.style.display = 'none';
        }
        
        // Check upload option
        if (o.name == 'upload' && editbanner.replaceimage[1] && o.value != '')
            editbanner.replaceimage[1].checked = true;
        if (o.name == 'uploadalt' && editbanner.replacealtimage[1] && o.value != '')
            editbanner.replacealtimage[1].checked = true;
    }
    
    function alterHtmlCheckbox() {
    
        if (editbanner.autohtml.checked) {
            editbanner.adserver.disabled = false;
        } else {
            editbanner.adserver.disabled = true;        
        }
    
    }
    
//-->
</script>

<?php


echo "<form id='editbanner' action='banner-edit.php' method='POST' enctype='multipart/form-data'>";
echo "<input type='hidden' name='clientid' value='".$clientid."'>";
echo "<input type='hidden' name='campaignid' value='".$campaignid."'>";
echo "<input type='hidden' name='bannerid' value='".$bannerid."'>";
echo "<input type='hidden' name='storagetype' value='".$storagetype."'>";


if ($storagetype == 'sql')
{
    echo "<br><table border='0' width='100%' cellpadding='0' cellspacing='0' bgcolor='#F6F6F6'>";
    echo "<tr><td height='25' colspan='3' bgcolor='#FFFFFF'><img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;<b>".$strMySQLBanner."</b></td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    
    if (isset($row['filename']) && $row['filename'] != '')
    {
        echo "<tr><td width='30'>&nbsp;</td>";
        echo "<td width='200' valign='top'>".$strUploadOrKeep."</td>";
        echo "<td><table cellpadding='0' cellspacing='0' border='0'>";
        echo "<tr valign='top'><td><input type='radio' name='replaceimage' value='f' checked tabindex='".($tabindex++)."' $input_disable></td><td>&nbsp;";
        
        switch ($row['contenttype'])
        {
            case 'swf':  echo "<img src='images/icon-filetype-swf.gif' align='absmiddle'> ".$row['filename']; break;
            case 'dcr':  echo "<img src='images/icon-filetype-swf.gif' align='absmiddle'> ".$row['filename']; break;
            case 'jpeg': echo "<img src='images/icon-filetype-jpg.gif' align='absmiddle'> ".$row['filename']; break;
            case 'gif':  echo "<img src='images/icon-filetype-gif.gif' align='absmiddle'> ".$row['filename']; break;
            case 'png':  echo "<img src='images/icon-filetype-png.gif' align='absmiddle'> ".$row['filename']; break;
            case 'rpm':  echo "<img src='images/icon-filetype-rpm.gif' align='absmiddle'> ".$row['filename']; break;
            case 'mov':  echo "<img src='images/icon-filetype-mov.gif' align='absmiddle'> ".$row['filename']; break;
            default:     echo "<img src='images/icon-banner-stored.gif' align='absmiddle'> ".$row['filename']; break;
        }
        
        $size = phpAds_ImageSize($storagetype, $row['filename']);
        if (round($size / 1024) == 0)
            echo " <i dir='".$phpAds_TextDirection."'>(".$size." bytes)</i>";
        else
            echo " <i dir='".$phpAds_TextDirection."'>(".round($size / 1024)." Kb)</i>";
        
        echo "</td></tr>";
        if (!phpAds_isUser(phpAds_Client) || phpAds_isAllowed(phpAds_ModifyBanner)) {
            echo "<tr valign='top'><td><input type='radio' name='replaceimage' value='t' tabindex='".($tabindex++)."'></td>";
            echo "<td>&nbsp;<input class='flat' size='26' type='file' name='upload' style='width:250px;' onChange='selectFile(this);' tabindex='".($tabindex++)."'>";
        
            echo "<div id='swflayer' style='display:none;'>";
            echo "<input type='checkbox' name='checkswf' value='t' checked tabindex='".($tabindex++)."'>&nbsp;".$strCheckSWF;
            echo "</div>";
        
            echo "</td></tr>";
        }
        echo "</table><br><br></td></tr>";
        echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
        echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    }
    else
    {
        echo "<input type='hidden' name='replaceimage' value='t'>";
        
        echo "<tr><td width='30'>&nbsp;</td>";
        echo "<td width='200' valign='top'>".$strNewBannerFile."</td>";
        echo "<td><input class='flat' size='26' type='file' name='upload' style='width:350px;' onChange='selectFile(this);' tabindex='".($tabindex++)."'>";
        
        echo "<div id='swflayer' style='display:none;'>";
        echo "<input type='checkbox' name='checkswf' value='t' checked tabindex='".($tabindex++)."'>&nbsp;".$strCheckSWF;
        echo "</div>";
        
        echo "<br><br></td></tr>";
        echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
        echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    }
    
    if (count($hardcoded_links) == 0)
    {
        echo "<tr><td width='30'>&nbsp;</td>";
        echo "<td width='200'>".$strURL."</td>";
        echo "<td><input class='flat' size='35' type='text' name='url' style='width:350px;' dir='ltr' value='".phpAds_htmlQuotes($row["url"])."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
        
        echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
        echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
        
        echo "<tr><td width='30'>&nbsp;</td>";
        echo "<td width='200'>".$strTarget."</td>";
        echo "<td><input class='flat' size='16' type='text' name='target' style='width:150px;' dir='ltr' value='".$row["target"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    }
    else
    {
        $i = 0;
        
        while (list($key, $val) = each($hardcoded_links))
        {
            if ($i > 0)
            {
                echo "<tr><td height='20' colspan='3'>&nbsp;</td></tr>";
                echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
                echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
            }
            
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$strURL."</td>";
            echo "<td><input class='flat' size='35' type='text' name='alink[".$key."]' style='width:330px;' dir='ltr' value='".phpAds_htmlQuotes($val)."' tabindex='".($tabindex++)."' $input_disable>";
            echo "<input type='radio' name='alink_chosen' value='".$key."'".($val == $row['url'] ? ' checked' : '')." tabindex='".($tabindex++)."' $input_disable></td></tr>";
            
            if (isset($hardcoded_targets[$key]))
            {
                echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
                echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
                
                echo "<tr><td width='30'>&nbsp;</td>";
                echo "<td width='200'>".$strTarget."</td>";
                echo "<td><input class='flat' size='16' type='text' name='atar[".$key."]' style='width:150px;' dir='ltr' value='".phpAds_htmlQuotes($hardcoded_targets[$key])."' tabindex='".($tabindex++)."' $input_disable>";
                echo "</td></tr>";
            }
            
            if (count($hardcoded_links) > 1)
            {
                echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
                echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
                
                echo "<tr><td width='30'>&nbsp;</td>";
                echo "<td width='200'>".$strOverwriteSource."</td>";
                echo "<td><input class='flat' size='50' type='text' name='asource[".$key."]' style='width:150px;' dir='ltr' value='".phpAds_htmlQuotes($hardcoded_sources[$key])."' tabindex='".($tabindex++)."' $input_disable>";
                echo "</td></tr>";
            }
            
            $i++;
        }
        
        echo "<input type='hidden' name='url' value='".$row['url']."'>";
    }
    
    echo "<tr><td height='30' colspan='3'>&nbsp;</td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strAlt."</td>";
    echo "<td><input class='flat' size='35' type='text' name='alt' style='width:350px;' value='".$row["alt"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
    echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strStatusText."</td>";
    echo "<td><input class='flat' size='35' type='text' name='status' style='width:350px;' value='".phpAds_htmlQuotes($row["status"])."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
    echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strTextBelow."</td>";
    echo "<td><input class='flat' size='35' type='text' name='bannertext' style='width:350px;' value='".$row["bannertext"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    
    if (isset($bannerid) && $bannerid != '')
    {
        echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
        echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
        
        echo "<tr><td width='30'>&nbsp;</td>";
        echo "<td width='200'>".$strSize."</td>";
        echo "<td>".$strWidth.": <input class='flat' size='5' type='text' name='width' value='".$row["width"]."' tabindex='".($tabindex++)."' $input_disable>&nbsp;&nbsp;&nbsp;";
        echo $strHeight.": <input class='flat' size='5' type='text' name='height' value='".$row["height"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    }
    
    echo "<tr><td height='20' colspan='3'>&nbsp;</td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "</table>";
}

if ($storagetype == 'web')
{
    echo "<br><table border='0' width='100%' cellpadding='0' cellspacing='0' bgcolor='#F6F6F6'>";
    if ($message != "") {
        if ($message == 'invalidFileType') {
            $message = 'you have submitted an invalid file type, please check and resubmit';
        }
        echo "<div class='errormessage' style='width: 500px;'><img class='errormessage' src='images/errormessage.gif' align='absmiddle'>";
        echo "<span class='tab-r'>$message</span></div>";
    }
    echo "<tr><td height='25' colspan='3' bgcolor='#FFFFFF'><img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;<b>".$strWebBanner."</b></td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    
    if (isset($row['filename']) && $row['filename'] != '')
    {
        echo "<tr><td width='30'>&nbsp;</td>";
        echo "<td width='200' valign='top'>".$strUploadOrKeep."</td>";
        echo "<td><table cellpadding='0' cellspacing='0' border='0'>";
        echo "<tr valign='top'><td><input type='radio' name='replaceimage' value='f' checked tabindex='".($tabindex++)."' $input_disable></td><td>&nbsp;";
        
        switch ($row['contenttype'])
        {
            case 'swf':  echo "<img src='images/icon-filetype-swf.gif' align='absmiddle'> ".$row['filename']; break;
            case 'dcr':  echo "<img src='images/icon-filetype-swf.gif' align='absmiddle'> ".$row['filename']; break;
            case 'jpeg': echo "<img src='images/icon-filetype-jpg.gif' align='absmiddle'> ".$row['filename']; break;
            case 'gif':  echo "<img src='images/icon-filetype-gif.gif' align='absmiddle'> ".$row['filename']; break;
            case 'png':  echo "<img src='images/icon-filetype-png.gif' align='absmiddle'> ".$row['filename']; break;
            case 'rpm':  echo "<img src='images/icon-filetype-rpm.gif' align='absmiddle'> ".$row['filename']; break;
            case 'mov':  echo "<img src='images/icon-filetype-mov.gif' align='absmiddle'> ".$row['filename']; break;
            default:     echo "<img src='images/icon-banner-stored.gif' align='absmiddle'> ".$row['filename']; break;
        }
        
        $size = phpAds_ImageSize($storagetype, $row['filename']);
        if (round($size / 1024) == 0)
            echo " <i>(".$size." bytes)</i>";
        else
            echo " <i>(".round($size / 1024)." Kb)</i>";
        
        echo "</td></tr>";
        if (!phpAds_isUser(phpAds_Client) || phpAds_isAllowed(phpAds_ModifyBanner)) {
            echo "<tr valign='top'><td><input type='radio' name='replaceimage' value='t' tabindex='".($tabindex++)."'></td>";
            echo "<td>&nbsp;<input class='flat' size='26' type='file' name='upload' style='width:250px;' onChange='selectFile(this);' tabindex='".($tabindex++)."'>";
        
            echo "<div id='swflayer' style='display:none;'>";
            echo "<input type='checkbox' name='checkswf' value='t' checked tabindex='".($tabindex++)."'>&nbsp;".$strCheckSWF;
            echo "</div>";
        
            echo "</td></tr>";
        }
        echo "</table><br><br></td></tr>";
        echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
        echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    }
    else
    {
        echo "<input type='hidden' name='replaceimage' value='t'>";
        echo "<tr><td width='30'>&nbsp;</td>";
        echo "<td width='200' valign='top'>".$strNewBannerFile."</td>";
        echo "<td><input class='flat' size='26' type='file' name='upload' style='width:350px;' onChange='selectFile(this);' tabindex='".($tabindex++)."'>";
        
        echo "<div id='swflayer' style='display:none;'>";
        echo "<input type='checkbox' name='checkswf' value='t' checked tabindex='".($tabindex++)."'>&nbsp;".$strCheckSWF;
        echo "</div>";
        
        echo "<br><br></td></tr>";
        echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
        echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    }
    
    if ($row['contenttype'] == 'swf')
    {
        if (isset($row['alt_filename']) && $row['alt_filename'] != '')
        {
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200' valign='top'>$strUploadOrKeepAlt</td>";
            echo "<td><table cellpadding='0' cellspacing='0' border='0'>";
            echo "<tr valign='top'><td><input type='radio' name='replacealtimage' value='f' checked tabindex='".($tabindex++)."' $input_disable></td><td>&nbsp;";
            
            echo "<img src='images/icon-filetype-gif.gif' align='absmiddle'> ".$row['alt_filename'];
            
            $size = phpAds_ImageSize($storagetype, $row['alt_filename']);
            if (round($size / 1024) == 0)
                echo " <i>(".$size." bytes)</i>";
            else
                echo " <i>(".round($size / 1024)." Kb)</i>";
            
            echo "</td></tr>";
            if (!phpAds_isUser(phpAds_Client) || phpAds_isAllowed(phpAds_ModifyBanner)) {
                echo "<tr valign='top'><td><input type='radio' name='replacealtimage' value='t' tabindex='".($tabindex++)."'></td>";
                echo "<td>&nbsp;<input class='flat' size='26' type='file' name='uploadalt' style='width:250px;' onChange='selectFile(this);' tabindex='".($tabindex++)."'>";
            
                echo "</td></tr>";
            }
            echo "</table><br><br></td></tr>";
            echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
            echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
        }
        else
        {
            if (!phpAds_isUser(phpAds_Client) || phpAds_isAllowed(phpAds_ModifyBanner)) {
                echo "<input type='hidden' name='replacealtimage' value='t'>";
                echo "<tr><td width='30'>&nbsp;</td>";
                echo "<td width='200' valign='top'>".$strNewBannerFileAlt."</td>";
                echo "<td><input class='flat' size='26' type='file' name='uploadalt' style='width:350px;' onChange='selectFile(this);' tabindex='".($tabindex++)."'>";
                
                echo "<br><br></td></tr>";
                echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
                echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
            }
        }
    }
    
    if (count($hardcoded_links) == 0)
    {
        echo "<tr><td width='30'>&nbsp;</td>";
        echo "<td width='200'>".$strURL."</td>";
        echo "<td><input class='flat' size='35' type='text' name='url' style='width:350px;' dir='ltr' value='".phpAds_htmlQuotes($row["url"])."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
        
        echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
        echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
        
        echo "<tr><td width='30'>&nbsp;</td>";
        echo "<td width='200'>".$strTarget."</td>";
        echo "<td><input class='flat' size='16' type='text' name='target' style='width:150px;' dir='ltr' value='".$row["target"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    }
    else
    {
        $i = 0;
        
        while (list($key, $val) = each($hardcoded_links))
        {
            if ($i > 0)
            {
                echo "<tr><td height='20' colspan='3'>&nbsp;</td></tr>";
                echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
                echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
            }
            
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$strURL."</td>";
            echo "<td><input class='flat' size='35' type='text' name='alink[".$key."]' style='width:330px;' dir='ltr' value='".phpAds_htmlQuotes($val)."' tabindex='".($tabindex++)."' $input_disable>";
            echo "<input type='radio' name='alink_chosen' value='".$key."'".($val == $row['url'] ? ' checked' : '')." tabindex='".($tabindex++)."' $input_disable></td></tr>";
            
            if (isset($hardcoded_targets[$key]))
            {
                echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
                echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
                
                echo "<tr><td width='30'>&nbsp;</td>";
                echo "<td width='200'>".$strTarget."</td>";
                echo "<td><input class='flat' size='16' type='text' name='atar[".$key."]' style='width:150px;' dir='ltr' value='".phpAds_htmlQuotes($hardcoded_targets[$key])."' tabindex='".($tabindex++)."' $input_disable>";
                echo "</td></tr>";
            }
            
            echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
            echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
            
            echo "<tr><td width='30'>&nbsp;</td>";
            echo "<td width='200'>".$strOverwriteSource."</td>";
            echo "<td><input class='flat' size='50' type='text' name='asource[".$key."]' style='width:150px;' dir='ltr' value='".phpAds_htmlQuotes($hardcoded_sources[$key])."' tabindex='".($tabindex++)."' $input_disable>";
            echo "</td></tr>";
            
            $i++;
        }
        
        echo "<input type='hidden' name='url' value='".$row['url']."'>";
    }
    
    echo "<tr><td height='30' colspan='3'>&nbsp;</td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strAlt."</td>";
    echo "<td><input class='flat' size='35' type='text' name='alt' style='width:350px;' value='".$row["alt"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
    echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strStatusText."</td>";
    echo "<td><input class='flat' size='35' type='text' name='status' style='width:350px;' value='".phpAds_htmlQuotes($row["status"])."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
    echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strTextBelow."</td>";
    echo "<td><input class='flat' size='35' type='text' name='bannertext' style='width:350px;' value='".$row["bannertext"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    
    if (isset($bannerid) && $bannerid != '')
    {
        echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
        echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
        
        echo "<tr><td width='30'>&nbsp;</td>";
        echo "<td width='200'>".$strSize."</td>";
        echo "<td>".$strWidth.": <input class='flat' size='5' type='text' name='width' value='".$row["width"]."' tabindex='".($tabindex++)."' $input_disable>&nbsp;&nbsp;&nbsp;";
        echo $strHeight.": <input class='flat' size='5' type='text' name='height' value='".$row["height"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    }
    
    echo "<tr><td height='20' colspan='3'>&nbsp;</td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "</table>";
}

if ($storagetype == 'url')
{
    echo "<br><table border='0' width='100%' cellpadding='0' cellspacing='0' bgcolor='#F6F6F6'>";
    echo "<tr><td height='25' colspan='3' bgcolor='#FFFFFF'><img src='images/icon-banner-url.gif' align='absmiddle'>&nbsp;<b>".$strURLBanner."</b></td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strNewBannerURL."</td>";
    echo "<td><input class='flat' size='35' type='text' name='imageurl' style='width:350px;' dir='ltr' value='".phpAds_htmlQuotes($row["imageurl"])."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    
    echo "<tr><td height='30' colspan='3'>&nbsp;</td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strURL."</td>";
    echo "<td><input class='flat' size='35' type='text' name='url' style='width:350px;' dir='ltr' value='".phpAds_htmlQuotes($row["url"])."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
    echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strTarget."</td>";
    echo "<td><input class='flat' size='16' type='text' name='target' style='width:150px;' dir='ltr' value='".$row["target"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    
    echo "<tr><td height='30' colspan='3'>&nbsp;</td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strAlt."</td>";
    echo "<td><input class='flat' size='35' type='text' name='alt' style='width:350px;' value='".$row["alt"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
    echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strStatusText."</td>";
    echo "<td><input class='flat' size='35' type='text' name='status' style='width:350px;' value='".phpAds_htmlQuotes($row["status"])."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
    echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strTextBelow."</td>";
    echo "<td><input class='flat' size='35' type='text' name='bannertext' style='width:350px;' value='".$row["bannertext"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
    echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strSize."</td>";
    echo "<td>".$strWidth.": <input class='flat' size='5' type='text' name='width' value='".$row["width"]."' tabindex='".($tabindex++)."' $input_disable>&nbsp;&nbsp;&nbsp;";
    echo $strHeight.": <input class='flat' size='5' type='text' name='height' value='".$row["height"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    
    echo "<tr><td height='20' colspan='3'>&nbsp;</td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "</table>";
}

if ($storagetype == 'html')
{
    echo "<br><table border='0' width='100%' cellpadding='0' cellspacing='0' bgcolor='#F6F6F6'>";
    echo "<tr><td height='25' colspan='3' bgcolor='#FFFFFF'><img src='images/icon-banner-html.gif' align='absmiddle'>&nbsp;<b>".$strHTMLBanner."</b></td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td colspan='2'><textarea class='code' cols='45' rows='10' name='banner' wrap='off' dir='ltr' style='width:550px;";
    echo "' tabindex='".($tabindex++)."' $input_disable>".htmlentities($row['htmltemplate'])."</textarea></td></tr>";
    
    // checkbox and dropdown list allowing user to choose whether to alter the html so it can be tracked by other ad servers
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td colspan='2'>";
    echo "<table><tr>";
    echo "<td><img src='admin/images/spacer.gif' height='1' width='250'></td>";
    echo "<td><img src='admin/images/spacer.gif' height='1' width='280'></td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td><input type='checkbox' onClick='alterHtmlCheckbox()' name='autohtml' value='t'".(!isset($row["autohtml"]) || $row["autohtml"] == 't' ? ' checked' : '')." tabindex='".($tabindex++)."' $input_disable> ".$strAutoChangeHTML."</td>";
    echo "<td align='right'><select name='adserver' $input_disable>";
    echo "<option value='' " . ($row["adserver"] == '' ? 'selected' : '') . " >Generic HTML banner</option>";
    echo "<option value='max' " . ($row["adserver"] == 'max' ? 'selected' : '') . " >Rich Media - M3 Max Media Manager</option>";
    echo "<option value='atlas' " . ($row["adserver"] == 'atlas' ? 'selected' : '') . " >Rich Media - Atlas</option>";
    echo "<option value='bluestreak' " . ($row["adserver"] == 'bluestreak' ? 'selected' : '') . " >Rich Media - Bluestreak</option>";
    echo "<option value='doubleclick' " . ($row["adserver"] == 'doubleclick' ? 'selected' : '') . " >Rich Media - DoubleClick</option>";
    echo "<option value='eyeblaster' " . ($row["adserver"] == 'eyeblaster' ? 'selected' : '') . " >Rich Media - Eyeblaster</option>";
    echo "<option value='mediaplex' " . ($row["adserver"] == 'mediaplex' ? 'selected' : '') . " >Rich Media - Mediaplex</option>";
    echo "<option value='tangozebra' " . ($row["adserver"] == 'tangozebra' ? 'selected' : '') . " >Rich Media - Tango Zebra</option>";
    echo "<option value='falk' " . ($row["adserver"] == 'falk' ? 'selected' : '') . " >Rich Media - Falk</option>";
    echo "</select></td>";
    echo "</tr></table>";
    echo "</td>";
    echo "</tr>";
    // end of modified section
    
    echo "<tr><td height='20' colspan='3'>&nbsp;</td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strURL."</td>";
    echo "<td><input class='flat' size='35' type='text' name='url' style='width:350px;' dir='ltr' value='".phpAds_htmlQuotes($row["url"])."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
    echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strTarget."</td>";
    echo "<td><input class='flat' size='35' type='text' name='target' style='width:350px;' dir='ltr' value='".$row["target"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
    echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strSize."</td>";
    echo "<td>".$strWidth.": <input class='flat' size='5' type='text' name='width' value='".$row["width"]."' tabindex='".($tabindex++)."' $input_disable>&nbsp;&nbsp;&nbsp;";
    echo $strHeight.": <input class='flat' size='5' type='text' name='height' value='".$row["height"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    
    echo "<tr><td height='20' colspan='3'>&nbsp;</td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "</table>";
}

if ($storagetype == 'txt')
{
    echo "<br><table border='0' width='100%' cellpadding='0' cellspacing='0' bgcolor='#F6F6F6'>";
    echo "<tr><td height='25' colspan='3' bgcolor='#FFFFFF'><img src='images/icon-banner-text.gif' align='absmiddle'>&nbsp;<b>".$strTextBanner."</b></td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td colspan='2'><textarea class='code' cols='45' rows='10' name='bannertext' wrap='off' style='width:550px; ";
    echo "' tabindex='".($tabindex++)."' $input_disable>".$row['bannertext']."</textarea></td></tr>";
    
    echo "<tr><td height='20' colspan='3'>&nbsp;</td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strURL."</td>";
    echo "<td><input class='flat' size='35' type='text' name='url' style='width:350px;' dir='ltr' value='".phpAds_htmlQuotes($row["url"])."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
    echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strTarget."</td>";
    echo "<td><input class='flat' size='16' type='text' name='target' style='width:150px;' dir='ltr' value='".$row["target"]."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    
    echo "<tr><td height='20' colspan='3'>&nbsp;</td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    
    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strStatusText."</td>";
    echo "<td><input class='flat' size='35' type='text' name='status' style='width:350px;' value='".phpAds_htmlQuotes($row["status"])."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
    
    echo "<tr><td height='20' colspan='3'>&nbsp;</td></tr>";
    echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    echo "</table>";
}

echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";

if ($phpAds_config['use_keywords'])
{
    if (phpAds_isUser(phpAds_Client)) { $keywords_disable = ' DISABLED '; }
    else { $keywords_disable = ''; }

    echo "<tr><td width='30'>&nbsp;</td>";
    echo "<td width='200'>".$strKeyword."</td>";
    echo "<td><input class='flat' size='35' type='text' name='keyword' style='width:350px;' value='".phpAds_htmlQuotes($row["keyword"])."' tabindex='".($tabindex++)."' $keywords_disable></td></tr>";
    echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
    echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
}
    
echo "<tr><td width='30'>&nbsp;</td>";
echo "<td width='200'>".$strDescription."</td>";
echo "<td><input class='flat' size='35' type='text' name='description' style='width:350px;' value='".phpAds_htmlQuotes($row["description"])."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";

echo "<tr><td width='30'>&nbsp;</td>";
echo "<td width='200'>".$strWeight."</td>";
echo "<td><input class='flat' size='6' type='text' name='weight' value='".(isset($row["weight"]) ? $row["weight"] : $phpAds_config['default_banner_weight'])."' tabindex='".($tabindex++)."' $input_disable></td></tr>";
echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
echo "<tr><td height='1' colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
echo "</table>";

echo "<br><br>";
if (!phpAds_isUser(phpAds_Client) || phpAds_isAllowed(phpAds_ModifyBanner)) {
    if (phpAds_isUser(phpAds_Client) && !phpAds_isAllowed(phpAds_ActivateBanner) && ($row['active'] == 't')) {
        $warn_deactivate = phpAds_DelConfirm($strConfirmDeactivate);
    } else { $warn_deactivate = ''; }
    
    echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
    echo "<tr><td height='35' colspan='3'><input type='submit' name='submit' value='".$strSaveChanges."' tabindex='".($tabindex++)."' $warn_deactivate></td></tr>";
    echo "</table>";
}
echo "</form>";

echo "<br><br>";
echo "<br><br>";



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

function _handleUploadedFile($name, $storageType, $imageOnly=false)
{
    // Set some default parameters
    $aFile = array();
    $aFile['filename'] = '';
    $aFile['contenttype'] = '';
    $aFile['editswf'] = false;
    $aFile['pluginversion'] = 0;
    $aFile['width'] = 0;
    $aFile['height'] = 0;
    
    if (!empty($_FILES[$name]['name']) && $_FILES[$name]['tmp_name'] != 'none') {
        $uploaded = $_FILES[$name];
        // Store the uploaded file
        _storeUploadedFile($uploaded);
        // Set some parameters of the file...
        $aFile['width'] = $uploaded['width'];
        $aFile['height'] = $uploaded['height'];
        $aFile['contenttype'] = _getFileContentType($uploaded['name']);
        // Set Flash-specific features
        if ($aFile['contenttype'] == 'swf') {
            $aFlashFile = _handleFlashFile($uploaded);
            $aFile = array_merge($aFile, $aFlashFile);
        }
        
        $aFile['filename'] = phpAds_ImageStore($storageType, basename(stripslashes($uploaded['name'])), $uploaded['buffer']);
    }
    
    return $aFile;
}

function _handleFlashFile($uploaded)
{
    $aFile = array();
    // Get dimensions of Flash file
    list ($aFile['width'], $aFile['height']) = phpAds_SWFDimensions($uploaded['buffer']);
    $aFile['pluginversion'] = phpAds_SWFVersion($uploaded['buffer']);
    // Check if the Flash banner includes hard coded urls
    $aFile['editswf'] = ($aFile['pluginversion'] >= 3 && phpAds_SWFInfo($uploaded['buffer']));
    
    return $aFile;
}

function _getBannerContentType($aVariables, $alt=false)
{
    $contentType = '';
    $storageType = $aVariables['storagetype'];
    
    switch ($storageType) {
        case 'html' :
            $contentType = $alt ? '' : 'html';
            break;
        case 'url' :
            $contentType = $alt ? '' : _getFileContentType($aVariables['imageurl']);
            break;
        case 'txt' :
            $contentType = 'txt';
            break;
        default :
            $fileName = $alt ? $aVariables['alt_filename'] : $aVariables['filename'];
            $contentType = _getFileContentType($fileName, $alt);
    }
    
    return $contentType;
}
function _getFileContentType($fileName, $alt=false)
{
    $contentType = '';
    
    $ext = substr($fileName, strrpos($fileName, '.') + 1);
    switch (strtolower($ext)) {
        case 'jpeg': $contentType = 'jpeg'; break;
        case 'jpg':  $contentType = 'jpeg'; break;
        case 'png':  $contentType = 'png';  break;
        case 'gif':  $contentType = 'gif';  break;
        case 'swf':  $contentType = $alt ? '' : 'swf';  break;
        case 'dcr':  $contentType = $alt ? '' : 'dcr';  break;
        case 'rpm':  $contentType = $alt ? '' : 'rpm';  break;
        case 'mov':  $contentType = $alt ? '' : 'mov';  break;
    }
    
    return $contentType;
}

function _storeUploadedFile(&$uploaded, $imageOnly=false)
{
    if (function_exists('is_uploaded_file')) {
        $upload_valid = @is_uploaded_file($uploaded['tmp_name']);
    } else {
        if (!$tmp_file = get_cfg_var('upload_tmp_dir')) {
            $tmp_file = tempnam('',''); 
            @unlink($tmp_file); 
            $tmp_file = dirname($tmp_file);
        }
        
        $tmp_file .= '/' . basename($uploaded['tmp_name']);
        $tmp_file = str_replace('\\', '/', $tmp_file);
        $tmp_file  = ereg_replace('/+', '/', $tmp_file);
        
        $up_file = str_replace('\\', '/', $uploaded['tmp_name']);
        $up_file = ereg_replace('/+', '/', $up_file);
        
        $upload_valid = ($tmp_file == $up_file);
    }
    

    $uploadError = true;
    $uploadErrorMessage = '';
    if (!$upload_valid) {
        $uploadErrorMessage = $GLOBALS['strErrorUploadSecurity'];
    } else {
        if (@file_exists ($uploaded['tmp_name'])) {
            // Read the contents of the file in a buffer
            if ($fp = @fopen($uploaded['tmp_name'], "rb")) {
                $uploaded['buffer'] = @fread($fp, @filesize($uploaded['tmp_name']));
                @fclose ($fp);
                $uploadError = false;
            }
            else
            {
                // Check if moving the file is possible
                if (function_exists("move_uploaded_file"))
                {
                    $tmp_dir = phpAds_path.'/misc/tmp/'.basename($uploaded['tmp_name']);
                    
                    // Try to move the file
                    if (@move_uploaded_file ($uploaded['tmp_name'], $tmp_dir))
                    {
                        $uploaded['tmp_name'] = $tmp_dir;
                        
                        // Try again if the file is readable
                        if ($fp = @fopen($uploaded['tmp_name'], "rb"))
                        {
                            $uploaded['buffer'] = @fread($fp, @filesize($uploaded['tmp_name']));
                            @fclose($fp);
                            $uploadError = false;
                        }
                    }
                }
            }
            
            if ($uploadError && empty($uploadErrorMessage)) {
                $uploadErrorMessage = $GLOBALS['strErrorUploadBasedir'];
            }
            
            // Determine width and height
            $size = @getimagesize($uploaded['tmp_name']);
            $uploaded['width'] = $size[0];
            $uploaded['height'] = $size[1];
        } else {
            $uploadErrorMessage = $GLOBALS['strErrorUploadUnknown'];
        }
    }
    
    if ($uploadError) {
        phpAds_PageHeader("1");
        phpAds_Die ('Error', $uploadErrorMessage);
    }
    
    // Remove temporary file
    if (@file_exists($uploaded['tmp_name']))
        @unlink ($uploaded['tmp_name']);
}
?>
