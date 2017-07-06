<?php



$a=array
(
    'version' => 1,
    'name' => 'E-commerce Module',
    'handle' => products,
    'handleAliases' => Array
        (
            0 => services,
            1 => store,
        ),

    'flow' => Array
        (
            '8001' => Array
                (
                    'name' => 'Product Category Hub',
                ),

            '8002' => Array
                (
                    'name' => 'Single Category',
                    'requiredParameters' => Array
                        (
                            0 => Category
                        ),

                ),

            '8003' => Array
                (
                    'name' => 'Single Subcategory',
                    'requiredParameters' => Array
                        (
                            '0' => Category,
                            '1' => Subcategory
                        ),

                ),

            '8004' => Array
                (
                    'name' => 'Single Product Page',
                    'optionalParameters' => Array
                        (
                            '0' => Category,
                            '1' => Subcategory
                        ),

                    'requiredParameters' => Array
                        (
                            '0' => 'Items_ID:ID'
                        ),

                ),

            '8005' => Array
                (
                    'name' => 'Basic Search'
                ),

        ),

    'navDevices' => Array
        (
        ),

);

$a=array(
	'version'=>1,
	'handle'=>'nav',
	'handleAliases'=>array('menu'),
	'name'=>'RSC Menu Creator',
	'gettable_parameters'=>array(
		/* gettable_parameters should be unique string values in RelateBase gettable as needed from the environment or other modules/components in the system.  the menu creator is a "module" not a component.  Components are used by modules (or can be, or can be stand alone)
		settable_parameters should be available parameters which other modules/components can use.
		*/
		0=> /* *settings* */ array(
			'required'=>array('menuID','insertRegion'),
		),
		'menuID'=>'{RB_DEFAULTMENUID}',
		'insertRegion'=>'{TEMPLATE_DEFAULTNAVREGION}',
	),
	'settable_parameters'=>array(
		'EEYORSFAVORITECOLOR'=>'green',
	),
	'component'=> /* must be located in components-juliet folder */ 'RSC20_menu_creator_v100.php',
);

$a=array(
	'version'=>1,
	'handle'=>'site',
	'handleAliases'=>array('rsc01'),
	'name'=>'RSC Site Creator',
	'gettable_parameters'=>array(
	),
	'settable_parameters'=>array(
		'thema'=>'basic',
	),
	'install'=>array(
		
	),
);

$a=array(
	'version'=>1,
	'handle'=>'nav',
	'handleAliases'=>array('menu'),
	'name'=>'RSC Menu Creator',
	'gettable_parameters'=>array(
		/* gettable_parameters should be unique string values in RelateBase gettable as needed from the environment or other modules/components in the system.  the menu creator is a "module" not a component.  Components are used by modules (or can be, or can be stand alone)
		settable_parameters should be available parameters which other modules/components can use.
		*/
		0=> /* *settings* */ array(
			'required'=>array('menuID','insertRegion'),
		),
		'menuID'=>'{RB_DEFAULTMENUID}',
		'insertRegion'=>'{TEMPLATE_DEFAULTNAVREGION}',
	),
	'settable_parameters'=>array(
		'EEYORSFAVORITECOLOR'=>'green',
	),
	'component'=> /* must be located in components-juliet folder */ 'RSC20_menu_creator_v100.php',
);


echo base64_encode(serialize($a));
exit;

?>
YTo3OntzOjc6InZlcnNpb24iO2k6MTtzOjY6ImhhbmRsZSI7czozOiJuYXYiO3M6MTM6ImhhbmRsZUFsaWFzZXMiO2E6MTp7aTowO3M6NDoibWVudSI7fXM6NDoibmFtZSI7czoxNjoiUlNDIE1lbnUgQ3JlYXRvciI7czoxOToiZ2V0dGFibGVfcGFyYW1ldGVycyI7YTozOntpOjA7YToxOntzOjg6InJlcXVpcmVkIjthOjI6e2k6MDtzOjY6Im1lbnVJRCI7aToxO3M6MTI6Imluc2VydFJlZ2lvbiI7fX1zOjY6Im1lbnVJRCI7czoxODoie1JCX0RFRkFVTFRNRU5VSUR9IjtzOjEyOiJpbnNlcnRSZWdpb24iO3M6Mjc6IntURU1QTEFURV9ERUZBVUxUTkFWUkVHSU9OfSI7fXM6MTk6InNldHRhYmxlX3BhcmFtZXRlcnMiO2E6MTp7czoxOToiRUVZT1JTRkFWT1JJVEVDT0xPUiI7czo1OiJncmVlbiI7fXM6OToiY29tcG9uZW50IjtzOjI3OiJSU0MyMF9tZW51X2NyZWF0b3JfdjEwMC5waHAiO30=