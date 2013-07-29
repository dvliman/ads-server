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
$Id: affiliate-edit.php 3145 2005-05-20 13:15:01Z andrew $
*/



// Include required files
require ("config.php");
require ("lib-statistics.inc.php");
require ("lib-zones.inc.php");
require ("lib-languages.inc.php");


// Register input variables
phpAds_registerGlobal ('move', 'name', 'website', 'contact', 'email', 'language', 'publiczones', 
					   'errormessage', 'username', 'password', 'affiliatepermissions', 'submit',
					   'publiczones_old', 'pwold', 'pw', 'pw2', 'mnemonic');


// Security check
phpAds_checkAccess(phpAds_Admin + phpAds_Agency + phpAds_Affiliate);


/*********************************************************/
/* Affiliate interface security                          */
/*********************************************************/

if (phpAds_isUser(phpAds_Affiliate))
{
	if (phpAds_isAllowed(phpAds_ModifyInfo))
	{
		$query = "SELECT agencyid".
			" FROM ".$phpAds_config['tbl_affiliates'].
			" WHERE affiliateid=".phpAds_getUserID();
		$res = phpAds_dbQuery($query)
			or phpAds_sqlDie();
		if ($row = phpAds_dbFetchArray($res))
		{
			$agencyid = $row['agencyid'];
			$affiliateid = phpAds_getUserID();
		}
		else
		{
			phpAds_PageHeader("2");
			phpAds_Die ($strAccessDenied, $strNotAdmin);
		}
	}
	else
	{
		phpAds_PageHeader("1");
		phpAds_Die ($strAccessDenied, $strNotAdmin);
	}
}
elseif (phpAds_isUser(phpAds_Agency))
{
	$agencyid = phpAds_getUserID();

	if (isset($affiliateid) && ($affiliateid != ''))
	{
		$query = "SELECT affiliateid".
			" FROM ".$phpAds_config['tbl_affiliates'].
			" WHERE affiliateid=".$affiliateid.
			" AND agencyid=".$agencyid;
			
		$res = phpAds_dbQuery($query) or phpAds_sqlDie();
		if (phpAds_dbNumRows($res) == 0)
		{
			phpAds_PageHeader("2");
			phpAds_Die ($strAccessDenied, $strNotAdmin);
		}
	}
}
else
{
	$agencyid = 0;
}




/*********************************************************/
/* Process submitted form                                */
/*********************************************************/

if (isset($submit))
{
	$errormessage = array();
	
	
	// Get previous values
	if (isset($affiliateid))
	{
		$res = phpAds_dbQuery("
			SELECT
				*
			FROM
				".$phpAds_config['tbl_affiliates']."
			WHERE
				affiliateid = '".$affiliateid."'
			") or phpAds_sqlDie();
		
		if (phpAds_dbNumRows($res))
		{
			$affiliate = phpAds_dbFetchArray($res);
		}
	}
	
	// Name
	if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
		$affiliate['name'] = trim($name);
	
    // Mnemonic
    $affiliate['mnemonic'] = trim($mnemonic);
    
	// Website
	if (isset($website) && $website == 'http://')
		$affiliate['website'] = '';
	else
		$affiliate['website'] = trim($website);
	
	// Default fields
	if (!isset($affiliate['agencyid'])) { $affiliate['agencyid'] = $agencyid; }
	$affiliate['contact']     = trim($contact);
	$affiliate['email'] 	  = trim($email);
	$affiliate['language']    = trim($language);
	
	// Public
	$affiliate['publiczones'] = isset($publiczones) ? 't' : 'f';
	
	
	if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
	{
		// Password
		if (isset($password))
		{
			if ($password == '')
				$affiliate['password'] = '';
			elseif ($password != '********')
				$affiliate['password'] = md5($password);
		}
		
		
		// Username
		if (isset($username) && $username != '')
		{
			$res = phpAds_dbQuery("
				SELECT
					*
				FROM
					".$phpAds_config['tbl_clients']."
				WHERE
					LOWER(clientusername) = '".strtolower($username)."'
			") or phpAds_sqlDie(); 
			
			$duplicateclient = (phpAds_dbNumRows($res) > 0);
			$duplicateadmin  = (strtolower($phpAds_config['admin']) == strtolower($username));
			
			if ($affiliateid == '')
			{
				$res = phpAds_dbQuery("
					SELECT
						*
					FROM
						".$phpAds_config['tbl_affiliates']."
					WHERE
						LOWER(username) = '".strtolower($username)."'
				") or phpAds_sqlDie(); 
				
				if (phpAds_dbNumRows($res) > 0 || $duplicateclient || $duplicateadmin)
					$errormessage[] = $strDuplicateClientName;
			}
			else
			{
				$res = phpAds_dbQuery("
					SELECT
						*
					FROM
						".$phpAds_config['tbl_affiliates']."
					WHERE
						LOWER(username) = '".strtolower($username)."' AND
						affiliateid != '$affiliateid'
					") or phpAds_sqlDie(); 
				
				if (phpAds_dbNumRows($res) > 0 || $duplicateclient || $duplicateadmin)
					$errormessage[] = $strDuplicateClientName;
			}
			
			if (count($errormessage) == 0)
			{
				// Set username
				$affiliate['username'] = $username;
			}
		}
		
		
		// Permissions
		$affiliate['permissions'] = 0;
		if (isset($affiliatepermissions) && is_array($affiliatepermissions))
		{
			for ($i=0;$i<sizeof($affiliatepermissions);$i++)
			{
				$affiliate['permissions'] += $affiliatepermissions[$i];
			}
		}
	}
	else
	{
		// Password
		if (isset($pwold) && strlen($pwold) ||
			isset($pw) && strlen($pw) ||
			isset($pw2) && strlen($pw2))
		{
			if (md5($pwold) != $affiliate['password'])
				$errormessage[] = $strPasswordWrong;
			elseif (!strlen($pw) || strstr("\\", $pw))
				$errormessage[] = $strInvalidPassword;
			elseif (strcmp($pw, $pw2))
				$errormessage[] = $strNotSamePasswords;
			else
				$affiliate['password'] = md5($pw);
		}
	}
	
	
	
	if (count($errormessage) == 0)
	{
		if ($affiliateid && $publiczones != 't' && $publiczones_old == 't')
		{
			// Reset append codes which called this affiliate's zones
			$res = phpAds_dbQuery("
					SELECT
						zoneid
					FROM
						".$phpAds_config['tbl_zones']."
					WHERE
						affiliateid = '$affiliateid'
				");
			
			$zones = array();
			while ($currentrow = phpAds_dbFetchArray($res))
				$zones[] = $currentrow['zoneid'];
			
			if (count($zones))
			{
				$res = phpAds_dbQuery("
						SELECT
							zoneid,
							append
						FROM
							".$phpAds_config['tbl_zones']."
						WHERE
							appendtype = ".phpAds_ZoneAppendZone." AND
							affiliateid <> '$affiliateid'
					");
				
				while ($currentrow = phpAds_dbFetchArray($res))
				{
					$append = phpAds_ZoneParseAppendCode($currentrow['append']);
					
					if (in_array($append[0]['zoneid'], $zones))
					{
						phpAds_dbQuery("
								UPDATE
									".$phpAds_config['tbl_zones']."
								SET
									appendtype = ".phpAds_ZoneAppendRaw.",
									append = ''
								WHERE
									zoneid = '".$currentrow['zoneid']."'
							");
					}
				}
			}
		}
		
		
		if (!isset($affiliateid) || $affiliateid == '')
		{
			$keys = array();
			$values = array();
			
			while (list($key, $value) = each($affiliate))
			{
				$keys[] = $key;
				$values[] = $value;
			}
			
			$query  = "INSERT INTO ".$phpAds_config['tbl_affiliates']." (";
			$query .= implode(", ", $keys);
			$query .= ") VALUES ('";
			$query .= implode("', '", $values);
			$query .= "')";
			
			// Insert
			phpAds_dbQuery($query) 
				or phpAds_sqlDie();
			
			$affiliateid = phpAds_dbInsertID();
			
			// Go to next page
			if (isset($move) && $move == 't')
			{
				// Move loose zones to this affiliate
				
				$res = phpAds_dbQuery("
					UPDATE
						".$phpAds_config['tbl_zones']."
					SET
						affiliateid = '".$affiliateid."'
					WHERE
						ISNULL(affiliateid) OR
						affiliateid = 0
				");
				
				header ("Location: affiliate-zones.php?affiliateid=".$affiliateid);
			}
			else
			{
				header ("Location: zone-edit.php?affiliateid=".$affiliateid);
			}
		}
		else
		{
			$pairs = array();
			
			while (list($key, $value) = each($affiliate))
				$pairs[] = " ".$key."='".$value."'";
			
			$query  = "UPDATE ".$phpAds_config['tbl_affiliates']." SET ";
			$query .= trim(implode(",", $pairs))." ";
			$query .= "WHERE affiliateid = ".$affiliateid;
			
			// Update
			phpAds_dbQuery($query) 
				or phpAds_sqlDie();
			
			// Go to next page
			if (phpAds_isUser(phpAds_Affiliate))
			{
				// Set current session to new language
				$Session['language'] = $language;
				phpAds_SessionDataStore();
			}
			
			header ("Location: affiliate-zones.php?affiliateid=".$affiliateid);
		}
		
		exit;
	}
	else
	{
		// If an error occured set the password back to its previous value
		$affiliate['password'] = $password;
	}
}



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

if ($affiliateid != "")
{
	if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
	{
		if (isset($Session['prefs']['affiliate-index.php']['listorder']))
			$navorder = $Session['prefs']['affiliate-index.php']['listorder'];
		else
			$navorder = '';
		
		if (isset($Session['prefs']['affiliate-index.php']['orderdirection']))
			$navdirection = $Session['prefs']['affiliate-index.php']['orderdirection'];
		else
			$navdirection = '';
		
		
		// Get other affiliates
		if (phpAds_isUser(phpAds_Admin))
		{
			$query="SELECT * FROM {$phpAds_config['tbl_affiliates']}" . phpAds_getAffiliateListOrder($navorder, $navdirection);
		}
		elseif (phpAds_isUser(phpAds_Agency))
		{
			$query="SELECT * FROM {$phpAds_config['tbl_affiliates']} WHERE agencyid=$agencyid" . phpAds_getAffiliateListOrder($navorder, $navdirection);
		}
		elseif (phpAds_isUser(phpAds_Affiliate))
		{
			$query="SELECT * FROM {$phpAds_config['tbl_affiliates']} WHERE affiliateid=$affiliateid" . phpAds_getAffiliateListOrder($navorder, $navdirection);
		}
		$res = phpAds_dbQuery($query)
			or phpAds_sqlDie();
		
		while ($row = phpAds_dbFetchArray($res))
		{
			phpAds_PageContext (
				phpAds_buildAffiliateName ($row['affiliateid'], $row['name']),
				"affiliate-edit.php?affiliateid=".$row['affiliateid'],
				$affiliateid == $row['affiliateid']
			);
		}
		
		phpAds_PageShortcut($strAffiliateHistory, 'stats-affiliate-history.php?affiliateid='.$affiliateid, 'images/icon-statistics.gif');	
		
		phpAds_PageHeader("4.2.2");
			echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;<b>".phpAds_getAffiliateName($affiliateid)."</b><br><br><br>";
	    phpAds_ShowSections(array("4.2.2", "4.2.3","4.2.4"));
	}
	else
	{
		phpAds_PageHeader("2.2");
			echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;<b>".phpAds_getAffiliateName($affiliateid)."</b><br><br><br>";
	       	phpAds_ShowSections(array("2.1", "2.2"));
	}
	
	
	// Do not get this information if the page
	// is the result of an error message
	if (!isset($affiliate))
	{
		$res = phpAds_dbQuery("
			SELECT
				*
			FROM
				".$phpAds_config['tbl_affiliates']."
			WHERE
				affiliateid = '".$affiliateid."'
			") or phpAds_sqlDie();
		
		if (phpAds_dbNumRows($res))
		{
			$affiliate = phpAds_dbFetchArray($res);
		}
		
		// Set password to default value
		if ($affiliate['password'] != '')
			$affiliate['password'] = '********';
	}
}
else
{
	phpAds_PageHeader("4.2.1");
		echo "<img src='images/icon-affiliate.gif' align='absmiddle'>&nbsp;<b>".phpAds_getAffiliateName($affiliateid)."</b><br><br><br>";
		phpAds_ShowSections(array("4.2.1"));
	
	// Do not set this information if the page
	// is the result of an error message
	if (!isset($affiliate))
	{
		$affiliate['name'] 			= $strUntitled;
		$affiliate['mnemonic']      = '';
		$affiliate['website'] 		= 'http://';
		$affiliate['contact'] 		= '';
		$affiliate['email'] 		= '';
		$affiliate['publiczones']	= 'f';
		
		$affiliate['username'] 		= '';
		$affiliate['password'] 		= '';
		$affiliate['permissions'] 	= 0;
	}
}

$tabindex = 1;



/*********************************************************/
/* Main code                                             */
/*********************************************************/

echo "<br><br>";
echo "<form name='affiliateform' method='post' action='affiliate-edit.php' onSubmit='return phpAds_formCheck(this);'>";
echo "<input type='hidden' name='affiliateid' value='".(isset($affiliateid) && $affiliateid != '' ? $affiliateid : '')."'>";
echo "<input type='hidden' name='move' value='".(isset($move) && $move != '' ? $move : '')."'>";


echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
echo "<tr><td height='25' colspan='3'><b>".$strBasicInformation."</b></td></tr>";
echo "<tr height='1'><td width='30'><img src='images/break.gif' height='1' width='30'></td>";
echo "<td width='200'><img src='images/break.gif' height='1' width='200'></td>";
echo "<td width='100%'><img src='images/break.gif' height='1' width='100%'></td></tr>";
echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";

// Name
echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strName."</td>";

if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
	echo "<td width='100%'><input onBlur='phpAds_formUpdate(this);' class='flat' type='text' name='name' size='35' style='width:350px;' value='".phpAds_htmlQuotes($affiliate['name'])."' tabindex='".($tabindex++)."'></td>";
else
	echo "<td width='100%'>".(isset($affiliate['name']) ? $affiliate['name'] : '');

echo "</td></tr><tr><td><img src='images/spacer.gif' height='1' width='30'></td>";
echo "<td colspan='1'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td><td><img src='images/spacer.gif' height='1' width='100%'></tr>";

// Mnemonic
echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strMnemonic."</td><td>";
echo "<input onBlur='phpAds_formUpdate(this);' class='flat' type='text' name='mnemonic' size='35' style='width:350px;' dir='ltr' value='".phpAds_htmlQuotes($affiliate['mnemonic'])."' tabindex='".($tabindex++)."'>";
echo "</td></tr><tr><td><img src='images/spacer.gif' height='1' width='30'></td>";
echo "<td colspan='1'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td><td><img src='images/spacer.gif' height='1' width='100%'></tr>";

// Website
echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strWebsite."</td><td>";
echo "<input onBlur='phpAds_formUpdate(this);' class='flat' type='text' name='website' size='35' style='width:350px;' dir='ltr' value='".phpAds_htmlQuotes($affiliate['website'])."' tabindex='".($tabindex++)."'>";
echo "</td></tr><tr><td><img src='images/spacer.gif' height='1' width='30'></td>";
echo "<td colspan='1'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td><td><img src='images/spacer.gif' height='1' width='100%'></tr>";

// Contact
echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strContact."</td><td>";
echo "<input onBlur='phpAds_formUpdate(this);' class='flat' type='text' name='contact' size='35' style='width:350px;' value='".phpAds_htmlQuotes($affiliate['contact'])."' tabindex='".($tabindex++)."'>";
echo "</td></tr><tr><td><img src='images/spacer.gif' height='1' width='30'></td>";
echo "<td colspan='1'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td><td><img src='images/spacer.gif' height='1' width='100%'></tr>";

// Email
echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strEMail."</td><td>";
echo "<input onBlur='phpAds_formUpdate(this);' class='flat' type='text' name='email' size='35' style='width:350px;' value='".phpAds_htmlQuotes($affiliate['email'])."' tabindex='".($tabindex++)."'>";
echo "</td></tr><tr><td><img src='images/spacer.gif' height='1' width='30'></td>";
echo "<td colspan='1'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td><td><img src='images/spacer.gif' height='1' width='100%'></tr>";

// Language
echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strLanguage."</td><td>";
echo "<select name='language' tabindex='".($tabindex++)."'>";
echo "<option value='' SELECTED>".$strDefault."</option>\n"; 

$languages = phpAds_AvailableLanguages();
while (list($k, $v) = each($languages))
{
	if (isset($affiliate['language']) && $affiliate['language'] == $k)
		echo "<option value='$k' selected>$v</option>\n";
	else
		echo "<option value='$k'>$v</option>\n";
}

echo "</select></td></tr><tr><td><img src='images/spacer.gif' height='1' width='30'></td>";
echo "<td colspan='1'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td><td><img src='images/spacer.gif' height='1' width='100%'></tr>";

// Public?
echo "<tr><td width='30'>&nbsp;</td><td colspan='2'>";
echo "<input type='hidden' name='publiczones_old' value='".$affiliate['publiczones']."'>";
echo "<input type='checkbox' name='publiczones' value='t'".($affiliate['publiczones'] == 't' ? ' CHECKED' : '')." tabindex='".($tabindex++)."'>&nbsp;";
echo $strMakePublisherPublic;
echo "</td></tr>";
echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";

echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
echo "</table>";




echo "<br><br>";
echo "<br><br>";

echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
echo "<tr><td height='25' colspan='3'><b>".$strLoginInformation."</b></td></tr>";
echo "<tr height='1'><td width='30'><img src='images/break.gif' height='1' width='30'></td>";
echo "<td width='200'><img src='images/break.gif' height='1' width='200'></td>";
echo "<td width='100%'><img src='images/break.gif' height='1' width='100%'></td></tr>";
echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";


// Error message?
if (isset($errormessage) && count($errormessage))
{
	echo "<tr><td>&nbsp;</td><td height='10' colspan='2'>";
	echo "<table cellpadding='0' cellspacing='0' border='0'><tr><td>";
	echo "<img src='images/error.gif' align='absmiddle'>&nbsp;";
	
	while (list($k,$v) = each($errormessage))
		echo "<font color='#AA0000'><b>".$v."</b></font><br>";
	
	echo "</td></tr></table></td></tr><tr><td height='10' colspan='3'>&nbsp;</td></tr>";
	echo "<tr><td><img src='images/spacer.gif' height='1' width='100%'></td>";
	echo "<td colspan='2'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td></tr>";
}


// Username
echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strUsername."</td>";

if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
	echo "<td width='370'><input onBlur='phpAds_formUpdate(this);' class='flat' type='text' name='username' size='25' value='".phpAds_htmlQuotes($affiliate['username'])."' tabindex='".($tabindex++)."'></td>";
else
	echo "<td width='370'>".(isset($affiliate['username']) ? $affiliate['username'] : '');

echo "</tr><tr><td><img src='images/spacer.gif' height='1' width='30'></td>";
echo "<td colspan='1'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td><td><img src='images/spacer.gif' height='1' width='100%'></tr>";


// Password
if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
{
	echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strPassword."</td>";
	echo "<td width='370'><input class='flat' type='password' name='password' size='25' value='".$affiliate['password']."' tabindex='".($tabindex++)."'></td>";
	echo "</tr><tr><td height='10' colspan='3'>&nbsp;</td></tr>";
}
else
{
	echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strOldPassword."</td><td width='100%'>";
	echo "<input onBlur='phpAds_formUpdate(this);' class='flat' type='password' name='pwold' size='25' value='' tabindex='".($tabindex++)."'>";
	echo "</td></tr><tr><td><img src='images/spacer.gif' height='1' width='30'></td>";
	echo "<td colspan='1'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td><td><img src='images/spacer.gif' height='1' width='100%'></tr>";
	
	echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strNewPassword."</td><td width='100%'>";
	echo "<input onBlur='phpAds_formUpdate(this);' class='flat' type='password' name='pw' size='25' value='' tabindex='".($tabindex++)."'>";
	echo "</td></tr><tr><td><img src='images/spacer.gif' height='1' width='30'></td>";
	echo "<td colspan='1'><img src='images/break-l.gif' height='1' width='200' vspace='6'></td><td><img src='images/spacer.gif' height='1' width='100%'></tr>";
	
	echo "<tr><td width='30'>&nbsp;</td><td width='200'>".$strRepeatPassword."</td><td width='100%'>";
	echo "<input onBlur='phpAds_formUpdate(this);' class='flat' type='password' name='pw2' size='25' value='' tabindex='".($tabindex++)."'>";
	echo "</td></tr>";
}

// Permissions
if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency))
{
	echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
	echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
	
	echo "<tr><td width='30'>&nbsp;</td><td colspan='2'>";
	echo "<input type='checkbox' name='affiliatepermissions[]' value='".phpAds_ModifyInfo."'".(phpAds_ModifyInfo & $affiliate['permissions'] ? ' CHECKED' : '')." tabindex='".($tabindex++)."'>&nbsp;";
	echo $strAllowAffiliateModifyInfo;
	echo "</td></tr>";
	
	echo "<tr><td width='30'>&nbsp;</td><td colspan='2'>";
	echo "<input type='checkbox' name='affiliatepermissions[]' value='".phpAds_EditZone."'".(phpAds_EditZone & $affiliate['permissions'] ? ' CHECKED' : '')." tabindex='".($tabindex++)."'>&nbsp;";
	echo $strAllowAffiliateModifyZones;
	echo "</td></tr>";
	
	echo "<tr><td width='30'>&nbsp;</td><td colspan='2'>";
	echo "<input type='checkbox' name='affiliatepermissions[]' value='".phpAds_LinkBanners."'".(phpAds_LinkBanners & $affiliate['permissions'] ? ' CHECKED' : '')." tabindex='".($tabindex++)."'>&nbsp;";
	echo $strAllowAffiliateLinkBanners;
	echo "</td></tr>";
	
	echo "<tr><td width='30'>&nbsp;</td><td colspan='2'>";
	echo "<input type='checkbox' name='affiliatepermissions[]' value='".phpAds_AddZone."'".(phpAds_AddZone & $affiliate['permissions'] ? ' CHECKED' : '')." tabindex='".($tabindex++)."'>&nbsp;";
	echo $strAllowAffiliateAddZone;
	echo "</td></tr>";
	
	echo "<tr><td width='30'>&nbsp;</td><td colspan='2'>";
	echo "<input type='checkbox' name='affiliatepermissions[]' value='".phpAds_DeleteZone."'".(phpAds_DeleteZone & $affiliate['permissions'] ? ' CHECKED' : '')." tabindex='".($tabindex++)."'>&nbsp;";
	echo $strAllowAffiliateDeleteZone;
	echo "</td></tr>";
}

echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
echo "</table>";

echo "<br><br>";
echo "<input type='submit' name='submit' value='".(isset($affiliateid) && $affiliateid != '' ? $strSaveChanges : $strNext.' >')."' tabindex='".($tabindex++)."'>";
echo "</form>";



/*********************************************************/
/* Form requirements                                     */
/*********************************************************/

// Get unique affiliate
$unique_names = array();

$res = phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_affiliates']." WHERE affiliateid != '".$affiliateid."'");
while ($row = phpAds_dbFetchArray($res))
	$unique_names[] = $row['name'];


// Get unique username
$unique_users = array($phpAds_config['admin']);

$res = phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_affiliates']." WHERE username != '' AND affiliateid != '".$affiliateid."'");
while ($row = phpAds_dbFetchArray($res))
	$unique_users[] = $row['username'];

$res = phpAds_dbQuery("SELECT * FROM ".$phpAds_config['tbl_clients']." WHERE clientusername != ''");
while ($row = phpAds_dbFetchArray($res))
	$unique_users[] = $row['clientusername'];

?>

<script language='JavaScript'>
<!--
	phpAds_formSetRequirements('contact', '<?php echo addslashes($strContact); ?>', true);
	phpAds_formSetRequirements('website', '<?php echo addslashes($strWebsite); ?>', true, 'url');
	phpAds_formSetRequirements('email', '<?php echo addslashes($strEMail); ?>', true, 'email');
<?php if (phpAds_isUser(phpAds_Admin) || phpAds_isUser(phpAds_Agency)) { ?>
	phpAds_formSetRequirements('name', '<?php echo addslashes($strName); ?>', true, 'unique');
	phpAds_formSetRequirements('username', '<?php echo addslashes($strUsername); ?>', false, 'unique');

	phpAds_formSetUnique('name', '|<?php echo addslashes(implode('|', $unique_names)); ?>|');
	phpAds_formSetUnique('username', '|<?php echo addslashes(implode('|', $unique_users)); ?>|');
<?php } ?>	
//-->
</script>

<?php



/*********************************************************/
/* HTML framework                                        */
/*********************************************************/

phpAds_PageFooter();

?>