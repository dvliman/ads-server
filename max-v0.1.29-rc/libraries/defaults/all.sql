--
-- Table structure for table `max_acls`
--

CREATE TABLE max_acls (
  bannerid mediumint(9) NOT NULL default '0',
  logical set('and','or') NOT NULL default '',
  type varchar(16) NOT NULL default '',
  comparison char(2) NOT NULL default '==',
  data text NOT NULL,
  executionorder int(10) unsigned NOT NULL default '0',
  UNIQUE KEY bannerid_executionorder (bannerid,executionorder),
  KEY bannerid (bannerid)
);

--
-- Table structure for table `max_adclicks`
--

CREATE TABLE max_adclicks (
  userid varchar(32) NOT NULL default '',
  bannerid mediumint(9) NOT NULL default '0',
  zoneid mediumint(9) NOT NULL default '0',
  t_stamp timestamp(14) NOT NULL,
  host varchar(255) NOT NULL default '',
  source varchar(50) NOT NULL default '',
  country char(2) NOT NULL default '',
  KEY date (t_stamp),
  KEY userid (userid),
  KEY bannerid (bannerid)
);

--
-- Table structure for table `max_adconversions`
--

CREATE TABLE max_adconversions (
  conversionid bigint(20) unsigned NOT NULL auto_increment,
  local_conversionid bigint(20) unsigned NOT NULL default '0',
  dbserver_ip varchar(16) NOT NULL default '',
  userid varchar(32) NOT NULL default '',
  trackerid mediumint(9) NOT NULL default '0',
  t_stamp timestamp(14) NOT NULL,
  host varchar(255) NOT NULL default '',
  country char(2) NOT NULL default '',
  conversionlogid mediumint(9) NOT NULL default '0',
  PRIMARY KEY  (conversionid),
  KEY userid (userid),
  KEY date (t_stamp),
  KEY trackerid (trackerid),
  KEY local_conversionid (local_conversionid)
);

--
-- Table structure for table `max_adstats`
--

CREATE TABLE max_adstats (
  views int(11) NOT NULL default '0',
  clicks int(11) NOT NULL default '0',
  conversions int(11) NOT NULL default '0',
  day date NOT NULL default '0000-00-00',
  hour tinyint(4) NOT NULL default '0',
  bannerid mediumint(9) NOT NULL default '0',
  zoneid mediumint(9) NOT NULL default '0',
  KEY day (day),
  KEY bannerid (bannerid),
  KEY zoneid (zoneid)
);

--
-- Table structure for table `max_adviews`
--

CREATE TABLE max_adviews (
  userid varchar(32) NOT NULL default '',
  bannerid mediumint(9) NOT NULL default '0',
  zoneid mediumint(9) NOT NULL default '0',
  t_stamp timestamp(14) NOT NULL,
  host varchar(255) NOT NULL default '',
  source varchar(50) NOT NULL default '',
  country char(2) NOT NULL default '',
  KEY date (t_stamp),
  KEY userid (userid),
  KEY bannerid (bannerid)
);

--
-- Table structure for table `max_affiliates`
--

CREATE TABLE max_affiliates (
  affiliateid mediumint(9) NOT NULL auto_increment,
  agencyid mediumint(9) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  mnemonic varchar(5) NOT NULL default '',
  contact varchar(255) default NULL,
  email varchar(64) NOT NULL default '',
  website varchar(255) default NULL,
  username varchar(64) default NULL,
  password varchar(64) default NULL,
  permissions mediumint(9) default NULL,
  language varchar(64) default NULL,
  publiczones enum('t','f') NOT NULL default 'f',
  PRIMARY KEY  (affiliateid)
);

--
-- Table structure for table `max_agency`
--

CREATE TABLE max_agency (
  agencyid mediumint(9) NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  contact varchar(255) default NULL,
  email varchar(64) NOT NULL default '',
  username varchar(64) default NULL,
  password varchar(64) default NULL,
  permissions mediumint(9) default NULL,
  language varchar(64) default NULL,
  PRIMARY KEY  (agencyid)
);

--
-- Table structure for table `max_application_variable`
--

CREATE TABLE max_application_variable (
  name varchar(255) NOT NULL default '',
  value varchar(255) NOT NULL default ''
);

--
-- Table structure for table `max_banners`
--

CREATE TABLE max_banners (
  bannerid mediumint(9) NOT NULL auto_increment,
  campaignid mediumint(9) NOT NULL default '0',
  active enum('t','f') NOT NULL default 't',
  priority int(11) NOT NULL default '0',
  contenttype enum('gif','jpeg','png','html','swf','dcr','rpm','mov','txt') NOT NULL default 'gif',
  pluginversion mediumint(9) NOT NULL default '0',
  storagetype enum('sql','web','url','html','network','txt') NOT NULL default 'sql',
  filename varchar(255) NOT NULL default '',
  imageurl varchar(255) NOT NULL default '',
  htmltemplate text NOT NULL,
  htmlcache text NOT NULL,
  width smallint(6) NOT NULL default '0',
  height smallint(6) NOT NULL default '0',
  weight tinyint(4) NOT NULL default '1',
  seq tinyint(4) NOT NULL default '0',
  target varchar(16) NOT NULL default '',
  url text NOT NULL default '',
  alt varchar(255) NOT NULL default '',
  status varchar(255) NOT NULL default '',
  keyword varchar(255) NOT NULL default '',
  bannertext text NOT NULL,
  description varchar(255) NOT NULL default '',
  autohtml enum('t','f') NOT NULL default 't',
  adserver varchar(50) NOT NULL default '',
  block int(11) NOT NULL default '0',
  capping int(11) NOT NULL default '0',
  session_capping int(11) NOT NULL default '0',
  compiledlimitation text NOT NULL,
  append text NOT NULL,
  appendtype tinyint(4) NOT NULL default '0',
  bannertype tinyint(4) NOT NULL default '0',
  alt_filename varchar(255) NOT NULL default '',
  alt_imageurl varchar(255) NOT NULL default '',
  alt_contenttype enum('gif','jpeg','png') NOT NULL default 'gif',
  PRIMARY KEY  (bannerid),
  KEY campaignid (campaignid)
);

--
-- Table structure for table `max_cache`
--

CREATE TABLE max_cache (
  cacheid varchar(255) NOT NULL default '',
  content blob NOT NULL,
  PRIMARY KEY  (cacheid)
);

--
-- Table structure for table `max_campaigns`
--

CREATE TABLE max_campaigns (
  campaignid mediumint(9) NOT NULL auto_increment,
  campaignname varchar(255) NOT NULL default '',
  clientid mediumint(9) NOT NULL default '0',
  views int(11) default '-1',
  clicks int(11) default '-1',
  conversions int(11) default '-1',
  expire date default '0000-00-00',
  activate date default '0000-00-00',
  active enum('t','f') NOT NULL default 't',
  priority enum('h','m','l') NOT NULL default 'l',
  weight tinyint(4) NOT NULL default '1',
  target int(11) NOT NULL default '0',
  optimise enum('t','f') NOT NULL default 'f',
  anonymous enum('t','f') NOT NULL default 'f',
  PRIMARY KEY  (campaignid)
);

--
-- Table structure for table `max_campaigns_trackers`
--

CREATE TABLE max_campaigns_trackers (
  campaign_trackerid mediumint(9) NOT NULL auto_increment,
  campaignid mediumint(9) NOT NULL default '0',
  trackerid mediumint(9) NOT NULL default '0',
  logstats enum('y','n') NOT NULL default 'y',
  viewwindow mediumint(9) NOT NULL default '0',
  clickwindow mediumint(9) NOT NULL default '0',
  PRIMARY KEY  (campaign_trackerid),
  KEY campaignid (campaignid),
  KEY trackerid (trackerid)
);

--
-- Table structure for table `max_clients`
--

CREATE TABLE max_clients (
  clientid mediumint(9) NOT NULL auto_increment,
  agencyid mediumint(9) NOT NULL default '0',
  clientname varchar(255) NOT NULL default '',
  contact varchar(255) default NULL,
  email varchar(64) NOT NULL default '',
  clientusername varchar(64) NOT NULL default '',
  clientpassword varchar(64) NOT NULL default '',
  permissions mediumint(9) default NULL,
  language varchar(64) default NULL,
  report enum('t','f') NOT NULL default 't',
  reportinterval mediumint(9) NOT NULL default '7',
  reportlastdate date NOT NULL default '0000-00-00',
  reportdeactivate enum('t','f') NOT NULL default 't',
  PRIMARY KEY  (clientid)
);

--
-- Table structure for table `max_config`
--

CREATE TABLE max_config (
  agencyid mediumint(9) NOT NULL default '0',
  config_version decimal(7,3) NOT NULL default '0.000',
  table_border_color varchar(7) default '#000099',
  table_back_color varchar(7) default '#CCCCCC',
  table_back_color_alternative varchar(7) default '#F7F7F7',
  main_back_color varchar(7) default '#FFFFFF',
  my_header varchar(255) default NULL,
  my_footer varchar(255) default NULL,
  language varchar(32) default 'english',
  name varchar(32) default NULL,
  company_name varchar(255) default 'mysite.com',
  override_gd_imageformat varchar(4) default NULL,
  begin_of_week tinyint(2) default '1',
  percentage_decimals tinyint(2) default '2',
  type_sql_allow enum('t','f') default 't',
  type_url_allow enum('t','f') default 't',
  type_web_allow enum('t','f') default 'f',
  type_html_allow enum('t','f') default 't',
  type_txt_allow enum('t','f') default 't',
  type_web_mode tinyint(2) default '0',
  type_web_dir varchar(255) default NULL,
  type_web_ftp varchar(255) default NULL,
  type_web_url varchar(255) default NULL,
  admin varchar(64) default 'phpadsuser',
  admin_pw varchar(64) default 'phpadspass',
  admin_fullname varchar(255) default 'Your Name',
  admin_email varchar(64) default 'your@email.com',
  warn_admin enum('t','f') default 't',
  warn_agency enum('t','f') default 't',
  warn_client enum('t','f') default 't',
  warn_limit mediumint(9) NOT NULL default '0',
  admin_email_headers varchar(64) default NULL,
  admin_novice enum('t','f') default 't',
  default_banner_weight tinyint(4) default '1',
  default_campaign_weight tinyint(4) default '1',
  client_welcome enum('t','f') default 't',
  client_welcome_msg text,
  content_gzip_compression enum('t','f') default 'f',
  userlog_email enum('t','f') default 't',
  userlog_priority enum('t','f') default 't',
  userlog_autoclean enum('t','f') default 't',
  gui_show_campaign_info enum('t','f') default 't',
  gui_show_campaign_preview enum('t','f') default 'f',
  gui_show_banner_info enum('t','f') default 't',
  gui_show_banner_preview enum('t','f') default 't',
  gui_show_banner_html enum('t','f') default 'f',
  gui_show_matching enum('t','f') default 't',
  gui_show_parents enum('t','f') default 'f',
  gui_hide_inactive enum('t','f') default 'f',
  gui_link_compact_limit int(11) default '50',
  qmail_patch enum('t','f') default 'f',
  updates_frequency tinyint(2) default '7',
  updates_timestamp int(11) default '0',
  updates_last_seen decimal(7,3) default '0.000',
  allow_invocation_plain enum('t','f') default 'f',
  allow_invocation_plain_nocookies enum('t','f') default 't',
  allow_invocation_js enum('t','f') default 't',
  allow_invocation_frame enum('t','f') default 'f',
  allow_invocation_xmlrpc enum('t','f') default 'f',
  allow_invocation_local enum('t','f') default 't',
  allow_invocation_interstitial enum('t','f') default 't',
  allow_invocation_popup enum('t','f') default 't',
  auto_clean_tables enum('t','f') default 'f',
  auto_clean_tables_interval tinyint(2) default '5',
  auto_clean_userlog enum('t','f') default 'f',
  auto_clean_userlog_interval tinyint(2) default '5',
  auto_clean_tables_vacuum enum('t','f') default 't',
  autotarget_factor float default '-1',
  maintenance_timestamp int(11) default '0',
  compact_stats enum('t','f') default 't',
  statslastday date NOT NULL default '0000-00-00',
  statslasthour tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (agencyid)
);

--
-- Table structure for table `max_conversionlog`
--

CREATE TABLE max_conversionlog (
  conversionlogid mediumint(9) NOT NULL auto_increment,
  conversionid bigint(20) unsigned NOT NULL default '0',
  campaignid mediumint(9) NOT NULL default '0',
  trackerid mediumint(9) NOT NULL default '0',
  userid varchar(32) NOT NULL default '',
  t_stamp timestamp(14) NOT NULL,
  host varchar(255) NOT NULL default '',
  country char(2) NOT NULL default '',
  cnv_logstats enum('y','n') default 'n',
  cnv_clickwindow mediumint(9) NOT NULL default '0',
  cnv_viewwindow mediumint(9) NOT NULL default '0',
  cnv_latest smallint(6) default NULL,
  action enum('view','click') default NULL,
  action_bannerid mediumint(9) NOT NULL default '0',
  action_zoneid mediumint(9) NOT NULL default '0',
  action_t_stamp timestamp(14) NOT NULL,
  action_host varchar(255) NOT NULL default '',
  action_source varchar(50) NOT NULL default '',
  action_country char(2) NOT NULL default '',
  PRIMARY KEY  (conversionlogid),
  KEY t_stamp (t_stamp)
);

--
-- Table structure for table `max_images`
--

CREATE TABLE max_images (
  filename varchar(128) NOT NULL default '',
  contents mediumblob NOT NULL,
  t_stamp timestamp(14) NOT NULL,
  PRIMARY KEY  (filename)
);

--
-- Table structure for table `max_log_maintenance`
--

CREATE TABLE max_log_maintenance (
  log_maintenance_id INT NOT NULL auto_increment,
  start_run DATETIME NULL,
  end_run DATETIME NULL,
  duration INT NULL,
  PRIMARY KEY(log_maintenance_id)
);

--
-- Table structure for table `max_session`
--

CREATE TABLE max_session (
  sessionid varchar(32) NOT NULL default '',
  sessiondata blob NOT NULL,
  lastused timestamp(14) NOT NULL,
  PRIMARY KEY  (sessionid)
);

--
-- Table structure for table `max_targetstats`
--

CREATE TABLE max_targetstats (
  day date NOT NULL default '0000-00-00',
  campaignid mediumint(9) NOT NULL default '0',
  target int(11) NOT NULL default '0',
  views int(11) NOT NULL default '0',
  modified tinyint(4) NOT NULL default '0'
);

--
-- Table structure for table `max_trackers`
--

CREATE TABLE max_trackers (
  trackerid mediumint(9) NOT NULL auto_increment,
  trackername varchar(255) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  clientid mediumint(9) NOT NULL default '0',
  viewwindow mediumint(9) NOT NULL default '0',
  clickwindow mediumint(9) NOT NULL default '0',
  blockwindow mediumint(9) NOT NULL default '0',
  PRIMARY KEY  (trackerid)
);

--
-- Table structure for table `max_userlog`
--

CREATE TABLE max_userlog (
  userlogid mediumint(9) NOT NULL auto_increment,
  timestamp int(11) NOT NULL default '0',
  usertype tinyint(4) NOT NULL default '0',
  userid mediumint(9) NOT NULL default '0',
  action mediumint(9) NOT NULL default '0',
  object mediumint(9) default NULL,
  details text,
  PRIMARY KEY  (userlogid)
);

--
-- Table structure for table `max_variables`
--

CREATE TABLE max_variables (
  variableid mediumint(9) unsigned NOT NULL auto_increment,
  trackerid varchar(32) NOT NULL default '',
  name varchar(250) NOT NULL default '',
  description varchar(250) default NULL,
  variabletype enum('js','qs') NOT NULL default 'js',
  datatype enum('int','string') NOT NULL default 'int',
  PRIMARY KEY  (variableid)
);

--
-- Table structure for table `max_variablevalues`
--

CREATE TABLE max_variablevalues (
  valueid bigint(20) unsigned NOT NULL auto_increment,
  t_stamp timestamp(14) NOT NULL,
  variableid mediumint(9) NOT NULL default '0',
  value varchar(50) default NULL,
  local_conversionid bigint(20) unsigned NOT NULL default '0',
  dbserver_ip varchar(16) NOT NULL default '',
  conversionid bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (valueid),
  KEY local_conversionid (local_conversionid),
  KEY conversionid (conversionid)
);

--
-- Table structure for table `max_zones`
--

CREATE TABLE max_zones (
  zoneid mediumint(9) NOT NULL auto_increment,
  affiliateid mediumint(9) default NULL,
  zonename varchar(245) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  delivery smallint(6) NOT NULL default '0',
  zonetype smallint(6) NOT NULL default '0',
  what blob NOT NULL,
  width smallint(6) NOT NULL default '0',
  height smallint(6) NOT NULL default '0',
  chain blob NOT NULL,
  prepend blob NOT NULL,
  append blob NOT NULL,
  appendtype tinyint(4) NOT NULL default '0',
  forceappend enum('t', 'f') DEFAULT 'f',
  PRIMARY KEY  (zoneid),
  KEY zonenameid (zonename,zoneid)
);
