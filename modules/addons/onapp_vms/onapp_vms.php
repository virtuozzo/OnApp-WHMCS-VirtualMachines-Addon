<?php
//todo extract GET parameters
include_once 'classes/Addon.php';

//error_reporting(E_ALL);
//ini_set('display_errors', 'On');

function onapp_vms_output( $vars ) {
	global $templates_compiledir, $customadminpath;

	include_once ROOTDIR . '/includes/smarty/Smarty.class.php';
	$smarty = new Smarty( );
	$compile_dir = file_exists( $templates_compiledir ) ? $templates_compiledir : ROOTDIR . '/' . $templates_compiledir;
	$smarty->compile_dir = $compile_dir;
	$smarty->template_dir = ROOTDIR . '/' . $customadminpath . '/templates/' . $GLOBALS[ 'aInt' ]->adminTemplate . '/onapp_vms_addon/';

	if( !file_exists( $smarty->template_dir ) ) {
		$msg = 'Copy folder ' . ROOTDIR . '/' . $customadminpath . '/templates/v4/onapp_vms_addon to '
			   . ROOTDIR . '/' . $customadminpath . '/templates/' . $GLOBALS[ 'aInt' ]->adminTemplate . '/';
		exit( $msg );
	}

	$base_url = $_SERVER[ 'SCRIPT_NAME' ] . '?module=' . $_GET[ 'module' ];
	$vars[ '_lang' ][ 'JSMessages' ] = json_encode( $vars[ '_lang' ][ 'JSMessages' ] );
	$smarty->assign( 'LANG', $vars[ '_lang' ] );
	$smarty->assign( 'BASE_CSS', '../' . $customadminpath . '/templates/' . $GLOBALS[ 'aInt' ]->adminTemplate . '/onapp_vms_addon' );
	$smarty->assign( 'BASE_JS', '../modules/addons/onapp_vms/js' );
	$smarty->assign( 'BASE', $base_url );

	$module = new OnApp_VMs_Addon( $smarty );

	if( isset( $_GET[ 'action' ] ) && ( $_GET[ 'action' ] == 'info' ) ) {
		$data = $module->getUserData( $_GET[ 'whmcs_user_id' ] );
		$smarty->assign( 'whmcs_user', $data[ 'data' ] );

		$data = $module->getUserVMsFromWHMCS( $_GET[ 'whmcs_user_id' ] );
		$smarty->assign( 'whmcs_user_products', $data[ 'data' ] );
	}
	elseif( isset( $_GET[ 'action' ] ) && ( $_GET[ 'action' ] == 'map' ) ) {
		$data = $module->getUserData( $_GET[ 'whmcs_user_id' ] );
		$smarty->assign( 'whmcs_user', $data[ 'data' ] );

		$data = $module->getProductData( $_GET[ 'product_id' ] );
		$smarty->assign( 'product', $data );

		$data = $module->getVMsFromOnApp( $data[ 'server_id' ] );
		$smarty->assign( 'onapp_vms', $data[ 'data' ] );
	}
	else {
		$data = $module->getUsersFromWHMCS( );
		$smarty->assign( 'whmcs_users', $data[ 'data' ] );
	}

	$smarty->assign( 'pages', $data[ 'pages' ] );
	$smarty->assign( 'current', $data[ 'current' ] );

	if( isset( $data[ 'prev' ] ) ) {
		$smarty->assign( 'prev', $data[ 'prev' ] );
	}
	if( isset( $data[ 'next' ] ) ) {
		$smarty->assign( 'next', $data[ 'next' ] );
	}

	$smarty->assign( 'total', $data[ 'total' ] );
	$smarty->assign( 'server_id', $_GET[ 'server_id' ] );

	echo $smarty->fetch( $smarty->template_dir . 'onapp_vms.tpl' );
}

function onapp_vms_config( ) {
	$config = array(
		'name' => 'OnApp Virtual Machines',
		'version' => '1.0',
		'author' => 'OnApp',
		'description' => 'This module allows you to map existing OnApp virtual machine to WHMCS\' controlled virtual machine and some other useful actions.',
		'language' => 'english'
	);

	return $config;
}