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
$Id: banner-htmlpreview.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
include_once ('lib-statistics.inc.php');
include_once ('../libraries/db.php');
include_once ('../libraries/lib-view-main.inc.php');

/*********************************************************/
/* Main code                                             */
/*********************************************************/

$aBanner = MAX_getBannerByBannerId($bannerid);

if (!empty($aBanner))
{
    $bannerName = strip_tags(phpAds_buildBannerName ($bannerid, $aBanner['description'], $aBanner['alt']));
    $sizeDescription = ($aBanner['storagetype'] == 'txt') ? '&nbsp;' : "&nbsp;&nbsp;&nbsp;width: {$aBanner['width']}&nbsp;&nbsp;height: {$aBanner['height']}";
    $bannerCode = MAX_buildBannerHtml($aBanner, 0, '', '', '', true, false, false);
    echo "
<html>
<head>
<title>$bannerName</title>
<link rel='stylesheet' href='images/$phpAds_TextDirection/interface.css'>
</head>
<body marginheight='0' marginwidth='0' leftmargin='0' topmargin='0' bgcolor='#EFEFEF'>
<table cellpadding='0' cellspacing='0' border='0'>
<tr height='32'>
    <td width='32'><img src='images/cropmark-tl.gif' width='32' height='32'></td>
    <td background='images/ruler-top.gif'>&nbsp;</td>
    <td width='32'><img src='images/cropmark-tr.gif' width='32' height='32'></td>
</tr>
<tr height='{$aBanner['height']}'>
    <td width='32' background='images/ruler-left.gif'>&nbsp;</td>
    <td bgcolor='#FFFFFF' width='{$aBanner['width']}'>
        $bannerCode
    </td>
    <td width='32'>&nbsp;</td>
</tr>
<tr height='32'>
    <td width='32'><img src='images/cropmark-bl.gif' width='32' height='32'></td>
    <td>$sizeDescription</td>
    <td width='32'><img src='images/cropmark-br.gif' width='32' height='32'></td>
</tr>
</table>
</body>
</html>";
}


?>