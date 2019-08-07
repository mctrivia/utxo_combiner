<?php
	//get inputs
	$rpc_pass=$_GET['rpc_pass'];
	$rpc_user=$_GET['rpc_user'];
	$rpc_port=$_GET['rpc_port'];
	
	//don't timeout
	ignore_user_abort(true);
	set_time_limit(0);

	//initialize
	require_once(__DIR__.'/includes/autoload.php');
	use Bitcoin\Bitcoin;
	
	//connected to wallets
	$digibyte = new Bitcoin($rpc_user,$rpc_pass,'localhost',$rpc_port);
	try {
		$inputs=$digibyte->listunspent(101,9999999,array(),false,array(
			"maximumAmount"=>	700
		));
		echo json_encode($inputs);
	} catch (\Exception $e) {
		echo json_encode(false);
	}