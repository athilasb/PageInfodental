<?php
	require_once("../lib/classes.php");
	$sql = new Mysql();
	$_p="ident_";
	

	// para executar precisa incluir get ?cmd=1
	if(!isset($_GET['cmd'])) {
		die('Processo perigoso.. consulte Luciano antes de rodar');
	}

	// cuidado ao executar.. pois ele
	$instancia='';
	$sql->consult("infodentalADM.infod_contas","*","where instancia='agenda'");
	if($sql->rows) {
		$instancia=mysqli_fetch_object($sql->mysqry);
	} 

	if(is_object($instancia)) {


		$_p=$instancia->instancia.".".$_p;

		$sql->add("infodentalADM.infod_contas_migracoes","data=now(),instancia='".$instancia->instancia."',sistema='clinicorp'");
		$id_migracao=$sql->ulid;

		if(empty($erro)) {



				$agendamentosCSV = file("csv/Agenda.csv");
				$pacientesCSV = file("csv/Cliente.csv");



				$usr=(object)array('id'=>0);

				// apaga pacientes e agendamentos
				$sql->del($_p."pacientes","");
				$sql->del($_p."pacientes_historico","");
				$sql->del($_p."agenda","");

		
				// pacientes
					$_pacientes=array();
					foreach($pacientesCSV as $p) {

						list($dataCastro,$nome,$telefone,$celular,$telefone_comercial,$email,$data_nascimento,$cpf,$rg,$sexo)=explode(";",$p);

						if($nome=="Nome") continue;


						$data=date('Y-m-d H:i',strtotime($dataCastro));

						
						$telefone=telefone($telefone);
						$celular=telefone($celular);
						$telefone_comercial=telefone($telefone_comercial);


						//echo $index."->".$Nome."->".$Telefone." -> $Data_do_Cadastro $Registro<br />";
						//if(isset($_pacientes[$index])) echo "=".$index." $Nome<BR>";
						$index=trim(str_replace(" ","_",$nome));
						$_pacientes[$index]=array('data'=>$data,
													'nome'=>($nome),
													'celular'=>$celular,
													'telefone'=>$telefone,
													'email'=>($email),
													'dn'=>substr($data_nascimento,0,10),
													'rg'=>$rg,
													'cpf'=>$cpf,
													'sexo'=>$sexo,
													'agenda'=>array());
					
						
					}

				// profissionais
					$_profissionais=array();
					foreach($agendamentosCSV as $p) {
						list(,$Name,)=explode(";",$p);

						if($Name!="Dentista") {

							

							$vSQLProfissional="nome='".addslashes($Name)."'";

							$where="WHERE nome='".addslashes($Name)."'";

							if(isset($_profissionais[$Name])) continue;
							$sql->consult($_p."colaboradores","*",$where);
							if($sql->rows) {
								$x=mysqli_fetch_object($sql->mysqry);

								$sql->update($_p."colaboradores",$vSQLProfissional,"where id=$x->id");
								$id_profissional=$x->id;
							} else {
								$sql->add($_p."colaboradores",$vSQLProfissional.",data=now()");
								$id_profissional=$sql->ulid;
							}

							$_profissionais[$Name]=array('id_profissional'=>$id_profissional,
														'nome'=>$Name);

						}
	
					}

				// cadeira
					$sql->consult($_p."parametros_cadeiras","*", "where lixo=0");
				
					if($sql->rows) {
						$c=mysqli_fetch_object($sql->mysqry);
						$id_cadeira=$c->id;
					} else {
						$sql->add($_p."parametros_cadeiras","titulo='CADEIRA 1'");
						$id_cadeira=$sql->ulid;
					}


				$_agendamentos=array();
				foreach($agendamentosCSV as $a) {
					
				
					//CreateDate;date;Dentist_PersonId;fromTime;id;MobilePhone;Notes;Patient_PersonId;PatientName;StatusId;toTime
					list($Data,$Dentista,$Descricao,$Telefone,$Duracao,$Compromisso_pessoal,$Cadeira,$Anotacoes)=explode(";",nl2br($a));
					
					if($Dentista=="Dentista") continue;

					if(isset($_profissionais[$Dentista])) $id_profissional=$_profissionais[$Dentista]['id_profissional'];
					else $id_profissional=0;
					//echo $Dentista."-".$id_profissional;die();

					$Data = substr($Data,0,19);
					//echo $Data;die();

					//$data=date('Y-m-d H:i',strtotime($CreateDate));
					$agenda_data=$Data;
					$agenda_data_final=date('Y-m-d H:i',strtotime($agenda_data." + 60 minute"));

					//echo $Duracao.": ".$agenda_data." a ".$agenda_data_final;die();


					//$index=strtolowerWLIB(str_replace(" ","",tirarAcentos($PatientName))).telefone($MobilePhone);
					$index=str_replace(" ","_",$Descricao);

					/*
					4764335248769020 - atrasado -> confirmado (2)
					5073308082503680 - confirmado (2)
					5765164147671040 - faltou (3)
					6175685258772480 - atendido (5)
					6351196329082880 - em atendimento (6)
					*/

					$idStatus=1; // a confirmar
					if(strtotime($agenda_data)<strtotime(date('Y-m-d H:i:s'))) $idStatus=5; // atendido

					if(!isset($_pacientes[$index]) and strtotime($agenda_data)>strtotime(date('Y-m-d H:i'))) {
				
						echo "nao achou paciente com agendamento futuro: $index $PatientName $agenda_data<BR>";
						continue;
					}



						$_pacientes[$index]['agenda'][]=array('data'=>$data,
																'agenda_data'=>$agenda_data,
																'profissionais'=>$id_profissional,
																'duracao'=>$Duracao,
																'id_status'=>$idStatus,
																'id_cadeira'=>$id_cadeira,
																'obs'=>$Anotacoes);
						
					


					
					
				}

				//echo json_encode($_pacientes);die();


				foreach($_pacientes as $x) {

					# Cadastra Paciente
						//$idPaciente=$x['id_paciente'];
						$vSQLPaciente="data='".$x['data']."',
										nome='".addslashes(utf8_decode($x['nome']))."',
										telefone1='".telefone($x['celular'])."',
										sexo='".addslashes($x['sexo'])."',
										cpf='".addslashes($x['cpf'])."',
										rg='".addslashes($x['rg'])."',
										data_nascimento='".($x['dn'])."'";

						
						$sql->add($_p."pacientes",$vSQLPaciente);
						$id_paciente=$sql->ulid;

						
						

					# Cadastra Agendamentos
						if(isset($x['agenda'])) {
							foreach($x['agenda'] as $a) {


								$duracao=$a['duracao']; 
								$vSQL="id_paciente='".$id_paciente."',
										profissionais=',".$a['profissionais'].",',
										id_cadeira='".$a['id_cadeira']."',
										id_unidade=1,
										id_status='".$a['id_status']."',
										data='".$a['data']."',
										agenda_data='".$a['agenda_data']."',
										agenda_data_original='".$a['agenda_data']."',
										agenda_data_final='".date('Y-m-d H:i:s',strtotime($a['agenda_data']." + $duracao minutes"))."',
										agenda_duracao='".$duracao."',
										obs='".addslashes(utf8_decode($a['obs']))."'
										";


								if(isset($_POST['obs'])) $vSQL.=",obs='".addslashes(utf8_decode($_POST['obs']))."'";


								$vSQL.=",id_usuario=$usr->id";


								$sql->add($_p."agenda",$vSQL);


								$id_agenda=$sql->ulid;
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."agenda',id_reg='".$id_agenda."'");

								//$idPaciente=((isset($_POST['id_paciente']) && is_numeric($_POST['id_paciente']))?$_POST['id_paciente']:'');
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
						//die();
				}
			

		}
	} 


		

	