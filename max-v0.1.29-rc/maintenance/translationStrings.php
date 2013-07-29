<?php
//  required by phpAds_deactivateMail()
$GLOBALS['strMailSubjectDeleted'] 		= "Deactivated banners";
$GLOBALS['strMailHeader'] 			= "Dear {contact},\n";
$GLOBALS['strMailClientDeactivated'] 		= "The following banners have been disabled because";
$GLOBALS['strNoMoreClicks']			= "there are no AdClicks remaining";
$GLOBALS['strNoMoreViews']			= "there are no AdViews remaining";
$GLOBALS['strBeforeActivate']			= "the activation date has not yet been reached";
$GLOBALS['strAfterExpire']			= "the expiration date has been reached";
$GLOBALS['strBannersWithoutCampaign']		= "Banners without a campaign";
$GLOBALS['strMailNothingLeft'] 			= "If you would like to continue advertising on our website, please feel free to contact us.\nWe'd be glad to hear from you.";
$GLOBALS['strMailFooter'] 			= "Regards,\n   {adminfullname}";
$GLOBALS['strUntitled']				= "Untitled";

//  various
$GLOBALS['strLogErrorClients'] 			= "[phpAds] An error occurred while trying to fetch the advertisers from the database.";

//  lib-reports::phpAds_SendMaintenanceReport()
$GLOBALS['strBanner'] 				= "Banner";
$GLOBALS['strCampaign']				= "Campaign";
$GLOBALS['strViews'] 				= "AdViews";
$GLOBALS['strClicks']				= "AdClicks";
$GLOBALS['strConversions']			= "AdSales";
$GLOBALS['strLinkedTo'] 			= "linked to";
$GLOBALS['strMailSubject'] 			= "Advertiser report";
$GLOBALS['strMailBannerStats'] 			= "Below you will find the banner statistics for {clientname}:";
$GLOBALS['strMailReportPeriod']			= "This report includes statistics from {startdate} up to {enddate}.";
$GLOBALS['strMailReportPeriodAll']		= "This report includes all statistics up to {enddate}.";
$GLOBALS['strLogErrorBanners'] 			= "[phpAds] An error occurred while trying to fetch the banners from the database.";
$GLOBALS['strLogErrorViews'] 			= "[phpAds] An error occurred while trying to fetch the adviews from the database.";
$GLOBALS['strLogErrorClicks'] 			= "[phpAds] An error occurred while trying to fetch the adclicks from the database.";
$GLOBALS['strLogErrorConversions'] 		= "[phpAds] An error occurred while trying to fetch the adsales from the database.";
$GLOBALS['strNoStatsForCampaign'] 		= "There are no statistics available for this campaign";
$GLOBALS['strNoViewLoggedInInterval']   	= "No AdViews were logged during the span of this report";
$GLOBALS['strNoClickLoggedInInterval']  	= "No AdClicks were logged during the span of this report";
$GLOBALS['strTotal'] 				= "Total";
$GLOBALS['strTotalThisPeriod']		= "Total this period";
$GLOBALS['date_format']				= "%d-%m-%Y";

//  lib-statistics
$GLOBALS['strLogin'] 				= "Login";
$GLOBALS['strShowBanner']			= "Show banner";


?>