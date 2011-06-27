<?php
exit('dfgfdgfdg');
define( 'ONAPP_USERS_ADDON_PATH', ROOTDIR . '/modules/addons/onapp_vms' );
var_dump( ONAPP_USERS_ADDON_PATH );

require_once ONAPP_USERS_ADDON_PATH . '/onapp_vms.php';

load_lang( );
$vars[ '_lang' ] = $_LANG;
onapp_vms_output( $vars );

function load_lang( ) {
	global $_LANG;

	$file = ONAPP_USERS_ADDON_PATH . '/lang/' . strtolower( @$_SESSION[ 'Language' ] ) . '.php';
	if( !file_exists( $file ) ) {
		$file = ONAPP_USERS_ADDON_PATH . '/lang/english.php';
	}

	include_once $file;

	$_LANG = array_merge( $_LANG, $_ADDONLANG );
}