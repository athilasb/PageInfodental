<?php
	$_title="Info Dental";
	$_p="ident_";

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

	
?>