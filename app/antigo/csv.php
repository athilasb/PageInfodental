<?php
	require_once 'lib/classes.php';
	require_once 'lib/conf.php';

	$sql = new Mysql();
	$file = file("contas.csv");
	$delimitador = detectDelimiter("contas.csv");
	array_shift($file);
	
	foreach($file as $v) {
		list($data,$diario,$lancamento,$conta,$parceiro,$referencia,$rotulo,$conta_analitica,$etiquetas,$impostos,$debito,$credito,$marcadores,$correspondencia,$vencimento)=explode($delimitador,strip_tags($v));

		$valor=0;
		$data_vencimento="0000-00-00";
		if(!empty($vencimento) and strpos($vencimento,"-")>0) {
			$data_vencimento=$vencimento;
		}
		if(!empty($debito)) {
			$valor="-".str_replace(",",".", $debito);
		}
		else if(!empty($credito)) {
			$valor=str_replace(",",".", $credito);
		}

		if(!empty($parceiro)){
			
			$sql->consult($_p."parametros_fornecedores","*","WHERE razao_social like '".utf8_decode($parceiro)."'");
			if($sql->rows==0) {
				$sql->add($_p."parametros_fornecedores","data=now(),tipo='FORNECEDOR',tipo_pessoa='PJ',razao_social='".utf8_decode($parceiro)."'");
				$id_fornecedor = $sql->ulid;
				$data="data=now(),data_emissao='".$data."',data_vencimento='".$data_vencimento."',credor_pagante='fornecedor',id_fornecedor='".$id_fornecedor."',valor='".$valor."'";
				//echo $data."<br>";
				$sql->add($_p."financeiro_fluxo", $data);
			} else {
				$x=mysqli_fetch_object($sql->mysqry);
				$data="data=now(),data_emissao='".$data."',data_vencimento='".$data_vencimento."',credor_pagante='fornecedor',id_fornecedor='".$x->id."',valor='".$valor."'";
				//echo $data."<br>";
				$sql->add($_p."financeiro_fluxo", $data);
			}
		}
	}