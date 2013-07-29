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
$Id: stats-campaign-affiliates.php 3145 2005-05-20 13:15:01Z andrew $
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

    // Get input variables
    $advertiserId   = MAX_getValue('clientid');
    $campaignId     = MAX_getValue('campaignid');
    $period         = MAX_getStoredValue('period', 'today');
    $showPublisher  = MAX_getStoredValue('showpublisher', 't');
    $expand         = MAX_getStoredValue('expand', '');
    $hideinactive   = MAX_getStoredValue('hideinactive', ($phpAds_config['gui_hide_inactive'] == 't'));
    $listorder      = MAX_getStoredValue('listorder', 'name');
    $orderdirection = MAX_getStoredValue('orderdirection', 'up');
    $aNodes         = MAX_getStoredArray('nodes', array());
    $collapse       = MAX_getValue('collapse');

    // Adjust which nodes are opened closed...
    MAX_adjustNodes($aNodes, $expand, $collapse);
    
    // TODO:  Rewrite this phpAdsNew code...
    // Make sure that the client has access to the campaign
    if (!MAX_checkCampaign($advertiserId, $campaignId)) {
		phpAds_PageHeader('2');
		phpAds_Die ($strAccessDenied, $strNotAdmin);
    }

    $campaigns = MAX_getCacheCampaignsByAdvertiserId($advertiserId);
    $anonymous = false;
    foreach ($campaigns as $campaign) {
        $thisCampaign = false;
        if ($campaign['campaign_id'] == $campaignId) {
            $anonymous = ($campaign['anonymous'] == 't' && phpAds_isUser(phpAds_Client));
            $showPublisher = $anonymous ? 'f' : $showPublisher;
            $thisCampaign = true;
        }
	    phpAds_PageContext (phpAds_buildName($campaign['campaign_id'], $campaign['campaign_name']), "stats-campaign-affiliates.php?clientid=$advertiserId&campaignid={$campaign['campaign_id']}", $thisCampaign);
	}
	
    if (phpAds_isUser(phpAds_Client))
    {
		phpAds_PageShortcut($strBannerOverview, "campaign-banners.php?clientid=$clientid&campaignid=$campaignid", 'images/icon-campaign.gif');
	    phpAds_PageHeader('1.2.3');
		echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;<b>".phpAds_getCampaignName($campaignid)."</b><br><br><br>";
		$sections = array('1.2.1', '1.2.2', '1.2.3');
		if (phpAds_isAllowed(phpAds_ViewTargetingStats)) $sections[] = '1.2.4';
		
		phpAds_ShowSections($sections);
    }
    elseif (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
    {
    	phpAds_PageShortcut($strClientProperties, "advertiser-edit.php?clientid=$advertiserId", 'images/icon-advertiser.gif');
    	phpAds_PageShortcut($strCampaignProperties, "campaign-edit.php?clientid=$advertiserId&campaignid=$campaignId", 'images/icon-campaign.gif');
	
    	phpAds_PageHeader("2.1.2.3");
		echo "<img src='images/icon-advertiser.gif' align='absmiddle'>&nbsp;".phpAds_getClientName($advertiserId);
		echo "&nbsp;<img src='images/$phpAds_TextDirection/caret-rs.gif'>&nbsp;";
		echo "<img src='images/icon-campaign.gif' align='absmiddle'>&nbsp;<b>".phpAds_getCampaignName($campaignId)."</b><br><br><br>";
		phpAds_ShowSections(array('2.1.2.1', '2.1.2.2', '2.1.2.3', '2.1.2.4'));
    }

    $agencyId = phpAds_isUser(phpAds_Admin) ? '' : phpAds_getAgencyID();
    $aDates = MAX_getDatesByPeriod($period);
    if ($showPublisher == 't') {
        $aStats = MAX_getCachePublisherZoneStatsByCampaignIdDate($campaignId, $aDates['day_begin'], $aDates['day_end']);
    } else {
        $aStats = MAX_getCacheZoneStatsByCampaignIdDate($campaignId, $aDates['day_begin'], $aDates['day_end']);
    }
    
    // Initialise some parameters
    $pageName = basename($_SERVER['PHP_SELF']);
    $tabindex = 1;
    
    // Display date filter form
    $entityIds = array('clientid'=>$advertiserId,'campaignid'=>$campaignId);
    MAX_displayDateSelectionForm($period, $aDates, $pageName, $tabindex, $entityIds);
    
    phpAds_ShowBreak();
    
    if ($showPublisher == 't') {
        MAX_displayPublisherZoneStats($aStats, $pageName, $anonymous, $aNodes, $expand, $listorder, $orderdirection, $hideinactive, $showPublisher, $entityIds);
    } else {
        MAX_displayZoneStats($aStats, $pageName, $anonymous, $aNodes, $expand, $listorder, $orderdirection, $hideinactive, $showPublisher, $entityIds);
    }
    
    // Store preferences
    $Session['prefs'][$pageName]['expand'] = $expand;
    $Session['prefs'][$pageName]['hideinactive'] = $hideinactive;
    $Session['prefs'][$pageName]['listorder'] = $listorder;
    $Session['prefs'][$pageName]['nodes'] = implode (",", $aNodes);
    $Session['prefs'][$pageName]['orderdirection'] = $orderdirection;
    $Session['prefs'][$pageName]['period'] = $period;
    $Session['prefs'][$pageName]['showpublisher'] = $showPublisher;
    phpAds_SessionDataStore();
    
    // Display page footer
    phpAds_PageFooter();
    

?>