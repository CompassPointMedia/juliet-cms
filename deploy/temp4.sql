-- Create syntax for TABLE 'relatebase__mail_batches'
drop table relatebase__mail_batches;
CREATE TABLE `relatebase__mail_batches` (
  `ID` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `Active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `Profiles_ID` int(7) unsigned DEFAULT NULL,
  `Name` varchar(80) NOT NULL DEFAULT '',
  `BatchNumber` varchar(35) NOT NULL DEFAULT '',
  `RecipientSource` varchar(12) NOT NULL DEFAULT '',
  `Views_ID` int(7) unsigned DEFAULT NULL,
  `ComplexQuery` text,
  `HTMOrText` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `Composition` varchar(12) NOT NULL DEFAULT '',
  `TemplateLocationURL` text,
  `Files_ID` int(7) unsigned DEFAULT NULL,
  `FileName` text,
  `Subject` text,
  `Body` text,
  `FromName` varchar(75) NOT NULL DEFAULT '',
  `FromEmail` varchar(85) NOT NULL DEFAULT '',
  `ReplyToName` varchar(75) NOT NULL DEFAULT '',
  `ReplyToEmail` varchar(85) NOT NULL DEFAULT '',
  `Importance` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `AttachedVCard` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ReturnReceipt` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `StartTime` date,
  `StopTime` date,
  `BounceEmail` varchar(85) NOT NULL DEFAULT '',
  `BatchRecordEmail` varchar(85) NOT NULL DEFAULT '',
  `BatchNotes` varchar(255) NOT NULL DEFAULT '',
  `RecordVersion` varchar(10) NOT NULL DEFAULT 'v00.00.00',
  `MailerVersion` varchar(10) NOT NULL DEFAULT 'v00.00.00',
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Files_ID` (`Files_ID`),
  KEY `Profiles_ID` (`Profiles_ID`),
  KEY `Name` (`Name`),
  KEY `RecipientSource` (`RecipientSource`),
  KEY `Views_ID` (`Views_ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='** THIS TABLE IS DEPRECATED, SEE _content_batches **';

-- Create syntax for TABLE 'relatebase__mail_batches_logs'
drop table relatebase__mail_batches_logs;
CREATE TABLE `relatebase__mail_batches_logs` (
  `Profiles_ID` int(7) unsigned NOT NULL DEFAULT '0',
  `Batches_ID` int(7) unsigned NOT NULL DEFAULT '0',
  `Email` char(85) NOT NULL DEFAULT '',
  `SentTime` char(30) NOT NULL DEFAULT '',
  `ResponseScriptID` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `ResponseTime` datetime ,
  PRIMARY KEY (`Profiles_ID`,`Batches_ID`,`Email`),
  KEY `Email` (`Email`(15))
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'relatebase_BatchesContacts'
drop table relatebase_BatchesContacts;
CREATE TABLE `relatebase_BatchesContacts` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Batches_ID` int(11) unsigned NOT NULL COMMENT 'relatebase_content_batches',
  `Contacts_ID` int(11) unsigned DEFAULT NULL COMMENT 'Contacts and Batches are NOT a unique key',
  `Visitors_ID` int(11) unsigned DEFAULT NULL COMMENT 'for SNW primarily - when contacts_ID n/a; but also usable for known people',
  `Email` char(255) NOT NULL,
  `Status` tinyint(2) unsigned DEFAULT NULL,
  `ReceiptLevel` char(25) NOT NULL,
  `ReturnReceiptReceived` tinyint(1) unsigned NOT NULL COMMENT 'Default 0, 1 if they have replied to a return-receipt request (program email)',
  `AuthToken` char(32) NOT NULL,
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Batches_ID` (`Batches_ID`),
  KEY `Email` (`Email`),
  KEY `Contacts_ID` (`Contacts_ID`),
  KEY `ReceiptLevel` (`ReceiptLevel`),
  KEY `AuthToken` (`AuthToken`),
  KEY `ReturnReceiptReplied` (`ReturnReceiptReceived`),
  KEY `Visitors_ID` (`Visitors_ID`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'relatebase_content_batches'
drop table relatebase_content_batches;
CREATE TABLE `relatebase_content_batches` (
  `ID` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `ContentObject` char(25) NOT NULL COMMENT 'cal_events | cms1_articles | relatebase_mail_profiles | stats_pageserved',
  `ContentKey` int(11) unsigned NOT NULL,
  `ContentControlFieldHash` char(255) NOT NULL,
  `FieldHash` longtext NOT NULL,
  `BatchNumber` varchar(35) NOT NULL DEFAULT '',
  `Network` char(25) NOT NULL DEFAULT 'Email' COMMENT 'Email | Facebook | Twitter | [eventually] Cell Phones etc.',
  `FromName` varchar(75) NOT NULL DEFAULT '',
  `FromEmail` varchar(85) NOT NULL DEFAULT '',
  `ReplyToName` varchar(75) NOT NULL DEFAULT '',
  `ReplyToEmail` varchar(85) NOT NULL DEFAULT '',
  `BounceEmail` varchar(85) NOT NULL DEFAULT '',
  `BounceName` char(85) NOT NULL DEFAULT '',
  `Importance` tinyint(1) unsigned NOT NULL,
  `AttachedVCard` tinyint(1) unsigned NOT NULL,
  `ReturnReceipt` tinyint(1) unsigned NOT NULL,
  `StartTime` datetime NOT NULL,
  `StopTime` datetime DEFAULT NULL COMMENT 'not required for some aps like FB links',
  `BatchNotes` varchar(255) NOT NULL DEFAULT '',
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Network` (`Network`),
  KEY `ContentObjectKey` (`ContentObject`,`ContentKey`)
) ENGINE=MyISAM AUTO_INCREMENT=860 DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'relatebase_env'
drop table relatebase_env;
CREATE TABLE `relatebase_env` (
  `ID` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `ParamAcct` char(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ParamUser` char(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'root',
  `Collection` char(8) COLLATE utf8_unicode_ci NOT NULL DEFAULT '_SESSION',
  `RootObject` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ParamName` char(85) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ParamValue` char(17) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `RecordVersion` char(9) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'v00.00.00',
  PRIMARY KEY (`ID`),
  KEY `ParamAcct` (`ParamAcct`),
  KEY `ParamUser` (`ParamUser`),
  KEY `Collection` (`Collection`),
  KEY `RootObject` (`RootObject`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Create syntax for TABLE 'relatebase_files'
drop table relatebase_files;
CREATE TABLE `relatebase_files` (
  `ID` mediumint(7) unsigned NOT NULL AUTO_INCREMENT,
  `Folders_ID` int(7) NOT NULL DEFAULT '1',
  `VOSFileName` varchar(30) NOT NULL DEFAULT 'error.jpg',
  `LocalPath` text,
  `LocalFileName` varchar(255) NOT NULL DEFAULT '',
  `LocalMachine` int(7) unsigned DEFAULT NULL,
  `FileSize` int(14) unsigned NOT NULL DEFAULT '0',
  `FileWidth` int(8) unsigned DEFAULT NULL,
  `FileHeight` int(8) unsigned DEFAULT NULL,
  `UNIXPermissions` smallint(2) unsigned NOT NULL DEFAULT '0',
  `Title` varchar(128) NOT NULL DEFAULT '',
  `Categories_ID` int(7) unsigned NOT NULL DEFAULT '0',
  `Description` varchar(255) NOT NULL DEFAULT '',
  `Keywords` text NOT NULL,
  `RecordVersion` varchar(10) NOT NULL DEFAULT 'v00.00.00',
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Folders_ID` (`Folders_ID`),
  KEY `VOSFileName` (`VOSFileName`),
  KEY `UNIXPermissions` (`UNIXPermissions`),
  KEY `LocalFileName` (`LocalFileName`),
  KEY `FileWidth` (`FileWidth`),
  KEY `FileHeight` (`FileHeight`),
  KEY `LocalMachine` (`LocalMachine`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'relatebase_files_categories'
drop table relatebase_files_categories;
CREATE TABLE `relatebase_files_categories` (
  `AcctName` varchar(30) NOT NULL DEFAULT '{RB_CURRENTACCTNAME}',
  `ID` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `Active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `Name` varchar(45) NOT NULL DEFAULT '',
  `Description` varchar(255) NOT NULL DEFAULT '',
  `Notes` varchar(75) NOT NULL DEFAULT '',
  `RecordVersion` varchar(10) NOT NULL DEFAULT 'v00.00.00',
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Name` (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'relatebase_folders'
drop table relatebase_folders;
CREATE TABLE `relatebase_folders` (
  `ID` int(7) NOT NULL AUTO_INCREMENT,
  `Name` varchar(128) NOT NULL DEFAULT '',
  `Title` varchar(40) NOT NULL DEFAULT '',
  `Categories_ID` int(7) unsigned NOT NULL DEFAULT '0',
  `Description` varchar(255) NOT NULL DEFAULT '',
  `Folders_ID` int(7) NOT NULL DEFAULT '0',
  `UNIXPermissions` smallint(2) unsigned NOT NULL DEFAULT '0',
  `Alias` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Name` (`Name`),
  KEY `Title` (`Title`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'relatebase_folders_categories'
drop table relatebase_folders_categories;
CREATE TABLE `relatebase_folders_categories` (
  `AcctName` varchar(30) NOT NULL DEFAULT '{RB_CURRENTACCTNAME}',
  `ID` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `Active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `Name` varchar(45) NOT NULL DEFAULT '',
  `Description` varchar(255) NOT NULL DEFAULT '',
  `Notes` varchar(75) NOT NULL DEFAULT '',
  `RecordVersion` varchar(10) NOT NULL DEFAULT 'v00.00.00',
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Name` (`Name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'relatebase_mail_profiles'
drop table relatebase_mail_profiles;
CREATE TABLE `relatebase_mail_profiles` (
  `ID` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `Active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `Name` varchar(80) NOT NULL DEFAULT '',
  `RecipientSource` varchar(15) NOT NULL DEFAULT '',
  `Views_ID` int(7) unsigned DEFAULT NULL,
  `Groups_ID` int(9) unsigned NOT NULL DEFAULT '0',
  `OverrideViewFilters` smallint(2) NOT NULL DEFAULT '0',
  `ImportType` varchar(15) NOT NULL DEFAULT '',
  `ImportHeaders` smallint(2) unsigned NOT NULL DEFAULT '0',
  `HTMLOrText` smallint(2) unsigned NOT NULL DEFAULT '1',
  `Composition` varchar(12) NOT NULL DEFAULT 'blank',
  `EditableRegion` varchar(15) NOT NULL DEFAULT '',
  `EditableRegionName` varchar(75) NOT NULL DEFAULT '',
  `TemplateMethod` varchar(10) NOT NULL DEFAULT '',
  `TemplateFileOrURL` varchar(255) NOT NULL DEFAULT '',
  `TemplateDefaultDirectory` int(7) unsigned NOT NULL DEFAULT '0',
  `AttachmentDefaultDirectory` int(7) unsigned NOT NULL DEFAULT '0',
  `FromName` varchar(75) NOT NULL DEFAULT '',
  `FromEmail` varchar(85) NOT NULL DEFAULT '',
  `ReplyToName` varchar(75) NOT NULL DEFAULT '',
  `ReplyToEmail` varchar(85) NOT NULL DEFAULT '',
  `BounceEmail` varchar(85) NOT NULL DEFAULT '',
  `TestEmail` varchar(85) NOT NULL DEFAULT '',
  `TestEmailBatch` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `TestEmailStart` mediumint(4) unsigned NOT NULL DEFAULT '0',
  `AlwaysPreview` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `Importance` smallint(2) NOT NULL DEFAULT '0',
  `AttachVCard` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ReturnReceipt` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `DefaultBatchName` varchar(45) NOT NULL DEFAULT '',
  `DefaultBatchNameAutoinc` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `BatchRecord` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `BatchRecordEmail` varchar(85) NOT NULL DEFAULT '',
  `LastUsageTime` datetime ,
  `SessionKey` varchar(8) NOT NULL DEFAULT '',
  `ResourceType` tinyint(1) unsigned DEFAULT NULL,
  `ResourceToken` varchar(20) NOT NULL DEFAULT '',
  `SessionType` tinyint(2) unsigned DEFAULT NULL,
  `RecordVersion` varchar(10) NOT NULL DEFAULT 'v00.00.00',
  `MailerVersion` varchar(10) NOT NULL DEFAULT 'v00.00.00',
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Views_ID` (`Views_ID`),
  KEY `Views_ID_2` (`Views_ID`),
  KEY `OverrideViewFilters` (`OverrideViewFilters`),
  KEY `ImportType` (`ImportType`),
  KEY `ImportHeaders` (`ImportHeaders`),
  KEY `TemplateDefaultDirectory` (`TemplateDefaultDirectory`),
  KEY `AttachmentDefaultDirectory` (`AttachmentDefaultDirectory`),
  KEY `Groups_ID` (`Groups_ID`),
  KEY `ResourceType` (`ResourceType`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'relatebase_mail_profiles_vars'
drop table relatebase_mail_profiles_vars;
CREATE TABLE `relatebase_mail_profiles_vars` (
  `ID` int(7) unsigned NOT NULL AUTO_INCREMENT,
  `Active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `Profiles_ID` int(7) unsigned NOT NULL DEFAULT '0',
  `Name` varchar(64) NOT NULL DEFAULT '',
  `Idx` int(7) unsigned DEFAULT NULL,
  `Ky` varchar(64) NOT NULL DEFAULT '',
  `Val` text NOT NULL,
  `Notes` varchar(75) NOT NULL DEFAULT '',
  `RecordVersion` varchar(10) NOT NULL DEFAULT 'v00.00.00',
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Profiles_ID` (`Profiles_ID`),
  KEY `Idx` (`Idx`),
  KEY `Ky` (`Ky`),
  KEY `Name` (`Name`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'relatebase_maillog'
drop table relatebase_maillog;
CREATE TABLE `relatebase_maillog` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `MailedToName` char(75) NOT NULL,
  `MailedToEmail` text NOT NULL COMMENT 'Allowed to handle a bulk email list',
  `ShuntedToEmail` char(255) DEFAULT NULL COMMENT 'Added 2010-02-24 by Samuel',
  `MailedBy` char(45) NOT NULL,
  `Subject` text NOT NULL,
  `Content` longtext NOT NULL,
  `FromAs` char(255) NOT NULL,
  `ReplyTo` char(255) NOT NULL,
  `SendMethod` enum('Plaintext','HTML') NOT NULL DEFAULT 'HTML',
  `Attachments` text NOT NULL,
  `Notes` text NOT NULL,
  `TemplateSource` text NOT NULL,
  `Version` float(3,2) NOT NULL DEFAULT '1.00',
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `SendMethod` (`SendMethod`),
  KEY `CreateDate` (`CreateDate`),
  KEY `MailedBy` (`MailedBy`)
) ENGINE=MyISAM AUTO_INCREMENT=3023 DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'relatebase_mailqueue'
drop table relatebase_mailqueue;
CREATE TABLE `relatebase_mailqueue` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `To` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `Subject` text COLLATE utf8_unicode_ci NOT NULL,
  `Message` longtext COLLATE utf8_unicode_ci NOT NULL,
  `Headers` text COLLATE utf8_unicode_ci NOT NULL,
  `FSwitchEmail` char(255) COLLATE utf8_unicode_ci NOT NULL,
  `QueueTime` datetime NOT NULL,
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `Status` (`Status`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

-- Create syntax for TABLE 'relatebase_object_structure'
drop table relatebase_object_structure;
create table `relatebase_object_structure` (
  `ID` smallint(7) NOT NULL AUTO_INCREMENT,
  `Active` tinyint(1) NOT NULL DEFAULT '1',
  `KeyName` varchar(45) NOT NULL DEFAULT '',
  `Description` varchar(75) NOT NULL DEFAULT '',
  `VersionAddedIn` float(3,2) NOT NULL DEFAULT '0.00',
  `Structure_ID` mediumint(6) NOT NULL DEFAULT '0',
  `RepresentsArrayOrString` smallint(1) NOT NULL DEFAULT '0',
  `DeterminedOrVariable` smallint(1) NOT NULL DEFAULT '0',
  `SampleValues` varchar(255) NOT NULL DEFAULT '',
  `Rules` text NOT NULL,
  `Aliases` varchar(75) NOT NULL DEFAULT '',
  `Storage` varchar(50) NOT NULL DEFAULT '',
  `Notes` text NOT NULL,
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=77 DEFAULT CHARSET=latin1;

-- Create syntax for TABLE 'relatebase_ObjectsTree'
drop table relatebase_ObjectsTree;
CREATE TABLE `relatebase_ObjectsTree` (
  `Objects_ID` int(11) unsigned NOT NULL,
  `ObjectName` char(25) NOT NULL DEFAULT '' COMMENT 'Added 2010-08-21 by Samuel',
  `Tree_ID` int(11) unsigned NOT NULL,
  `Relationship` char(30) NOT NULL COMMENT 'Added 2011-03-03 by Samuel',
  `Title` char(75) NOT NULL COMMENT 'Added 2011-03-03 by Samuel',
  `Description` char(255) NOT NULL COMMENT 'Added 2011-03-03 by Samuel',
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`Objects_ID`,`ObjectName`,`Tree_ID`),
  KEY `Relationship` (`Relationship`),
  KEY `Editor` (`Editor`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'relatebase_queries'
drop table relatebase_queries;
CREATE TABLE `relatebase_queries` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Title` char(255) NOT NULL,
  `SystemIdentifier` char(75) NOT NULL,
  `Version` char(10) NOT NULL DEFAULT '1.0' COMMENT 'version 1.0 deals with notation for dynamic variables and how it relates to many other dynamic variable a user might want access to',
  `Content` longtext NOT NULL,
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Title` (`Title`),
  KEY `SystemIdentifier` (`SystemIdentifier`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- Create syntax for TABLE 'relatebase_tree'
drop table relatebase_tree;
CREATE TABLE `relatebase_tree` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `Tree_ID` int(11) unsigned DEFAULT NULL,
  `Type` enum('file','folder','link-symbolic','link') NOT NULL,
  `Name` char(255) NOT NULL,
  `Title` char(255) DEFAULT NULL,
  `Description` text,
  `VOSFileName` char(32) NOT NULL,
  `LocalPath` text,
  `LocalFileName` char(255) DEFAULT NULL,
  `LocalMachines_ID` int(11) unsigned DEFAULT NULL,
  `FileSize` int(14) unsigned DEFAULT NULL,
  `FileWidth` mediumint(5) unsigned DEFAULT NULL,
  `FileHeight` mediumint(5) unsigned DEFAULT NULL,
  `MimeType` char(32) DEFAULT NULL,
  `CreateDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `Creator` varchar(30) NOT NULL DEFAULT '{RB_ACCTNAME}',
  `EditDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Editor` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `Trees_ID` (`Tree_ID`),
  KEY `Types_ID` (`Type`),
  KEY `FileName` (`Name`),
  KEY `Title` (`Title`),
  KEY `VOSFileName` (`VOSFileName`),
  KEY `FileSize` (`FileSize`),
  KEY `CreateDate` (`CreateDate`)
) ENGINE=MyISAM AUTO_INCREMENT=89 DEFAULT CHARSET=latin1 ;