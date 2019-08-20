-- Create syntax for TABLE 'bais_help'
CREATE TABLE `bais_help` (
  `he_id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `he_title` varchar(150) NOT NULL DEFAULT '',
  `he_shortdescription` varchar(255) NOT NULL DEFAULT '',
  `he_body` text NOT NULL,
  `he_keywords` text NOT NULL,
  `he_unusername` varchar(30) NOT NULL DEFAULT '',
  `he_createdate` datetime DEFAULT CURRENT_TIMESTAMP,
  `he_creator` varchar(30) NOT NULL DEFAULT 'system',
  `he_editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `he_editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`he_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='[RTCS:v2.0] [Created:2005-11-08] [tmp:cpm014] [id:467]';

-- Create syntax for TABLE 'bais_logs'
CREATE TABLE `bais_logs` (
  `lg_id` mediumint(7) unsigned NOT NULL AUTO_INCREMENT,
  `lg_machines_id` int(11) unsigned NOT NULL COMMENT 'unique machine id from which they are logged in',
  `lg_acctname` char(64) CHARACTER SET latin1 NOT NULL DEFAULT 'admin1' COMMENT 'as of 2012-04-01 this has not been used',
  `lg_stusername` char(64) CHARACTER SET latin1 NOT NULL DEFAULT '' COMMENT 'the logged in user; note st_ prefix is inaccurate as it may be a contact or other object',
  `lg_masterlogin` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `lg_stemail` char(85) CHARACTER SET latin1 NOT NULL DEFAULT '' COMMENT 'email address at time of login',
  `lg_action` mediumint(5) NOT NULL DEFAULT '0',
  `lg_requesttype` mediumint(5) NOT NULL DEFAULT '0',
  `lg_sessionkey` char(32) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `lg_ipaddress` char(16) CHARACTER SET latin1 NOT NULL DEFAULT '0.0.0.0',
  `lg_referrer` char(128) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `lg_feed` char(64) CHARACTER SET latin1 NOT NULL DEFAULT '',
  `lg_lastping` datetime NOT NULL COMMENT 'last ajax ping to determine still logged in',
  `lg_entertime` datetime ,
  `lg_exittime` datetime ,
  `lg_logouttime` datetime ,
  `lg_editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'this will not be changed with a ping; only a logout or other edit',
  PRIMARY KEY (`lg_id`),
  KEY `lg_machines_id` (`lg_machines_id`),
  KEY `lg_acctname` (`lg_acctname`),
  KEY `lg_stusername` (`lg_stusername`),
  KEY `lg_masterlogin` (`lg_masterlogin`),
  KEY `lg_lastping` (`lg_lastping`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create syntax for TABLE 'bais_logs_history'
CREATE TABLE `bais_logs_history` (
  `ID` bigint(14) unsigned NOT NULL AUTO_INCREMENT,
  `Logs_ID` int(11) unsigned NOT NULL,
  `PageTitle` char(255) CHARACTER SET latin1 NOT NULL,
  `PageLevel` tinyint(2) unsigned DEFAULT NULL COMMENT 'Null means not known (do not treat as level 0)',
  `Environment` longtext CHARACTER SET latin1 NOT NULL,
  `Type` enum('View','Refresh','Exe') CHARACTER SET latin1 NOT NULL DEFAULT 'View',
  `Page` char(255) CHARACTER SET latin1 NOT NULL,
  `QueryString` text CHARACTER SET latin1 NOT NULL,
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `Logs_ID` (`Logs_ID`),
  KEY `EditDate` (`EditDate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create syntax for TABLE 'bais_offices'
CREATE TABLE `bais_offices` (
  `of_oausername` varchar(20) NOT NULL,
  `PrimaryOffice` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `Classes_ID` int(11) unsigned NOT NULL,
  `Address` varchar(255) NOT NULL DEFAULT '',
  `City` varchar(255) NOT NULL DEFAULT '',
  `State` char(3) NOT NULL DEFAULT '',
  `Zip` varchar(10) NOT NULL DEFAULT '',
  `Country` char(3) NOT NULL DEFAULT 'USA',
  `WorkPhone` varchar(24) NOT NULL DEFAULT '',
  `Fax` varchar(24) NOT NULL DEFAULT '',
  `Cell` varchar(24) NOT NULL DEFAULT '',
  `Email` varchar(85) NOT NULL DEFAULT '',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`of_oausername`),
  KEY `PrimaryOffice` (`PrimaryOffice`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'bais_OfficesStaff'
CREATE TABLE `bais_OfficesStaff` (
  `os_unusername` char(20) NOT NULL DEFAULT '',
  `os_stusername` char(20) NOT NULL DEFAULT '',
  `os_permissions` tinyint(3) unsigned NOT NULL DEFAULT '31' COMMENT 'Value of 31 means all privileges, initial state',
  `os_assignor` char(20) NOT NULL DEFAULT '',
  `os_editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`os_unusername`,`os_stusername`),
  KEY `os_stunusername` (`os_stusername`),
  KEY `os_permissions` (`os_permissions`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Offices to lower staff';

-- Create syntax for TABLE 'bais_orgaliases'
CREATE TABLE `bais_orgaliases` (
  `oa_unusername` char(20) NOT NULL DEFAULT '',
  `oa_businessname` char(75) NOT NULL DEFAULT '',
  `oa_orgcode` char(5) DEFAULT NULL ,
  `oa_org1` char(128) NOT NULL DEFAULT '',
  `oa_org2` char(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`oa_unusername`),
  KEY `oa_orgcode` (`oa_orgcode`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'bais_processes'
CREATE TABLE `bais_processes` (
  `pr_id` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `pr_name` varchar(80) NOT NULL DEFAULT '',
  `pr_handle` varchar(24) NOT NULL DEFAULT '',
  `pr_description` text NOT NULL,
  `pr_version` float(3,1) unsigned NOT NULL DEFAULT '1.0',
  `pr_createdate` datetime DEFAULT CURRENT_TIMESTAMP,
  `pr_creator` varchar(20) NOT NULL DEFAULT 'system',
  `pr_editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `pr_editor` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`pr_id`),
  UNIQUE KEY `processVersion` (`pr_handle`,`pr_version`),
  KEY `pr_name` (`pr_name`),
  KEY `pr_handle` (`pr_handle`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'bais_roles'
CREATE TABLE `bais_roles` (
  `ro_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ro_name` char(100) NOT NULL DEFAULT '',
  `ro_shortname` char(17) NOT NULL DEFAULT '',
  `ro_rank` int(11) unsigned NOT NULL,
  `ro_locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ro_grantable` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `ro_description` char(255) NOT NULL DEFAULT '',
  `ro_createdate` datetime DEFAULT CURRENT_TIMESTAMP,
  `ro_creator` char(20) NOT NULL DEFAULT 'system',
  `ro_editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ro_editor` char(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`ro_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'bais_RolesProcesses'
CREATE TABLE `bais_RolesProcesses` (
  `rp_roid` int(7) unsigned NOT NULL DEFAULT '0',
  `rp_prid` int(7) unsigned NOT NULL DEFAULT '0',
  `rp_notes` char(255) NOT NULL DEFAULT '',
  `rp_createdate` datetime DEFAULT CURRENT_TIMESTAMP,
  `rp_creator` char(20) NOT NULL DEFAULT 'system',
  `rp_editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `rp_editor` char(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`rp_roid`,`rp_prid`),
  KEY `rp_prid` (`rp_prid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'bais_settings'
CREATE TABLE `bais_settings` (
  `UserName` char(20) NOT NULL,
  `vargroup` char(35) NOT NULL DEFAULT 'default',
  `varnode` varchar(35) NOT NULL,
  `varkey` varchar(35) NOT NULL,
  `varvalue` text NOT NULL,
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserName`,`vargroup`,`varnode`,`varkey`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'bais_staff'
CREATE TABLE `bais_staff` (
  `st_unusername` char(20) NOT NULL DEFAULT '',
  `st_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `st_status` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '1=can log in, 0=locked out',
  `st_hiredate` date NOT NULL,
  `st_dischargedate` date NOT NULL,
  `st_dischargereason` char(75) NOT NULL,
  `Gender` char(15) NOT NULL,
  `Race` char(25) NOT NULL,
  `SocSecurityNumber` char(11) NOT NULL,
  `BirthDate` date NOT NULL,
  `JobTitle` char(75) DEFAULT NULL,
  `Address` char(75) NOT NULL,
  `City` char(75) NOT NULL,
  `State` char(3) NOT NULL,
  `Zip` char(10) NOT NULL,
  `Country` char(3) NOT NULL DEFAULT 'USA',
  `Phone` char(40) NOT NULL,
  `WorkPhone` char(40) NOT NULL,
  `PagerVoice` char(40) NOT NULL,
  `Cell` char(40) NOT NULL,
  `CellCarrier` char(30) DEFAULT NULL,
  `MisctextStaffnotes` text NOT NULL,
  `GLF_Recruiter` char(35) NOT NULL ,
  `GLF_TransactionFee` float(7,2) unsigned NOT NULL,
  `GLF_EOFee` float(7,2) unsigned NOT NULL ,
  `st_createdate` datetime DEFAULT CURRENT_TIMESTAMP,
  `st_creator` char(20) NOT NULL DEFAULT 'system',
  `st_editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `st_editor` char(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`st_unusername`),
  KEY `st_active` (`st_active`),
  KEY `SocSecurityNumber` (`SocSecurityNumber`),
  KEY `GLF_Recruiter` (`GLF_Recruiter`),
  KEY `GLF_TransactionFee` (`GLF_TransactionFee`),
  KEY `GLF_EOFee` (`GLF_EOFee`),
  KEY `CellCarrier` (`CellCarrier`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'bais_StaffOffices'
CREATE TABLE `bais_StaffOffices` (
  `so_stusername` char(20) NOT NULL DEFAULT '',
  `so_unusername` char(20) NOT NULL DEFAULT '',
  `so_roid` float(2,1) unsigned NOT NULL DEFAULT '3.0' COMMENT 'Clarifies role level',
  `so_permissions` tinyint(3) unsigned NOT NULL DEFAULT '31' COMMENT 'Value of 64 means All privileges, initial state',
  `so_assignor` char(20) NOT NULL DEFAULT '',
  `so_editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`so_stusername`,`so_unusername`,`so_roid`),
  KEY `so_unusername` (`so_unusername`),
  KEY `so_permissions` (`so_permissions`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Senior staff to offices';

-- Create syntax for TABLE 'bais_StaffRoles'
CREATE TABLE `bais_StaffRoles` (
  `sr_stusername` char(20) NOT NULL DEFAULT '',
  `sr_roid` int(11) unsigned NOT NULL,
  `sr_permissions` tinyint(3) unsigned NOT NULL DEFAULT '31' ,
  `sr_assignor` char(20) NOT NULL DEFAULT '',
  `sr_editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sr_stusername`,`sr_roid`),
  KEY `sr_roid` (`sr_roid`),
  KEY `sr_permissions` (`sr_permissions`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'bais_uclogs'
CREATE TABLE `bais_uclogs` (
  `ul_id` mediumint(7) unsigned NOT NULL AUTO_INCREMENT,
  `ul_acctname` char(64) NOT NULL DEFAULT 'admin1',
  `ul_unusername` char(20) NOT NULL DEFAULT '',
  `ul_unemail` char(85) NOT NULL DEFAULT '',
  `ul_action` mediumint(5) NOT NULL DEFAULT '0',
  `ul_requesttype` mediumint(5) NOT NULL DEFAULT '0',
  `ul_sessionkey` char(32) NOT NULL DEFAULT '',
  `ul_ipaddress` char(16) NOT NULL DEFAULT '0.0.0.0',
  `ul_referrer` char(128) NOT NULL DEFAULT '',
  `ul_feed` char(64) NOT NULL DEFAULT '',
  `ul_entertime` datetime ,
  `ul_exittime` datetime ,
  `ul_logouttime` datetime ,
  `ul_editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ul_id`),
  KEY `ul_unusername` (`ul_unusername`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'bais_universal'
CREATE TABLE `bais_universal` (
  `un_id` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `un_username` char(20) NOT NULL DEFAULT '',
  `un_password` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `un_passwordtmp` char(12) NOT NULL DEFAULT '',
  `un_email` char(85) NOT NULL DEFAULT '',
  `un_firstname` char(25) NOT NULL DEFAULT '',
  `un_middlename` char(25) NOT NULL DEFAULT '',
  `un_lastname` char(30) NOT NULL DEFAULT '',
  `un_helpnotify` tinyint(1) unsigned NOT NULL DEFAULT '0' ,
  `un_createdate` datetime DEFAULT CURRENT_TIMESTAMP,
  `un_creator` char(20) NOT NULL DEFAULT 'system',
  `un_editdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `un_editor` char(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`un_id`),
  UNIQUE KEY `un_username` (`un_username`),
  KEY `un_helpnotify` (`un_helpnotify`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;