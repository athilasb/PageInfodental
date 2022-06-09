<?php
	ini_set("max_execution_time","30000");
	require_once("../../lib/conf.php");
	require_once("../../lib/classes.php");


	echo "Desativado! ";die();

	$sql = new Mysql();

	$agendamentosCSV = file("agendamentos.csv");
	$pacientesCSV = file("pacientes2.csv");

	$usr=(object)array('id'=>0);


	//Registro;Data do Cadastro;Nome;Telefone;Celular;Telefone Comercial;E-mail;Data de Nascimento;Endereço;Número;Complemento;Bairro;Cidade;Estado;Cep;CPF;RG;Sexo;Especialidades;Titular;Convênio;Plano;Número do Plano;Validade do Plano;Responsável;Nome do Pai;Nome da Mãe 

	$_pacientes=array();
	foreach($pacientesCSV as $p) {

		list($Registro,$Data_do_Cadastro,$Nome,$Telefone,$Celular,$Telefone_Comercial,$Email,$Data_de_Nascimento,$Endereco,$Numero,$Complemento,$Bairro,$Cidade,$Estado,$Cep,$CPF,$RG,$Sexo,$Especialidades,$Titular,$Convenio,$Plano,$Número_do_Plano,$Validade_do_Plano,$Responsavel,$Nome_do_Pai,$Nome_da_Mae)=explode(";",$p);

		
		if(strlen($Data_do_Cadastro)==10) {


			// Endereco
				if(!empty($Numero)) $Endereco.=", $Numero";
				if(!empty($Complemento)) $Endereco.=", $Complemento";
				if(!empty($Bairro)) $Endereco.=", $Bairro";
				if(!empty($Cidade)) $Endereco.=", $Cidade";
				if(!empty($Estado)) $Endereco.=", $Estado";

			// Data de Nascimento
				$dn='0000-00-00';
				if(!empty($Data_de_Nascimento)) {

					if(strlen($Data_de_Nascimento)<10) {
						$dnAux=explode("/",$Data_de_Nascimento);
						if(count($dnAux)==3) {
							$dn=($dnAux[2]>22?"19":"20").$dnAux[2]."-";
							$dn.=$dnAux[0]<=9?"0".$dnAux[0]:$dnAux[0];
							$dn.="-";
							$dn.=$dnAux[1]<=9?"0".$dnAux[1]:$dnAux[1];
							
						}
					} else {
						$dn=invDate($Data_de_Nascimento);
					}
				}

				
			
			// Celular
				if(empty($Celular)) {
					$Celular=$Telefone;
				}

				if(empty($Celular)) {
					$Celular=$Telefone_Comercial;
				}

			$index=strtolowerWLIB(str_replace(" ","",tirarAcentos($Nome))).telefone($Celular);

			//echo $index."->".$Nome."->".$Telefone." -> $Data_do_Cadastro $Registro<br />";
			if(isset($_pacientes[$index])) echo "=".$index." $Nome<BR>";
			$_pacientes[$index]=array('id_paciente'=>$Registro,
											'data'=>strlen($Data_do_Cadastro)==10?invDate($Data_do_Cadastro):'0000-00-00',
											'nome'=>$Nome,
											'telefone'=>telefone($Telefone),
											'celular'=>telefone($Celular),
											'email'=>$Email,
											'dn'=>($dn),
											'endereco'=>$Endereco,
											'numero'=>$Numero,
											'complemento'=>$Complemento,
											'cep'=>$Cep,
											'cpf'=>$CPF,
											'rg'=>$RG,
											'sexo'=>$Sexo=="Masculino"?"M":"F",
											'responsavel'=>$Responsavel,
											'pai'=>$Nome_do_Pai,
											'mae'=>$Nome_da_Mae,
											'agenda'=>array());
		} else  {
			//echo $p."<BR>";
		}
		

	}

	echo "tem certeza que deseja ativas?";die();




	$agendamentosCSV2=array();
	$cont=0;

	// recupera as anotacoes que estao com quebra de linhas
		foreach($agendamentosCSV as $a) {
			$a=trim($a);
			if(empty($a)) continue;

			$aux = explode(";",$a);
			//echo (count($aux)!=13?1:0)." >";continue;
			if(count($aux)==13) {
				$agendamentosCSV2[$cont]=$a;
			} else {
				$ind=$cont;

				do {
					$ind--;
				} while(!isset($agendamentosCSV2[$ind]));

				$agendamentosCSV2[$ind].=$a;
			}

			$cont++;
		}


	// capta cadeiras e profissionais
		$cadeiras=$profissionais=array();
		foreach($agendamentosCSV2 as $a) {
			if(strlen($a)<47 or substr($a,0,4)=="Data") continue;
			
			//echo count($aux)."<BR>";continue;
			list($Data,$Dentista,$Descricao,$Numero_do_Cadastro,$Telefone,$Convenio,$Motivo,$Email,$Duracao,$Compromisso_pessoal,$Situacao,$Cadeira,$Anotacoes)=explode(";",$a);

			if(!empty($Cadeira) and !isset($cadeiras[$Cadeira])) {
				$cadeiras[$Cadeira]=$Cadeira;
				//echo $cadeira."<BR>";
			}

			if(!empty($Dentista) and !isset($profissionais[$Dentista])) {
				$profissionais[$Dentista]=$Dentista;
			}
		}

	// persiste cadeiras
		$cadeirasSistema=array();
		foreach($cadeiras as $x) {

			$x=trim(utf8_decode($x));

			$sql->consult($_p."parametros_cadeiras","*","where titulo='".trim(addslashes($x))."'");
			if($sql->rows) {
				$c=mysqli_fetch_object($sql->mysqry);
				$idCadeira=$c->id;
			} else {
				$sql->add($_p."parametros_cadeiras","titulo='".trim(addslashes($x))."'");
				$idCadeira=$sql->ulid;
			}

			$cadeirasSistema[$x]=$idCadeira;


		}


	// persiste profissionais
		$profissionaisSistema=array();
		foreach($profissionais as $x) {

			$x=trim(utf8_decode($x));

			$sql->consult($_p."colaboradores","*","where nome='".trim(addslashes($x))."'");
			if($sql->rows) {
				$c=mysqli_fetch_object($sql->mysqry);
				$idProf=$c->id;

				$sql->update($_p."colaboradores","check_agendamento=1","where id=$c->id");
			} else {
				$sql->add($_p."colaboradores","data=now(),nome='".trim(addslashes($x))."'");
				$idProf=$sql->ulid;
			}

			$profissionaisSistema[$x]=$idProf;


		}


	foreach($agendamentosCSV2 as $a) {
			if(strlen($a)<47 or substr($a,0,4)=="Data") continue;
			
			//echo count($aux)."<BR>";continue;
			list($Data,$Dentista,$Descricao,$Numero_do_Cadastro,$Telefone,$Convenio,$Motivo,$Email,$Duracao,$Compromisso_pessoal,$Situacao,$Cadeira,$Anotacoes)=explode(";",$a);

			if($Compromisso_pessoal=="Sim") continue;

			// define telefone
				if(strrpos($Telefone,",")) {
					$aux=explode(",",$Telefone);
					$Telefone=$aux[0];
				}

			// define status
				$idStatus="1";
				if($Situacao=="Confirmar") $idStatus=1;
				else if($Situacao=="Atendido") $idStatus=5;
				else if($Situacao=="Desmarcou") $idStatus=4;
				else if($Situacao=="Faltou") $idStatus=3;
				else if($Situacao=="Confirmado") $idStatus=2;
				else if($Situacao=="Pre Confirmado") $idStatus=1;
				else if($Situacao=="Em Atendimento") $idStatus=6;
				else if($Situacao=="Cliente Chegou") $idStatus=7;
				else if($Situacao=="Covid 19") $idStatus=4;

				$dtAux=explode("-",$Data);
				$Data=invDate(trim($dtAux[0]))." ".trim($dtAux[1]).":00";

			// define cadeira
				$idCadeira=isset($cadeirasSistema[utf8_decode($Cadeira)])?$cadeirasSistema[utf8_decode($Cadeira)]:0;
				//

			// define duracao	
				$duracao=numero($Duracao);
				$duracao = (isset($Duracao) and is_numeric($Duracao))?$Duracao:30;

			// define profissional
				$profissionais='';
				if(!empty($Dentista) and isset($profissionaisSistema[$Dentista])) $profissionais=",".$profissionaisSistema[$Dentista].",";


			$index=strtolowerWLIB(str_replace(" ","",tirarAcentos($Descricao))).telefone($Telefone);
			
			// se nao encontrou na lista de pacientes
				if(!isset($_pacientes[$index])) {

					if(strtotime($Data)>=strtotime(date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." - 5 month")))) {
						//echo $Data." ($index) $Descricao<br>";continue;

						$novoPaciente=array('id_paciente'=>$Registro,
													'data'=>$Data,
													'nome'=>$Descricao,
													'telefone'=>telefone($Telefone),
													'celular'=>'',
													'email'=>$Email,
													'dn'=>'',
													'endereco'=>'',
													'numero'=>'',
													'complemento'=>'',
													'cep'=>'',
													'cpf'=>'',
													'rg'=>'',
													'sexo'=>'',
													'responsavel'=>'',
													'pai'=>'',
													'mae'=>'',
													'agenda'=>array());
					

						$_pacientes[$index]=$novoPaciente;
						
					} else continue;
				} 
			
			$_pacientes[$index]['agenda'][]=array('data'=>$Data,
													'profissionais'=>$profissionais,
													'duracao'=>$duracao,
													'id_paciente'=>$Numero_do_Cadastro,
													'id_status'=>$idStatus,
													'id_cadeira'=>$idCadeira,
													'obs'=>$Anotacoes);
			
	}

	foreach($_pacientes as $x) {

		# Cadastra Paciente
			$idPaciente=$x['id_paciente'];
			$vSQLPaciente="data='".$x['data']."',
							nome='".addslashes(utf8_decode($x['nome']))."',
							telefone1='".telefone($x['celular'])."',
							telefone2='".telefone($x['telefone'])."',
							sexo='".addslashes($x['sexo'])."',
							cpf='".addslashes($x['cpf'])."',
							rg='".addslashes($x['rg'])."',
							data_nascimento='".($x['dn'])."',
							endereco='".addslashes(utf8_decode($x['endereco']))."'";
			

			/*$where="where nome=''";
			$sql->consult($_p."pacientes","id",$where);
			if($sql->rows) {
				$y=mysqli_fetch_object($sql->mysqry);
				$id_paciente=$y->id;
				$sql->update($_p."pacientes",$vSQLPaciente,"where id=$id_paciente");
			} else {*/
				$sql->add($_p."pacientes",$vSQLPaciente);
				$id_paciente=$sql->ulid;
			//}
			

		# Cadastra Agendamentos
			if(isset($x['agenda'])) {
				foreach($x['agenda'] as $a) {
					$vSQL="id_paciente='".$id_paciente."',
							profissionais='".$a['profissionais']."',
							id_cadeira='".$a['id_cadeira']."',
							id_unidade=1,
							id_status='".$a['id_status']."',
							agenda_data='".$a['data']."',
							agenda_data_final='".date('Y-m-d H:i:s',strtotime($a['data']." + $duracao minutes"))."',
							agenda_duracao='".$duracao."',
							obs='".addslashes(utf8_decode($a['obs']))."'
							";



					if(isset($_POST['obs'])) $vSQL.=",obs='".addslashes(utf8_decode($_POST['obs']))."'";


					$vSQL.=",data=now(),id_usuario=$usr->id";

					$sql->add($_p."agenda",$vSQL);


					$id_agenda=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$id_agenda."'");

					$idPaciente=((isset($_POST['id_paciente']) && is_numeric($_POST['id_paciente']))?$_POST['id_paciente']:'');
					$vSQLHistorico="data=now(),
						id_usuario=$usr->id,
						evento='agendaNovo',
						id_paciente=".$id_paciente.",
						id_agenda=$id_agenda,
						id_status_antigo=0,
						id_status_novo=".$a['id_status'].",
						descricao=''";


					$sql->add($_p."pacientes_historico",$vSQLHistorico);


				} 
			}

	}


?>