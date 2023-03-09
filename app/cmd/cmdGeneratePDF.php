<?php
	$endpoint="https://".$_SERVER['HTTP_HOST']."/services/api.php";

	$params = [];
	$params['token']='ee7a1554b556f657e8659a56d1a19c315684c39d';
	$params['method']='generatePDF';
	$params['infoConta']='studiodental';
	$params['id_evolucao']=4724;
	$params['enviaWhatsapp']=0;

		
	$curl = curl_init();

	curl_setopt_array($curl, [
	  CURLOPT_URL => $endpoint,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_POSTFIELDS => json_encode($params),
	  CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);
	if ($err) {
	  echo "cURL Error #:" . $err;
	} else {
	  echo $response;
	}

?>