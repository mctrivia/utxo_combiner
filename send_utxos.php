<?php
	//get inputs
	$inputs=json_decode(file_get_contents('php://input'),true);
	
	$rpc_pass=$_GET['rpc_pass'];
	$rpc_user=$_GET['rpc_user'];
	$rpc_port=$_GET['rpc_port'];
	$donation=$inputs['donation'];
	$txs=$inputs['txs'];
	$password=$inputs['password'];
	
	
	//don't timeout
	ignore_user_abort(true);
	set_time_limit(0);


	//initialize
	require_once(__DIR__.'/includes/autoload.php');
	use Bitcoin\Bitcoin;
	
	//connected to wallets
	$digibyte = new Bitcoin($rpc_user,$rpc_pass,'localhost',$rpc_port);
	$txids=[];
	foreach ($txs as $txData) {
		try {
			//get tx data
			$usable=$txData['usable'];
			$inputs=$txData['utxos'];
			$curDonation=0;
			
			//handle donation
			if (($donation>0) && ($usable>0.0014)) {
				//try to send as much of donation as possible
				$curDonation=min($usable,$donation);
				$donation-=$curDonation;
				$usable-=$curDonation;
				
				//make sure usable is not an impossible value to send
				if (($usable<0.0007)&&($usable!=0)) {
					$curDonation-=0.0007-$usable;
					$usable=0.0007;
				}
			}
			
			//generate recipients list
			$recipients=array();
			if ($curDonation>0) $recipients['DTWt3uTBdipxtzuegiMoyo3xjqJJKuVUVe']=floor($curDonation*100000000)/100000000;
			if ($usable>0) {
				$address=$digibyte->getnewaddress('utxo_combiner');
				$recipients[$address]=floor($usable*100000000)/100000000;
			}
			
			//create transaction
			$hex=$digibyte->createrawtransaction($inputs,$recipients);
			
			//sign transaction
			$digibyte->walletpassphrase($password,1000);
			$data=$digibyte->signrawtransactionwithwallet($hex);
			
			//if no errors send and record
			if (!isset($data["errors"])) {
				$txid=$digibyte->sendrawtransaction($data['hex'],false);
				$txids[]=array($txid,$usable);
			} else {
			}
		} catch (\Exception $e) {
		}
		
	}
	
	//return txids
	echo json_encode($txids);