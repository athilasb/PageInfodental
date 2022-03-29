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

	$_cloudinaryURL='https://res.cloudinary.com/infodental/image/upload/';
	$_cloudinaryPath=$_ENV['S3_BUCKET']."/".$_ENV['NAME']."/";
	$_cloudinaryUploadPresent="ir9b4eem";
	$_cloudinaryCloudName="infodental";
	$_cloudinaryText=array('pt'=>array('local'=>array('browse'=>'Carregar',
														'main_title'=>'Enviar',
														'dd_title_single'=>'Carregue e solte a imagem aqui',
														'dd_title_multi'=>'Carregue e solte as imagens aqui',
														'drop_title_single'=>'Solte a imagem para carrega',
														'drop_title_multiple'=>'Solte as imagems para carregar',
														'upload-queue-title'=>'Enviando'
														)
										));

	$_usuariosTipos=array('admin'=>'Administrador',
						  'moderador'=>'Moderador');

	ksort($_usuariosTipos);

	$_googleMapsKey="AIzaSyDi3GasDqpa_yfvnd9303Dz_shp5XSLqAY";

	$_dias=explode(",","Domingo,Segunda-Feira,Terça-Feira,Quarta-Feira,Quinta-Feira,Sexta-Feira,Sábado");
	
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

	$_regimes = array(
		'CLT' => 'CLT',
		'ESTAGIO' => 'Estágio',
		'MEI' => 'MEI',
		'AUTONOMO' => 'Autônomo',
		'PROLABORE' => 'Prolabore'
	);
	ksort($_regimes);
	
	$_tipoCRO = array(
		'CD'  => 'CD',
		'ASB' => 'ASB',
		'TSB' => 'TSB',
		'TPD' => 'TPD',
		'APD' => 'APD'
	);

	$_cargaHoraria = array(
		1 => '08:00 - 18:00',
		2 => '17:00 - 23:50'
	);

	$_cargos = array(
		'ASB' => 'ASB',
		'TSB' => 'TSB',
		'TPD' => 'TPD',
		'APD' => 'APD',
		'CD'  => 'Cirurgião Dentista',
		'AF'  => 'Administrador Financeiro',
		'R'   => 'Recepcionista', 
		'GR'  => 'Gerente Geral'
	);

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

	$_bancos=array(1=>'Itaú',
					2=>'Bradesco',
					3=>'Sicoob',
					4=>'Nubank');

	asort($_bancos);

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

	$_pixTipos=array('cpfcnpj'=>'CPF/CNPJ',
						'telefone'=>'Telefone',
						'email'=>'E-mail',
						'chave'=>'Chave Aletória'
					);

	
?>