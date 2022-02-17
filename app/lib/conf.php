<?php
	$_title="Info Dental";
	$_p="ident_";


	if(isset($_SERVER['HTTP_HOST'])) {
		if($_SERVER['HTTP_HOST']=="163.172.187.183:5000") {
			$_ENV['MYSQL_HOST']='51.159.74.70:23821';
			$_ENV['NAME']=$_ENV['MYSQL_DB']='studiodental';
			$_ENV['MYSQL_USER']="dentalinfo";
			$_ENV['MYSQL_PASS']="d3ntaL@inf0"; 
		} 
	}



	$_unidadesEspeciais['studiodental']=1;

	$_infodentalCompleto=isset($_unidadesEspeciais[$_ENV['NAME']])?1:0;


	$_usuariosTipos=array('admin'=>'Administrador',
						  'moderador'=>'Moderador');

	ksort($_usuariosTipos);

	$_pacienteSituacao=array('BI'=>'BI','EXCLUIDO'=>'EXCLUÍDO');

	$_pacienteEstadoCivil=array('SOLTEIRO'=>'SOLTEIRO','CASADO'=>'CASADO','SEPARADO'=>'SEPARADO','CASADO'=>'CASADO','VIÚVO'=>'VIÚVO');

	$_preferenciaContato=array('LIGACAO'=>'LIGAÇÃO',
								'WHATSAPP'=>'WHATSAPP');

	$_parametrosIndicacoesTipo=array('LISTA'=>'LISTA PERSONALIZADA','PACIENTE'=>'LISTA DE PACIENTES');

	$_formasDePagamentos=array('Dinheiro','Crédito','Débito','Desconto');

	$optTipoIndicacao=array('PACIENTE'=>'PACIENTE',
							'PROFISSIONAL'=>'CIRURGIÃO DENTISTA',
							'INDICACAO'=>'LISTA DE PERSONALIZADA');

	$_tipoBaixa=array('pagamento'=>'PAGAMENTO',
						'despesa'=>'DESPESA',
						'desconto'=>'DESCONTO');

	$_bancosEContasTipos=array('contacorrente'=>'CONTA CORRENTE',
								'dinheiro'=>'DINHEIRO');

	$_optUF = array(
		"AC" => "Acre",
		"AL" => "Alagoas",
		"AP" => "Amapá",
	    "AM" => "Amazonas",
		"BA" => "Bahia",
		"CE" => "Ceará",
		"DF" => "Distrito Federal",
		"ES" => "Espírito Santo",
		"GO" => "Goiás",
		"MA" => "Maranhão",
		"MT" => "Mato Grosso",					    
		"MS" => "Mato Grosso do Sul",
		"MG" => "Minas Gerais",					    
		"PA" =>"Pará",
		"PB" =>"Paraíba",
		"PR" => "Paraná",
		"PE" => "Pernambuco",
		"PI" => "Piauí",
		"RJ" => "Rio de Janeiro",
		"RN" => "Rio Grande do Norte",
		"RS" => "Rio Grande do Sul",
		"RO" => "Rondônia",
		"RR" => "Roraima",
		"SC" => "Santa Catarina",
		"SP" => "São Paulo",
		"SE" => "Sergipe",
		"TO" => "Tocantins"				    					    					   		    
	);

	$_tiposReceitas=array('interno'=>'USO INTERNO',
							'externo'=>'USO EXTERNO');

	$optAgendaDuracao=array(10,30,60,90,120);

	
	$_tiposFornecedores=array('FORNECEDOR'=>'FORNECEDOR',
								'LABORATORIO'=>'LABORATÓRIO',
								'CLINICA'=>'CLÍNICA RADIOLÓGICA');

	/*$_codigoBI=array(1=>'Paciente Novo',
						6=>'Paciente Antigo',
						2=>'Prospect',
						3=>'Tratamento',
						4=>'Contenção',
						5=>'Desativado',);*/

	$_codigoBI=array(1=>'Novo',
						2=>'Antigo',
						3=>'Alto Potencial',
						4=>'Em Tratamento',
						5=>'Em Acompanhamento',
						6=>'Baixo Potencial',
						7=>'Desativado');

	$_codigoBICores=array(1=>'#24e6c4',
							2=>'#0b4946',
							3=>'#fc7e09',
							4=>'#55d429',
							5=>'#0f8efe',
							6=>'#fd4b3e',
							7=>'#e7000e');

	$_selectSituacaoOptions=array('iniciar'=>array('titulo'=>'NÃO INICIADO','cor'=>'orange'),
											'iniciado'=>array('titulo'=>'EM TRATAMENTO','cor'=>'blue'),
											'finalizado'=>array('titulo'=>'FINALIZADO','cor'=>'green'),
											'cancelado'=>array('titulo'=>'CANCELADO','cor'=>'red'),
											//'cancelado'=>array('titulo'=>'CANCELADO');
										);

	//$selectSituacaoOptions='<select class="js-situacao">';
	$selectSituacaoOptions='';
	foreach($_selectSituacaoOptions as $key=>$value) {
		$selectSituacaoOptions.='<option value="'.$key.'" data-cor="'.$value['cor'].'">'.$value['titulo'].'</option>';
	}

	
?>