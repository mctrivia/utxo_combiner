<?php
	//get inputs
	$rpc_pass=$_POST['rpc_pass'];
	$rpc_user=$_POST['rpc_user'];
	$rpc_port=$_POST['rpc_port'];
	$max_combine=$_POST['max_combine'];
	$wallet_pass=$_POST['wallet_pass'];
	$donate_percent=$_POST['donate']/100;
	
	//don't timeout
	ignore_user_abort(true);
	set_time_limit(0);

	//initialize
	require_once(__DIR__.'/includes/autoload.php');
	use Bitcoin\Bitcoin;
	
	//connected to wallets
	$digibyte = new Bitcoin($rpc_user,$rpc_pass,'localhost',$rpc_port);
	
	//get list of utxos
	$inputs=$digibyte->listunspent(101,9999999,array(),false,array(
		"maximumAmount"=>	$max_combine
	));	
	$count=count($inputs);
		
	if ($count>60) {
		echo '<h1>Processing Done</h1>List of txids:<br><table border="1"><tr><td>TXID</td><td>Balance</td></tr>';
		for ($i=0;$i<$count;$i+=60) {
			//get list of inputs to use for blocks
			$source=array_slice($inputs,$i,60);
			if (count($source)<40) break;
			
			//get total to send minus fee
			$total=-0.0001;
			foreach ($source as $value) {
				$total+=$value["amount"];			
			}
			
			//get new reserve address
			$address=$digibyte->getnewaddress('mct_combined');
			
			//see if donation
			$donate=floor($total*$donate_percent*100000000)/100000000;
			if ($donate<0.001) {
				$donate=0;
			}
			$total-=$donate;
			$to=array();
			$to[$address]=$total;
			if ($donate>0) $to['DTWt3uTBdipxtzuegiMoyo3xjqJJKuVUVe']=$donate;			
			
			//create transaction
			$hex=$digibyte->createrawtransaction($source,$to);
			
			//sign transaction
			$digibyte->walletpassphrase($wallet_pass,1000);
			$data=$digibyte->signrawtransactionwithwallet($hex);
			
			//send if there was no errors
			if (!isset($data['error'])) {
				$txid=$digibyte->sendrawtransaction($data['hex'],false);
				echo "<tr><td>$txid</td><td>$total</td></tr>";
			}		
		}

		echo '</table>';
	}