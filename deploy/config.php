<?php
/**
 * Juliet CMS
 * This file with actual values needs to go into `../private/config.php` AS OF version 0.2.  Watch out for changes; there will be some, along with the meaning of the values.  All repos I write are dependent upon a config file outside the git root folder.
 *
 * Things being batted around at the batcave:
 *  1. going with an ini file (universal, easily readable, are we really going to need it)
 *  2. having subfolder config files in folders like `vagrant`, `qa`, etc. similar to the way Laravel does it.
 */


//this account - this doesn't have to be the db name but you're probably safer at this point to do it that way
$acct = 'cpm105';

//nodded to talk to the database
$CMSBVersion='311';
$SUPER_MASTER_USERNAME='administrator';
$SUPER_MASTER_PASSWORD='super-secret';
$SUPER_MASTER_HOSTNAME='localhost';
$SUPER_MASTER_DATABASE='relatebase';

$RECORD_MASTER_DATABASE = $acct;
$MASTER_DATABASE = $acct;
$MASTER_USERNAME = $acct;
$MASTER_HOSTNAME = 'localhost';
$MASTER_PASSWORD = 'still-secret';
$companyName = 'Compass Point Media';
$adminPhone = '512-555-1212'; //not sure if this should be companyPhone or adminPhone
$adminEmail = 'email@yourdomain.com';
$developerEmail = 'email.samuel.fullman@gmail.com';

$suppressWrappers['mainWrapSub']=false;

// this was from file /config.pre.cpm185.php in the root folder
$shoppingCartVersion=410;
$qVersion=130;
//$pJCenterContentInsetWide=2;
$subkeySortVersion=300;

$addedEmbeddedModulesAuth=md5($MASTER_PASSWORD);
$addedEmbeddedModules=array(
    'resourcescheduler'=>array(
        'moduleAdminSettings'=>array(
            'handle'=>'members',
            'componentPage'=>'resourcescheduler.php',
        ),
    ),
);

// I have this read-only connection to a db called `z_public` which houses lists such as states, cities, countries, counties, etc.
$public_cnx=array('localhost','z_public','secret','z_public');







