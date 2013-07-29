<?php

/************************************************************************/
/* phpAdsNew 2                                                          */
/* ===========                                                          */
/*                                                                      */
/* Copyright (c) 2000-2002 by the phpAdsNew developers                  */
/* For more information visit: http://www.phpadsnew.com                 */
/*                                                                      */
/*                                                                      */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/





// Set text direction and characterset
$GLOBALS['phpAds_TextDirection']  		= "ltr";
$GLOBALS['phpAds_TextAlignRight'] 		= "right";
$GLOBALS['phpAds_TextAlignLeft']  		= "left";
$GLOBALS['phpAds_CharSet'] 				= "EUC-KR";

$GLOBALS['phpAds_DecimalPoint']			= ',';
$GLOBALS['phpAds_ThousandsSeperator']	= '.';


// Date & time configuration
$GLOBALS['date_format']				= "%d-%m-%Y";
$GLOBALS['time_format']				= "%H:%M:%S";
$GLOBALS['minute_format']			= "%H:%M";
$GLOBALS['month_format']			= "%m-%Y";
$GLOBALS['day_format']				= "%d-%m";
$GLOBALS['week_format']				= "%W-%Y";
$GLOBALS['weekiso_format']			= "%V-%G";



/*********************************************************/
/* Translations                                          */
/*********************************************************/

$GLOBALS['strHome'] 				= "Home";
$GLOBALS['strHelp']				= "����";
$GLOBALS['strNavigation'] 			= "Navigation";
$GLOBALS['strShortcuts'] 			= "�ٷΰ���";
$GLOBALS['strAdminstration'] 			= "���";
$GLOBALS['strMaintenance']			= "��������";
$GLOBALS['strProbability']			= "Ȯ��";
$GLOBALS['strInvocationcode']			= "ȣ���ڵ�";
$GLOBALS['strBasicInformation'] 		= "�⺻ ����";
$GLOBALS['strContractInformation'] 		= "��� ����";
$GLOBALS['strLoginInformation'] 		= "�α��� ����";
$GLOBALS['strOverview']				= "��Ϻ���";
$GLOBALS['strSearch']				= "�˻�(<u>S</u>)";
$GLOBALS['strHistory']				= "���";
$GLOBALS['strPreferences'] 			= "����";
$GLOBALS['strDetails']				= "�ڼ���";
$GLOBALS['strCompact']				= "������";
$GLOBALS['strVerbose']				= "���γ���";
$GLOBALS['strUser']				= "�����";
$GLOBALS['strEdit']				= "����";
$GLOBALS['strCreate']				= "����";
$GLOBALS['strDuplicate']			= "����";
$GLOBALS['strMoveTo']				= "�̵��ϱ�";
$GLOBALS['strDelete'] 				= "����";
$GLOBALS['strActivate']				= "Ȱ��ȭ";
$GLOBALS['strDeActivate'] 			= "�������";
$GLOBALS['strConvert']				= "��ȯ";
$GLOBALS['strRefresh']				= "���ΰ�ħ";
$GLOBALS['strSaveChanges']		 	= "������� ����";
$GLOBALS['strUp'] 				= "����";
$GLOBALS['strDown'] 				= "�Ʒ���";
$GLOBALS['strSave'] 				= "����";
$GLOBALS['strCancel']				= "���";
$GLOBALS['strPrevious'] 			= "����";
$GLOBALS['strPrevious_Key'] 			= "����(<u>p</u>)";
$GLOBALS['strNext'] 				= "����";
$GLOBALS['strNext_Key'] 				= "����(<u>n</u>)";
$GLOBALS['strYes']				= "��";
$GLOBALS['strNo']				= "�ƴϿ�";
$GLOBALS['strNone'] 				= "����";
$GLOBALS['strCustom']				= "����� ����";
$GLOBALS['strDefault'] 				= "�⺻����";
$GLOBALS['strOther']				= "��Ÿ";
$GLOBALS['strUnknown']				= "�˷����� ����";
$GLOBALS['strUnlimited'] 			= "���Ѿ���";
$GLOBALS['strUntitled']				= "�������";
$GLOBALS['strAll'] 				= "���";
$GLOBALS['strAvg'] 				= "���";
$GLOBALS['strAverage']				= "���";
$GLOBALS['strOverall'] 				= "��ü";
$GLOBALS['strTotal'] 				= "�հ�";
$GLOBALS['strActive'] 				= "��� ����";
$GLOBALS['strFrom']				= "From";
$GLOBALS['strTo']				= "to";
$GLOBALS['strLinkedTo'] 			= "linked to";
$GLOBALS['strDaysLeft'] 			= "���� �Ⱓ";
$GLOBALS['strCheckAllNone']			= "��� ����/ ����";
$GLOBALS['strKiloByte']				= "KB";
$GLOBALS['strExpandAll']			= "��� ����(<u>E</u>)";
$GLOBALS['strCollapseAll']			= "��� �����(<u>C</u>)";
$GLOBALS['strShowAll']				= "��� ����";
$GLOBALS['strNoAdminInteface']			= "���񽺸� �̿��� �� �����ϴ�.";
$GLOBALS['strFilterBySource']			= "�ҽ� ���͸�";
$GLOBALS['strFieldContainsErrors']		= "���� �ʵ忡 ������ �ֽ��ϴ�.:";
$GLOBALS['strFieldFixBeforeContinue1']		= "������ ������ �Ŀ�";
$GLOBALS['strFieldFixBeforeContinue2']		= "�ٽ� �����ؾ� �մϴ�..";
$GLOBALS['strDelimiter']			= "���б�ȣ";
$GLOBALS['strMiscellaneous']		= "��Ÿ";



// Properties
$GLOBALS['strName']				= "�̸�";
$GLOBALS['strSize']				= "ũ��";
$GLOBALS['strWidth'] 				= "�ʺ�";
$GLOBALS['strHeight'] 				= "����";
$GLOBALS['strURL2']				= "URL";
$GLOBALS['strTarget']				= "��� ������";
$GLOBALS['strLanguage'] 			= "���";
$GLOBALS['strDescription'] 			= "����";
$GLOBALS['strID']				= "ID";


// Login & Permissions
$GLOBALS['strAuthentification'] 		= "����";
$GLOBALS['strWelcomeTo']			= "ȯ���մϴ�. ";
$GLOBALS['strEnterUsername']			= "�α����ϱ� ���� �����ID�� ��й�ȣ�� �Է��ϼ���.";
$GLOBALS['strEnterBoth']			= "�����ID�� ��й�ȣ�� ��� �Է��ϼ���.";
$GLOBALS['strEnableCookies']			= $phpAds_productname."�� ����Ϸ��� ��Ű�� Ȱ��ȭ�ؾ��մϴ�.";
$GLOBALS['strLogin'] 				= "�α���";
$GLOBALS['strLogout'] 				= "�α׾ƿ�";
$GLOBALS['strUsername'] 			= "�����ID";
$GLOBALS['strPassword']				= "��й�ȣ";
$GLOBALS['strAccessDenied']			= "�׼����� �� �����ϴ�.";
$GLOBALS['strPasswordWrong']			= "�ùٸ� ��й�ȣ�� �ƴմϴ�.";
$GLOBALS['strNotAdmin']				= "������ �����ϴ�.";
$GLOBALS['strDuplicateClientName']		= "�Է��� ID�� �̹� �ֽ��ϴ�. �ٸ� ID�� �Է��ϼ���.";


// General advertising
$GLOBALS['strViews'] 				= "AdViews";
$GLOBALS['strClicks']				= "AdClicks";
$GLOBALS['strCTRShort'] 			= "Ŭ����";
$GLOBALS['strCTR'] 				= "Ŭ����";
$GLOBALS['strTotalViews'] 			= "�� AdViews";
$GLOBALS['strTotalClicks'] 			= "�� AdClicks";
$GLOBALS['strViewCredits'] 			= "AdView credits";
$GLOBALS['strClickCredits'] 			= "AdClick credits";


// Time and date related
$GLOBALS['strDate'] 				= "��¥";
$GLOBALS['strToday'] 				= "����";
$GLOBALS['strDay']				= "��";
$GLOBALS['strDays']				= "��";
$GLOBALS['strLast7Days']			= "�ֱ� 7��";
$GLOBALS['strWeek'] 				= "��";
$GLOBALS['strWeeks']				= "��";
$GLOBALS['strMonths']				= "��";
$GLOBALS['strThisMonth'] 			= "��";
$GLOBALS['strMonth'] 				= array("1��","2��","3��","4��","5��","6��","7��", "8��", "9��", "10��", "11��", "12��");
$GLOBALS['strDayShortCuts'] 			= array("��","��","ȭ","��","��","��","��");
$GLOBALS['strHour']				= "��";
$GLOBALS['strSeconds']				= "��";
$GLOBALS['strMinutes']				= "��";
$GLOBALS['strHours']				= "��";
$GLOBALS['strTimes']				= "ȸ";


// Advertiser
$GLOBALS['strClient']				= "������";
$GLOBALS['strClients'] 				= "������";
$GLOBALS['strClientsAndCampaigns']		= "������ & ķ����";
$GLOBALS['strAddClient'] 			= "�� ������ �߰�";
$GLOBALS['strAddClient_Key'] 		= "�� ������ �߰�(<u>n</u>)";
$GLOBALS['strTotalClients'] 			= "�� ������ ��";
$GLOBALS['strClientProperties']			= "������ �������";
$GLOBALS['strClientHistory']			= "������ ���";
$GLOBALS['strNoClients']			= "���� ��ϵ� �����ְ� �����ϴ�.";
$GLOBALS['strConfirmDeleteClient'] 		= "�ش� �����ָ� �����մϱ�?";
$GLOBALS['strConfirmResetClientStats']		= "�ش� �����ֿ� ���� ��� ��踦 �����մϱ�?";
$GLOBALS['strHideInactiveAdvertisers']		= "������� �ʴ� ������ �����";
$GLOBALS['strInactiveAdvertisersHidden']	= "�����ְ� ������ �ֽ��ϴ�.";


// Advertisers properties
$GLOBALS['strContact'] 				= "����ó";
$GLOBALS['strEMail'] 				= "�̸���";
$GLOBALS['strSendAdvertisingReport']		= "���� ������ �̸��Ϸ� �߼��մϴ�.";
$GLOBALS['strNoDaysBetweenReports']		= "���� �߼� ����";
$GLOBALS['strSendDeactivationWarning']  	= "ķ������ �����Ǹ� �˸��ϴ�.";
$GLOBALS['strAllowClientModifyInfo'] 		= "����ڰ� ������ �����ϴ� ���� ����մϴ�.";
$GLOBALS['strAllowClientModifyBanner'] 		= "����ڰ� ��ʸ� �����ϴ� ���� ����մϴ�.";
$GLOBALS['strAllowClientAddBanner'] 		= "����ڰ� ��ʸ� �߰��� �� �ְ� �մϴ�.";
$GLOBALS['strAllowClientDisableBanner'] 	= "����ڰ� �ڽ��� ��ʸ� ������ �� �ְ� �մϴ�.";
$GLOBALS['strAllowClientActivateBanner'] 	= "����ڰ� �ڽ��� ��ʸ� ��� �� �ְ� �մϴ�.";


// Campaign
$GLOBALS['strCampaign']				= "ķ����";
$GLOBALS['strCampaigns']			= "ķ����";
$GLOBALS['strTotalCampaigns'] 			= "�� ķ���� ��";
$GLOBALS['strActiveCampaigns'] 			= "����� ķ����";
$GLOBALS['strAddCampaign'] 			= "�� ķ���� �߰�";
$GLOBALS['strAddCampaign_Key'] 		= "�� ķ���� �߰�(<u>n</u>)";
$GLOBALS['strCreateNewCampaign']		= "�� ķ���� ����";
$GLOBALS['strModifyCampaign']			= "ķ���� ����";
$GLOBALS['strMoveToNewCampaign']		= "���ο� ķ�������� �̵�";
$GLOBALS['strBannersWithoutCampaign']		= "ķ������ ���� ���(�������)";
$GLOBALS['strDeleteAllCampaigns']		= "��� ķ���� ����";
$GLOBALS['strCampaignStats']			= "ķ���� ���";
$GLOBALS['strCampaignProperties']		= "ķ���� �������";
$GLOBALS['strCampaignOverview']			= "ķ���� ���";
$GLOBALS['strCampaignHistory']			= "ķ���� ���";
$GLOBALS['strNoCampaigns']			= "���� ���ǵ� ķ������ �����ϴ�.";
$GLOBALS['strConfirmDeleteAllCampaigns']	= "�ش� �������� ��� ķ������ �����մϱ�?";
$GLOBALS['strConfirmDeleteCampaign']		= "�� �������� ������ �����մϱ�?";
$GLOBALS['strHideInactiveCampaigns']		= "����� �ʴ� ķ���� �����";
$GLOBALS['strInactiveCampaignsHidden']		= "ķ������ ������ �ֽ��ϴ�.";


// Campaign properties
$GLOBALS['strDontExpire']			= "�� ķ������ �������� �ʽ��ϴ�.";
$GLOBALS['strActivateNow'] 			= "�� �������� ���� Ȱ��ȭ�մϴ�.";
$GLOBALS['strLow']				= "����";
$GLOBALS['strHigh']				= "����";
$GLOBALS['strExpirationDate']			= "������";
$GLOBALS['strActivationDate']			= "Ȱ����";
$GLOBALS['strViewsPurchased'] 			= "AdView ���� �Ⱓ";
$GLOBALS['strClicksPurchased'] 			= "AdClick ���� �Ⱓ";
$GLOBALS['strCampaignWeight']			= "ķ���� ����ġ";
$GLOBALS['strHighPriority']			= "�� ķ������ ��ʸ� ���� �켱 ������ �����մϴ�.<br>�� �ɼ��� �����ϸ� phpAdsNew�� �Ϸ絿�� ������ Ƚ����ŭ ��ʸ� �����Ű�� �˴ϴ�. �̿� ���� ������ �Ϸ��� �̹� ������ ��谡 �ʿ��մϴ�.";
$GLOBALS['strLowPriority']			= "�� ķ������ ��ʸ� ���� �켱 ������ �����մϴ�.<br>�� �ɼ��� ���� �켱 ������ ��ʰ� ������� ���� �� ��ʸ� �����ϱ� ���� ����մϴ�. ���� Ƚ���� ������ �� ������, ����ġ�� ���ؼ� ���� �켱 ���� ��ʵ鰣�� ������ ������ �� �ֽ��ϴ�.";
$GLOBALS['strTargetLimitAdviews']		= "���� �ִ� AdView�� ";
$GLOBALS['strTargetPerDay']			= "ȸ�� �����մϴ�.";
$GLOBALS['strPriorityAutoTargeting']		= "���� �Ⱓ ���� ��ʸ� �յ��ϰ� �����ŵ�ϴ�. ���� ���� ���� AdView�� ���� Ƚ���� �����˴ϴ�.";



// Banners (General)
$GLOBALS['strBanner'] 				= "���";
$GLOBALS['strBanners'] 				= "���";
$GLOBALS['strAddBanner'] 			= "�� ��� �߰�";
$GLOBALS['strAddBanner_Key'] 			= "�� ��� �߰�(<u>n</u>)";
$GLOBALS['strModifyBanner'] 			= "��� ����";
$GLOBALS['strActiveBanners'] 			= "����� ���";
$GLOBALS['strTotalBanners'] 			= "�� ��� ��";
$GLOBALS['strShowBanner']			= "��� ����";
$GLOBALS['strShowAllBanners']	 		= "��� ��� ����";
$GLOBALS['strShowBannersNoAdClicks']		= "AdClick ���� ��� ����";
$GLOBALS['strShowBannersNoAdViews']		= "AdView ���� ��� ����";
$GLOBALS['strDeleteAllBanners']	 		= "��� ��� ����";
$GLOBALS['strActivateAllBanners']		= "��� ��� Ȱ��ȭ";
$GLOBALS['strDeactivateAllBanners']		= "��� �������";
$GLOBALS['strBannerOverview']			= "��� ���";
$GLOBALS['strBannerProperties']			= "��� ��� ����";
$GLOBALS['strBannerHistory']			= "��� ���";
$GLOBALS['strBannerNoStats'] 			= "�ش� ��ʿ� ���� ��谡 �����ϴ�.";
$GLOBALS['strNoBanners']			= "���� ��ϵ� ��ʰ� �����ϴ�.";
$GLOBALS['strConfirmDeleteBanner']		= "�ش� ��ʸ� �����մϱ�??";
$GLOBALS['strConfirmDeleteAllBanners']		= "�ش� ķ������ ��� ��ʸ� �����մϱ�?";
$GLOBALS['strConfirmResetBannerStats']		= "�ش� ��ʿ� ���� ��� ��踦 �����մϱ�?";
$GLOBALS['strShowParentCampaigns']		= "���� ķ���� ǥ��";
$GLOBALS['strHideParentCampaigns']		= "���� ķ���� �����";
$GLOBALS['strHideInactiveBanners']		= "������� �ʴ� ��� ����";
$GLOBALS['strInactiveBannersHidden']		= "��ʰ� ������ �ֽ��ϴ�.";



// Banner (Properties)
$GLOBALS['strChooseBanner'] 			= "��� ������ �����ϼ���.";
$GLOBALS['strMySQLBanner'] 			= "���� ���(SQL - DB ������)";
$GLOBALS['strWebBanner'] 			= "���� ���(������ - �� ���� ���)";
$GLOBALS['strURLBanner'] 			= "�ܺ� ���";
$GLOBALS['strHTMLBanner'] 			= "HTML ���";
$GLOBALS['strTextBanner'] 			= "�ؽ�Ʈ ����";
$GLOBALS['strAutoChangeHTML']			= "AdClick�� �����ϱ� ���� HTML�� �����մϴ�.";
$GLOBALS['strUploadOrKeep']			= "���� �̹����� �̿��ϰų�<br> �ٸ� �̹����� ���ε���<br> �� �ֽ��ϴ�.";
$GLOBALS['strNewBannerFile'] 			= "��ʿ� ����� �̹����� �����ϼ���.";
$GLOBALS['strNewBannerURL'] 			= "�̹��� URL(incl. http://)";
$GLOBALS['strURL'] 				= "��� URL(incl. http://)";
$GLOBALS['strHTML'] 				= "HTML";
$GLOBALS['strTextBelow'] 			= "�̹��� ����";
$GLOBALS['strKeyword'] 				= "Ű����";
$GLOBALS['strWeight'] 				= "����ġ";
$GLOBALS['strAlt'] 				= "Alt �ؽ�Ʈ";
$GLOBALS['strStatusText']			= "����ǥ���� ����";
$GLOBALS['strBannerWeight']			= "��� ����ġ";


// Banner (swf)
$GLOBALS['strCheckSWF']				= "�÷��� ���Ͽ� �Էµ� ��ũ�� Ȯ���մϴ�.";
$GLOBALS['strConvertSWFLinks']			= "�÷��� ��ũ ��ȯ�մϴ�.";
$GLOBALS['strHardcodedLinks']			= "����� ��ũ";
$GLOBALS['strConvertSWF']			= "<br>���ε��� �÷��� ���Ͽ� URL�� ���Խ�ų �� �ֽ��ϴ�. ���ε��� �÷��� ���Ͽ� URL�� ���ԵǾ� �ֽ��ϴ�. phpAdsNew�� �÷��� ���Ͽ� ���Ե� URL�� ��ȯ���� ������ ��ʿ� ���� AdClick ���� ������ �� �����ϴ�. ������ �÷��� ���Ͽ� ���Ե� URL ����Դϴ�. �� URL�� ��ȯ�Ϸ��� <b>��ȯ</b>�� Ŭ���ϰ�, �ƴϸ� <b>���</b>�� Ŭ���ϼ���.Cancel</b>.<br><br>����: <b>��ȯ</b>�� Ŭ���ϸ� ���ε��� �÷��� ������ ������ �����մϴ�.<br>���� ������ ����Ͻʽÿ�. ��ʸ� ����µ� ����� �÷��� ������ ������ ������� ��� ������ �÷��� 4 ���Ϸ� �����մϴ�.<br><br>";
$GLOBALS['strCompressSWF']			= "���� ���� ���� ������ ���� SWF ���� ����";
$GLOBALS['strOverwriteSource']		= "�ҽ� �Ķ���� �����";


// Banner (network)
$GLOBALS['strBannerNetwork']			= "HTML ���ø�";
$GLOBALS['strChooseNetwork']			= "����� ���ø� �����ϼ���.";
$GLOBALS['strMoreInformation']			= "�ڼ��� ����...";
$GLOBALS['strRichMedia']			= "��ġ�̵��";
$GLOBALS['strTrackAdClicks']			= "AdClicks ����";


// Display limitations
$GLOBALS['strModifyBannerAcl'] 			= "�������� �ɼ�";
$GLOBALS['strACL'] 				= "��������";
$GLOBALS['strACLAdd'] 				= "�� ���� �߰�";
$GLOBALS['strACLAdd_Key'] 				= "�� ���� �߰�(<u>n</u>)";
$GLOBALS['strNoLimitations']			= "���� ����";
$GLOBALS['strApplyLimitationsTo']		= "���� �����ϱ�";
$GLOBALS['strRemoveAllLimitations']		= "��� ���� ����";
$GLOBALS['strEqualTo']				= "���� ���";
$GLOBALS['strDifferentFrom']			= "�ٸ� ���";
$GLOBALS['strAND']				= "�׸���";  						// logical operator
$GLOBALS['strOR']				= "�Ǵ�"; 						// logical operator
$GLOBALS['strOnlyDisplayWhen']			= "���� ���ǿ����� ��ʸ� ǥ���մϴ�.:";
$GLOBALS['strWeekDay'] 				= "����(��-��)";
$GLOBALS['strTime'] 				= "�ð�";
$GLOBALS['strUserAgent'] 			= "����� ������Ʈ";
$GLOBALS['strDomain'] 				= "������";
$GLOBALS['strClientIP'] 			= "Ŭ���̾�Ʈ IP";
$GLOBALS['strSource'] 				= "Source";
$GLOBALS['strBrowser'] 				= "������";
$GLOBALS['strOS'] 				= "OS";
$GLOBALS['strCountry'] 				= "����";
$GLOBALS['strContinent'] 			= "����";
$GLOBALS['strDeliveryLimitations']		= "�������� ����";
$GLOBALS['strDeliveryCapping']			= "�������� ����(Delivery capping)";
$GLOBALS['strTimeCapping']			= "����ڿ��� ��ʸ� �����ָ� ���� �Ⱓ ���� ��ʸ� �ٽ� �����Ű�� �ʽ��ϴ�.:";
$GLOBALS['strImpressionCapping']		= "������ ����ڿ��� ��ʸ� �����ϴ� Ƚ��:";


// Publisher
$GLOBALS['strAffiliate']			= "����Խ���";
$GLOBALS['strAffiliates']			= "����Խ���";
$GLOBALS['strAffiliatesAndZones']		= "����Խ��� & ����";
$GLOBALS['strAddNewAffiliate']			= "�� ����Խ��� �߰�";
$GLOBALS['strAddNewAffiliate_Key']			= "�� ����Խ��� �߰�(<u>n</u>)";
$GLOBALS['strAddAffiliate']			= "����Խ��� ����";
$GLOBALS['strAffiliateProperties']		= "����Խ��� �������";
$GLOBALS['strAffiliateOverview']		= "����Խ��� ���";
$GLOBALS['strAffiliateHistory']			= "����Խ��� ���";
$GLOBALS['strZonesWithoutAffiliate']		= "����Խ��� �̵�� ����";
$GLOBALS['strMoveToNewAffiliate']		= "�� ����Խ��ڷ� �̵�";
$GLOBALS['strNoAffiliates']			= "���� ���ǵ� ����Խ��ڰ� �����ϴ�.";
$GLOBALS['strConfirmDeleteAffiliate']		= "�ش� ����Խ��ڸ� �����մϱ�?";
$GLOBALS['strMakePublisherPublic']		= "����Խ��ڿ��� �ش��ϴ� ������ �����մϴ�.";


// Publisher (properties)
$GLOBALS['strWebsite']				= "�� ����Ʈ";
$GLOBALS['strAllowAffiliateModifyInfo'] 	= "����ڰ� ������ �����ϴ� ���� ����մϴ�.";
$GLOBALS['strAllowAffiliateModifyZones'] 	= "����ڰ� ������ �����ϴ� ���� ����մϴ�.";
$GLOBALS['strAllowAffiliateLinkBanners'] 	= "����ڰ� �ڽ��� ������ ��ʸ� ������ �� �ְ� �մϴ�.";
$GLOBALS['strAllowAffiliateAddZone'] 		= "����ڰ� �� ������ �����ϴ� ���� ����մϴ�.";
$GLOBALS['strAllowAffiliateDeleteZone'] 	= "����ڰ� ���� ������ �����ϴ� ���� ����մϴ�.";


// Zone
$GLOBALS['strZone']				= "����";
$GLOBALS['strZones']				= "����";
$GLOBALS['strAddNewZone']			= "�� ���� �߰�";
$GLOBALS['strAddNewZone_Key']			= "�� ���� �߰�(<u>n</u>)";
$GLOBALS['strAddZone']				= "���� ����";
$GLOBALS['strModifyZone']			= "���� ����";
$GLOBALS['strLinkedZones']			= "����� ����";
$GLOBALS['strZoneOverview']			= "���� ���";
$GLOBALS['strZoneProperties']			= "���� �������";
$GLOBALS['strZoneHistory']			= "���� ���";
$GLOBALS['strNoZones']				= "���� ��ϵ� ������ �����ϴ�.";
$GLOBALS['strConfirmDeleteZone']		= "�� ������ �����մϱ�?";
$GLOBALS['strZoneType']				= "���� ����";
$GLOBALS['strBannerButtonRectangle']		= "���, ��ư �Ǵ� �簢��";
$GLOBALS['strInterstitial']			= "���� �Ǵ� �÷��� DHTML";
$GLOBALS['strPopup']				= "�˾�";
$GLOBALS['strTextAdZone']			= "�ؽ�Ʈ ����";
$GLOBALS['strShowMatchingBanners']		= "��ġ�ϴ� ��� ����";
$GLOBALS['strHideMatchingBanners']		= "��ġ�ϴ� ��� �����";


// Advanced zone settings
$GLOBALS['strAdvanced']				= "��� ����";
$GLOBALS['strChains']				= "����";
$GLOBALS['strChainSettings']			= "���� ����";
$GLOBALS['strZoneNoDelivery']			= "�� �������� � ��ʵ� ������ �� �����ϴ�...";
$GLOBALS['strZoneStopDelivery']			= "���������� �����ϰ� ��ʸ� ǥ������ �ʽ��ϴ�.";
$GLOBALS['strZoneOtherZone']			= "���õ� ������ ��� ǥ���մϴ�.";
$GLOBALS['strZoneUseKeywords']			= "�Ʒ��� �Էµ� Ű���带 ����ؼ� ��ʸ� �����ϼ���.";
$GLOBALS['strZoneAppend']			= "�� ������ ����� ��ʿ� �˾��̳� ���� ��� ȣ�� �ڵ带 �׻� �߰��մϴ�.";
$GLOBALS['strAppendSettings']			= "��� ÷�� ����";
$GLOBALS['strZonePrependHTML']			= "�� ������ ǥ�õ� �ؽ�Ʈ ���� �տ� HTML �ڵ带 �߰��մϴ�.";
$GLOBALS['strZoneAppendHTML']			= "�� ������ ǥ�õ� �ؽ�Ʈ ���� �ڿ� HTML �ڵ带 �߰��մϴ�.";


// Zone probability
$GLOBALS['strZoneProbListChain']		= "������ ������ ����� ��ʴ� ��� ��(null) �켱�����Դϴ�. ���� ������ ������ �����ϴ�.:";
$GLOBALS['strZoneProbNullPri']			= "�� ������ ����� ��ʴ� ��� ��(null) �켱�����Դϴ�.";


// Linked banners/campaigns
$GLOBALS['strSelectZoneType']			= "������ ����� ������ �����ϼ���.";
$GLOBALS['strBannerSelection']			= "��� ����";
$GLOBALS['strCampaignSelection']		= "ķ���� ����";
$GLOBALS['strInteractive']			= "Interactive";
$GLOBALS['strRawQueryString']			= "Ű����";
$GLOBALS['strIncludedBanners']			= "����� ���";
$GLOBALS['strLinkedBannersOverview']		= "����� ��� ���";
$GLOBALS['strLinkedBannerHistory']		= "����� ��� ���";
$GLOBALS['strNoZonesToLink']			= "��ʿ� ������ �� �ִ� ������ �����ϴ�.";
$GLOBALS['strNoBannersToLink']			= "���� �� ������ ������ ��ʰ� �����ϴ�.";
$GLOBALS['strNoLinkedBanners']			= "���� �� ������ ����� ��ʰ� �����ϴ�.";
$GLOBALS['strMatchingBanners']			= "��ġ�ϴ� ��� �� {count}";
$GLOBALS['strNoCampaignsToLink']		= "���� �� ������ ������ �������� �����ϴ�.";
$GLOBALS['strNoZonesToLinkToCampaign']  	= "���� �� ������ ����� �������� �����ϴ�.";
$GLOBALS['strSelectBannerToLink']		= "�� ������ ������ ��ʸ� �����ϼ���:";
$GLOBALS['strSelectCampaignToLink']		= "�� ������ ������ ķ���� �����մϴ�:";


// Statistics
$GLOBALS['strStats'] 				= "���";
$GLOBALS['strNoStats']				= "���� �̿��� �� �ִ� ��谡 �����ϴ�.";
$GLOBALS['strConfirmResetStats']		= "��� ��踦 �����Ͻðڽ��ϱ�?";
$GLOBALS['strGlobalHistory']			= "��ü ���";
$GLOBALS['strDailyHistory']			= "���� ���";
$GLOBALS['strDailyStats'] 			= "���� ���";
$GLOBALS['strWeeklyHistory']			= "�ְ� ���";
$GLOBALS['strMonthlyHistory']			= "���� ���";
$GLOBALS['strCreditStats'] 			= "Credit statistics";
$GLOBALS['strDetailStats'] 			= "Detailed statistics";
$GLOBALS['strTotalThisPeriod']			= "�Ⱓ �հ�";
$GLOBALS['strAverageThisPeriod']		= "�Ⱓ ���";
$GLOBALS['strDistribution']			= "����";
$GLOBALS['strResetStats'] 			= "��� �ʱ�ȭ";
$GLOBALS['strSourceStats']			= "�ҽ� ���";
$GLOBALS['strSelectSource']			= "��ȸ�� �ҽ��� �����ϼ���:";
$GLOBALS['strSizeDistribution']		= "ũ�⺰ ����";
$GLOBALS['strCountryDistribution']	= "������ ����";
$GLOBALS['strEffectivity']			= "Effectivity";


// Hosts
$GLOBALS['strHosts']				= "ȣ��Ʈ";
$GLOBALS['strTopHosts'] 			= "��û ȣ��Ʈ ����";
$GLOBALS['strTopCountries'] 		= "��û ���� ����";
$GLOBALS['strRecentHosts'] 			= "�ֱ� ��û ȣ��Ʈ";


// Expiration
$GLOBALS['strExpired']				= "�����";
$GLOBALS['strExpiration'] 			= "������";
$GLOBALS['strNoExpiration'] 			= "������ ��������";
$GLOBALS['strEstimated'] 			= "���� ������";


// Reports
$GLOBALS['strReports']				= "����";
$GLOBALS['strSelectReport']			= "������ ������ �����ϼ���.";


// Userlog
$GLOBALS['strUserLog']				= "����� �α�";
$GLOBALS['strUserLogDetails']			= "����� �α� �׸�";
$GLOBALS['strDeleteLog']			= "�α� ����";
$GLOBALS['strAction']				= "Ȱ�� ���";
$GLOBALS['strNoActionsLogged']			= "��ϵ� ������ �����ϴ�.";


// Code generation
$GLOBALS['strGenerateBannercode']		= "��� �ڵ� ����";
$GLOBALS['strChooseInvocationType']		= "ȣ���� ��� ������ �����ϼ���.";
$GLOBALS['strGenerate']				= "�����ϱ�";
$GLOBALS['strParameters']			= "�Ķ����";
$GLOBALS['strFrameSize']			= "������ ũ��";
$GLOBALS['strBannercode']			= "����ڵ�";


// Errors
$GLOBALS['strMySQLError'] 			= "SQL ����:";
$GLOBALS['strLogErrorClients'] 			= "[phpAds] �����ͺ��̽����� �����ָ� �������� ���� ������ �߻��߽��ϴ�..";
$GLOBALS['strLogErrorBanners'] 			= "[phpAds] �����ͺ��̽����� ��ʸ� �������� ���� ������ �߻��߽��ϴ�..";
$GLOBALS['strLogErrorViews'] 			= "[phpAds] �����ͺ��̽����� AdView�� �������� ���� ������ �߻��߽��ϴ�..";
$GLOBALS['strLogErrorClicks'] 			= "[phpAds] �����ͺ��̽����� AdClick�� �������� ���� ������ �߻��߽��ϴ�.";
$GLOBALS['strErrorViews'] 			= "�� Ƚ���� �Է��ϰų� �������� ���� ���ڸ� üũ�ؾ��մϴ�!";
$GLOBALS['strErrorNegViews'] 			= "������ ����� �� �����ϴ�.";
$GLOBALS['strErrorClicks'] 			= "Ŭ�� Ƚ���� �Է��ϰų� �������� ���� ���ڸ� üũ�ؾ��մϴ�!";
$GLOBALS['strErrorNegClicks'] 			= "������ Ŭ��(negative click)�� ������ �ʽ��ϴ�.";
$GLOBALS['strNoMatchesFound']			= "�˻� ����� �����ϴ�.";
$GLOBALS['strErrorOccurred']			= "������ �߻��߽��ϴ�.";
$GLOBALS['strErrorUploadSecurity']		= "���� ������ �߰ߵǾ����ϴ�. ���ε带 �����մϴ� !";
$GLOBALS['strErrorUploadBasedir']		= "���ε�� ���Ͽ� �׼����� �� �����ϴ�. ���� ��� �Ǵ� open_basedir ���� ������ �� �ֽ��ϴ�.";
$GLOBALS['strErrorUploadUnknown']		= "�� �� ���� ������ ���ε�� ���Ͽ� �׼����� �� �����ϴ�. PHP ������ Ȯ���Ͻʽÿ�.";
$GLOBALS['strErrorStoreLocal']			= "���� ���͸��� ��ʸ� �����ϴ� ���� ������ �߻��߽��ϴ�. ���� ���͸� ��� ������ �߸��Ǿ��� �� �ֽ��ϴ�.";
$GLOBALS['strErrorStoreFTP']			= "FTP ������ ��ʸ� ���ε��ϴ� ���� ������ �߻��߽��ϴ�. ������ �̿��� �� ���ų� FTP ���� ������ �߸��Ǿ��� �� �ֽ��ϴ�.";


// E-mail
$GLOBALS['strMailSubject'] 			= "������ ����";
$GLOBALS['strAdReportSent']			= "������ ������ ���½��ϴ�";
$GLOBALS['strMailSubjectDeleted'] 		= "��� �����";
$GLOBALS['strMailHeader'] 			= "{contact}��,\n";
$GLOBALS['strMailBannerStats'] 			= "{clientname}�� ��� ���� ������ �����ϴ�.";
$GLOBALS['strMailFooter'] 			= "Regards,\n   {adminfullname}";
$GLOBALS['strMailClientDeactivated'] 		= "���� ��ʴ� ���� ������ �̿��� �� �����ϴ�.";
$GLOBALS['strMailNothingLeft'] 			= "�� ����Ʈ�� ��� ȫ���Ϸ��� ����ڿ��� �����Ͻʽÿ�.";
$GLOBALS['strClientDeactivated']		= "�� ķ������ ���� ������ ���� ������ ����� �ʽ��ϴ�.";
$GLOBALS['strBeforeActivate']			= "���� �������� �ƴմϴ�.";
$GLOBALS['strAfterExpire']			= "�������Դϴ�.";
$GLOBALS['strNoMoreClicks']			= "�����ִ� AdClicks�� �����ϴ�.";
$GLOBALS['strNoMoreViews']			= "�����ִ� AdViews�� �����ϴ�.";
$GLOBALS['strWarnClientTxt']			= "��ʿ� �����ִ� AdClciks �Ǵ� AdViews�� {limit}�Դϴ�. \n�����ִ� AdCliks�� AdViews�� ���� �� ��ʸ� � �����մϴ�. ";
$GLOBALS['strViewsClicksLow']			= "AdViews/AdClicks�� �����ϴ�.";
$GLOBALS['strNoViewLoggedInInterval']   	= "�� �Ⱓ ������ ������ ��ϵ� AdViews�� �����ϴ�.";
$GLOBALS['strNoClickLoggedInInterval']  	= "�� �Ⱓ ������ ������ ��ϵ� AdClicks�� �����ϴ�.";
$GLOBALS['strMailReportPeriod']			= "�� �������� {startdate}���� {enddate}������ ��踦 �����ϰ� �ֽ��ϴ�.";
$GLOBALS['strMailReportPeriodAll']		= "�� �������� {enddate}������ ��踦 �����ϰ� �ֽ��ϴ�.";
$GLOBALS['strNoStatsForCampaign'] 		= "�� ķ���ο��� �̿��� �� �ִ� ��谡 �����ϴ�.";


// Priority
$GLOBALS['strPriority']				= "�켱����";


// Settings
$GLOBALS['strSettings'] 			= "����";
$GLOBALS['strGeneralSettings']			= "�Ϲ� ����";
$GLOBALS['strMainSettings']			= "����";
$GLOBALS['strAdminSettings']			= "���� ����";


// Product Updates
$GLOBALS['strProductUpdates']			= "��ǰ ������Ʈ";




/*********************************************************/
/* Keyboard shortcut assignments                         */
/*********************************************************/


// Reserved keys
// Do not change these unless absolutely needed
$GLOBALS['keyHome']			= 'h';
$GLOBALS['keyUp']			= 'u';
$GLOBALS['keyNextItem']		= '.';
$GLOBALS['keyPreviousItem']	= ',';
$GLOBALS['keyList']			= 'l';


// Other keys
// Please make sure you underline the key you
// used in the string in default.lang.php
$GLOBALS['keySearch']		= 's';
$GLOBALS['keyCollapseAll']	= 'c';
$GLOBALS['keyExpandAll']	= 'e';
$GLOBALS['keyAddNew']		= 'n';
$GLOBALS['keyNext']			= 'n';
$GLOBALS['keyPrevious']		= 'p';

?>