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
$Id: keywordhistory.plugin.php 3145 2005-05-20 13:15:01Z andrew $
*/

// Public name of the plugin info function
$plugin_info_function		= "PluginKeywordHistoryInfo";

// Public info function
function PluginKeywordHistoryInfo()
{
    global $strCampaign, $strPluginCampaign, $strDelimiter, $strStartDate, $strEndDate;
    
    $plugininfo = array (
        "plugin-name"        => "Keyword History",
        "plugin-description" => $strPluginCampaign,
        "plugin-author"      => "Andrew",
        "plugin-export"      => "csv",
        "plugin-authorize"   => phpAds_Admin+phpAds_Agency,
        "plugin-execute"     => "PluginKeywordhistoryExecute",
        "plugin-import"      => array (
            "campaignid"			=> array (
                "title"					=> $strCampaign,
                "type"					=> "campaignid-dropdown"
            ),
            "start"			=> array (
                "title"				=> $strStartDate,
                "type"				=> "edit",
                "size"				=> 10,
                "default"			=> date("Y/m/d",mktime (0,0,0,date("m"),date("d")-7,  date("Y")))
            ),
            "end"			=> array (
                "title"				=> $strEndDate,
                "type"				=> "edit",
                "size"				=> 10,
                "default"			=> date("Y/m/d",mktime (0,0,0,date("m"),date("d")-1,  date("Y")))
            ),
            "delimiter"		=> array (
                "title"					=> $strDelimiter,
                "type"					=> "edit",
                "size"					=> 1,
                "default"				=> ","
            )
        )
    );
    
    return ($plugininfo);
}

/*********************************************************/
/* Private plugin function                               */
/*********************************************************/

function PluginKeywordHistoryExecute($campaignid, $start, $end, $delimiter=",")
{
    global $phpAds_config;
    global $strCampaign;
    
    // Format the start and end dates
    $dbStart = date("Ymd000000", strtotime($start));
    $dbEnd   = date("Ymd235959", strtotime($end));
    $start = date("Y/m/d", strtotime($start));
    $end   = date("Y/m/d", strtotime($end));
    
    // Print the content header
    header ("Content-type: application/csv\nContent-Disposition: inline; filename=\"keywordhistory.csv\"");
    
    // Build the array containing the required statistics
    
    // Get trackers linked to this campaign, to avoid doing this over and over...
    // (The campaign is supplied as a parameter to the function)
    $resTrackers = phpAds_dbQuery("
            SELECT 
                c.trackerid, 
                t.trackername 
            FROM 
                ".$phpAds_config['tbl_campaigns_trackers']." as c, 
                ".$phpAds_config['tbl_trackers']." as t 
            WHERE 
                c.campaignid = ".$campaignid." 
                AND c.trackerid = t.trackerid"
    );
    
    // Store the trackers linked to this campaign, so they can be used :-)
    while ($rowTrackers = phpAds_dbFetchArray($resTrackers)) {
        $trackers[$rowTrackers['trackerid']]['name'] = $rowTrackers["trackername"];
    }
    
    // Get banner IDs for this campaign
    $resBanners = phpAds_dbQuery("
				SELECT 
					bannerid, 
					description, 
					active 
				FROM 
					".$phpAds_config['tbl_banners']." 
				WHERE 
					campaignid = ".$campaignid
    );

    // Initialise the index of banners, and a counter for
    // the number of hidden banners
    $bannerIndex   = 0;
    $bannersHidden = 0;
    
    // For each banner ID...
    while ($rowBanners = phpAds_dbFetchArray($resBanners)) {

         // Store the banner ID and name in the next $bannerIndex branch
        $stats[$bannerIndex]['id']   = $rowBanners['bannerid'];
        $stats[$bannerIndex]['name'] = $rowBanners['description'];

        // Is banner active?
        if ($rowBanners['active'] == 't') {
            $stats[$bannerIndex]['active'] = true;
        } else {
            $stats[$bannerIndex]['active'] = false;
            $bannersHidden++;
        }

        // Check whether total cost is set for this banner
        $key = 'total_cost' . $stats[$bannerIndex]['id'];
        if (isset($HTTP_POST_VARS[$key])) {
            $stats[$bannerIndex]['totalCost'] = $HTTP_POST_VARS[$key];
        } else {
            $stats[$bannerIndex]['totalCost'] = 0;
        }
        
        // Select all the clicks for the current banner, and 
        // group them by source (keyword)
        $resClicks = phpAds_dbQuery("
					SELECT 
						bannerid, 
						source as keywords, 
						count(source) as clicks 
					FROM 
						".$phpAds_config['tbl_adclicks']." 
					WHERE 
						bannerid = ".$stats[$bannerIndex]['id']." 
						AND t_stamp >= '".$dbStart."' 
						AND t_stamp <  '".$dbEnd."' 
					GROUP BY 
						source"
        );
        
        // Initialise the index of the keyword
        $keywordIndex = 0;

        while ($rowClicks = phpAds_dbFetchArray($resClicks)) {
            
            // Store the source (keyword) name and resulting clicks
            // for the banner
            $stats[$bannerIndex]['keywords'][$keywordIndex]['name']   = "    ".$rowClicks['keywords'];
            $stats[$bannerIndex]['keywords'][$keywordIndex]['clicks'] =        $rowClicks['clicks'];

            // Add the tracker details for the campaign to the stats array,
            // in a 'trackers' index for the current banner
            foreach ($trackers as $key => $value) {
                $stats[$bannerIndex]['keywords'][$keywordIndex]['trackers'][$key]['name'] = $value['name'];
            }
            
            // Get the conversions for the current banner and keyword
            // where the conversions are limited to 'cnv_latest=1', ensuring
            // that the conversion has resulted from the current banner
            // being the last banner shown to a user
            $select = "SELECT 
								c.conversionid, 
                                c.cnv_logstats, 
                                c.trackerid, 
                                v.value 
							FROM 
								".$phpAds_config['tbl_conversionlog']." as c 
                            LEFT JOIN 
                                ".$phpAds_config['tbl_variablevalues']." as v 
                            ON 
                                c.conversionid = v.conversionid 
							WHERE 
								c.action_bannerid = ".$stats[$bannerIndex]['id']." 
								AND c.action_source = '".$rowClicks['keywords']."' 
								AND c.t_stamp >= '".$dbStart."' 
								AND c.t_stamp <  '".$dbEnd."' 
								AND c.cnv_latest = 1";
            
            $resConversions = phpAds_dbQuery($select);
            
            // Set the number of sale and non-sale conversions, and the total value
            // of the conversions for this banner/keyword combination to zero
            $stats[$bannerIndex]['keywords'][$keywordIndex]['sales']       = 0;
            $stats[$bannerIndex]['keywords'][$keywordIndex]['conversions'] = 0;
            $stats[$bannerIndex]['keywords'][$keywordIndex]['totalValue']  = 0;
            
            // For each conversion...
            while ($rowConversions = phpAds_dbFetchArray($resConversions)) {

                // If the conversion was a sale (ie. not a non-sale conversion)...
                if ($rowConversions['cnv_logstats'] == 'y') {
                    // Log the sale for the current banner/keyword
                    $stats[$bannerIndex]['keywords'][$keywordIndex]['sales']++;
                    // Update the total value, if applicable
                    if (isset($rowConversions['value'])) {
                        $stats[$bannerIndex]['keywords'][$keywordIndex]['totalValue'] += $rowConversions['value'];
                    }
                }
                    
                // Log the conversion for the current banner/keyword, regardless 
                // of it being a sale or otherwise...
                $stats[$bannerIndex]['keywords'][$keywordIndex]['conversions']++;
                if (!isset($stats[$bannerIndex]['keywords'][$keywordIndex]['trackers'][$rowConversions['trackerid']]['conversions'])) {
                    $stats[$bannerIndex]['keywords'][$keywordIndex]['trackers'][$rowConversions['trackerid']]['conversions'] = 0;
                }
                $stats[$bannerIndex]['keywords'][$keywordIndex]['trackers'][$rowConversions['trackerid']]['conversions']++;
            }
            
            // Calculate the statistics for this banner/keyword combination
            if ($stats[$bannerIndex]['keywords'][$keywordIndex]['clicks'] > 0) {
                $stats[$bannerIndex]['keywords'][$keywordIndex]['cpc']          = $stats[$bannerIndex]['keywords'][$keywordIndex]['totalCost'] / $stats[$bannerIndex]['keywords'][$keywordIndex]['clicks'];
                $stats[$bannerIndex]['keywords'][$keywordIndex]['cpc']          = phpAds_formatPercentage($stats[$bannerIndex]['keywords'][$keywordIndex]['cpc'], 2);
                $stats[$bannerIndex]['keywords'][$keywordIndex]['sr']           = $stats[$bannerIndex]['keywords'][$keywordIndex]['sales'] / $stats[$bannerIndex]['keywords'][$keywordIndex]['clicks'];
                $stats[$bannerIndex]['keywords'][$keywordIndex]['sr']           = phpAds_formatPercentage($stats[$bannerIndex]['keywords'][$keywordIndex]['sr'], 2);
            } else {
                $stats[$bannerIndex]['keywords'][$keywordIndex]['cpc']          = 0;
                $stats[$bannerIndex]['keywords'][$keywordIndex]['sr']           = 0;
            }
            if ($stats[$bannerIndex]['keywords'][$keywordIndex]['conversions'] > 0) {
                $stats[$bannerIndex]['keywords'][$keywordIndex]['cpco']         = $stats[$bannerIndex]['keywords'][$keywordIndex]['totalCost'] / $stats[$bannerIndex]['keywords'][$keywordIndex]['conversions'];
            } else {
                $stats[$bannerIndex]['keywords'][$keywordIndex]['cpco']         = 0;
            }
            if ($stats[$bannerIndex]['keywords'][$keywordIndex]['sales'] > 0) {
                $stats[$bannerIndex]['keywords'][$keywordIndex]['averageValue'] = $stats[$bannerIndex]['keywords'][$keywordIndex]['totalValue'] / $stats[$bannerIndex]['keywords'][$keywordIndex]['sales'];
            } else {
                $stats[$bannerIndex]['keywords'][$keywordIndex]['averageValue'] = 0;
            }
            
            // Update the keyword index
            $keywordIndex++;            
            
        }
        
        // Calculate the banner totals and other statistics
        $totalClicksForBanner      = 0;
        $totalSalesForBanner       = 0;
        $totalConversionsForBanner = 0;
        $totalValueForBanner       = 0;
                    
        if (isset($stats[$bannerIndex]['keywords'])) {
            foreach($stats[$bannerIndex]['keywords'] as $sources) {
                $totalClicksForBanner      += $sources['clicks'];
                $totalSalesForBanner       += $sources['sales'];
                $totalConversionsForBanner += $sources['conversions'];
                $totalValueForBanner       += $sources['totalValue'];
            }
        }
        
        $stats[$bannerIndex]['clicks']           = $totalClicksForBanner;
        $stats[$bannerIndex]['sales']            = $totalSalesForBanner;
        $stats[$bannerIndex]['conversions']      = $totalConversionsForBanner;
        if ($stats[$bannerIndex]['clicks'] > 0) {
            $stats[$bannerIndex]['cpc']          = $stats[$bannerIndex]['totalCost'] / $stats[$bannerIndex]['clicks'];
            $stats[$bannerIndex]['cpc']          = phpAds_formatPercentage($stats[$bannerIndex]['cpc'], 2);
            $stats[$bannerIndex]['sr']           = $stats[$bannerIndex]['sales'] / $stats[$bannerIndex]['clicks'];
            $stats[$bannerIndex]['sr']           = phpAds_formatPercentage($stats[$bannerIndex]['sr'], 2);
        } else {
            $stats[$bannerIndex]['cpc']          = 0;
            $stats[$bannerIndex]['sr']           = 0;
        }
        if ($stats[$bannerIndex]['conversions'] > 0) {
            $stats[$bannerIndex]['cpco']         = $stats[$bannerIndex]['totalCost'] / $stats[$bannerIndex]['conversions'];
        } else {
            $stats[$bannerIndex]['cpco']         = 0;
        }
        $stats[$bannerIndex]['totalValue']       = $totalValueForBanner;
        if ($stats[$bannerIndex]['sales'] > 0) {
            $stats[$bannerIndex]['averageValue'] = $stats[$bannerIndex]['totalValue'] / $stats[$bannerIndex]['sales'];
        } else {
            $stats[$bannerIndex]['averageValue'] = 0;
        }
        
        // Update the banner index
        $bannerIndex++;
        
    }
    
    // Print the campaign information
    echo $strCampaign.": ".strip_tags(phpAds_getCampaignName($campaignid))." - ".$start." - ".$end."\n\n";
    
    foreach($stats as $banner) {
        
        // Print the main column headings for each banner
        echo $GLOBALS['strName'].$delimiter.
             $GLOBALS['strID'].$delimiter.
             $GLOBALS['strClicks'].$delimiter.
             $GLOBALS['strCPCShort'].$delimiter.
             $GLOBALS['strTotalCost'].$delimiter.
             $GLOBALS['strConversions'].$delimiter.
             $GLOBALS['strCNVRShort'].$delimiter.
             $GLOBALS['strCPCoShort'].$delimiter.
             'Total Value'.$delimiter.
             'Average Value';
        
        // Print the different tracker column headings for each banner
        foreach ($trackers as $trackerid => $tracker) {
            echo $delimiter.$trackerid." - ".$tracker['name'];
        }
        
        echo "\n\n";
        
        // Print the banner totals information
        echo 'Banner: '.$banner['name'].$delimiter.
             $banner['id'].$delimiter.
             ($banner['clicks']       == 0 ? '' : $banner['clicks']).$delimiter.
             ($banner['cpc']          == 0 ? '' : phpAds_formatNumber($banner['cpc'],2)).$delimiter.
             ($banner['totalCost']    == 0 ? '' : $banner['totalCost']).$delimiter.
             ($banner['sales']        == 0 ? '' : $banner['sales']).$delimiter.
             ($banner['sr']           == 0 ? '' : $banner['sr']).$delimiter.
             ($banner['cpco']         == 0 ? '' : phpAds_formatNumber($banner['cpco'],2)).$delimiter.
             ($banner['totalValue']   == 0 ? '' : number_format($banner['totalValue'], 2, $phpAds_DecimalPoint, $phpAds_ThousandsSeperator)).$delimiter.
             ($banner['averageValue'] == 0 ? '' : number_format($banner['averageValue'], 2, $phpAds_DecimalPoint, $phpAds_ThousandsSeperator));
        
        echo "\n\n";
        
        // Print each keyword line for the banner
        if (isset($banner['keywords'])) {
            foreach ($banner['keywords'] as $source) {
                echo $source['name'].$delimiter.
                     $source['id'].$delimiter.
                     ($source['clicks']       == 0 ? '' : $source['clicks']).$delimiter.
                     ($source['cpc']          == 0 ? '' : phpAds_formatNumber($source['cpc'],2)).$delimiter.
                     ($source['totalCost']    == 0 ? '' : $source['totalCost']).$delimiter.
                     ($source['sales']        == 0 ? '' : $source['sales']).$delimiter.
                     ($source['sr']           == 0 ? '' : $source['sr']).$delimiter.
                     ($source['cpco']         == 0 ? '' : phpAds_formatNumber($source['cpco'],2)).$delimiter.
                     ($source['totalValue']   == 0 ? '' : number_format($source['totalValue'], 2, $phpAds_DecimalPoint, $phpAds_ThousandsSeperator)).$delimiter.
                     ($source['averageValue'] == 0 ? '' : number_format($source['averageValue'], 2, $phpAds_DecimalPoint, $phpAds_ThousandsSeperator));
                
                foreach ($source['trackers'] as $id=>$tracker) {
                    echo $delimiter.$tracker['conversions'];
                }
                
                echo "\n";
                
            }
        }
        
        echo "\n\n";
        
    }
    
}

?>
