<?php

class OnApp_VMs_Addon {
	private $smarty = null;
	private $lang = null;
	private $servers = array( );
	private $limit = 10;
	private $offset = 0;
	private $product;

	public function __construct( &$smarty = null ) {
		$this->smarty = $smarty;
		$this->lang = $smarty->get_template_vars( 'LANG' );

		$this->smarty->assign( 'onapp_servers', $this->getServers( ) );
		$server = current( $this->servers );
		$smarty->assign( 'server_id', $_GET[ 'server_id' ] = $server[ 'id' ] );

		switch( $_GET[ 'action' ] ) {
			case 'info':
				$smarty->assign( 'info', true );
				break;

			case 'map':
				$smarty->assign( 'map', true );
				break;

			case 'domap':
				$smarty->assign( 'info', true );
				$this->map( );
				break;

			case 'unmap': //todo
				$this->unmap( );
				break;
		}

		if( !isset( $_GET[ 'page' ] ) ) {
			$_GET[ 'page' ] = 1;
		}

		$this->offset = $this->limit * ( $_GET[ 'page' ] - 1 );
	}

	public function getServers( ) {
		$sql = 'SELECT `id`, `name`, `ipaddress`, `hostname`, `username`, `password`'
			   . ' FROM `tblservers` WHERE `type` = "onapp"';

		$res = full_query( $sql );

		while( $row = mysql_fetch_assoc( $res ) ) {
			$this->servers[ $row[ 'id' ] ] = $this->getServerData( $row );
		}

		return $this->servers;
	}

	public function getUsersFromWHMCS( $id = false ) {
		$sql = 'SELECT SQL_CALC_FOUND_ROWS whmcs.*, onapp.email as mail, onapp.client_id, onapp.server_id, onapp.onapp_user_id'
			   . ' FROM `tblclients` AS whmcs LEFT JOIN `tblonappclients` AS onapp ON whmcs.`id` = onapp.`client_id`'
			   . ' WHERE onapp.`server_id` = ' . $_GET[ 'server_id' ]
			   . ' LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;

		$res = full_query( $sql );

		while( $row = mysql_fetch_assoc( $res ) ) {
			$results[ 'data' ][ $row[ 'id' ] ] = $row;
		}

		$results[ 'total' ] = mysql_result( mysql_query( 'SELECT FOUND_ROWS( )' ), 0 );

		$results[ 'pages' ] = ceil( $results[ 'total' ] / $this->limit );
		$results[ 'current' ] = $_GET[ 'page' ];

		if( $_GET[ 'page' ] > 1 ) {
			$results[ 'prev' ] = $_GET[ 'page' ] - 1;
		}

		if( ( $this->offset + $this->limit ) < $results[ 'total' ] ) {
			$results[ 'next' ] = $_GET[ 'page' ] + 1;
		}

		return $results;
	}

	public function getUserVMsFromWHMCS( $id ) {
		$sql = 'SELECT SQL_CALC_FOUND_ROWS hosting.* FROM `tblhosting` AS hosting'
			   . ' LEFT JOIN `tblproducts` AS products ON products.`id` = hosting.`packageid`'
			   . ' WHERE hosting.`userid` = ' . $id
			   . ' AND products.`servertype` = "onapp" AND hosting.`id` NOT IN ( SELECT service_id FROM tblonappservices )'
			   . ' LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;

		$res = full_query( $sql );

		while( $row = mysql_fetch_assoc( $res ) ) {
			$results[ 'data' ][ $row[ 'id' ] ] = $row;
		}

		$results[ 'total' ] = mysql_result( mysql_query( 'SELECT FOUND_ROWS( )' ), 0 );

		$results[ 'pages' ] = ceil( $results[ 'total' ] / $this->limit );
		$results[ 'current' ] = $_GET[ 'page' ];

		if( $_GET[ 'page' ] > 1 ) {
			$results[ 'prev' ] = $_GET[ 'page' ] - 1;
		}

		if( ( $this->offset + $this->limit ) < $results[ 'total' ] ) {
			$results[ 'next' ] = $_GET[ 'page' ] + 1;
		}

		return $results;
	}

	public function getUserData( $id ) {
		$sql = 'SELECT whmcs.* FROM `tblclients` AS whmcs WHERE whmcs.`id` = ' . $id . ' LIMIT 1';

		$result[ 'data' ] = mysql_fetch_assoc( full_query( $sql ) );
		return $result;
	}

	public function getVMsFromOnApp( $server_id ) {
		$server = $this->servers[ $server_id ];

		$class = $this->getOnAppObject( 'ONAPP_VirtualMachine', $server[ 'address' ], $server[ 'username' ], $server[ 'password' ] );
		$vms = $class->getList( $_GET[ 'onapp_user_id' ] );

		$this->product = get_service( $_GET[ 'product_id' ] );

		$limit = $this->limit;
		for( $i = 0; $i < $limit; $i++ ) {
			if( !isset( $vms[ $this->offset + $i ] ) ) {
				break;
			}

			$ips = array();
			$tmp = (array)$vms[ $this->offset + $i ];
			foreach( $tmp[ '_ip_addresses' ] as $ip ) {
				$ips[] = $ip->_address;
			}
			$tmp[ '_ip_addresses' ] = implode( '<br/>', $ips );
			$tmp[ 'resource_errors' ] = $this->checkResources( $tmp, $server );

			$results[ 'data' ][ ] = $tmp;
		}

		$results[ 'total' ] = $vms ? count( $vms ) : 0;
		$results[ 'pages' ] = ceil( $results[ 'total' ] / $this->limit );
		$results[ 'current' ] = $_GET[ 'page' ];

		if( $_GET[ 'page' ] > 1 ) {
			$results[ 'prev' ] = $_GET[ 'page' ] - 1;
		}

		if( ( $this->offset + $this->limit ) < $results[ 'total' ] ) {
			$results[ 'next' ] = $_GET[ 'page' ] + 1;
		}

		return $results;
	}

	public function getProductData( $id ) {
		$sql = 'SELECT hosting.*, product.`configoption1` AS server_id FROM `tblhosting` AS hosting LEFT JOIN `tblproducts` AS product ON product.`id` = hosting.`packageid`'
			   . ' WHERE hosting.`id` = ' . $id;

		$res = full_query( $sql );

		return mysql_fetch_assoc( $res );
	}

	private function checkResources( $vm, &$server ) {
		$product = $this->product;
		$errors = array();

		$class = $this->getOnAppObject( 'ONAPP_Disk', $server[ 'address' ], $server[ 'username' ], $server[ 'password' ] );
		$disks = $class->getList( $vm[ '_id' ] );

		//check disks
		foreach( $disks as $disk ) {
			if( $disk->_primary === 'true' ) {
				if( $disk->_disk_size != $product[ 'configoption11' ] ) {
					$errors[ 'Primary disk' ] = array( $disk->_disk_size => $product[ 'configoption11' ] );
				}
			}
			elseif( $disk->_is_swap === 'true' ){
				if( $disk->_disk_size != $product[ 'configoption9' ] ) {
					$errors[ 'SWAP disk' ] = array( $disk->_disk_size => $product[ 'configoption9' ] );
				}
			}
			else {
				continue;
			}
		}

		//check RAM
		if( $vm[ '_memory' ] != $product[ 'configoption3' ] ) {
			$errors[ 'RAM' ] = array( $vm[ '_memory' ] => $product[ 'configoption3' ] );
		}
		//check CPU Priority
		if( $vm[ '_cpu_shares' ] != $product[ 'configoption7' ] ) {
			$errors[ 'CPU Priority' ] = array( $vm[ '_cpu_shares' ] => $product[ 'configoption7' ] );
		}
		//check CPU Cores
		if( $vm[ '_cpus' ] != $product[ 'configoption5' ] ) {
			$errors[ 'CPU Cores' ] = array( $vm[ '_cpus' ] => $product[ 'configoption5' ] );
		}
		//check Template
		if( $vm[ '_template_id' ] != $product[ 'os' ] ) {
			$errors[ 'Template' ] = array( $vm[ '_template_label' ] => $product[ 'configoptions' ][ 33 ][ 'options' ][ $product[ 'os' ] ][ 'name' ] );
		}

		if( empty( $errors ) ) {
			return false;
		}
		else {
			return $errors;
		}
	}

	private function map( ) {
		insert_query( 'tblonappservices', array(
												  'service_id' => $_GET[ 'service_id' ],
												  'vm_id' => $_GET[ 'vm_id' ]
											 ) );

		$this->smarty->assign( 'msg', true );
		$this->smarty->assign( 'msg_text', $this->lang[ 'MapedSuccessfully' ] );
		$this->smarty->assign( 'msg_ok', true );

		$_GET[ 'action' ] = 'info';
	}

	private function getServerData( $server = null ) {
		if( is_null( $server ) ) {
			$sql = 'SELECT `id`, `name`, `ipaddress`, `hostname`, `username`, `password`'
				   . ' FROM `tblservers` WHERE `id` = ' . $_GET[ 'server_id' ];
			$res = full_query( $sql );
			$server = mysql_fetch_assoc( $res );
		}
		$server[ 'password' ] = decrypt( $server[ 'password' ] );

		if( !empty( $server[ 'ipaddress' ] ) ) {
			$server[ 'address' ] = $server[ 'ipaddress' ];
		}
		else {
			$server[ 'address' ] = $server[ 'hostname' ];
		}

		return $server;
	}

	private function getOnAppObject( $class, $server_ip, $username = null, $apikey = null ) {
		$obj = new $class;
		$obj->auth( $server_ip, $username, $apikey );

		return $obj;
	}
}