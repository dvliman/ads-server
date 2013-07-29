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
$Id: common.php 375 2004-06-14 13:24:39Z scott $
*/

    function MAX_checkAdvertiser($advertiserId)
    {
        global $phpAds_config;
        
        $allowed = false;
        if (is_numeric($advertiserId)) {
            if (phpAds_isUser(phpAds_Admin)) {
                $allowed = true;
            } elseif (phpAds_isUser(phpAds_Client)) {
                $allowed = ($advertiserId == phpAds_getUserID());
            } elseif (phpAds_isUser(phpAds_Agency)) {
                $agencyId = phpAds_getAgencyID();
                $query = "
                    SELECT
                        c.clientid
                    FROM
                        {$phpAds_config['tbl_clients']} AS c
                    WHERE
                        clientid=$advertiserId
                        AND agencyid=$agencyId
                ";
                
                $res = phpAds_dbQuery($query) or phpAds_sqlDie();
                $allowed = (phpAds_dbNumRows($res) > 0);
            }
        }
        
        return $allowed;
    }
    
    function MAX_checkCampaign($advertiserId, $campaignId)
    {
        global $phpAds_config;
        
        $allowed = false;
        if (is_numeric($advertiserId) && is_numeric($campaignId)) {
            $whereAgency = false;
            if (phpAds_isUser(phpAds_Admin)) {
                $whereAgency = '';
            } elseif (phpAds_isUser(phpAds_Agency)) {
                $whereAgency = 'AND c.agencyid=' . phpAds_getUserID();
            } elseif (phpAds_isUser(phpAds_Client)) {
                $whereAgency = 'AND c.agencyid=' . phpAds_getAgencyID();
            }
            
            if (!($whereAgency === false)) {
                
                $query = "
                    SELECT
                        c.clientid,
                        m.campaignid
                    FROM
                        {$phpAds_config['tbl_clients']} AS c,
                        {$phpAds_config['tbl_campaigns']} AS m
                    WHERE
                        c.clientid=m.clientid
                        AND m.campaignid=$campaignId
                        AND c.clientid=$advertiserId
                        $whereAgency
                ";
                
                $res = phpAds_dbQuery($query) or phpAds_sqlDie();
                $allowed = (phpAds_dbNumRows($res) > 0);
            }
        }
        
        return $allowed;
    }
    
    function MAX_checkBanner($advertiserId, $campaignId, $bannerId)
    {
        global $phpAds_config;
        
        $allowed = false;
        if (is_numeric($advertiserId) && is_numeric($campaignId) && is_numeric($bannerId)) {
            $whereAgency = false;
            if (phpAds_isUser(phpAds_Admin)) {
                $whereAgency = '';
            } elseif (phpAds_isUser(phpAds_Agency)) {
                $whereAgency = 'AND c.agencyid=' . phpAds_getUserID();
            } elseif (phpAds_isUser(phpAds_Client)) {
                $whereAgency = 'AND c.agencyid=' . phpAds_getAgencyID();
            }
            
            if (!($whereAgency === false)) {
                
                $query = "
                    SELECT
                        c.clientid,
                        m.campaignid,
                        b.bannerid
                    FROM
                        {$phpAds_config['tbl_clients']} AS c,
                        {$phpAds_config['tbl_campaigns']} AS m,
                        {$phpAds_config['tbl_banners']} AS b
                    WHERE
                        c.clientid=m.clientid
                        AND m.campaignid=b.campaignid
                        AND b.bannerid=$bannerId
                        AND m.campaignid=$campaignId
                        AND c.clientid=$advertiserId
                        $whereAgency
                ";
                
                $res = phpAds_dbQuery($query) or phpAds_sqlDie();
                $allowed = (phpAds_dbNumRows($res) > 0);
            }
        }
        
        return $allowed;
    }
    
    function MAX_isExpanded($id, $expand, &$aNodes, $prefix)
    {
        $isExpanded = false;
        if ($expand == 'all') {
            $isExpanded = true;
            if (!in_array($prefix . $id, $aNodes)) {
                $aNodes[] = $prefix . $id;
            }
        } elseif ($expand != 'none' && in_array($prefix . $id, $aNodes)) {
                $isExpanded = true;
        }
        
        return $isExpanded;
    }
    
    function MAX_getValue($key, $default = null)
    {
        $value = $default;
        if (isset($_REQUEST[$key])) {
            $value = $_REQUEST[$key];
        }
        
        return $value;
    }
    
    function MAX_getStoredValue($key, $default)
    {
        global $Session;
        $pageName = basename($_SERVER['PHP_SELF']);

        $value = $default;
        if (isset($_REQUEST[$key])) {
            $value = $_REQUEST[$key];
        } elseif (isset($Session['prefs'][$pageName][$key])) {
            $value = $Session['prefs'][$pageName][$key];
        }
        
        return $value;
    }
    
    function MAX_getStoredArray($key, $default)
    {
        global $Session;
        $pageName = basename($_SERVER['PHP_SELF']);
        
        $value = $default;
        if (isset($_REQUEST[$key])) {
            $value = explode(',',$_REQUEST[$key]);
        } elseif (isset($Session['prefs'][$pageName][$key])) {
            $value = explode(',',$Session['prefs'][$pageName][$key]);
        }
        
        return $value;
    }
    
    function MAX_adjustNodes(&$aNodes, $expand, $collapse)
    {
        if (!empty($expand)) {
            if ($expand != 'all') {
                if ($expand == 'none') {
                    $aNodes = array();
                }
                elseif (!in_array($expand, $aNodes)) {
                    $aNodes[] = $expand;
                }
            }
        }
        
        if (!empty($collapse) && in_array($collapse, $aNodes) ) {
            unset($aNodes[array_search($collapse, $aNodes)]);
        }
    }
    
    function MAX_getEntityIcons()
    {
        $icons = array();
        $icons['advertiser']['t'][''] = 'images/icon-advertiser.gif';
        $icons['advertiser']['f'][''] = 'images/icon-advertiser.gif';
        $icons['campaign']['t'][''] = 'images/icon-campaign.gif';
        $icons['campaign']['f'][''] = 'images/icon-campaign-d.gif';
        $icons['banner']['t']['html'] = 'images/icon-banner-html.gif';
        $icons['banner']['t']['txt'] = 'images/icon-banner-text.gif';
        $icons['banner']['t']['url'] = 'images/icon-banner-url.gif';
        $icons['banner']['t']['web'] = 'images/icon-banner-stored.gif';
        $icons['banner']['t'][''] = 'images/icon-banner-stored.gif';
        $icons['banner']['f']['html'] = 'images/icon-banner-html-d.gif';
        $icons['banner']['f']['txt'] = 'images/icon-banner-text-d.gif';
        $icons['banner']['f']['url'] = 'images/icon-banner-url-d.gif';
        $icons['banner']['f']['web'] = 'images/icon-banner-stored-d.gif';
        $icons['banner']['f'][''] = 'images/icon-banner-stored-d.gif';
        $icons['publisher']['t'][''] = 'images/icon-affiliate.gif';
        $icons['publisher']['f'][''] = 'images/icon-affiliate.gif';
        $icons['zone']['t'][''] = 'images/icon-zone.gif';
        $icons['zone']['f'][''] = 'images/icon-zone.gif';
        
        return $icons;
    }
    
    function MAX_getBannerName($description, $alt)
    {
        global $strUntitled;
        
        $name = $strUntitled;
        if (!empty($alt)) $name = $alt;
        if (!empty($description)) $name = $description;                        
        $name = phpAds_breakString ($name, '30');
        
        return $name;
    }
?>