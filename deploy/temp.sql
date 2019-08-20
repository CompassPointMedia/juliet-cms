-- Create syntax for TABLE 'addr_access'
CREATE TABLE `addr_access` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Name` char(30) NOT NULL,
  `Description` char(255) NOT NULL,
  `Category` char(30) NOT NULL,
  `EditDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  KEY `Name` (`Name`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'addr_contacts'
CREATE TABLE `addr_contacts` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `Category` varchar(35) NOT NULL DEFAULT '',
  `RepCode` char(20) NOT NULL,
  `Salesreps_ID` int(11) unsigned NOT NULL,
  `FirstName` varchar(35) NOT NULL DEFAULT '',
  `MiddleName` varchar(35) NOT NULL DEFAULT '',
  `LastName` varchar(40) NOT NULL DEFAULT '',
  `Suffix` char(15) NOT NULL,
  `Title` varchar(50) NOT NULL DEFAULT '',
  `Nickname` varchar(30) NOT NULL DEFAULT '',
  `Email` char(85) DEFAULT NULL,
  `Email2` varchar(85) NOT NULL DEFAULT '',
  `HomeAddress` text NOT NULL,
  `HomeCity` varchar(35) NOT NULL DEFAULT '',
  `HomeState` char(3) NOT NULL DEFAULT '',
  `HomeZip` varchar(10) NOT NULL DEFAULT '',
  `HomeCountry` char(3) NOT NULL DEFAULT '',
  `HomeDefault` smallint(1) unsigned NOT NULL DEFAULT '0',
  `HomePhone` varchar(24) NOT NULL DEFAULT '',
  `HomeFax` varchar(24) NOT NULL DEFAULT '',
  `HomeMobile` varchar(24) NOT NULL DEFAULT '',
  `HomeWebsite` varchar(255) NOT NULL DEFAULT '',
  `Company` varchar(75) NOT NULL DEFAULT '',
  `BusAddress` text NOT NULL,
  `BusCity` varchar(35) NOT NULL DEFAULT '',
  `BusState` char(3) NOT NULL DEFAULT '',
  `BusZip` varchar(10) NOT NULL DEFAULT '',
  `BusCountry` char(3) NOT NULL DEFAULT '',
  `BusTitle` varchar(35) NOT NULL DEFAULT '',
  `BusDepartment` varchar(45) NOT NULL DEFAULT '',
  `BusOffice` varchar(40) NOT NULL DEFAULT '',
  `BusPhone` varchar(24) NOT NULL DEFAULT '',
  `BusFax` varchar(24) NOT NULL DEFAULT '',
  `BusPager` varchar(24) NOT NULL DEFAULT '',
  `BusWebsite` varchar(85) NOT NULL DEFAULT '',
  `WholesaleAccess` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `WholesaleNumber` char(30) NOT NULL,
  `WholesaleState` char(3) NOT NULL,
  `WholesaleNotes` text NOT NULL,
  `Spouse` varchar(100) DEFAULT NULL,
  `Children` text NOT NULL,
  `Gender` smallint(1) unsigned NOT NULL DEFAULT '0',
  `Birthday` date NULL,
  `Anniversary` date NULL,
  `Notes` text NOT NULL,
  `StaffNotes` text NOT NULL,
  `EntryType` mediumint(3) NOT NULL DEFAULT '0',
  `Familiarity` varchar(25) NOT NULL DEFAULT '',
  `NewsletterOK` tinyint(2) unsigned NOT NULL DEFAULT '4' COMMENT 'Defined 2011-06-03: 0=none;1=optout volitionaly;2=optout invol.;3=optin invol;4=optin vol;',
  `NewsletterSettings` text NOT NULL,
  `NewsletterFrequency` char(30) NOT NULL,
  `NewsletterHTML` tinyint(1) unsigned NOT NULL,
  `LastContactMailer` int(9) unsigned NOT NULL DEFAULT '0',
  `LastContactDate` datetime NULL,
  `ToBeExported` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ExportDate` datetime NULL,
  `Exporter` varchar(20) NOT NULL DEFAULT '',
  `Importer_ID` int(11) unsigned DEFAULT NULL,
  `Imports_ID` int(11) unsigned DEFAULT NULL,
  `Importer` char(20) NOT NULL,
  `ImportSource` varchar(20) NOT NULL DEFAULT '',
  `FriendAllowance` tinyint(1) unsigned DEFAULT '1' COMMENT 'Added 2011-06-03 - 0=no value; 1=rlx in table; 2=people w/o business; 4=people w/business',
  `UserName` varchar(30) NOT NULL,
  `Password` varchar(30) NOT NULL DEFAULT '',
  `PasswordMD5` varchar(32) NOT NULL DEFAULT '',
  `EnrollmentAuthToken` varchar(32) DEFAULT NULL,
  `EnrollmentAuthDuration` char(2) NOT NULL DEFAULT '',
  `ReferralSource` varchar(25) NOT NULL DEFAULT '',
  `ReferralCode` varchar(16) NOT NULL DEFAULT '',
  `ReferralTerm` varchar(45) NOT NULL DEFAULT '',
  `ReferralHTTP` text NOT NULL,
  `CreateDate` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `Creator` char(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UserName` (`UserName`),
  KEY `FirstName` (`FirstName`),
  KEY `LastName` (`LastName`),
  KEY `Email2` (`Email2`),
  KEY `Salesreps_ID` (`Salesreps_ID`),
  KEY `NewsletterOK` (`NewsletterOK`),
  KEY `Email` (`Email`),
  KEY `Importer` (`Importer`),
  KEY `Imports_ID` (`Imports_ID`),
  KEY `Importer_ID` (`Importer_ID`),
  KEY `FriendAllowance` (`FriendAllowance`)
) ENGINE=MyISAM AUTO_INCREMENT=1391 DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'addr_contacts_posts'
CREATE TABLE `addr_contacts_posts` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Contacts_ID` int(11) unsigned DEFAULT NULL,
  `Objects_ID` int(11) unsigned NOT NULL,
  `ObjectName` char(20) NOT NULL DEFAULT 'cms1_articles',
  `Posts_ID` int(11) unsigned DEFAULT NULL,
  `PostDate` datetime NOT NULL,
  `IPAddress` char(15) NOT NULL,
  `Name` char(75) NOT NULL,
  `OwnerContacts_ID` int(11) unsigned DEFAULT NULL,
  `Content` text NOT NULL,
  `Category` char(25) NOT NULL DEFAULT '',
  `Active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Objects_ID` (`Objects_ID`),
  KEY `Contacts_ID` (`Contacts_ID`),
  KEY `Active` (`Active`),
  KEY `PostDate` (`PostDate`),
  KEY `IPAddress` (`IPAddress`),
  KEY `Posts_ID` (`Posts_ID`),
  KEY `ObjectName` (`ObjectName`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'addr_ContactsAccess'
CREATE TABLE `addr_ContactsAccess` (
  `Contacts_ID` int(11) unsigned NOT NULL,
  `Access_ID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`Contacts_ID`,`Access_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'addr_ContactsContacts'
CREATE TABLE `addr_ContactsContacts` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ParentContacts_ID` int(11) unsigned NOT NULL,
  `ChildContacts_ID` int(11) unsigned NOT NULL,
  `Relationship` tinyint(1) unsigned NOT NULL,
  `CreateDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Creator` char(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` char(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create syntax for TABLE 'addr_ContactsEvents'
CREATE TABLE `addr_ContactsEvents` (
  `Contacts_ID` int(7) unsigned NOT NULL DEFAULT '0',
  `Events_ID` int(11) unsigned NOT NULL DEFAULT '0',
  `Status` mediumint(4) unsigned NOT NULL DEFAULT '1',
  `Comments` char(255) NOT NULL DEFAULT '',
  `CreateDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Creator` char(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` char(20) NOT NULL,
  PRIMARY KEY (`Contacts_ID`,`Events_ID`),
  KEY `Status` (`Status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'addr_ContactsGroups'
CREATE TABLE `addr_ContactsGroups` (
 `Active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `Contacts_ID` int(7) unsigned NOT NULL DEFAULT '0',
  `Contacts_DataSource` char(75) NOT NULL DEFAULT 'addr_contacts',
  `Groups_ID` int(7) unsigned NOT NULL DEFAULT '0',
  `Idx` int(7) unsigned NOT NULL DEFAULT '0',
  `GroupPrimary` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `Title` char(75) NOT NULL DEFAULT '',
  `Position` char(45) NOT NULL DEFAULT '',
  `CreateDate` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `Creator` char(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` char(30) NOT NULL DEFAULT '',
   PRIMARY KEY (`Contacts_ID`,`Groups_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'addr_groups'
CREATE TABLE `addr_groups` (
  `ID` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `Active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `Category` varchar(30) NOT NULL DEFAULT 'Initial',
  `Name` varchar(50) NOT NULL DEFAULT '',
  `AllowUserToUnsubscribe` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `Address` text,
  `City` varchar(35) NOT NULL DEFAULT '',
  `State` char(3) NOT NULL DEFAULT '',
  `Zip` varchar(10) NOT NULL DEFAULT '',
  `Country` char(3) NOT NULL DEFAULT '',
  `Website` varchar(85) NOT NULL DEFAULT '',
  `Phone` varchar(24) NOT NULL DEFAULT '',
  `Fax` varchar(24) NOT NULL DEFAULT '',
  `Notes` text,
  `CreateDate` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `Creator` char(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Category` (`Category`),
  KEY `Name` (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'addr_groups_categories'
CREATE TABLE `addr_groups_categories` (
  `ID` mediumint(7) NOT NULL DEFAULT '0' AUTO_INCREMENT PRIMARY KEY,
  `Active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `Category` varchar(30) NOT NULL DEFAULT 'Initial',
  `Name` varchar(50) NOT NULL DEFAULT '',
  `Notes` text,
  `CreateDate` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `Creator` char(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'addr_GroupsGroups'
CREATE TABLE `addr_GroupsGroups` (
  `Active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `Parent_groups_id` int(7) unsigned NOT NULL DEFAULT '0',
  `Child_groups_id` int(7) unsigned NOT NULL DEFAULT '0',
  `Idx` int(7) unsigned NOT NULL DEFAULT '0',
  `GroupPrimary` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `Notes` char(255) NOT NULL DEFAULT '',
  `CreateDate` datetime NULL DEFAULT CURRENT_TIMESTAMP,
  `Creator` char(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` char(30) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'addr_u'
CREATE TABLE `addr_u` (
  `u` tinyint(1) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create syntax for TABLE 'bais_settings'
CREATE TABLE `bais_settings` (
  `UserName` char(20) NOT NULL,
  `vargroup` char(35) NOT NULL DEFAULT 'default',
  `varnode` varchar(35) NOT NULL,
  `varkey` varchar(35) NOT NULL,
  `varvalue` varchar(35) NOT NULL,
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`UserName`,`vargroup`,`varnode`,`varkey`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;