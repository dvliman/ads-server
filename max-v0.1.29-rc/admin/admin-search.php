<?php // $Revision: 1.0 

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
$Id: admin-search.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");


// Register input variables
phpAds_registerGlobal ('keyword', 'client', 'campaign', 'banner', 'zone', 'affiliate', 'compact');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency);


// Check Searchselection
if (!isset($client)) $client = false;
if (!isset($campaign)) $campaign = false;
if (!isset($banner)) $banner= false;
if (!isset($zone)) $zone = false;
if (!isset($affiliate)) $affiliate = false;


if ($client == false &&    $campaign == false &&
    $banner == false &&    $zone == false &&
    $affiliate == false)
{
    $client = true;
    $campaign = true;
    $banner = true;
    $zone = true;
    $affiliate = true;
}

if (!isset($compact))
    $compact = false;

if (!isset($keyword))
    $keyword = '';

?>

<html<?php echo $phpAds_TextDirection != 'ltr' ? " dir='".$phpAds_TextDirection."'" : '' ?>>
    <head>
        <title><?php echo strip_tags($strSearch); ?></title>
        <meta http-equiv='Content-Type' content='text/html<?php echo isset($phpAds_CharSet) && $phpAds_CharSet != "" ? "; charset=".$phpAds_CharSet : "" ?>'>
        <meta name='author' content='phpAdsNew - http://sourceforge.net/projects/phpadsnew'>
        <link rel='stylesheet' href='images/<?php echo $phpAds_TextDirection; ?>/interface.css'>
        <script language='JavaScript' src='interface.js'></script>
        <script language='JavaScript'>
        <!--
            function GoOpener(url, reload)
            {
                opener.location.href = url;
                
                if (reload == true)
                {
                    // Reload
                    document.search.submit();
                }
            }
        //-->
        </script>
    </head>
    
<body bgcolor='#FFFFFF' text='#000000' leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<!-- Top -->
<table width='100%' border='0' cellspacing='0' cellpadding='0'>
<?php
     if ($phpAds_config['name'] != "")
    {
        echo "<tr><td colspan='2' height='48' bgcolor='#000063' valign='middle'>";
        echo "<span class='phpAdsNew'>&nbsp;&nbsp;&nbsp;".$phpAds_config['name']." &nbsp;&nbsp;&nbsp;</span>";
    }
    else
    {
        echo "<tr><td colspan='2' height='48' bgcolor='#000063' valign='bottom'>";
        echo "<span class='phpAdsNew'>&nbsp;&nbsp;&nbsp;".$phpAds_productname."&nbsp;&nbsp;&nbsp;</span>";
    }
?>
</td></tr>

<!-- Spacer -->
<tr><td colspan='2' height='24' bgcolor='#000063'><img src='images/spacer.gif' height='1' width='1'></td></tr>

<!-- Tabbar -->
<tr>
    <td colspan='2' height='24' bgcolor="#000063"> 
        <table cellpadding='0' cellspacing='0' border='0' bgcolor='#FFFFFF' height='24'>
            <form name='search' action='admin-search.php' method='post'>
            <input type='hidden' name='client' value='<?php echo $client; ?>'>
            <input type='hidden' name='campaign' value='<?php echo $campaign; ?>'>
            <input type='hidden' name='banner' value='<?php echo $banner; ?>'>
            <input type='hidden' name='zone' value='<?php echo $zone; ?>'>
            <input type='hidden' name='affiliate' value='<?php echo $affiliate; ?>'>
            <input type='hidden' name='compact' value='<?php echo $compact; ?>'>
            <tr height='24'>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td class='tab-s' valign='bottom'><?php echo $strSearch; ?>:&nbsp;<input type='text' name='keyword' size='15' value='<?php print $keyword ?>' accesskey='<? print $keySearch ?>'>&nbsp;
                <input type='image' src='images/<?php echo $phpAds_TextDirection; ?>/go_blue.gif' border='0'></td>
                <td height='24'><img src='images/<?php echo $phpAds_TextDirection; ?>/tab-ew.gif' height='24' width='10'></td>
            </tr>
            </form>
        </table>
    </td>
</tr>
</table>

<br>

<!-- Search selection -->
<table width='100%' cellpadding='0' cellspacing='0' border='0'>
    <tr><td width='20'>&nbsp;</td><td>
    
    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
        <form name='searchselection' action='admin-search.php'>
        <input type='hidden' name='keyword' value='<?php echo $keyword; ?>'>
        <tr>
            <td nowrap><input type='checkbox' name='client' value='t'<?php echo ($client ? ' checked': ''); ?> onClick='this.form.submit()'>
                <?php echo $strClients; ?>&nbsp;&nbsp;&nbsp;</td>
            <td nowrap><input type='checkbox' name='campaign' value='t'<?php echo ($campaign ? ' checked': ''); ?> onClick='this.form.submit()'>
                <?php echo $strCampaign; ?>&nbsp;&nbsp;&nbsp;</td>
            <td nowrap><input type='checkbox' name='banner' value='t'<?php echo ($banner ? ' checked': ''); ?> onClick='this.form.submit()'>
                <?php echo $strBanners; ?>&nbsp;&nbsp;&nbsp;</td>
        </tr>
        <tr>
            <td nowrap><input type='checkbox' name='affiliate' value='t'<?php echo ($affiliate ? ' checked': ''); ?> onClick='this.form.submit()'>
                <?php echo $strAffiliates; ?>&nbsp;&nbsp;&nbsp;</td>
            <td nowrap><input type='checkbox' name='zone' value='t'<?php echo ($zone ? ' checked': ''); ?> onClick='this.form.submit()'>
                <?php echo $strZones; ?>&nbsp;&nbsp;&nbsp;</td>
            <td width='100%'>&nbsp;</td>
            <td nowrap align='right'><input type='checkbox' name='compact' value='t'<?php echo ($compact ? ' checked': ''); ?> onClick='this.form.submit()'>
                <?php echo $strCompact; ?>&nbsp;&nbsp;&nbsp;</td>
        </tr>
        </form>
    </table>

    </td><td width='20'>&nbsp;</td></tr>
</table>
    
<!-- Seperator -->
<img src='images/break-el.gif' height='1' width='100%' vspace='5'>
<br><br>

<!-- Search Results -->    
<table width='100%' cellpadding='0' cellspacing='0' border='0'>
<tr><td width='20'>&nbsp;</td><td>
    
<?php

    $whereAffiliate = is_numeric($keyword) ? " OR a.affiliateid=$keyword" : '';
    $whereBanner = is_numeric($keyword) ? " OR b.bannerid=$keyword" : '';
    $whereBannerKeyword = $phpAds_config['use_keywords'] ? " OR b.keyword LIKE '%$keyword%'" : '';
    $whereCampaign = is_numeric($keyword) ? " OR m.campaignid=$keyword" : '';
    $whereClient = is_numeric($keyword) ? " OR c.clientid=$keyword" : '';
    $whereZone = is_numeric($keyword) ? " OR z.zoneid=$keyword" : '';
    
    $query_clients = "
        SELECT
            c.clientid AS clientid,
            c.clientname AS clientname
        FROM 
            {$phpAds_config['tbl_clients']} AS c
        WHERE
            c.clientname LIKE '%$keyword%'
            $whereClient
    ";

    $query_campaigns = "
        SELECT
            m.campaignid AS campaignid,
            m.campaignname AS campaignname,
            m.clientid AS clientid
        FROM
            {$phpAds_config['tbl_campaigns']} AS m,
            {$phpAds_config['tbl_clients']} AS c
        WHERE
            m.clientid=c.clientid
            AND (m.campaignname LIKE '%$keyword%'
                $whereCampaign)
    ";

    $query_banners = "
        SELECT
            b.bannerid as bannerid,
            b.description as description,
            b.alt as alt,
            b.keyword as keyword,
            b.campaignid as campaignid,
            m.clientid as clientid
        FROM
            {$phpAds_config['tbl_banners']} AS b,
            {$phpAds_config['tbl_campaigns']} AS m,
            {$phpAds_config['tbl_clients']} AS c
        WHERE
            m.clientid=c.clientid
            AND b.campaignid=m.campaignid
            AND (b.alt LIKE '%$keyword%'
                OR b.description LIKE '%$keyword%'
                $whereBanner
                $whereBannerKeyword)
    ";
    
    $query_affiliates = "
        SELECT
            a.affiliateid AS affiliateid,
            a.name AS name
        FROM
            {$phpAds_config['tbl_affiliates']} AS a
        WHERE
            a.name LIKE '%$keyword%'
            $whereAffiliate
    ";

    $query_zones = "
        SELECT
            z.zoneid AS zoneid,
            z.zonename AS zonename,
            z.description AS description,
            a.affiliateid AS affiliateid
        FROM
            {$phpAds_config['tbl_zones']} AS z,
            {$phpAds_config['tbl_affiliates']} AS a
        WHERE
            z.affiliateid=a.affiliateid
            AND (z.zonename LIKE '%$keyword%'
            OR description LIKE '%$keyword%'
            $whereZone)
    ";
    if (phpAds_isUser(phpAds_Agency)) {
        $agencyId = phpAds_getAgencyID();
        $query_clients .= " AND c.agencyid=$agencyId";
        $query_campaigns .= " AND c.agencyid=$agencyId";
        $query_banners .= " AND c.agencyid=$agencyId";
        $query_affiliates .= " AND a.agencyid=$agencyId";
        $query_zones .= " AND a.agencyid=$agencyId";
    }
    
    $res_clients = phpAds_dbQuery($query_clients) or phpAds_sqlDie();
    $res_campaigns = phpAds_dbQuery($query_campaigns) or phpAds_sqlDie();
    $res_banners = phpAds_dbQuery($query_banners) or phpAds_sqlDie();
    $res_affiliates = phpAds_dbQuery($query_affiliates) or phpAds_sqlDie();
    $res_zones = phpAds_dbQuery($query_zones) or phpAds_sqlDie();
    
    
    if (phpAds_dbNumRows($res_clients) > 0 ||
        phpAds_dbNumRows($res_campaigns) > 0 ||
        phpAds_dbNumRows($res_banners) > 0 ||
        phpAds_dbNumRows($res_affiliates) > 0 ||
        phpAds_dbNumRows($res_zones) > 0)
    {
        echo "<table width='100%' border='0' align='center' cellspacing='0' cellpadding='0'>";
        echo "<tr height='25'>";
        echo "<td height='25'><b>&nbsp;&nbsp;".$GLOBALS['strName']."</b></td>";
        echo "<td height='25'><b>".$GLOBALS['strID']."</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>";
        echo "<td height='25'>&nbsp;</td>";
        echo "<td height='25'>&nbsp;</td>";
        echo "<td height='25'>&nbsp;</td>";
        echo "</tr>";
        
        echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    }
    
    
    $i=0;
    
    
    if ($client && phpAds_dbNumRows($res_clients) > 0)
    {
        while ($row_clients = phpAds_dbFetchArray($res_clients))
        {
            if ($i > 0) echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
            
            echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
            
            echo "<td height='25'>";
            echo "&nbsp;&nbsp;";
            echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;";
            echo "<a href='JavaScript:GoOpener(\"advertiser-edit.php?clientid=".$row_clients['clientid']."\")'>".$row_clients['clientname']."</a>";
            echo "</td>";
            
            echo "<td height='25'>".$row_clients['clientid']."</td>";
            
            // Empty
            echo "<td>&nbsp;</td>";
               
            // Button 1
            echo "<td height='25'>";
            echo "<a href='JavaScript:GoOpener(\"advertiser-campaigns.php?clientid=".$row_clients['clientid']."\")'><img src='images/icon-overview.gif' border='0' align='absmiddle' alt='$strOverview'>&nbsp;$strOverview</a>&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "</td>";
             
            // Button 2
            echo "<td height='25'>";
            echo "<a href='JavaScript:GoOpener(\"advertiser-delete.php?clientid=".$row_clients['clientid']."\", true)'".phpAds_DelConfirm($strConfirmDeleteClient)."><img src='images/icon-recycle.gif' border='0' align='absmiddle' alt='$strDelete'>&nbsp;$strDelete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "</td></tr>";
            
            
            
            if (!$compact)
            {
                $query_c_expand = "SELECT campaignid,campaignname FROM ".$phpAds_config['tbl_campaigns']." WHERE clientid=".$row_clients['clientid'];
                  $res_c_expand = phpAds_dbQuery($query_c_expand) or phpAds_sqlDie();
                
                while ($row_c_expand = phpAds_dbFetchArray($res_c_expand))
                {
                    echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break-el.gif' height='1' width='100%'></td></tr>";
                    
                    echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
                    
                    echo "<td height='25'>";
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;";
                    echo "<a href='JavaScript:GoOpener(\"campaign-edit.php?clientid=".$row_clients['clientid']."&campaignid=".$row_c_expand['campaignid']."\")'>".$row_c_expand['campaignname']."</a>";
                    echo "</td>";
                    
                    echo "<td height='25'>".$row_c_expand['campaignid']."</td>";
                    
                    // Empty
                    echo "<td>&nbsp;</td>";
                       
                    // Button 1
                    echo "<td height='25'>";
                    echo "<a href='JavaScript:GoOpener(\"campaign-banners.php?clientid=".$row_clients['clientid']."&campaignid=".$row_c_expand['campaignid']."\")'><img src='images/icon-overview.gif' border='0' align='absmiddle' alt='$strOverview'>&nbsp;$strOverview</a>&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "</td>";
                     
                    // Button 2
                    echo "<td height='25'>";
                    echo "<a href='JavaScript:GoOpener(\"campaign-delete.php?clientid=".$row_clients['clientid']."&campaignid=".$row_c_expand['campaignid']."\", true)'".phpAds_DelConfirm($strConfirmDeleteCampaign)."><img src='images/icon-recycle.gif' border='0' align='absmiddle' alt='$strDelete'>&nbsp;$strDelete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "</td></tr>";
                    
                    
                    
                    
                    $query_b_expand = "SELECT bannerid, campaignid, description, alt, keyword, storagetype FROM ".$phpAds_config['tbl_banners']." WHERE campaignid=".$row_c_expand['campaignid'];
                      $res_b_expand = phpAds_dbQuery($query_b_expand) or phpAds_sqlDie();
                    
                    while ($row_b_expand = phpAds_dbFetchArray($res_b_expand))
                    { 
                        $name = $strUntitled;
                        if (isset($row_b_expand['alt']) && $row_b_expand['alt'] != '') $name = $row_b_expand['alt'];
                        if (isset($row_b_expand['description']) && $row_b_expand['description'] != '') $name = $row_b_expand['description'];
                        
                        $name = phpAds_breakString ($name, '30');
                        
                        echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break-el.gif' height='1' width='100%'></td></tr>";
                        
                        echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
                        
                        echo "<td height='25'>";
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                        
                        if ($row_b_expand['storagetype'] == 'html')
                        {
                            echo "<img src='images/icon-banner-html.gif' align='absmiddle'>&nbsp;";
                        }
                        elseif ($row_b_expand['storagetype'] == 'url')
                        {
                            echo "<img src='images/icon-banner-url.gif' align='absmiddle'>&nbsp;";
                        }
                        else
                        {
                            echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;";
                        }
                        
                        echo "<a href='JavaScript:GoOpener(\"banner-edit.php?clientid=".$row_clients['clientid']."&campaignid=".$row_b_expand['campaignid']."&bannerid=".$row_b_expand['bannerid']."\")'>".$name."</a>";
                        echo "</td>";
                        
                        echo "<td height='25'>".$row_b_expand['bannerid']."</td>";
                        
                        // Empty
                        echo "<td>&nbsp;</td>";
                           
                        // Button 1
                        echo "<td height='25'>";
                        echo "<a href='JavaScript:GoOpener(\"banner-acl.php?clientid=".$row_clients['clientid']."&campaignid=".$row_b_expand['campaignid']."&bannerid=".$row_b_expand['bannerid']."\")'><img src='images/icon-acl.gif' border='0' align='absmiddle' alt='$strACL'>&nbsp;$strACL</a>&nbsp;&nbsp;&nbsp;&nbsp;";
                        echo "</td>";
                        
                        // Button 2
                        echo "<td height='25'>";
                        echo "<a href='JavaScript:GoOpener(\"banner-delete.php?clientid=".$row_clients['clientid']."&campaignid=".$row_b_expand['campaignid']."&bannerid=".$row_b_expand['bannerid']."\", true)'".phpAds_DelConfirm($strConfirmDeleteBanner)."><img src='images/icon-recycle.gif' border='0' align='absmiddle' alt='$strDelete'>&nbsp;$strDelete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
                        echo "</td></tr>";
                    }
                }
            }
            
            $i++;
        }
    }
    
    if ($campaign && phpAds_dbNumRows($res_campaigns) > 0)
    {;
        while ($row_campaigns = phpAds_dbFetchArray($res_campaigns))
        {
            if ($i > 0) echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
            
            echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
            
            echo "<td height='25'>";
            echo "&nbsp;&nbsp;";
            echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;";
            echo "<a href='JavaScript:GoOpener(\"campaign-edit.php?clientid=".$row_campaigns['clientid']."&campaignid=".$row_campaigns['campaignid']."\")'>".$row_campaigns['campaignname']."</a>";
            echo "</td>";
            
            echo "<td height='25'>".$row_campaigns['campaignid']."</td>";
            
            // Empty
            echo "<td>&nbsp;</td>";
               
            // Button 1
            echo "<td height='25'>";
            echo "<a href='JavaScript:GoOpener(\"campaign-banners.php?clientid=".$row_campaigns['clientid']."&campaignid=".$row_campaigns['campaignid']."\")'><img src='images/icon-overview.gif' border='0' align='absmiddle' alt='$strOverview'>&nbsp;$strOverview</a>&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "</td>";
             
            // Button 2
            echo "<td height='25'>";
            echo "<a href='JavaScript:GoOpener(\"campaign-delete.php?clientid=".$row_campaigns['clientid']."&campaignid=".$row_campaigns['campaignid']."\", true)'".phpAds_DelConfirm($strConfirmDeleteCampaign)."><img src='images/icon-recycle.gif' border='0' align='absmiddle' alt='$strDelete'>&nbsp;$strDelete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "</td></tr>";
            
            
            if (!$compact)
            {
                $query_b_expand = "SELECT bannerid,campaignid,description,alt,keyword,storagetype FROM ".$phpAds_config['tbl_banners']." WHERE campaignid=".$row_campaigns['campaignid'];
                  $res_b_expand = phpAds_dbQuery($query_b_expand) or phpAds_sqlDie();
                
                while ($row_b_expand = phpAds_dbFetchArray($res_b_expand))
                {
                    $name = $strUntitled;
                    if (isset($row_b_expand['alt']) && $row_b_expand['alt'] != '') $name = $row_b_expand['alt'];
                    if (isset($row_b_expand['description']) && $row_b_expand['description'] != '') $name = $row_b_expand['description'];
                    
                    $name = phpAds_breakString ($name, '30');
                    
                    echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break-el.gif' height='1' width='100%'></td></tr>";
                    
                    echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
                    
                    echo "<td height='25'>";
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    
                    if ($row_b_expand['storagetype'] == 'html')
                    {
                        echo "<img src='images/icon-banner-html.gif' align='absmiddle'>&nbsp;";
                    }
                    elseif ($row_b_expand['storagetype'] == 'url')
                    {
                        echo "<img src='images/icon-banner-url.gif' align='absmiddle'>&nbsp;";
                    }
                    else
                    {
                        echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;";
                    }
                    echo "<a href='JavaScript:GoOpener(\"banner-edit.php?clientid=".$row_campaigns['clientid']."&campaignid=".$row_b_expand['campaignid']."&bannerid=".$row_b_expand['bannerid']."\")'>".$name."</a>";
                    echo "</td>";
                    
                    echo "<td height='25'>".$row_b_expand['bannerid']."</td>";
                    
                    // Empty
                    echo "<td>&nbsp;</td>";
                       
                    // Button 1
                    echo "<td height='25'>";
                    echo "<a href='JavaScript:GoOpener(\"banner-acl.php?clientid=".$row_campaigns['clientid']."&campaignid=".$row_b_expand['campaignid']."&bannerid=".$row_b_expand['bannerid']."\")'><img src='images/icon-acl.gif' border='0' align='absmiddle' alt='$strACL'>&nbsp;$strACL</a>&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "</td>";
                    
                    // Button 2
                    echo "<td height='25'>";
                    echo "<a href='JavaScript:GoOpener(\"banner-delete.php?clientid=".$row_campaigns['clientid']."&bannerid=".$row_b_expand['bannerid']."&campaignid=".$row_b_expand['campaignid']."\", true)'".phpAds_DelConfirm($strConfirmDeleteBanner)."><img src='images/icon-recycle.gif' border='0' align='absmiddle' alt='$strDelete'>&nbsp;$strDelete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "</td></tr>";
                }
            }
            
            $i++;
        }
    }
    
    if ($banner && phpAds_dbNumRows($res_banners) > 0)
    {
        while ($row_banners = phpAds_dbFetchArray($res_banners))
        {
            $name = $strUntitled;
            if (isset($row_banners['alt']) && $row_banners['alt'] != '') $name = $row_banners['alt'];
            if (isset($row_banners['description']) && $row_banners['description'] != '') $name = $row_banners['description'];
            
            $name = phpAds_breakString ($name, '30');
            
            if ($i > 0) echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
            
            echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
            
            echo "<td height='25'>";
            echo "&nbsp;&nbsp;";
            
            if ($row_banners['storagetype'] == 'html')
            {
                echo "<img src='images/icon-banner-html.gif' align='absmiddle'>&nbsp;";
            }
            elseif ($row_banners['storagetype'] == 'url')
            {
                echo "<img src='images/icon-banner-url.gif' align='absmiddle'>&nbsp;";
            }
            else
            {
                echo "<img src='images/icon-banner-stored.gif' align='absmiddle'>&nbsp;";
            }
            echo "<a href='JavaScript:GoOpener(\"banner-edit.php?clientid=".$row_banners['clientid']."&campaignid=".$row_banners['campaignid']."&bannerid=".$row_banners['bannerid']."\")'>".$name."</a>";
            echo "</td>";
            
            echo "<td height='25'>".$row_banners['bannerid']."</td>";
            
            // Empty
            echo "<td>&nbsp;</td>";
               
            // Button 1
            echo "<td height='25'>";
            echo "<a href='JavaScript:GoOpener(\"banner-acl.php?clientid=".$row_banners['clientid']."&campaignid=".$row_banners['campaignid']."&bannerid=".$row_banners['bannerid']."\")'><img src='images/icon-acl.gif' border='0' align='absmiddle' alt='$strACL'>&nbsp;$strACL</a>&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "</td>";
            
            // Button 2
            echo "<td height='25'>";
            echo "<a href='JavaScript:GoOpener(\"banner-delete.php?clientid=".$row_banners['clientid']."&campaignid=".$row_banners['campaignid']."&bannerid=".$row_banners['bannerid']."\", true)'".phpAds_DelConfirm($strConfirmDeleteBanner)."><img src='images/icon-recycle.gif' border='0' align='absmiddle' alt='$strDelete'>&nbsp;$strDelete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "</td></tr>";
            
            $i++;
        }
    }
    
    if ($affiliate && phpAds_dbNumRows($res_affiliates) > 0)
    {
        while ($row_affiliates = phpAds_dbFetchArray($res_affiliates))
        {
            $name = $row_affiliates['name'];
            $name = phpAds_breakString ($name, '30');
            
            if ($i > 0) echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
            
            echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
            
            echo "<td height='25'>";
            echo "&nbsp;&nbsp;";
            
            echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;";
            echo "<a href='JavaScript:GoOpener(\"affiliate-edit.php?affiliateid=".$row_affiliates['affiliateid']."\")'>".$name."</a>";
            echo "</td>";
            
            echo "<td height='25'>".$row_affiliates['affiliateid']."</td>";
            
            // Empty
            echo "<td>&nbsp;</td>";
               
            // Button 1
            echo "<td height='25'>";
            echo "<a href='JavaScript:GoOpener(\"affiliate-zones.php?affiliateid=".$row_affiliates['affiliateid']."\")'><img src='images/icon-overview.gif' border='0' align='absmiddle' alt='$strOverview'>&nbsp;$strOverview</a>&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "</td>";
            
            // Button 2
            echo "<td height='25'>";
            echo "<a href='JavaScript:GoOpener(\"affiliate-delete.php?affiliateid=".$row_affiliates['affiliateid']."\", true)'".phpAds_DelConfirm($strConfirmDeleteAffiliate)."><img src='images/icon-recycle.gif' border='0' align='absmiddle' alt='$strDelete'>&nbsp;$strDelete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "</td></tr>";
            
            $i++;
            
            
            if (!$compact)
            {
                $query_z_expand = "SELECT affiliateid,zoneid,zonename,description FROM ".$phpAds_config['tbl_zones']." WHERE affiliateid=".$row_affiliates['affiliateid'];
                  $res_z_expand = phpAds_dbQuery($query_z_expand) or phpAds_sqlDie();
                
                while ($row_z_expand = phpAds_dbFetchArray($res_z_expand))
                {
                    $name = $row_z_expand['zonename'];
                    $name = phpAds_breakString ($name, '30');
                    
                    if ($i > 0) echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
                    
                    echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
                    
                    echo "<td height='25'>";
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    
                    echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;";
                    echo "<a href='JavaScript:GoOpener(\"zone-edit.php?affiliateid=".$row_z_expand['affiliateid']."&zoneid=".$row_z_expand['zoneid']."\")'>".$name."</a>";
                    echo "</td>";
                    
                    echo "<td height='25'>".$row_z_expand['zoneid']."</td>";
                    
                    // Empty
                    echo "<td>&nbsp;</td>";
                       
                    // Button 1
                    echo "<td height='25'>";
                    echo "<a href='JavaScript:GoOpener(\"zone-include.php?affiliateid=".$row_z_expand['affiliateid']."&zoneid=".$row_z_expand['zoneid']."\")'><img src='images/icon-zone-linked.gif' border='0' align='absmiddle' alt='$strIncludedBanners'>&nbsp;$strIncludedBanners</a>&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "</td>";
                    
                    // Button 2
                    echo "<td height='25'>";
                    echo "<a href='JavaScript:GoOpener(\"zone-delete.php?affiliateid=".$row_z_expand['affiliateid']."&zoneid=".$row_z_expand['zoneid']."\", true)'".phpAds_DelConfirm($strConfirmDeleteZone)."><img src='images/icon-recycle.gif' border='0' align='absmiddle' alt='$strDelete'>&nbsp;$strDelete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "</td></tr>";
                }
            }
        }
    }
    
    if ($zone && phpAds_dbNumRows($res_zones) > 0)
    {
        while ($row_zones = phpAds_dbFetchArray($res_zones))
        {
            $name = $row_zones['zonename'];
            $name = phpAds_breakString ($name, '30');
            
            if ($i > 0) echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break-l.gif' height='1' width='100%'></td></tr>";
            
            echo "<tr height='25' ".($i%2==0?"bgcolor='#F6F6F6'":"").">";
            
            echo "<td height='25'>";
            echo "&nbsp;&nbsp;";
            
            echo "<img src='images/icon-zone.gif' align='absmiddle'>&nbsp;";
            echo "<a href='JavaScript:GoOpener(\"zone-edit.php?affiliateid=".$row_zones['affiliateid']."&zoneid=".$row_zones['zoneid']."\")'>".$name."</a>";
            echo "</td>";
            
            echo "<td height='25'>".$row_zones['zoneid']."</td>";
            
            // Empty
            echo "<td>&nbsp;</td>";
               
            // Button 1
            echo "<td height='25'>";
            echo "<a href='JavaScript:GoOpener(\"zone-include.php?affiliateid=".$row_zones['affiliateid']."&zoneid=".$row_zones['zoneid']."\")'><img src='images/icon-zone-linked.gif' border='0' align='absmiddle' alt='$strIncludedBanners'>&nbsp;$strIncludedBanners</a>&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "</td>";
            
            // Button 2
            echo "<td height='25'>";
            echo "<a href='JavaScript:GoOpener(\"zone-delete.php?affiliateid=".$row_zones['affiliateid']."&zoneid=".$row_zones['zoneid']."\", true)'".phpAds_DelConfirm($strConfirmDeleteZone)."><img src='images/icon-recycle.gif' border='0' align='absmiddle' alt='$strDelete'>&nbsp;$strDelete</a>&nbsp;&nbsp;&nbsp;&nbsp;";
            echo "</td></tr>";
            
            $i++;
        }
    }
    
    if (phpAds_dbNumRows($res_clients) > 0 ||
        phpAds_dbNumRows($res_campaigns) > 0 ||
        phpAds_dbNumRows($res_banners) > 0 ||
        phpAds_dbNumRows($res_affiliates) > 0 ||
        phpAds_dbNumRows($res_zones) > 0)
    {
        echo "<tr height='1'><td colspan='5' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
    }
    else
    {
        echo $strNoMatchesFound;
    }
?>
</table>

</td><td width='20'>&nbsp;</td></tr>
</table>

<br><br> 

</body>
</html>
