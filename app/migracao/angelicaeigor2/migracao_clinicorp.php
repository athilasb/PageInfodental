<?php
	require_once("../lib/classes.php");
	$sql = new Mysql();
	$_p="ident_";
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Infodental - Sistema de Migração</title>
</head>
<body>


	<?php
	$sistema="clinicorp";

	

	$instancias=array();
	$sql->consult("infodentalADM.infod_contas","*","where migracao=1 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$instancias[$x->instancia]=$x;
	}

	if(isset($_POST['acao'])) {

		if(isset($_POST['instancia']) and isset($instancias[$_POST['instancia']])) $instancia=$instancias[$_POST['instancia']];


		if(empty($instancia)) $erro='Selecione a instancia!';
		else if(!isset($_FILES['agendamentos'])) $erro='Anexe os agendamentos (csv)';
		else if(!isset($_FILES['pacientes'])) $erro='Anexe os pacientes (csv)';

		if(empty($erro)) {



			$_p=$instancia->instancia.".".$_p;
			//echo $_p;die();

			$sql->add("infodentalADM.infod_contas_migracoes","data=now(),instancia='".$instancia->instancia."',sistema='clinicorp'");
			$id_migracao=$sql->ulid;


			if(copy($_FILES['agendamentos']['tmp_name'],"arqs/agendamentos_".$id_migracao.".csv")) {

				if(copy($_FILES['pacientes']['tmp_name'],"arqs/pacientes_".$id_migracao.".csv")) {
					if(copy($_FILES['profissionais']['tmp_name'],"arqs/profissionais_".$id_migracao.".csv")) {

					} else $erro='Erro ao realizar o upload dos Profissionais';

				} else $erro='Erro ao realizar o upload dos Pacientes';

			} else $erro='Erro ao realizar o upload dos Agendamentos';


			if(empty($erro)) {

				# Clinicorp
				if($sistema=="clinicorp") {


					$agendamentosCSV = file("arqs/agendamentos_".$id_migracao.".csv");
					$pacientesCSV = file("arqs/pacientes_".$id_migracao.".csv");
					$profissionaisCSV = file("arqs/profissionais_".$id_migracao.".csv");



					$usr=(object)array('id'=>0);

					// apaga pacientes e agendamentos
					$sql->del($_p."pacientes","");
					$sql->del($_p."pacientes_historico","");
					$sql->del($_p."agenda","");

			
					// pacientes
						$_pacientes=array();
						foreach($pacientesCSV as $p) {

							list($BirthDate,$CivilStatus,$CreatedAt,$DocumentId,$Email,$id,$MobilePhone,$Name,$OtherDocumentIdSearch,$Sex)=explode(";",$p);

							if($BirthDate=="BirthDate") continue;

							// retira os ultimos 3 digitos
							$id=trim(substr($id,0,-3));



							$dn = date('Y-m-d',strtotime($BirthDate));

							$estadoCivil="";

							if($CivilStatus=="SINGLE") $estadoCivil="SOLTEIRO";
							else if($CivilStatus=="DIVORCED") $estadoCivil="SEPARADO";
							else if($CivilStatus=="MARRIED") $estadoCivil="CASADO";
							else if($CivilStatus=="WIDOWED") $estadoCivil="VIÚVO";


							$data=date('Y-m-d H:i',strtotime($CreatedAt));

							
							$celular=telefone($MobilePhone);

							//$index=strtolowerWLIB(str_replace(" ","",tirarAcentos(($Name)))).telefone($celular);
							$index=$id;

							//echo $index."->".$Nome."->".$Telefone." -> $Data_do_Cadastro $Registro<br />";
							//if(isset($_pacientes[$index])) echo "=".$index." $Nome<BR>";
							$_pacientes[$index]=array('id_paciente'=>$id,
														'data'=>$data,
														'nome'=>($Name),
														'estado_civil'=>$estadoCivil,
														'celular'=>telefone($MobilePhone),
														'email'=>($Email),
														'dn'=>$dn,
														'rg'=>strlen($DocumentId)>2?$DocumentId:"",
														'cpf'=>strlen($OtherDocumentIdSearch)==11?$OtherDocumentIdSearch:"",
														'sexo'=>$Sex=="M"?"M":"F",
														'agenda'=>array());
						
							
						}

					// profissionais
						$_profissionais=array();
						foreach($profissionaisCSV as $p) {

							list($BirthDate,$CivilStatus,$Color,$CRO,$DocumentId,$Email,$id,$Name,$OtherDocumentIdSearch,$Sex)=explode(";",$p);

							$id=substr($id,0,-3);

							if($BirthDate!="BirthDate") {

								$dnAno = substr($BirthDate,0,4);
								$dnMes = substr($BirthDate,4,2);
								$dnDia = substr($BirthDate,6,2);

								$Name=trim($Name);
								$OtherDocumentIdSearch=numero(trim($OtherDocumentIdSearch));

								if(empty($dnAno) or empty($dnMes) or empty($dnDia)) $dn='';
								else $dn=$dnAno."-".$dnMes."-".$dnDia;

								if(strlen($OtherDocumentIdSearch)!=11) $OtherDocumentIdSearch="";
								else $OtherDocumentIdSearch=trim($OtherDocumentIdSearch);

								if(strlen($Sex)<=1) $Sex="";

								$estadoCivil="";
								if($CivilStatus=="SINGLE") $estadoCivil="SOLTEIRO";
								else if($CivilStatus=="DIVORCED") $estadoCivil="SEPARADO";
								else if($CivilStatus=="MARRIED") $estadoCivil="CASADO";
								else if($CivilStatus=="WIDOWED") $estadoCivil="VIÚVO";

								$vSQLProfissional="data_nascimento='".addslashes($dn)."',
													estado_civil='".addslashes($estadoCivil)."',
													calendario_cor='".addslashes($Color)."',
													cro='".addslashes($CRO)."',
													check_agendamento=1,
													email='".addslashes($Email)."',
													nome='".addslashes($Name)."',
													cpf='".addslashes($OtherDocumentIdSearch)."',
													sexo='".addslashes(trim($Sex))."'";

								$where="WHERE nome='".addslashes($Name)."'";

								if(strlen($OtherDocumentIdSearch)==11) $where.=" and cpf='".addslashes($OtherDocumentIdSearch)."'";

								$sql->consult($_p."colaboradores","*",$where);
								if($sql->rows) {
									$x=mysqli_fetch_object($sql->mysqry);

									$sql->update($_p."colaboradores",$vSQLProfissional,"where id=$x->id");
									$id_profissional=$x->id;
								} else {
									$sql->add($_p."colaboradores",$vSQLProfissional.",data=now()");
									$id_profissional=$sql->ulid;
								}

								$_profissionais[$id]=array('id_profissional'=>$id_profissional,
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
						list($CreateDate,$date,$Dentist_PersonId,$fromTime,$id,$MobilePhone,$Notes,$Patient_PersonId,$PatientName,$StatusId,$toTime)=explode(";",nl2br($a));
						
						if($CreateDate=="CreateDate") continue;

						// retira os ultimos 3 digitos
						$Dentist_PersonId=substr($Dentist_PersonId,0,-3);
						$Patient_PersonId=trim(substr($Patient_PersonId,0,-3));


						$data=date('Y-m-d H:i',strtotime($CreateDate));
						$agenda_data=date('Y-m-d',strtotime($date))." ".$fromTime;
						$agenda_data_final=date('Y-m-d',strtotime($date))." ".$toTime;


						$dif = strtotime($agenda_data_final)-strtotime($agenda_data);

						if($dif<0) $duracao=30;
						else $duracao=$dif/60;

						$id_profissional=$Dentist_PersonId;
						if(isset($_profissionais[$Dentist_PersonId])) $id_profissional=$_profissionais[$Dentist_PersonId]['id_profissional'];

						//$index=strtolowerWLIB(str_replace(" ","",tirarAcentos($PatientName))).telefone($MobilePhone);
						$index=$Patient_PersonId;

						/*
						4764335248769020 - atrasado -> confirmado (2)
						5073308082503680 - confirmado (2)
						5765164147671040 - faltou (3)
						6175685258772480 - atendido (5)
						6351196329082880 - em atendimento (6)
						*/

						$idStatus=1; // a confirmar
						if($StatusId==4764335248769020) $idStatus=2;
						else if($StatusId==5073308082503680) $idStatus=2;
						else if($StatusId==5765164147671040) $idStatus=3;
						else if($StatusId==6175685258772480) $idStatus=5;
						else if($StatusId==6351196329082880) $idStatus=6;

						if(!isset($_pacientes[$index]) and strtotime($agenda_data)>strtotime(date('Y-m-d H:i'))) {
					
							echo "nao achou paciente com agendamento futuro: $index $PatientName $agenda_data<BR>";
							continue;
						}



							$_pacientes[$index]['agenda'][]=array('data'=>$data,
																	'agenda_data'=>$agenda_data,
																	'profissionais'=>$id_profissional,
																	'duracao'=>$duracao,
																	'id_paciente'=>$Patient_PersonId,
																	'id_status'=>$idStatus,
																	'id_cadeira'=>$id_cadeira,
																	'obs'=>$Notes);
							
						


						
					
					}


					foreach($_pacientes as $x) {

						# Cadastra Paciente
							$idPaciente=$x['id_paciente'];
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
				} else {
					$erro="Sistema não encontrado!";
				}

			}
		} 


		if(!empty($erro)) {
		?>
		<script type="text/javascript">alert('<?php echo $erro;?>')</script>
		<?php
		}
	}

	?>
	<p>Ok, vamos migrar dados do sistema <b>Clinicorp</b> para o <b>Infodental</b>. Certo?</p>
	<p>Agora selecione a instancia:</p>
	<form method="post" enctype="multipart/form-data">
		<input type="hidden" name="acao" value="importar" />
		<select name="instancia">
			<option value="">-</option>
			<?php
			foreach($instancias as $i) {
				echo '<option value="'.$i->instancia.'"'.((isset($_POST['instancia']) and $_POST['instancia']==$i->instancia)?' selected':'').'>'.$i->titulo.' ('.$i->instancia.'.infodental.dental)</option>';
			}
			?>
		</select>

		<p>Agendamentos</p>
		<input type="file" name="agendamentos" accept=".csv" />
		<p style="font-size:11px;color:#666;">CreateDate;date;Dentist_PersonId;fromTime;id;MobilePhone;Notes;Patient_PersonId;PatientName;StatusId;toTime</p>
		<p>Pacientes</p>
		<p style="font-size:11px;color:#666;">BirthDate;CivilStatus;CreatedAt;DocumentId;Email;id;MobilePhone;Name;OtherDocumentIdSearch;Sex</p>
		<input type="file" name="pacientes" accept=".csv" />
		<p>Profissionais</p>
		<p style="font-size:11px;color:#666;">BirthDate,CivilStatus,Color,CRO,DocumentId,Email,id,Name,OtherDocumentIdSearch,Sex</p>
		<input type="file" name="profissionais" accept=".csv" />

		<br /><br />
		<button>Importar !</button>
	</form>


</body>
</html>