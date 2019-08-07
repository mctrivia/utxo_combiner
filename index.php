<html>
	<head>
	<style>
		.flex-container {
			display: flex;
			width: 100%;
			padding: 5px;
		}
		.fill-width {
			flex: 1;
		}
		.code {
			padding: 10px;
			
		}
		.pages {
			display: none;
		}
		
		#tx_table{
			display: 			table;
			width: 				100%;
		}
		.table_row{
			display: 			table-row;	
		}
		.table_header{
			display: 			table-cell;
			border: 			1px solid #999999;
			background-color: 	#888888;	
		}
		.table_cell{
			display: 			table-cell;
			border: 			1px solid #999999;
			background-color: 	#FFFFFF;
		}
	</style>
	
	</head>
	<body>
	
<div id="page1">
	<h1>DigiByte Core Wallet UTXO Combiner</h1>
	This app will go through your wallet and combine every 60 utxo with at least 101 confirms into new addresses.<br>
	It runs entirely on your own machine comunicating only with your wallet.<br>
	Source codes are available at <a href="https://github.com/mctrivia/utxo_combiner">Github</a> and can be run uncompiled if you install PHP.<br>
	Please consider donating a percentage of funds to help continued development of tools<br>

	<h3>Wallet Access Info:</h3>
	<div class="flex-container">
		<label>RPC User:&nbsp;</label><input type="text" id="rpc_user" value="rpc_user" class="fill-width">
	</div>
	<div class="flex-container">
		<label>RPC Password:&nbsp;</label><input type="text" id="rpc_pass" value="rpc_pass" class="fill-width">
	</div>
	<div class="flex-container">
		<label>RPC Port:&nbsp;</label><input type="text" id="rpc_port" value="14022" class="fill-width">
	</div>
	<input type="button" id="rpc_connect" value="Log in to wallet"><br>

	<b>To setup your core wallet</b><br>
	Press Settings->Options->Open Configuration File<br>
	Add the following lines:<br>
	<div class="code">
		whitelist=127.0.0.1<br>
		rpcallowip=127.0.0.1<br>
		rpcbind=127.0.0.1<br>
		rpcuser=rpc_user<br>
		rpcpassword=rpc_pass<br>
		rpcport=14022<br>
	</div>
	Then save and reboot your wallet.
</div>
<div id="page2" class="pages">
	<h1>DigiByte Core Wallet UTXO Combiner</h1>
	<div class="flex-container">
		<label>Min UTXO:&nbsp;</label><input type="number" id="min_utxo" value="0" class="fill-width">
	</div>
	<div class="flex-container">
		<label>Max UTXO:&nbsp;</label><input type="number" id="max_utxo" value="700" class="fill-width">
	</div>
	<div class="flex-container">
		<label>Number of TX Needed:&nbsp;</label><input type="number" id="tx_needed" class="fill-width" disabled>
	</div>
	<div class="flex-container">
		<label>Funds Combined:&nbsp;</label><input type="number" id="funds" class="fill-width" disabled>
	</div>
	<div class="flex-container">
		<label>Donation:&nbsp;</label><input type="number" id="donation" class="fill-width">
	</div>
	<div class="flex-container">
		<label>Wallet password:&nbsp;</label><input type="password" id="password" class="fill-width">
	</div>
	<input type="button" id="send" value="Send Funds">
</div>
<div id="page3" class="pages">
	<h1>DigiByte Core Wallet UTXO Combiner</h1>
	<div id="tx_table"></div>
</div>


	<script src="xmr.js"></script>
	<script>
		var rpc_user,rpc_pass,rpc_port;
		var utxos_use;
		
		//process UTXOs
		var processUTXOs=function() {
			//get user inputs
			var a=Math.min(700,parseFloat(document.getElementById("max_utxo").value));
			var b=Math.max(0,parseFloat(document.getElementById("min_utxo").value));
			var minInput=Math.min(a,b);
			var maxInput=Math.max(a,b);
			document.getElementById("max_utxo").value=maxInput;
			document.getElementById("min_utxo").value=minInput;
		
			//determine which utxos to use
			var use=[];
			for (var utxo of utxos) {
				if (utxo['amount']>maxInput) continue;
				if (utxo['amount']<minInput) continue;
				use.push(utxo);				
			}
			console.log(use);
			
			//get usableFunds and transaction count
			var txCount=Math.round(use.length/60);
			var usableFunds=0-txCount*0.0001;
			utxos_use=[];
			for (var i=0;i<txCount;i++) {
				utxos_use[i]={
					"usable":-0.0001,
					"utxos":[]
				};
				for (var ii=0;ii<60;ii++) {
					if (i*60+ii<use.length) {
						utxos_use[i]['utxos'].push(use[i*60+ii]);
						utxos_use[i]['usable']+=use[i*60+ii]['amount'];
						usableFunds+=use[i*60+ii]['amount'];
					}
				}
			}
			document.getElementById("tx_needed").value=txCount;
			document.getElementById("funds").value=usableFunds.toFixed(8);
			
			//set recomended donation
			var donation=Math.max(0.0007,usableFunds/1000);
			if (donation>usableFunds) donation=0;
			document.getElementById("donation").value=donation.toFixed(8);
		}
		document.getElementById('min_utxo').addEventListener('change', processUTXOs);
		document.getElementById('max_utxo').addEventListener('change', processUTXOs);
		
		
	
		//try to connect to wallet
		document.getElementById("rpc_connect").addEventListener('click', function() {
			rpc_user=encodeURIComponent(document.getElementById("rpc_user").value);
			rpc_pass=encodeURIComponent(document.getElementById("rpc_pass").value);
			rpc_port=encodeURIComponent(document.getElementById("rpc_port").value);
			xmr.getJSON('get_utxos.php?rpc_user='+rpc_user+'&rpc_pass='+rpc_pass+'&rpc_port='+rpc_port).then(function(data) {
				if (data==false) {
					alert("Could not connect to wallet");
				} else {
					utxos=data;
					processUTXOs();
					
					//show next page
					document.getElementById("page1").style.display="none";
					document.getElementById("page2").style.display="block";
				}
			});
		});
		
		//send funds
		document.getElementById("send").addEventListener('click', function() {
			var donation=parseFloat(document.getElementById("donation").value);
			var password=document.getElementById("password").value;
			console.log(utxos_use);
			xmr.postJSON('send_utxos.php?rpc_user='+rpc_user+'&rpc_pass='+rpc_pass+'&rpc_port='+rpc_port,{
				"txs": utxos_use,
				"donation":	donation,
				"password": password
			}).then(function(data) {
				if (data.length==0) {
					alert("Send failed");
				} else {
					//make tx_table
					var html='<div class="table_row"><div class="table_header">TXID</div><div class="table_header">DGB Received</div></div>';
					for (var txid of data) {
						html+='<div class="table_row"><div class="table_cell">'+txid[0]+'</div><div class="table_cell">'+txid[1].toFixed(8)+' DGB</div></div>'
					}
					document.getElementById("tx_table").innerHTML=html;
					
					//show table
					document.getElementById("page2").style.display="none";
					document.getElementById("page3").style.display="block";
				}
			});
		});
	</script>
	</body>
</html>