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
		if( !isset( $_GET[ 'server_id' ] ) || empty( $_GET[ 'server_id' ] ) ) {
			$server = current( $this->servers );
			$_GET[ 'server_id' ] = $server[ 'id' ];
		}
		$smarty->assign( 'server_id', $_GET[ 'server_id' ] );

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
			$tmp = $vms[ $this->offset + $i ];
			foreach( $tmp->_ip_addresses as $ip ) {
				$ips[] = $ip->_address;
			}
			$tmp->_ip_addresses = implode( '<br/>', $ips );
			$tmp->resource_errors = $this->checkResources( $tmp, $server );

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
		$disks = $class->getList( $vm->_id );

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
		if( $vm->_memory != $product[ 'configoption3' ] ) {
			$errors[ 'RAM' ] = array( $vm->_memory => $product[ 'configoption3' ] );
		}
		//check CPU Priority
		if( $vm->_cpu_shares != $product[ 'configoption7' ] ) {
			$errors[ 'CPU Priority' ] = array( $vm->_cpu_shares => $product[ 'configoption7' ] );
		}
		//check CPU Cores
		if( $vm->_cpus != $product[ 'configoption5' ] ) {
			$errors[ 'CPU Cores' ] = array( $vm->_cpus => $product[ 'configoption5' ] );
		}
		//check Template
		if( $vm->_template_id != $product[ 'os' ] ) {
			$errors[ 'Template' ] = array( $vm->_template_id => $product[ 'configoptions' ][ 33 ][ 'options' ][ $product[ 'os' ] ][ 'name' ] );
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
			)
		);

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

function get_service($service_id) {
    $select_service = "SELECT
        tblproducts.id as productid,
        tblhosting.id as id,
        userid,
        tblproducts.configoption1 as serverid,
        tblonappservices.vm_id as vmid,
        tblhosting.password,
        tblhosting.domain as domain,
        tblhosting.orderid as orderid,
        tblproducts.name as product,
        tblproducts.configoptionsupgrade,
        tblproducts.configoption1,
        tblproducts.configoption2,
        tblproducts.configoption3,
        tblproducts.configoption4,
        tblproducts.configoption5,
        tblproducts.configoption6,
        tblproducts.configoption7,
        tblproducts.configoption8,
        tblproducts.configoption9,
        tblproducts.configoption10,
        tblproducts.configoption11,
        tblproducts.configoption12,
        tblproducts.configoption13,
        tblproducts.configoption14,
        tblproducts.configoption15,
        tblproducts.configoption16,
        tblproducts.configoption17,
        tblproducts.configoption18,
        tblproducts.configoption19,
        tblproducts.configoption20,
        0 as additionalram,
        0 as additionalcpus,
        0 as additionalcpushares,
        0 as additionaldisksize,
        0 as additionalips,
        0 as additionalportspead
    FROM
        tblhosting
        LEFT JOIN tblproducts ON tblproducts.id = packageid
        LEFT JOIN tblonappservices ON service_id = tblhosting.id
    WHERE
        servertype = 'onapp'
        AND tblhosting.id = '$service_id'
        AND tblhosting.domainstatus = 'Active'";

    $service_rows = full_query($select_service);

    if ( ! $service_rows )
        return false;

    $service = mysql_fetch_assoc( $service_rows );

    $productid =  $service["productid"];

    $select_config ="
    SELECT
        optionssub.id,
        optionssub.optionname,
        sub.configid,
        tblproductconfigoptions.optionname as configoptionname,
        tblproductconfigoptions.optiontype,
        tblproductconfigoptions.qtymaximum AS max,
        tblproductconfigoptions.qtyminimum AS min,
        options.qty,
        optionssub.sortorder,
        options.optionid as active
    FROM
        tblhostingconfigoptions AS options
        LEFT JOIN tblproductconfigoptionssub AS sub
            ON options.configid = sub.configid
            AND optionid = sub.id
        LEFT JOIN tblproductconfigoptions
            ON tblproductconfigoptions.id = options.configid
        LEFT JOIN tblproductconfigoptionssub AS optionssub
            ON optionssub.configid = tblproductconfigoptions.id
    WHERE
        relid = '$service_id'
    ORDER BY optionssub.id ASC;";

    $select_config = "
    SELECT
        optionssub.id,
        optionssub.optionname,
        tblproductconfigoptions.id as configid,
        tblproductconfigoptions.optionname as configoptionname,
        tblproductconfigoptions.optiontype,
        tblproductconfigoptions.qtymaximum AS max,
        tblproductconfigoptions.qtyminimum AS min,
        options.qty,
        optionssub.sortorder,
        IF(options.optionid, options.optionid, optionssub.id) as active
    FROM
        tblproductconfiglinks
        LEFT JOIN tblproductconfigoptions
            ON tblproductconfigoptions.gid = tblproductconfiglinks.gid
        LEFT JOIN tblhostingconfigoptions AS options
            ON options.configid = tblproductconfigoptions.id AND relid = $service_id
        LEFT JOIN tblproductconfigoptionssub AS sub
            ON options.configid = sub.configid
            AND optionid = sub.id
        LEFT JOIN tblproductconfigoptionssub AS optionssub
            ON optionssub.configid = tblproductconfigoptions.id
    WHERE
        tblproductconfiglinks.pid = $productid
    ORDER BY optionssub.id ASC;";
    $config_rows = full_query($select_config);

    if ( ! $config_rows )
        return false;

    $onappconfigoptions = array(
        $service["configoption12"], // additional ram
        $service["configoption13"], // additional cpus
        $service["configoption14"], // additional cpu shares
        $service["configoption15"], // additional disk size
        $service["configoption16"], // additional ips
        $service["configoption19"], // operation system
        $service["configoption20"]  // port spead
    );

    $service["configoptions"] = array();

    while ( $row = mysql_fetch_assoc($config_rows) )
        if ( in_array($row["configid"], $onappconfigoptions ) ) {
            switch ( $row['optiontype'] ) {
                case '1': // Dropdown
                    $row['order'] = $row['sortorder'];
                    break;
                case '2': // Radio
                    $row['order'] = $row['sortorder'];
                    break;
                case '3': // Yes/No
                    $row['order'] = 0;
                    break;
                case '4': // Quantity
                    $row['order'] = $row['qty'] * $row['sortorder'];
                    break;
            };

            if(!isset($service["configoptions"][$row['configid']]))
                $service["configoptions"][$row['configid']] = array(
                    'name'       => $row['configoptionname'],
                    'active'     => $row['active'],
                    'optiontype' => $row['optiontype'],
                    'sortorder'  => $row['sortorder']
                );

            if ( $row["id"] == $row["active"]) {
                if ($service["configoption12"] == $row["configid"]) {
                    $service["additionalram"] = $row["order"];
                    $service["configoptions"][$row['configid']]['order'] = $service['configoption3'];
                    $service["configoptions"][$row['configid']]['prefix'] = 'MB';
                } elseif ($service["configoption13"] == $row["configid"]) {
                    $service["additionalcpus"] = $row["order"];
                    $service["configoptions"][$row['configid']]['order'] = $service['configoption5'];
                    $service["configoptions"][$row['configid']]['prefix'] = '';
                } elseif ($service["configoption14"] == $row["configid"]) {
                    $service["additionalcpushares"] = $row["order"];
                    $service["configoptions"][$row['configid']]['order'] = $service['configoption7'];
                    $service["configoptions"][$row['configid']]['prefix'] = '%';
                } elseif ($service["configoption15"] == $row["configid"]) {
                    $service["additionaldisksize"] = $row["order"];
                    $service["configoptions"][$row['configid']]['order'] = $service['configoption11'];
                    $service["configoptions"][$row['configid']]['prefix'] = 'GB';
                } elseif ($service["configoption16"] == $row["configid"]) {
                    $service["additionalips"] = $row["order"];
                    $service["configoptions"][$row['configid']]['order'] = $service['configoption18'];
                    $service["configoptions"][$row['configid']]['prefix'] = '';
                } elseif ($service["configoption20"] == $row["configid"]) {
                    $service["additionalportspead"] = $row["order"];
                    $service["configoptions"][$row['configid']]['order'] = $service['configoption8'];
                    $service["configoptions"][$row['configid']]['prefix'] = 'Mbps';
                } elseif ($service["configoption19"] == $row["configid"]) {
                    $service["os"] = $row["order"];
                };

                $service["configoptions"][$row['configid']]['value'] = $row['qty'];
            };

            $service["configoptions"][$row['configid']]['options'][$row['sortorder']] = array(
                'id'   => $row['id'],
                'name' => $row['optionname'],
                'max'  => $row['max'],
                'min'  => $row['min']
            );

        };
    return $service;
}
