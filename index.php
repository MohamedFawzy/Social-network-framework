<?php

session_start();

DEFINE("FRAMEWORK_PATH", dirname( __FILE__ ) ."/" );


require('registry/registry.class.php');
$registry = new Registry();
// setup our core registry objects
$registry->createAndStoreObject( 'template', 'template' );
$registry->createAndStoreObject( 'mysqldb', 'db' );
$registry->createAndStoreObject( 'authenticate', 'authenticate' );
$registry->createAndStoreObject( 'urlprocessor', 'url' );
$registry->getObject('url')->getURLData();
// database settings
include(FRAMEWORK_PATH . 'config.php');
// create a database connection
$registry->getObject('db')->newConnection( $configs['db_host_sn'], $configs['db_user_sn'], $configs['db_pass_sn'], $configs['db_name_sn']);

$registry->getObject('authenticate')->checkForAuthentication();

// store settings in our registry
$settingsSQL = "SELECT `key`, `value` FROM settings";
$registry->getObject('db')->executeQuery( $settingsSQL );
while( $setting = $registry->getObject('db')->getRows() )
{
	$registry->storeSetting( $setting['value'], $setting['key'] );
}
$registry->getObject('template')->getPage()->addTag( 'siteurl', $registry->getSetting('siteurl') );
$registry->getObject('template')->buildFromTemplates('header.tpl.php', 'main.tpl.php', 'footer.tpl.php');
				
$controllers = array();
$controllersSQL = "SELECT * FROM controllers WHERE active=1";
$registry->getObject('db')->executeQuery( $controllersSQL );
while( $controller = $registry->getObject('db')->getRows() )
{
	$controllers[] = $controller['controller'];
}
$controller = $registry->getObject('url')->getURLBit(0);


if( $registry->getObject('authenticate')->isLoggedIn() )
{
	$registry->getObject('template')->addTemplateBit('userbar', 'userbar_loggedin.tpl.php');
	$registry->getObject('template')->getPage()->addTag( 'username', $registry->getObject('authenticate')->getUser()->getUsername() );
	
}
else
{
	$registry->getObject('template')->addTemplateBit('userbar', 'userbar.tpl.php');
}


if( in_array( $controller, $controllers ) )
{

	require_once( FRAMEWORK_PATH . 'controllers/' . $controller . '/controller.php');
	$controllerInc = $controller.'controller';
	$controller = new $controllerInc( $registry, true );

}
else
{
	// default controller, or pass control to CMS type system?
}


$registry->getObject('template')->parseOutput();
print $registry->getObject('template')->getPage()->getContentToPrint();


?>