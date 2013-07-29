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
$Id: maintenance-activation.php 3145 2005-05-20 13:15:01Z andrew $
*/

/*********************************************************/
/* Mail clients and check for activation  				 */
/* and expiration dates					 				 */
/*********************************************************/

$res_clients = phpAds_dbQuery("
	SELECT
        clientid,
        clientname,
        contact,
        email,
        language,
        reportdeactivate
    FROM " . $conf['table']['clients']
) or die($strLogErrorClients);

while ($client = phpAds_dbFetchArray($res_clients)) {
	// Send Query
	$res_campaigns = phpAds_dbQuery(
		"SELECT".
		" campaignid".
		",campaignname".
		",clientid".
		",views".
		",clicks".
		",expire".
		",UNIX_TIMESTAMP(expire) as expire_st".
		",activate".
		",UNIX_TIMESTAMP(activate) as activate_st".
		",active".
		" FROM ".$conf['table']['campaigns'].
		" WHERE clientid=".$client['clientid']
	) or die($strLogErrorClients);
	
	while ($campaign = phpAds_dbFetchArray($res_campaigns)) {
		$active = "t";

		if ($campaign["clicks"] == 0 || $campaign["views"] == 0)
			$active = "f";
		if (time() < $campaign["activate_st"])
			$active = "f";
		if (time() > $campaign["expire_st"] && $campaign["expire_st"] != 0)
			$active = "f";
		if ($campaign["active"] != $active) {
			if ($active == "t") {
				phpAds_userlogAdd(phpAds_actionActiveCampaign, $campaign['campaignid']);
			} else {
				phpAds_userlogAdd(phpAds_actionDeactiveCampaign, $campaign['campaignid']);
				phpAds_deactivateMail($campaign);
			}
			phpAds_dbQuery("UPDATE ".$conf['table']['campaigns']." SET active='$active' WHERE campaignid=".$campaign['campaignid']);
		}
	}
}
?>
