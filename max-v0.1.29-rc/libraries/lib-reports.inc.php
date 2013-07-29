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
$Id: lib-reports.inc.php 3145 2005-05-20 13:15:01Z andrew $
*/


if (!defined('LIBMAIL_INCLUDED'))
	require (phpAds_path.'/libraries/lib-mail.inc.php');


function phpAds_SendMaintenanceReport ($clientid, $first_unixtimestamp, $last_unixtimestamp, $update=true)
{
	global $phpAds_config
			,$phpAds_CharSet
			,$date_format
			,$strBanner
			,$strCampaign
			,$strViews
			,$strClicks
			,$strConversions
			,$strLinkedTo
			,$strMailSubject
			,$strMailHeader
			,$strMailBannerStats
			,$strMailFooter
			,$strMailReportPeriod
			,$strMailReportPeriodAll
			,$strLogErrorBanners
			,$strLogErrorClients
			,$strLogErrorViews
			,$strLogErrorClicks
			,$strLogErrorConversions
			,$strNoStatsForCampaign
			,$strNoViewLoggedInInterval
			,$strNoClickLoggedInInterval
			,$strNoCampaignLoggedInInterval
			,$strTotal
			,$strTotalThisPeriod
	;
	
	
	// Convert timestamps to SQL format
	$last_sqltimestamp    = date ("YmdHis", $last_unixtimestamp);
	$first_sqltimestamp   = date ("YmdHis", $first_unixtimestamp);
	
	
	// Get Client information
	$res_client = phpAds_dbQuery(
		"SELECT".
		" clientid".
		",clientname".
		",contact".
		",email".
		",language".
		",report".
		",reportinterval".
		",reportlastdate".
		",UNIX_TIMESTAMP(reportlastdate) AS reportlastdate_t".
		" FROM ".$phpAds_config['tbl_clients'].
		" WHERE clientid=".$clientid
	);
	
	if (phpAds_dbNumRows($res_client) > 0)
	{
		$client = phpAds_dbFetchArray($res_client);
		
		// Load client language strings
		@include (phpAds_path.'/language/english/default.lang.php');
		if ($client['language'] != '') $phpAds_config['language'] = $client['language'];
		if ($phpAds_config['language'] != 'english' && file_exists(phpAds_path.'/language/'.$phpAds_config['language'].'/default.lang.php'))
			@include (phpAds_path.'/language/'.$phpAds_config['language'].'/default.lang.php');
		
		
		$active_campaigns = false;
		$log = "";
		
		// Fetch all campaigns belonging to client
		
		$res_campaigns = phpAds_dbQuery(
			"SELECT".
			" campaignid".
			",campaignname".
			",views".
			",clicks".
			",conversions".
			",expire".
			",UNIX_TIMESTAMP(expire) as expire_st".
			",activate".
			",UNIX_TIMESTAMP(activate) as activate_st".
			",active".
			" FROM ".$phpAds_config['tbl_campaigns'].
			" WHERE clientid=".$client['clientid'])
		or die($strLogErrorClients);
		
		while($campaign = phpAds_dbFetchArray($res_campaigns))
		{
			// Fetch all banners belonging to campaign
			$res_banners = phpAds_dbQuery(
				"SELECT".
				" bannerid".
				",campaignid".
				",URL".
				",active".
				",description".
				",alt".
				" FROM ".$phpAds_config['tbl_banners'].
				" WHERE campaignid=".$campaign['campaignid']
			) or die($strLogErrorBanners);
			
			$active_banners = false;
		    
			$log .= "\n".$strCampaign."  ".strip_tags(phpAds_buildName ($campaign['campaignid'], $campaign['campaignname']))."\n";
			$log .= "=======================================================\n\n";
			
			while($row_banners = phpAds_dbFetchArray($res_banners))
			{
				$adviews = phpAds_totalViews($row_banners["bannerid"]);
		        $client["views_used"] = $adviews;
				$adclicks = phpAds_totalClicks($row_banners["bannerid"]);
				$campaign["clicks_used"] = $adclicks;
				$adconversions = phpAds_totalConversions($row_banners["bannerid"]);
				$campaign["conversions_used"] = $adconversions;
				
				if ($adviews > 0 || $adclicks > 0 || $adconversions > 0)
				{
					$log .= $strBanner."  ".strip_tags(phpAds_buildBannerName ($row_banners['bannerid'], $row_banners['description'], $row_banners['alt']))."\n";
					$log .= $strLinkedTo.": ".$row_banners['URL']."\n";
					$log .= "-------------------------------------------------------\n";
					
					$active_banner_stats = false;
					
					if ($adviews > 0)
					{
						$log .= $strViews." (".$strTotal."):    ".$adviews."\n";
						
						// Fetch all adviews belonging to banner belonging to client, grouped by day
			            $res_adviews = phpAds_dbQuery(
			            	"SELECT".
			            	" SUM(views) as qnt".
			            	",DATE_FORMAT(day, '$date_format') as t_stamp_f".
			            	",TO_DAYS(day) AS the_day".
			            	" FROM ".$phpAds_config['tbl_adstats'].
			            	" WHERE bannerid=".$row_banners['bannerid'].
			            	" AND views>0".
			            	" AND UNIX_TIMESTAMP(day)>=".$first_unixtimestamp.
			            	" AND UNIX_TIMESTAMP(day)<".$last_unixtimestamp.
			            	" GROUP BY day".
			            	" ORDER BY day DESC")
			            or die($strLogErrorViews." ".phpAds_dbError());
				        
						if (phpAds_dbNumRows($res_adviews))
						{
							$total = 0;
							
							while($row_adviews = phpAds_dbFetchArray($res_adviews))
							{
								$log .= "      ".$row_adviews['t_stamp_f'].":   ".$row_adviews['qnt']."\n";
								$total += $row_adviews['qnt'];
							}
							
							$log .= $strTotalThisPeriod.": ".$total."\n";
							$active_banner_stats = true;
						}
						else
						{
							$log .= "      ".$strNoViewLoggedInInterval."\n";
						}
			        }
					
					if ($adclicks > 0)
					{
						// Total adclicks
				        $log .= "\n".$strClicks." (".$strTotal."):   ".$adclicks."\n";
						
						// Fetch all adclicks belonging to banner belonging to client, grouped by day
			            $res_adclicks = phpAds_dbQuery("SELECT".
			            	" SUM(clicks) as qnt".
			            	",DATE_FORMAT(day, '$date_format') as t_stamp_f".
			            	",TO_DAYS(day) AS the_day".
			            	" FROM ".$phpAds_config['tbl_adstats'].
			            	" WHERE bannerid = ".$row_banners['bannerid'].
			            	" AND clicks>0".
			            	" AND UNIX_TIMESTAMP(day)>=".$first_unixtimestamp.
			            	" AND UNIX_TIMESTAMP(day)<".$last_unixtimestamp.
			            	" GROUP BY day".
			            	" ORDER BY day DESC")
			            or die($strLogErrorClicks." ".phpAds_dbError());
						
						if (phpAds_dbNumRows($res_adclicks))
						{
							$total = 0;
							
							while($row_adclicks = phpAds_dbFetchArray($res_adclicks))
							{
								$log .= "      ".$row_adclicks['t_stamp_f'].":   ".$row_adclicks['qnt']."\n";
								$total += $row_adclicks['qnt'];
							}
							
							$log .= $strTotalThisPeriod.": ".$total."\n";
							$active_banner_stats = true;
						}
						else
						{
							$log .= "      ".$strNoClickLoggedInInterval."\n";
						}
					}
					
					if ($adconversions > 0)
					{
						// Total adconversions
				        $log .= "\n".$strConversions." (".$strTotal."):   ".$adconversions."\n";
						
						// Fetch all adclicks belonging to banner belonging to client, grouped by day
			            $res_adconversions = phpAds_dbQuery("SELECT".
			            	" SUM(conversions) as qnt".
			            	",DATE_FORMAT(day, '$date_format') as t_stamp_f".
			            	",TO_DAYS(day) AS the_day".
			            	" FROM ".$phpAds_config['tbl_adstats'].
			            	" WHERE bannerid = ".$row_banners['bannerid'].
			            	" AND conversions>0".
			            	" AND UNIX_TIMESTAMP(day)>=".$first_unixtimestamp.
			            	" AND UNIX_TIMESTAMP(day)<".$last_unixtimestamp.
			            	" GROUP BY day".
			            	" ORDER BY day DESC")
			            or die($strLogErrorConversions." ".phpAds_dbError());
						
						if (phpAds_dbNumRows($res_adconversions))
						{
							$total = 0;
							
							while($row_adconversions = phpAds_dbFetchArray($res_adconversions))
							{
								$log .= "      ".$row_adcconversions['t_stamp_f'].":   ".$row_adconversions['qnt']."\n";
								$total += $row_adconversions['qnt'];
							}
							
							$log .= $strTotalThisPeriod.": ".$total."\n";
							$active_banner_stats = true;
						}
						else
						{
							$log .= "      ".$strNoConversionLoggedInInterval."\n";
						}
					}
					
					$log .= "\n\n";
					
					if ($active_banner_stats == true || ($active_banner_stats == false && $campaign['active'] == 't'))
						$active_banners = true;
				}
			}
			
			if ($active_banners == true)
			{
				$active_campaigns = true;
			}
			else
			{
				$log .= $strNoStatsForCampaign."\n\n\n";
			}
		}
		
		
		// E-mail Stats to active clients
		if ($client["email"] != '' && $active_campaigns == true)
		{
			$Subject  = $strMailSubject.": ".$client["clientname"];
			
			$Body    = "$strMailHeader\n";
			$Body   .= "$strMailBannerStats\n";
			
			if ($first_unixtimestamp == 0)
				$Body   .= "$strMailReportPeriodAll\n\n";
			else
				$Body   .= "$strMailReportPeriod\n\n";
				
			$Body   .= "$log\n";
			$Body   .= "$strMailFooter";
			
			$Body    = str_replace ("{clientname}", 	$client['clientname'], $Body);
			$Body	 = str_replace ("{contact}", 		$client['contact'], $Body);
			$Body    = str_replace ("{adminfullname}", 	$phpAds_config['admin_fullname'], $Body);
			$Body	 = str_replace ("{startdate}", 		date(str_replace('%', '', $date_format), $first_unixtimestamp), $Body);
			$Body	 = str_replace ("{enddate}", 		date(str_replace('%', '', $date_format), $last_unixtimestamp), $Body);
			
			if ($phpAds_config['userlog_email']) 
				phpAds_userlogAdd (phpAds_actionAdvertiserReportMailed, $client['clientid'], $Subject."\n\n".$Body);
			
			if (phpAds_sendMail ($client['email'], $client['contact'], $Subject, $Body))
			{
				// Update last run
				if ($update == true)
					$res_update = phpAds_dbQuery("UPDATE ".$phpAds_config['tbl_clients']." SET reportlastdate=NOW() WHERE clientid=".$client['clientid']);
				
				return (true);
			}
		}
	}
	
	return (false);
}