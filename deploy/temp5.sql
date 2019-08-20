-- Create syntax for TABLE 'system_machines'
drop table if exists system_machines;
CREATE TABLE `system_machines` (
  `ID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `MachineName` char(32) NOT NULL DEFAULT '' COMMENT 'This is e.g. davidsmightymen',
  `MachineDescription` char(255) NOT NULL DEFAULT '' COMMENT 'E.g. my work computer, my office computer - user friendly description',
  `MachineType` char(30) NOT NULL DEFAULT '' COMMENT 'General type of device - desktop, laptop, mobile device, iPad etc.',
  `ControlLevel` smallint(3) unsigned DEFAULT NULL,
  `TimeVariance` time,
  `TimeZone` tinyint(3) NOT NULL DEFAULT '0',
  `UniqueKey` char(32) NOT NULL DEFAULT '' COMMENT 'Browser MUST allow cookie to be set',
  `IPAddress` char(15) NOT NULL,
  `OS` char(75) DEFAULT NULL,
  `UserAgent` char(128) NOT NULL COMMENT 'was previously named "Browser"',
  `MonitorResolution` char(35) NOT NULL DEFAULT '',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Comments` char(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `UniqueKey` (`UniqueKey`),
  KEY `MachineType` (`MachineType`),
  KEY `ControlLevel` (`ControlLevel`),
  KEY `OS` (`OS`),
  KEY `IPAddress` (`IPAddress`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'system_poststorage'
drop table if exists system_poststorage;
CREATE TABLE `system_poststorage` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Reposted` tinyint(2) unsigned NOT NULL,
  `UserName` char(20) NOT NULL,
  `Mode` char(255) NOT NULL,
  `Session` longtext NOT NULL,
  `Content` longtext NOT NULL,
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,
  PRIMARY KEY (`ID`),
  KEY `UserName` (`UserName`),
  KEY `Mode` (`Mode`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'system_profiles'
drop table if exists system_profiles;
CREATE TABLE `system_profiles` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Tables_ID` int(11) unsigned NOT NULL,
  `Identifier` char(30) NOT NULL DEFAULT 'default' ,
  `Type` char(20) NOT NULL DEFAULT 'Export',
  `Category` char(30) NOT NULL,
  `Name` char(255) NOT NULL,
  `Description` text NOT NULL,
  `Settings` longtext NOT NULL,
  `Version` char(3) NOT NULL DEFAULT '1.0',
  `CreateDate` datetime NOT NULL,
  `Creator` char(20) NOT NULL,
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` char(20) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `Tables_ID` (`Tables_ID`),
  KEY `Identifier` (`Identifier`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- Create syntax for TABLE 'system_tables'
drop table if exists system_tables;
CREATE TABLE `system_tables` (
  `ID` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `SystemName` char(255) NOT NULL,
  `Name` char(255) NOT NULL,
  `KeyField` char(255) NOT NULL,
  `Description` text NOT NULL,
  `Type` enum('table','view') NOT NULL DEFAULT 'table',
  `Level` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `Settings` longtext NOT NULL,
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

drop table if exists system_ContactsMachines;
CREATE TABLE `system_ContactsMachines` (
  `Contacts_ID` int(11) unsigned NOT NULL,
  `Machines_ID` int(11) unsigned NOT NULL,
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Comments` char(75) NOT NULL DEFAULT '',
  PRIMARY KEY (`Contacts_ID`,`Machines_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;





CREATE OR REPLACE VIEW `_v_contacts_generic_v200`
AS SELECT
   `a`.`ID` AS `ID`,
   `c`.`Active` AS `Active`,
   `a`.`Statuses_ID` AS `Statuses_ID`,
   `d`.`Name` AS `Status`,
   `d`.`StatusHandle` AS `StatusHandle`,
   `a`.`ToBeExported` AS `ToBeExported`,
   `a`.`Exporter` AS `Exporter`,
   `a`.`ExportTime` AS `ExportTime`,
   `a`.`CompanyName` AS `CompanyName`,
   `a`.`ClientName` AS `ClientName`,
   `a`.`Address1` AS `ClientAddress`,
   `a`.`City` AS `ClientCity`,
   `a`.`State` AS `ClientState`,
   `a`.`Zip` AS `ClientZip`,
   `a`.`Country` AS `ClientCountry`,
   `a`.`ShippingAddress` AS `ShippingAddress`,
   `a`.`ShippingCity` AS `ShippingCity`,
   `a`.`ShippingState` AS `ShippingState`,
   `a`.`ShippingCountry` AS `ShippingCountry`,
   `c`.`CreateDate` AS `CreateDate`,
   `c`.`FirstName` AS `FirstName`,
   `c`.`MiddleName` AS `MiddleName`,
   `c`.`LastName` AS `LastName`,
   `c`.`Suffix` AS `Suffix`,
   `c`.`Title` AS `Title`,
   `c`.`Email` AS `Email`,
   `c`.`Email2` AS `AlternateEmail`,
   `c`.`HomeAddress` AS `HomeAddress`,
   `c`.`HomeCity` AS `HomeCity`,
   `c`.`HomeState` AS `HomeState`,
   `c`.`HomeZip` AS `HomeZip`,
   `c`.`HomeCountry` AS `HomeCountry`,
   `c`.`HomePhone` AS `HomePhone`,
   `c`.`HomeFax` AS `HomeFax`,
   `c`.`HomeMobile` AS `HomeMobile`,
   `c`.`HomeMobile` AS `CellPhone`,
   `c`.`BusAddress` AS `BusinessAddress`,
   `c`.`BusCity` AS `BusinessCity`,
   `c`.`BusState` AS `BusinessState`,
   `c`.`BusZip` AS `BusinessZip`,
   `c`.`BusCountry` AS `BusinessCountry`,
   `c`.`BusPhone` AS `BusinessPhone`,
   `c`.`BusFax` AS `BusinessFax`,
   `c`.`BusWebsite` AS `BusinessWebsite`,
   `c`.`UserName` AS `UserName`,
   `c`.`ID` AS `Contacts_ID`,
   `c`.`EnrollmentAuthToken` AS `EnrollmentAuthToken`,
   `f`.`Name` AS `Category`,count(distinct `e`.`Categories_ID`) AS `CategoryCount`,
   `sr`.`RepCode` AS `RepCode`,
   `c`.`Salesreps_ID` AS `Salesreps_ID`,
   `src`.`FirstName` AS `RepFirstName`,
   `src`.`LastName` AS `RepLastName`,
   `src`.`Email` AS `RepEmail`,
   `src`.`UserName` AS `RepUserName`,
   `src`.`Password` AS `RepPasswordMD5`
FROM (((((((`finan_clients` `a` left join `finan_ClientsContacts` `cc` on(((`a`.`ID` = `cc`.`Clients_ID`) and (`cc`.`Type` = _latin1'Primary')))) left join `addr_contacts` `c` on((`cc`.`Contacts_ID` = `c`.`ID`))) left join `finan_clients_statuses` `d` on((`a`.`Statuses_ID` = `d`.`ID`))) left join `finan_ClientsCategories` `e` on((`a`.`ID` = `e`.`Clients_ID`))) left join `finan_items_categories` `f` on((`e`.`Categories_ID` = `f`.`ID`))) left join `finan_salesreps` `sr` on((`c`.`Salesreps_ID` = `sr`.`Contacts_ID`))) left join `addr_contacts` `src` on((`sr`.`Contacts_ID` = `src`.`ID`))) where (`a`.`ResourceType` is not null) group by `a`.`ID`;