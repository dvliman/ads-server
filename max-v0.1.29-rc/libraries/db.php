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
$Id: db.php 375 2004-06-14 13:24:39Z scott $
*/

    function MAX_getCacheZoneByZoneId($zoneId)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        
        $cacheId = "zoneid:$zoneId";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aZone, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new zone details
                $aTmpZone = MAX_getZoneByZoneId($zoneId);
                // Check to make sure that we successfully retrieved the banner from cache
                if (!empty($aTmpZone)) {
                    $aZone = $aTmpZone;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new zone details
            $aZone = MAX_getZoneByZoneId($zoneId);
            if (!empty($aZone)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the zone back to cache
        if ($storeCache) {
            // Write the zone back to cache
            $cache = array ($aZone, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aZone;
    }
    
    function MAX_getCacheBannerByBannerId($bannerId)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        $cacheId = "bannerid:$bannerId";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aBanner, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new banner details
                $aTmpBanner = MAX_getBannerByBannerId($bannerId);
                // Check to make sure that we successfully retrieved the banner from cache
                if (!empty($aTmpBanner)) {
                    $aBanner = $aTmpBanner;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new banner details
            $aBanner = MAX_getBannerByBannerId($bannerId);
            if (!empty($aBanner)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the banner back to cache
        if ($storeCache) {
            // Write the banner back to cache
            $cache = array ($aBanner, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aBanner;
    }
    
    function MAX_getCacheCampaignByCampaignId($campaignId)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        $cacheId = "campaignid:$campaignId";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aCampaign, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new banner details
                $aTmpCampaign = MAX_getCampaignByCampaignId($campaignId);
                // Check to make sure that we successfully retrieved the campaign from cache
                if (!empty($aTmpCampaign)) {
                    $aCampaign = $aTmpCampaign;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new banner details
            $aCampaign = MAX_getCampaignByCampaignId($campaignId);
            if (!empty($aCampaign)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the banner back to cache
        if ($storeCache) {
            // Write the banner back to cache
            $cache = array ($aCampaign, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aCampaign;
    }
    
    function MAX_getCacheAdvertiserCampaignBannerStatsByAgencyIdDate($agencyId, $beginDate, $endDate)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        // In the future, old stats should be cached longer and stats for today should be cached max 10 minutes...
        // the complete variable will tell the cache whether the date range falls on today or later...
        $complete = true;
        $cacheId = "advertisercampaignbannerstats:agency:$agencyId:daybegin:$beginDate:dayend:$endDate:complete:$complete";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aStats, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new zone details
                $aTmpStats = MAX_getAdvertiserCampaignBannerStatsByAgencyIdDate($agencyId, $beginDate, $endDate);
                // Check to make sure that we successfully retrieved the banner from cache
                if (!empty($aTmpStats)) {
                    $aStats = $aTmpStats;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new zone details
            $aStats = MAX_getAdvertiserCampaignBannerStatsByAgencyIdDate($agencyId, $beginDate, $endDate);
            if (!empty($aStats)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the zone back to cache
        if ($storeCache) {
            // Write the zone back to cache
            $cache = array ($aStats, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aStats;
    }
    
    function MAX_getCacheCampaignBannerStatsByAdvertiserIdDate($advertiserId, $beginDate, $endDate)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        // In the future, old stats should be cached longer and stats for today should be cached max 10 minutes...
        // the complete variable will tell the cache whether the date range falls on today or later...
        $complete = true;
        $cacheId = "campaignbannerstats:advertiser:$advertiserId:daybegin:$beginDate:dayend:$endDate:complete:$complete";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aStats, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new zone details
                $aTmpStats = MAX_getCampaignBannerStatsByAdvertiserIdDate($advertiserId, $beginDate, $endDate);
                // Check to make sure that we successfully retrieved the banner from cache
                if (!empty($aTmpStats)) {
                    $aStats = $aTmpStats;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new zone details
            $aStats = MAX_getCampaignBannerStatsByAdvertiserIdDate($advertiserId, $beginDate, $endDate);
            if (!empty($aStats)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the zone back to cache
        if ($storeCache) {
            // Write the zone back to cache
            $cache = array ($aStats, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aStats;
    }
    
    function MAX_getCachePublisherZoneStatsByAgencyIdDate($agencyId, $beginDate, $endDate)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        // In the future, old stats should be cached longer and stats for today should be cached max 10 minutes...
        // the complete variable will tell the cache whether the date range falls on today or later...
        $complete = true;
        $cacheId = "publisherzonestats:agency:$agencyId:daybegin:$beginDate:dayend:$endDate:complete:$complete";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aStats, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new zone details
                $aTmpStats = MAX_getPublisherZoneStatsByAgencyIdDate($agencyId, $beginDate, $endDate);
                // Check to make sure that we successfully retrieved the banner from cache
                if (!empty($aTmpStats)) {
                    $aStats = $aTmpStats;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new zone details
            $aStats = MAX_getPublisherZoneStatsByAgencyIdDate($agencyId, $beginDate, $endDate);
            if (!empty($aStats)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the zone back to cache
        if ($storeCache) {
            // Write the zone back to cache
            $cache = array ($aStats, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aStats;
    }
    
    function MAX_getCachePublisherZoneStatsByBannerIdDate($bannerId, $beginDate, $endDate)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        // In the future, old stats should be cached longer and stats for today should be cached max 10 minutes...
        // the complete variable will tell the cache whether the date range falls on today or later...
        $complete = true;
        $cacheId = "publisherzonestats:banner:$bannerId:daybegin:$beginDate:dayend:$endDate:complete:$complete";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aStats, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new zone details
                $aTmpStats = MAX_getPublisherZoneStatsByBannerIdDate($bannerId, $beginDate, $endDate);
                // Check to make sure that we successfully retrieved the banner from cache
                if (!empty($aTmpStats)) {
                    $aStats = $aTmpStats;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new zone details
            $aStats = MAX_getPublisherZoneStatsByBannerIdDate($bannerId, $beginDate, $endDate);
            if (!empty($aStats)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the zone back to cache
        if ($storeCache) {
            // Write the zone back to cache
            $cache = array ($aStats, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aStats;
    }
    
    function MAX_getCachePublisherZoneStatsByCampaignIdDate($campaignId, $beginDate, $endDate)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        // In the future, old stats should be cached longer and stats for today should be cached max 10 minutes...
        // the complete variable will tell the cache whether the date range falls on today or later...
        $complete = true;
        $cacheId = "publisherzonestats:campaign:$campaignId:daybegin:$beginDate:dayend:$endDate:complete:$complete";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aStats, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new zone details
                $aTmpStats = MAX_getPublisherZoneStatsByCampaignIdDate($campaignId, $beginDate, $endDate);
                // Check to make sure that we successfully retrieved the banner from cache
                if (!empty($aTmpStats)) {
                    $aStats = $aTmpStats;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new zone details
            $aStats = MAX_getPublisherZoneStatsByCampaignIdDate($campaignId, $beginDate, $endDate);
            if (!empty($aStats)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the zone back to cache
        if ($storeCache) {
            // Write the zone back to cache
            $cache = array ($aStats, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aStats;
    }
    
    function MAX_getCacheZoneStatsByCampaignIdDate($campaignId, $beginDate, $endDate)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        // In the future, old stats should be cached longer and stats for today should be cached max 10 minutes...
        // the complete variable will tell the cache whether the date range falls on today or later...
        $complete = true;
        $cacheId = "zonestats:campaign:$campaignId:daybegin:$beginDate:dayend:$endDate:complete:$complete";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aStats, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new zone details
                $aTmpStats = MAX_getZoneStatsByCampaignIdDate($campaignId, $beginDate, $endDate);
                // Check to make sure that we successfully retrieved the banner from cache
                if (!empty($aTmpStats)) {
                    $aStats = $aTmpStats;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new zone details
            $aStats = MAX_getZoneStatsByCampaignIdDate($campaignId, $beginDate, $endDate);
            if (!empty($aStats)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the zone back to cache
        if ($storeCache) {
            // Write the zone back to cache
            $cache = array ($aStats, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aStats;
    }
    
    function MAX_getCacheZoneStatsByBannerIdDate($bannerId, $beginDate, $endDate)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        // In the future, old stats should be cached longer and stats for today should be cached max 10 minutes...
        // the complete variable will tell the cache whether the date range falls on today or later...
        $complete = true;
        $cacheId = "zonestats:banner:$bannerId:daybegin:$beginDate:dayend:$endDate:complete:$complete";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aStats, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new zone details
                $aTmpStats = MAX_getZoneStatsByBannerIdDate($bannerId, $beginDate, $endDate);
                // Check to make sure that we successfully retrieved the banner from cache
                if (!empty($aTmpStats)) {
                    $aStats = $aTmpStats;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new zone details
            $aStats = MAX_getZoneStatsByBannerIdDate($bannerId, $beginDate, $endDate);
            if (!empty($aStats)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the zone back to cache
        if ($storeCache) {
            // Write the zone back to cache
            $cache = array ($aStats, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aStats;
    }
    
    function MAX_getCacheCampaignsByAdvertiserId($advertiserId)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        
        $cacheId = "campaigns:advertiser:$advertiserId";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aCampaigns, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new zone details
                $aTmpCampaigns = MAX_getCampaignsByAdvertiserId($advertiserId);
                // Check to make sure that we successfully retrieved the banner from cache
                if (!empty($aTmpCampaigns)) {
                    $aCampaigns = $aTmpCampaigns;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new zone details
            $aCampaigns = MAX_getCampaignsByAdvertiserId($advertiserId);
            if (!empty($aCampaigns)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the zone back to cache
        if ($storeCache) {
            // Write the zone back to cache
            $cache = array ($aCampaigns, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aCampaigns;
    }
    
    function MAX_getCacheBannersByCampaignId($campaignId)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        
        $cacheId = "banners:campaign:$campaignId";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aBanners, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new zone details
                $aTmpBanners = MAX_getBannersByCampaignId($campaignId);
                // Check to make sure that we successfully retrieved the banner from cache
                if (!empty($aTmpBanners)) {
                    $aBanners = $aTmpBanners;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new zone details
            $aBanners = MAX_getBannersByCampaignId($campaignId);
            if (!empty($aBanners)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the zone back to cache
        if ($storeCache) {
            // Write the zone back to cache
            $cache = array ($aBanners, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aBanners;
    }
    
    function MAX_getCacheVariablesByTrackerId($trackerId)
    {
        global $phpAds_config, $phpAds_db_link;
        
        include_once (phpAds_path . "/libraries/deliverycache/cache-{$phpAds_config['delivery_caching']}.inc.php");
        
        $cacheId = "trackervariables:$trackerId";
        $cache = phpAds_cacheFetch($cacheId);
        $storeCache = false;
        if ($cache) {
            list($aTrackerVar, $cacheTimestamp) = $cache;
            // Check to see if the cache is expired
            if ($cacheTimestamp < time() - $phpAds_config['cacheExpire']) {
                // Connect to the admin DB if not already connected
                if (!$phpAds_db_link) phpAds_dbConnect();
                // Get new zone details
                $aTmpTrackerVar = MAX_getVariablesByTrackerId($trackerId);
                // Check to make sure that we successfully retrieved the banner from cache
                if (!($aTmpTrackerVar === false)) {
                    $aTrackerVar = $aTmpTrackerVar;
                    $storeCache = true;
                }
            }
        } else {
            // Connect to the admin DB if not already connected
            if (!$phpAds_db_link) phpAds_dbConnect();
            // Get new zone details
            $aTrackerVar = MAX_getVariablesByTrackerId($trackerId);
            if (!($aTrackerVar === false)) {
                $storeCache = true;
            }
        }
        
        // Check to see if we need to store the zone back to cache
        if ($storeCache) {
            // Write the zone back to cache
            $cache = array ($aTrackerVar, time());
            phpAds_cacheStore ($cacheId, $cache);
        }        
    
        return $aTrackerVar;
    }
    
    function MAX_getZoneByZoneId($zoneId)
    {
        global $phpAds_config;
        
        $query = "
            SELECT
                z.zoneid AS zoneid,
                z.affiliateid AS affiliateid,
                z.zonename AS zonename,
                z.description AS description,
                z.delivery AS delivery,
                z.zonetype AS zonetype,
                z.what AS what,
                z.width AS width,
                z.height AS height,
                z.chain AS chain,
                z.prepend AS prepend,
                z.append AS append,
                z.appendtype AS appendtype
            FROM
                {$phpAds_config['tbl_zones']} AS z
            WHERE
                z.zoneid=$zoneId
        ";
        
        $res = phpAds_dbQuery($query);
        $row = phpAds_dbFetchArray($res);
        
        $aZone = false;
        if (!empty($row))
            $aZone = $row;
            
        return $aZone;
    }
    function MAX_getBannerByBannerId($bannerId)
    {
        global $phpAds_config;
        
        $query = "
            SELECT
                b.bannerid AS bannerid,
                b.campaignid AS campaignid,
                b.active AS active,
                b.priority AS priority,
                b.contenttype AS contenttype,
                b.pluginversion AS pluginversion,
                b.storagetype AS storagetype,
                b.filename AS filename,
                b.imageurl AS imageurl,
                b.htmltemplate AS htmltemplate,
                b.htmlcache AS htmlcache,
                b.width AS width,
                b.height AS height,
                b.weight AS weight,
                b.seq AS seq,
                b.target AS target,
                b.url AS url,
                b.alt AS alt,
                b.status AS status,
                b.keyword AS keyword,
                b.bannertext AS bannertext,
                b.description AS description,
                b.autohtml AS autohtml,
                b.adserver AS adserver,
                b.block AS block,
                b.capping AS capping,
                b.session_capping AS session_capping,
                b.compiledlimitation AS compiledlimitation,
                b.append AS append,
                b.appendtype AS appendtype,
                b.bannertype AS bannertype,
                b.alt_filename AS alt_filename,
                b.alt_imageurl AS alt_imageurl,
                b.alt_contenttype AS alt_contenttype
            FROM
                {$phpAds_config['tbl_banners']} AS b
            WHERE
                b.bannerid=$bannerId
        ";
        
        $res = phpAds_dbQuery($query);
        $row = phpAds_dbFetchArray($res);
        
        $aBanner = false;
        if (!empty($row))
            $aBanner = $row;
            
        return $aBanner;
    }
    
    function MAX_getBannersByCampaignId($campaignId)
    {
        global $phpAds_config;
        
        $query = "
            SELECT
                b.bannerid AS bannerid,
                b.campaignid AS campaignid,
                b.active AS active,
                b.priority AS priority,
                b.contenttype AS contenttype,
                b.pluginversion AS pluginversion,
                b.storagetype AS storagetype,
                b.filename AS filename,
                b.imageurl AS imageurl,
                b.htmltemplate AS htmltemplate,
                b.htmlcache AS htmlcache,
                b.width AS width,
                b.height AS height,
                b.weight AS weight,
                b.seq AS seq,
                b.target AS target,
                b.url AS url,
                b.alt AS alt,
                b.status AS status,
                b.keyword AS keyword,
                b.bannertext AS bannertext,
                b.description AS description,
                b.autohtml AS autohtml,
                b.adserver AS adserver,
                b.block AS block,
                b.capping AS capping,
                b.session_capping AS session_capping,
                b.compiledlimitation AS compiledlimitation,
                b.append AS append,
                b.appendtype AS appendtype,
                b.bannertype AS bannertype,
                b.alt_filename AS alt_filename,
                b.alt_imageurl AS alt_imageurl,
                b.alt_contenttype AS alt_contenttype
            FROM
                {$phpAds_config['tbl_banners']} AS b
            WHERE
                b.campaignid=$campaignId
        ";
        
        $res = phpAds_dbQuery($query);
        $aBanners = array();
        while ($row = phpAds_dbFetchArray($res)) {
            $aBanners[] = $row;
        }
        
        return !empty($aBanners) ? $aBanners : false;
    }
    
    function MAX_getCampaignsByAdvertiserId($advertiserId)
    {
        global $phpAds_config;
        
        $query = "
            SELECT
                m.campaignid AS campaign_id,
                m.campaignname AS campaign_name,
                m.clientid AS advertiser_id,
                m.views AS views,
                m.clicks AS clicks,
                m.conversions AS conversions,
                m.expire AS expire,
                m.activate AS activate,
                m.active AS active,
                m.priority AS priority,
                m.weight AS weight,
                m.target AS target,
                m.optimise AS optimise,
                m.anonymous AS anonymous
            FROM
                {$phpAds_config['tbl_campaigns']} AS m
            WHERE
                m.clientid=$advertiserId
        ";
        
        $res = phpAds_dbQuery($query);
        $aCampaigns = array();
        while ($row = phpAds_dbFetchArray($res)) {
            $aCampaigns[] = $row;
        }
        
        return !empty($aCampaigns) ? $aCampaigns : false;
    }
    
    function MAX_getCampaignByCampaignId($campaignId)
    {
        global $phpAds_config;
        
        $query = "
            SELECT
                m.campaignid AS campaign_id,
                m.campaignname AS campaign_name,
                m.clientid AS advertiser_id,
                m.views AS views,
                m.clicks AS clicks,
                m.conversions AS conversions,
                m.expire AS expire,
                m.activate AS activate,
                m.active AS active,
                m.priority AS priority,
                m.weight AS weight,
                m.target AS target,
                m.optimise AS optimise,
                m.anonymous AS anonymous
            FROM
                {$phpAds_config['tbl_campaigns']} AS m
            WHERE
                m.campaignid=$campaignId
        ";
        
        $res = phpAds_dbQuery($query);
        $row = phpAds_dbFetchArray($res);
        
        $aCampaign = false;
        if (!empty($row))
            $aCampaign = $row;
            
        return $aCampaign;
    }
    
    function MAX_getVariablesByTrackerId($trackerId)
    {
        global $phpAds_config;
        
        $query = "
            SELECT
                v.variableid AS variableid,
                v.trackerid AS trackerid,
                v.name AS name,
                v.description AS description,
                v.variabletype AS variabletype,
                v.datatype AS datatype
            FROM
                {$phpAds_config['tbl_variables']} AS v
            WHERE
                v.trackerid=$trackerId
        ";

        $queryValid = true;
        $res = phpAds_dbQuery($query)
            or $queryValid = false;
        if ($queryValid) {
            $aTrackerVar = array();
            while ($row = phpAds_dbFetchArray($res)) {
                $aTrackerVar[] = $row;
            }
        } else {
            $aTrackerVar = false;
        }
            
        return $aTrackerVar;
    }
    function MAX_setBannerByBannerId($bannerId, $aParameters)
    {
        global $phpAds_config;
        
        $set = array();
        foreach ($aParameters as $name => $value) {
            $set[] = "$name='$value'";
        }
        $set = implode(',', $set);
        $query = "
            UPDATE {$phpAds_config['tbl_banners']}
            SET
                $set
            WHERE
                bannerid=$bannerId
        ";
        $res = phpAds_dbQuery($query)
            or phpAds_sqlDie();
        
        return $bannerId;
    }
    
    function MAX_addBanner($aParameters)
    {
        global $phpAds_config;
        
        $names = array();
        $values = array();
        foreach ($aParameters as $name => $value) {
            $names[] = $name;
            $values[] = "'$value'";
        }
        $names = implode(',', $names);
        $values = implode(',', $values);
        
        $query = "
            INSERT INTO {$phpAds_config['tbl_banners']}
                ($names)
            VALUES
                ($values)
        ";
        $res = phpAds_dbQuery($query)
            or phpAds_sqlDie();
        
        $bannerId = phpAds_dbInsertID();
        return $bannerId;
    }

    function MAX_getAdvertiserCampaignBannerStatsByAgencyIdDate($agencyId, $beginDate, $endDate)
    {
        include_once 'common.php';
        
        global $phpAds_config;
        
        // Where clause
        $aWhere = array();
        if (!empty($beginDate) && !empty($endDate))
            $aWhere[] = "s.day>='$beginDate' AND s.day<='$endDate'";
        if (!empty($agencyId))
            $aWhere[] = "c.agencyid=$agencyId";
        $where = implode(' AND ', $aWhere);
        $where = !empty($where) ? "WHERE $where" : '';
        
        $query = "
            SELECT
                c.clientid AS advertiser_id,
                c.clientname AS advertiser_name,
                m.campaignid AS campaign_id,
                m.campaignname AS campaign_name,
                m.active AS campaign_active,
                b.bannerid AS banner_id,
                b.description AS banner_description,
                b.alt AS banner_alt,
                b.storagetype AS banner_storagetype,
                b.active AS banner_active,
                SUM(s.views) AS sum_views,
                SUM(s.clicks) AS sum_clicks,
                SUM(s.conversions) AS sum_conversions
            FROM
                {$phpAds_config['tbl_clients']} AS c
                LEFT JOIN {$phpAds_config['tbl_campaigns']} AS m ON c.clientid=m.clientid
                LEFT JOIN {$phpAds_config['tbl_banners']} AS b ON m.campaignid=b.campaignid
                LEFT JOIN {$phpAds_config['tbl_adstats']} AS s ON b.bannerid=s.bannerid
            $where
            GROUP BY
                banner_id
        ";
        
        $aStats = array();
        // Initialise stats...
        $aStats['views'] = 0;
        $aStats['clicks'] = 0;
        $aStats['conversions'] = 0;
    
        $res = phpAds_dbQuery($query)
            or phpAds_sqlDie();
        while ($row = phpAds_dbFetchArray($res))
        {
            if (empty($row['advertiser_id'])) $row['advertiser_id'] = 0;
            if (empty($row['campaign_id'])) $row['campaign_id'] = 0;
            if (empty($row['banner_id'])) $row['banner_id'] = 0;
            
            $advertiserId = $row['advertiser_id'];
            $campaignId = $row['campaign_id'];
            $bannerId = $row['banner_id'];
            
            // Set the advertiser properties
            if (empty($aStats['children'][$advertiserId])) {
                $aStats['children'][$advertiserId] = array(
                    'entity' => 'advertiser',
                    'id' => $row['advertiser_id'],
                    'name' => $row['advertiser_name'],
                    'type' => '',
                    'active' => 'f'
                );
            }
            
            if (empty($aStats['children'][$advertiserId]['views'])) {
                $aStats['children'][$advertiserId]['views'] = $row['sum_views'];
            } else {
                $aStats['children'][$advertiserId]['views'] += $row['sum_views'];
            }
            
            if (empty($aStats['children'][$advertiserId]['clicks'])) {
                $aStats['children'][$advertiserId]['clicks'] = $row['sum_clicks'];
            } else {
                $aStats['children'][$advertiserId]['clicks'] += $row['sum_clicks'];
            }
            
            if (empty($aStats['children'][$advertiserId]['conversions'])) {
                $aStats['children'][$advertiserId]['conversions'] = $row['sum_conversions'];
            } else {
                $aStats['children'][$advertiserId]['conversions'] += $row['sum_conversions'];
            }
            
            // Set the campaign properties
            if (empty($aStats['children'][$advertiserId]['children'][$campaignId])) {
                $aStats['children'][$advertiserId]['children'][$campaignId] = array(
                    'entity' => 'campaign',
                    'id' => $row['campaign_id'],
                    'name' => $row['campaign_name'],
                    'type' => '',
                    'active' => $row['campaign_active']
                );
            }
            
            // Flag the advertiser as 'active' if any campaign or banner in the advertiser is active.
            if ($row['campaign_active'] == 't' && $row['banner_active'] == 't')
                $aStats['children'][$advertiserId]['active'] = 't';
            
            if (empty($aStats['children'][$advertiserId]['children'][$campaignId]['views'])) {
                $aStats['children'][$advertiserId]['children'][$campaignId]['views'] = $row['sum_views'];
            } else {
                $aStats['children'][$advertiserId]['children'][$campaignId]['views'] += $row['sum_views'];
            }
            
            if (empty($aStats['children'][$advertiserId]['children'][$campaignId]['clicks'])) {
                $aStats['children'][$advertiserId]['children'][$campaignId]['clicks'] = $row['sum_clicks'];
            } else {
                $aStats['children'][$advertiserId]['children'][$campaignId]['clicks'] += $row['sum_clicks'];
            }
            
            if (empty($aStats['children'][$advertiserId]['children'][$campaignId]['conversions'])) {
                $aStats['children'][$advertiserId]['children'][$campaignId]['conversions'] = $row['sum_conversions'];
            } else {
                $aStats['children'][$advertiserId]['children'][$campaignId]['conversions'] += $row['sum_conversions'];
            }
            
            // Set the banner properties
            if (empty($aStats['children'][$advertiserId]['children'][$campaignId]['children'][$bannerId])) {
                $aStats['children'][$advertiserId]['children'][$campaignId]['children'][$bannerId] = array(
                    'entity' => 'banner',
                    'id' => $row['banner_id'],
                    'name' => MAX_getBannerName($row['banner_description'], $row['banner_alt']),
                    'type' => $row['banner_storagetype'],
                    'active' => $row['banner_active'],
                    'views' => $row['sum_views'],
                    'clicks' => $row['sum_clicks'],
                    'conversions' => $row['sum_conversions']
                );
            }
            
            $aStats['views'] += $row['sum_views'];
            $aStats['clicks'] += $row['sum_clicks'];
            $aStats['conversions'] += $row['sum_conversions'];
        }
        
        return $aStats;
    }

    function MAX_getCampaignBannerStatsByAdvertiserIdDate($advertiserId, $beginDate, $endDate)
    {
        include_once 'common.php';
        
        global $phpAds_config;
        
        // Where clause
        $aWhere = array();
        if (!empty($beginDate) && !empty($endDate))
            $aWhere[] = "s.day>='$beginDate' AND s.day<='$endDate'";
        $aWhere[] = "m.clientid=$advertiserId";
        $where = implode(' AND ', $aWhere);
        $where = !empty($where) ? "WHERE $where" : '';
        
        $query = "
            SELECT
                m.campaignid AS campaign_id,
                m.campaignname AS campaign_name,
                m.active AS campaign_active,
                m.anonymous AS campaign_anonymous,
                b.bannerid AS banner_id,
                b.description AS banner_description,
                b.alt AS banner_alt,
                b.storagetype AS banner_storagetype,
                b.active AS banner_active,
                SUM(s.views) AS sum_views,
                SUM(s.clicks) AS sum_clicks,
                SUM(s.conversions) AS sum_conversions
            FROM
                {$phpAds_config['tbl_campaigns']} AS m
                LEFT JOIN {$phpAds_config['tbl_banners']} AS b ON m.campaignid=b.campaignid
                LEFT JOIN {$phpAds_config['tbl_adstats']} AS s ON b.bannerid=s.bannerid
            $where
            GROUP BY
                banner_id
        ";
        
        $aStats = array();
        // Initialise stats...
        $aStats['views'] = 0;
        $aStats['clicks'] = 0;
        $aStats['conversions'] = 0;
    
        $res = phpAds_dbQuery($query)
            or phpAds_sqlDie();
        while ($row = phpAds_dbFetchArray($res)) //Looping though banner by banner
        {
            if (empty($row['campaign_id'])) $row['campaign_id'] = 0;
            if (empty($row['banner_id'])) $row['banner_id'] = 0;
            
            $campaignId = $row['campaign_id'];
            $bannerId = $row['banner_id'];
            
            if ((phpAds_isUser(phpAds_Client)) and $row['campaign_anonymous'] == 't') $row['campaign_name'] = $GLOBALS['strHiddenCampaign'].' '.$row['campaign_id'];
            
            // Set the campaign properties
            if (empty($aStats['children'][$campaignId])) {
                $aStats['children'][$campaignId] = array(
                    'entity' => 'campaign',
                    'id' => $row['campaign_id'],
                    'name' => $row['campaign_name'],
                    'type' => '',
                    'active' => $row['campaign_active'],
                    'anonymous' => $row['campaign_anonymous']
                );
            }
            
            if (empty($aStats['children'][$campaignId]['views'])) {
                $aStats['children'][$campaignId]['views'] = $row['sum_views'];
            } else {
                $aStats['children'][$campaignId]['views'] += $row['sum_views'];
            }
            
            if (empty($aStats['children'][$campaignId]['clicks'])) {
                $aStats['children'][$campaignId]['clicks'] = $row['sum_clicks'];
            } else {
                $aStats['children'][$campaignId]['clicks'] += $row['sum_clicks'];
            }
            
            if (empty($aStats['children'][$campaignId]['conversions'])) {
                $aStats['children'][$campaignId]['conversions'] = $row['sum_conversions'];
            } else {
                $aStats['children'][$campaignId]['conversions'] += $row['sum_conversions'];
            }
            
            // Set the banner properties
            if (empty($aStats['children'][$campaignId]['children'][$bannerId])) {
                $aStats['children'][$campaignId]['children'][$bannerId] = array(
                    'entity' => 'banner',
                    'id' => $row['banner_id'],
                    'name' => MAX_getBannerName($row['banner_description'], $row['banner_alt']),
                    'type' => $row['banner_storagetype'],
                    'active' => $row['banner_active'],
                    'views' => $row['sum_views'],
                    'clicks' => $row['sum_clicks'],
                    'conversions' => $row['sum_conversions']
                );
            }
            
            $aStats['views'] += $row['sum_views'];
            $aStats['clicks'] += $row['sum_clicks'];
            $aStats['conversions'] += $row['sum_conversions'];
        }
        
        return $aStats;
    }

    function MAX_getPublisherZoneStatsByAgencyIdDate($agencyId, $beginDate, $endDate)
    {
        include_once 'common.php';
        
        global $phpAds_config;
        
        // Where clause
        $aWhere = array();
        if (!empty($beginDate) && !empty($endDate))
            $aWhere[] = "s.day>='$beginDate' AND s.day<='$endDate'";
        if (!empty($agencyId))
            $aWhere[] = "a.agencyid=$agencyId";
        $where = implode(' AND ', $aWhere);
        $where = !empty($where) ? "WHERE $where" : '';
        
        $query = "
            SELECT
                a.affiliateid AS publisher_id,
                a.name AS publisher_name,
                z.zoneid AS zone_id,
                z.zonename AS zone_name,
                SUM(s.views) AS sum_views,
                SUM(s.clicks) AS sum_clicks,
                SUM(s.conversions) AS sum_conversions
            FROM
                {$phpAds_config['tbl_affiliates']} AS a
                LEFT JOIN {$phpAds_config['tbl_zones']} AS z ON a.affiliateid=z.affiliateid
                LEFT JOIN {$phpAds_config['tbl_adstats']} AS s ON z.zoneid=s.zoneid
            $where
            GROUP BY
                zone_id
        ";
        
        $aStats = array();
        // Initialise stats...
        $aStats['views'] = 0;
        $aStats['clicks'] = 0;
        $aStats['conversions'] = 0;
    
        $res = phpAds_dbQuery($query)
            or phpAds_sqlDie();
        while ($row = phpAds_dbFetchArray($res))
        {
            if (empty($row['publisher_id'])) $row['publisher_id'] = 0;
            if (empty($row['zone_id'])) $row['zone_id'] = 0;
            
            $publisherId = $row['publisher_id'];
            $zoneId = $row['zone_id'];
            
            // Set the publisher properties
            if (empty($aStats['children'][$publisherId])) {
                $aStats['children'][$publisherId] = array(
                    'entity' => 'publisher',
                    'id' => $row['publisher_id'],
                    'name' => $row['publisher_name'],
                    'type' => '',
                    'active' => 't'
                );
            }
            
            if (empty($aStats['children'][$publisherId]['views'])) {
                $aStats['children'][$publisherId]['views'] = $row['sum_views'];
            } else {
                $aStats['children'][$publisherId]['views'] += $row['sum_views'];
            }
            
            if (empty($aStats['children'][$publisherId]['clicks'])) {
                $aStats['children'][$publisherId]['clicks'] = $row['sum_clicks'];
            } else {
                $aStats['children'][$publisherId]['clicks'] += $row['sum_clicks'];
            }
            
            if (empty($aStats['children'][$publisherId]['conversions'])) {
                $aStats['children'][$publisherId]['conversions'] = $row['sum_conversions'];
            } else {
                $aStats['children'][$publisherId]['conversions'] += $row['sum_conversions'];
            }
            
            // Set the zone properties
            if (empty($aStats['children'][$publisherId]['children'][$zoneId])) {
                $aStats['children'][$publisherId]['children'][$zoneId] = array(
                    'entity' => 'zone',
                    'id' => $row['zone_id'],
                    'name' => $row['zone_name'],
                    'type' => '',
                    'active' => 't',
                    'views' => $row['sum_views'],
                    'clicks' => $row['sum_clicks'],
                    'conversions' => $row['sum_conversions']
                );
            }
            
            $aStats['views'] += $row['sum_views'];
            $aStats['clicks'] += $row['sum_clicks'];
            $aStats['conversions'] += $row['sum_conversions'];
        }
        
        return $aStats;
    }
    
    function MAX_getPublisherZoneStatsByBannerIdDate($bannerId, $beginDate, $endDate)
    {
        include_once 'common.php';
        
        global $phpAds_config;
        
        // Where clause
        $aWhere = array();
        if (!empty($beginDate) && !empty($endDate))
            $aWhere[] = "s.day>='$beginDate' AND s.day<='$endDate'";
        $aWhere[] = "s.bannerid=$bannerId";
        $where = implode(' AND ', $aWhere);
        $where = !empty($where) ? "WHERE $where" : '';
        
        $query = "
            SELECT
                a.affiliateid AS publisher_id,
                a.name AS publisher_name,
                z.zoneid AS zone_id,
                z.zonename AS zone_name,
                SUM(s.views) AS sum_views,
                SUM(s.clicks) AS sum_clicks,
                SUM(s.conversions) AS sum_conversions
            FROM
                {$phpAds_config['tbl_affiliates']} AS a
                LEFT JOIN {$phpAds_config['tbl_zones']} AS z ON a.affiliateid=z.affiliateid
                LEFT JOIN {$phpAds_config['tbl_adstats']} AS s ON z.zoneid=s.zoneid
            $where
            GROUP BY
                zone_id
        ";
        
        $aStats = array();
        // Initialise stats...
        $aStats['views'] = 0;
        $aStats['clicks'] = 0;
        $aStats['conversions'] = 0;
    
        $res = phpAds_dbQuery($query)
            or phpAds_sqlDie();
        while ($row = phpAds_dbFetchArray($res))
        {
            if (empty($row['publisher_id'])) $row['publisher_id'] = 0;
            if (empty($row['zone_id'])) $row['zone_id'] = 0;
            
            $publisherId = $row['publisher_id'];
            $zoneId = $row['zone_id'];
            
            // Set the publisher properties
            if (empty($aStats['children'][$publisherId])) {
                $aStats['children'][$publisherId] = array(
                    'entity' => 'publisher',
                    'id' => $row['publisher_id'],
                    'name' => $row['publisher_name'],
                    'type' => '',
                    'active' => 't'
                );
            }
            
            if (empty($aStats['children'][$publisherId]['views'])) {
                $aStats['children'][$publisherId]['views'] = $row['sum_views'];
            } else {
                $aStats['children'][$publisherId]['views'] += $row['sum_views'];
            }
            
            if (empty($aStats['children'][$publisherId]['clicks'])) {
                $aStats['children'][$publisherId]['clicks'] = $row['sum_clicks'];
            } else {
                $aStats['children'][$publisherId]['clicks'] += $row['sum_clicks'];
            }
            
            if (empty($aStats['children'][$publisherId]['conversions'])) {
                $aStats['children'][$publisherId]['conversions'] = $row['sum_conversions'];
            } else {
                $aStats['children'][$publisherId]['conversions'] += $row['sum_conversions'];
            }
            
            // Set the zone properties
            if (empty($aStats['children'][$publisherId]['children'][$zoneId])) {
                $aStats['children'][$publisherId]['children'][$zoneId] = array(
                    'entity' => 'zone',
                    'id' => $row['zone_id'],
                    'name' => $row['zone_name'],
                    'type' => '',
                    'active' => 't',
                    'views' => $row['sum_views'],
                    'clicks' => $row['sum_clicks'],
                    'conversions' => $row['sum_conversions']
                );
            }
            
            $aStats['views'] += $row['sum_views'];
            $aStats['clicks'] += $row['sum_clicks'];
            $aStats['conversions'] += $row['sum_conversions'];
        }
        
        return $aStats;
    }

    function MAX_getPublisherZoneStatsByCampaignIdDate($campaignId, $beginDate, $endDate)
    {
        include_once 'common.php';
        
        global $phpAds_config;
        
        // Where clause
        $aWhere = array();
        if (!empty($beginDate) && !empty($endDate))
            $aWhere[] = "s.day>='$beginDate' AND s.day<='$endDate'";
        $aWhere[] = "b.campaignid=$campaignId";
        $where = implode(' AND ', $aWhere);
        $where = !empty($where) ? "WHERE $where" : '';
        
        $query = "
            SELECT
                a.affiliateid AS publisher_id,
                a.name AS publisher_name,
                z.zoneid AS zone_id,
                z.zonename AS zone_name,
                SUM(s.views) AS sum_views,
                SUM(s.clicks) AS sum_clicks,
                SUM(s.conversions) AS sum_conversions
            FROM
                {$phpAds_config['tbl_affiliates']} AS a
                LEFT JOIN {$phpAds_config['tbl_zones']} AS z ON a.affiliateid=z.affiliateid
                LEFT JOIN {$phpAds_config['tbl_adstats']} AS s ON z.zoneid=s.zoneid
                LEFT JOIN {$phpAds_config['tbl_banners']} AS b ON b.bannerid=s.bannerid
            $where
            GROUP BY
                zone_id
        ";
        
        $aStats = array();
        // Initialise stats...
        $aStats['views'] = 0;
        $aStats['clicks'] = 0;
        $aStats['conversions'] = 0;
    
        $res = phpAds_dbQuery($query)
            or phpAds_sqlDie();
        while ($row = phpAds_dbFetchArray($res))
        {
            if (empty($row['publisher_id'])) $row['publisher_id'] = 0;
            if (empty($row['zone_id'])) $row['zone_id'] = 0;
            
            $publisherId = $row['publisher_id'];
            $zoneId = $row['zone_id'];
            
            // Set the publisher properties
            if (empty($aStats['children'][$publisherId])) {
                $aStats['children'][$publisherId] = array(
                    'entity' => 'publisher',
                    'id' => $row['publisher_id'],
                    'name' => $row['publisher_name'],
                    'type' => '',
                    'active' => 't'
                );
            }
            
            if (empty($aStats['children'][$publisherId]['views'])) {
                $aStats['children'][$publisherId]['views'] = $row['sum_views'];
            } else {
                $aStats['children'][$publisherId]['views'] += $row['sum_views'];
            }
            
            if (empty($aStats['children'][$publisherId]['clicks'])) {
                $aStats['children'][$publisherId]['clicks'] = $row['sum_clicks'];
            } else {
                $aStats['children'][$publisherId]['clicks'] += $row['sum_clicks'];
            }
            
            if (empty($aStats['children'][$publisherId]['conversions'])) {
                $aStats['children'][$publisherId]['conversions'] = $row['sum_conversions'];
            } else {
                $aStats['children'][$publisherId]['conversions'] += $row['sum_conversions'];
            }
            
            // Set the zone properties
            if (empty($aStats['children'][$publisherId]['children'][$zoneId])) {
                $aStats['children'][$publisherId]['children'][$zoneId] = array(
                    'entity' => 'zone',
                    'id' => $row['zone_id'],
                    'name' => $row['zone_name'],
                    'type' => '',
                    'active' => 't',
                    'views' => $row['sum_views'],
                    'clicks' => $row['sum_clicks'],
                    'conversions' => $row['sum_conversions']
                );
            }
            
            $aStats['views'] += $row['sum_views'];
            $aStats['clicks'] += $row['sum_clicks'];
            $aStats['conversions'] += $row['sum_conversions'];
        }
        
        return $aStats;
    }
    
    function MAX_getZoneStatsByCampaignIdDate($campaignId, $beginDate, $endDate)
    {
        include_once 'common.php';
        
        global $phpAds_config;
        
        // Where clause
        $aWhere = array();
        if (!empty($beginDate) && !empty($endDate))
            $aWhere[] = "s.day>='$beginDate' AND s.day<='$endDate'";
        $aWhere[] = "b.campaignid=$campaignId";
        $where = implode(' AND ', $aWhere);
        $where = !empty($where) ? "WHERE $where" : '';
        
        $query = "
            SELECT
                a.affiliateid AS publisher_id,
                a.name AS publisher_name,
                z.zoneid AS zone_id,
                z.zonename AS zone_name,
                SUM(s.views) AS sum_views,
                SUM(s.clicks) AS sum_clicks,
                SUM(s.conversions) AS sum_conversions
            FROM
                {$phpAds_config['tbl_affiliates']} AS a
                LEFT JOIN {$phpAds_config['tbl_zones']} AS z ON a.affiliateid=z.affiliateid
                LEFT JOIN {$phpAds_config['tbl_adstats']} AS s ON z.zoneid=s.zoneid
                LEFT JOIN {$phpAds_config['tbl_banners']} AS b ON b.bannerid=s.bannerid
            $where
            GROUP BY
                zone_id
        ";
        
        $aStats = array();
        // Initialise stats...
        $aStats['views'] = 0;
        $aStats['clicks'] = 0;
        $aStats['conversions'] = 0;
    
        $res = phpAds_dbQuery($query)
            or phpAds_sqlDie();
        while ($row = phpAds_dbFetchArray($res))
        {
            if (empty($row['publisher_id'])) $row['publisher_id'] = 0;
            if (empty($row['zone_id'])) $row['zone_id'] = 0;
            
            $publisherId = $row['publisher_id'];
            $zoneId = $row['zone_id'];
            
            // Set the publisher properties
            if (empty($aStats['children'][$zoneId])) {
                $aStats['children'][$zoneId] = array(
                    'entity' => 'zone',
                    'id' => $row['zone_id'],
                    'parentid' => $row['publisher_id'],
                    'name' => $row['zone_name'],
                    'type' => '',
                    'active' => 't',
                    'views' => $row['sum_views'],
                    'clicks' => $row['sum_clicks'],
                    'conversions' => $row['sum_conversions']
                );
            }
            
            $aStats['views'] += $row['sum_views'];
            $aStats['clicks'] += $row['sum_clicks'];
            $aStats['conversions'] += $row['sum_conversions'];
        }
        
        return $aStats;
    }
    
    function MAX_getZoneStatsByBannerIdDate($bannerId, $beginDate, $endDate)
    {
        include_once 'common.php';
        
        global $phpAds_config;
        
        // Where clause
        $aWhere = array();
        if (!empty($beginDate) && !empty($endDate))
            $aWhere[] = "s.day>='$beginDate' AND s.day<='$endDate'";
        $aWhere[] = "s.bannerid=$bannerId";
        $where = implode(' AND ', $aWhere);
        $where = !empty($where) ? "WHERE $where" : '';
        
        $query = "
            SELECT
                a.affiliateid AS publisher_id,
                a.name AS publisher_name,
                z.zoneid AS zone_id,
                z.zonename AS zone_name,
                SUM(s.views) AS sum_views,
                SUM(s.clicks) AS sum_clicks,
                SUM(s.conversions) AS sum_conversions
            FROM
                {$phpAds_config['tbl_affiliates']} AS a
                LEFT JOIN {$phpAds_config['tbl_zones']} AS z ON a.affiliateid=z.affiliateid
                LEFT JOIN {$phpAds_config['tbl_adstats']} AS s ON z.zoneid=s.zoneid
            $where
            GROUP BY
                zone_id
        ";
        
        $aStats = array();
        // Initialise stats...
        $aStats['views'] = 0;
        $aStats['clicks'] = 0;
        $aStats['conversions'] = 0;
    
        $res = phpAds_dbQuery($query)
            or phpAds_sqlDie();
        while ($row = phpAds_dbFetchArray($res))
        {
            if (empty($row['publisher_id'])) $row['publisher_id'] = 0;
            if (empty($row['zone_id'])) $row['zone_id'] = 0;
            
            $publisherId = $row['publisher_id'];
            $zoneId = $row['zone_id'];
            
            // Set the publisher properties
            if (empty($aStats['children'][$zoneId])) {
                $aStats['children'][$zoneId] = array(
                    'entity' => 'zone',
                    'id' => $row['zone_id'],
                    'parentid' => $row['publisher_id'],
                    'name' => $row['zone_name'],
                    'type' => '',
                    'active' => 't',
                    'views' => $row['sum_views'],
                    'clicks' => $row['sum_clicks'],
                    'conversions' => $row['sum_conversions']
                );
            }
            
            $aStats['views'] += $row['sum_views'];
            $aStats['clicks'] += $row['sum_clicks'];
            $aStats['conversions'] += $row['sum_conversions'];
        }
        
        return $aStats;
    }
    
    function MAX_getDailyHistoryStatsByAgencyIdDate($agencyId, $beginDate = null, $endDate = null)
    {
        include_once 'common.php';
        
        global $phpAds_config;
        
        // Where clause
        $aWhere = array();
        if (!empty($beginDate) && !empty($endDate))
            $aWhere[] = "s.day>='$beginDate' AND s.day<='$endDate'";
        if (!empty($agencyId))
            $aWhere[] = "c.agencyid=$agencyId";
        $where = implode(' AND ', $aWhere);
        $where = !empty($where) ? "AND $where" : '';
        
        $query = "
            SELECT
                s.day AS name,
                SUM(s.views) AS views,
                SUM(s.clicks) AS clicks,
                SUM(s.conversions) AS conversions
            FROM
                {$phpAds_config['tbl_clients']} AS c,
                {$phpAds_config['tbl_campaigns']} AS m,
                {$phpAds_config['tbl_banners']} AS b,
                {$phpAds_config['tbl_adstats']} AS s
            WHERE
                s.bannerid=b.bannerid
                AND b.campaignid=m.campaignid
                AND m.clientid=c.clientid
                $where
            GROUP BY
                name
        ";
        
        $aHistoryStats = array();
    
        $res = phpAds_dbQuery($query)
            or phpAds_sqlDie();
        while ($row = phpAds_dbFetchArray($res)) {
            $aHistoryStats[$row['name']] = $row;
        }
        
        return $aHistoryStats;
    }
?>
