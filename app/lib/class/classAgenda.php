<?php

	class Agenda { 

		function __construct($attr) {
			$this->prefixo = $attr['prefixo'];
		} 

		function kanban($attr) {
			$_p=$this->prefixo;
			$sql = new Mysql();

			$diasExtenso=array('(domingo)','(segunda-feira)','(terça-feira)','(quarta-feria)','(quinta-feira)','(sexta-feira)','(sábado)');

			$data='';
			if(isset($attr['data']) and !empty($attr['data'])) {
				list($ano,$mes,$dia)=explode("-",$attr['data']);
				if(checkdate($mes, $dia, $ano)) $data=$attr['data'];
			}


			$id_profissional=(isset($attr['id_profissional']) and is_numeric($attr['id_profissional']))?$attr['id_profissional']:0;
			$id_cadeira=(isset($attr['id_cadeira']) and is_numeric($attr['id_cadeira']))?$attr['id_cadeira']:0;

			$_profissionais=array();
			$sql->consult($_p."colaboradores","id,nome","");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_profissionais[$x->id]=$x;
			}

			if(!empty($data)) {
				$agenda=$agendaIds=array();
				$pacientesIds=$pacientesAtendidosIds=array(-1);
				$where="where agenda_data>='".$data." 00:00:00' and agenda_data<='".$data." 23:59:59' and lixo=0";
				if($id_profissional>0) $where.=" and profissionais like '%,$id_profissional,%'";
				if($id_cadeira>0) $where.=" and id_cadeira = '$id_cadeira'";
				$registros=array();
				$sql->consult($_p."agenda","id,id_paciente,data,agenda_data,id_status,agenda_data_final,profissionais",$where." order by data asc");
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$registros[]=$x;
					$pacientesIds[]=$x->id_paciente;
					$agendaIds[]=$x->id;

					// ATENDIDO
					if($x->id_status==5) {
						$pacientesAtendidosIds[]=$x->id_paciente;
						$_pacientesAgendamentos[$x->id_paciente]=$x;
					}
				}

				// busca agendamentos que tiveram "Proxima Consulta / Lembrete de proxima consulta ou Quero agendar ou Confirmação de periodicidade"
				if(count($agendaIds)>0) {

					$agendaFuturo=array();
					$sql->consult($_p."agenda","distinct id_paciente","where agenda_data>'".date('Y-m-d',strtotime($data." + 1 day"))."' and id_paciente IN (".implode(",",$pacientesIds).") and lixo=0 and id_status IN (1,2,5,6,7)");
					while($x=mysqli_fetch_object($sql->mysqry)) {
						//if($x->id_paciente==8454) echo $data." ".$x->id_paciente;
						$agendaFuturo[$x->id_paciente]=1;
					}

					// Lembrete de proxima consulta
					$_pacientesProximaConsulta=array();
					$sql->consult($_p."pacientes_proximasconsultas","id_agenda_origem","where id_agenda_origem in (".implode(",",$agendaIds).") and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_pacientesProximaConsulta[$x->id_agenda_origem]=1;
						}
					}


					// Quero Agendar
					$_pacientesHistorico=array();
					$sql->consult($_p."pacientes_historico","id_agenda_origem","where id_agenda_origem in (".implode(",",$agendaIds).") and evento='agendaNovo' and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_pacientesHistorico[$x->id_agenda_origem]=1;
						}
					}


					// Confirmação de periodicidade
					$_pacientesPeriodicidade=array();
					$sql->consult($_p."pacientes","id_agenda_origem","where id_agenda_origem in (".implode(",",$agendaIds).") and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_pacientesPeriodicidade[$x->id_agenda_origem]=1;
						}
					}

				}


				// busca prontuarios
				$_pacientesProntuario=array();
				if(count($pacientesIds)>0) {

					/*$sql->consult($_p."pacientes_prontuarios","id_paciente","where data>='".$data." 00:00:00' and data<='".$data." 23:59:59' and id_paciente IN (".implode(",",$pacientesIds).") and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_pacientesProntuario[$x->id_paciente]=$x;
						}
					}*/

					$sql->consult($_p."pacientes_evolucoes","id_paciente","where data>='".$data." 00:00:00' and data<='".$data." 23:59:59' and id_paciente IN (".implode(",",$pacientesIds).") and id_tipo IN (2,9) and lixo=0");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_pacientesProntuario[$x->id_paciente]=$x;
						}
					}
				}


				// busca se pacientes atendidos foram marcado agendamento na data do atendimento
				$_pacientesAgendados=array();
				$sql->consult($_p."agenda","id,data,agenda_data,id_paciente","where id_paciente in (".implode(",",$pacientesAtendidosIds).") and agenda_data>now() and lixo=0");

				while($x=mysqli_fetch_object($sql->mysqry)) {
					if(isset($_pacientesAgendamentos[$x->id_paciente])) {
						$ag=$_pacientesAgendamentos[$x->id_paciente];


						// se a data em que foi marcada a agenda é a mesma data do agendamento em que foi atendido
						if(strtotime(date('Y-m-d',strtotime($ag->agenda_data)))==strtotime(date('Y-m-d',strtotime($x->data)))) {
							//echo $ag->id."=> agendado em $x->data => $ag->agenda_data\n<BR>";
							$_pacientesHistorico[$ag->id]=1;
						}
					}
				}

				$pacientesEvolucoes=array();
				$where="where data_evolucao='".$data."' and id_paciente IN (".implode(",",$pacientesAtendidosIds).") and lixo=0";
				$sql->consult($_p."pacientes_evolucoes","*",$where);
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$pacientesEvolucoes[$x->id_paciente][]=$x;
					}
				}


				$camposParaFichaCompleta=explode(",","nome,sexo,rg,rg_orgaoemissor,rg_uf,cpf,data_nascimento,estado_civil,telefone1,lat,lng,endereco");

				$_pacientes=array();
				$sql->consult($_p."pacientes","*","where id IN (".implode(",",$pacientesIds).") and lixo=0");
				while($x=mysqli_fetch_object($sql->mysqry)) {

					// verifica se a ficha do paciente esta completa
						$fichaCompleta=1;

						foreach($camposParaFichaCompleta as $c) {
							if(empty($x->$c)) {
								$fichaCompleta=0;
								break;
							}
						}

					$_pacientes[$x->id]=(object)array('id'=>$x->id,
														'nome'=>$x->nome,
														'telefone1'=>$x->telefone1,
														'codigo_bi'=>$x->codigo_bi,
														'fichaCompleta'=>$fichaCompleta);
				}

				$_agendamentosConfirmacaoWts=array();
				$_agendamentosLembretes=array();
				if(count($agendaIds)>0) {
					$sql->consult($_p."whatsapp_mensagens","*","where id_agenda IN (".implode(",",$agendaIds).") and id_tipo IN (1,2)");
					while($x=mysqli_fetch_object($sql->mysqry)) {

						if($x->id_tipo==1) {
							$_agendamentosConfirmacaoWts[$x->id_agenda]=1;

							if($x->resposta_sim==1) $_agendamentosConfirmacaoWts[$x->id_agenda]=2;
							else if($x->resposta_nao==1) $_agendamentosConfirmacaoWts[$x->id_agenda]=3;
							else if($x->resposta_naocompreendida>0) $_agendamentosConfirmacaoWts[$x->id_agenda]=4;
							else if($x->enviado==0 and $x->erro==1) $_agendamentosConfirmacaoWts[$x->id_agenda]=6;
							else {
								$dif = strtotime(date('Y-m-d H:i'))-strtotime($x->data_enviado);
								$dif /= 60;
								$dif = ceil($dif);

								if($dif>4) {
									 $_agendamentosConfirmacaoWts[$x->id_agenda]=5;
								}
							}
						} else if($x->id_tipo==2) {
							$_agendamentosLembretes[$x->id_agenda]=1;
						}
					}
				}

				foreach($registros as $x) {

					$pacientePeriodicidade=$pacienteHistorico=$pacienteProximaConsulta=$pacienteProntuario=0;

					if(isset($_pacientesProximaConsulta[$x->id])) $pacienteProximaConsulta=1;
					if(isset($_pacientesHistorico[$x->id])) $pacienteHistorico=1;
					if(isset($_pacientesPeriodicidade[$x->id])) $pacientePeriodicidade=1;
					if(isset($_pacientesProntuario[$x->id_paciente])) $pacienteProntuario=1;


					if(isset($_pacientes[$x->id_paciente])) {
						$paciente=$_pacientes[$x->id_paciente];

						$dataAg=date('d/m',strtotime($x->agenda_data));
						$dia=" ".$diasExtenso[date('w',strtotime($x->agenda_data))];

						$profissionais='';
						if(!empty($x->profissionais)) {
							$profAux=explode(",",$x->profissionais);
							foreach($profAux as $idP) {
								if(!empty($idP) and isset($_profissionais[$idP])) {
									$prof=$_profissionais[$idP];
									$profissionais.=utf8_encode($prof->nome)."<BR>";

								}
							}
						}
						//echo $paciente->nome." ". $pacientePeriodicidade." ".$pacienteProximaConsulta." ".$pacienteHistorico."\n";

						if(!empty($profissionais)) $profissionais=substr($profissionais,0,strlen($profissionais)-4);
 
						$mais24="";

						$dif = round((strtotime($x->agenda_data)-strtotime($x->data))/(60*60));

						$agenda[]=(object) array('id_agenda'=>$x->id,
													'dt'=>$x->data,
													'data'=>$dataAg,//.$dia,
													'hora'=>date('H:i',strtotime($x->agenda_data))." às ".date('H:i',strtotime($x->agenda_data_final)),
													'id_status'=>$x->id_status,
													'id_paciente'=>$paciente->id,
													'statusBI'=>isset($_codigoBI[$paciente->codigo_bi])?$_codigoBI[$paciente->codigo_bi]:'',
													'paciente'=>ucwords(strtolowerWLIB(utf8_encode($_pacientes[$x->id_paciente]->nome))),
													'telefone1'=>mask($_pacientes[$x->id_paciente]->telefone1),
													'evolucao'=>isset($pacientesEvolucoes[$x->id_paciente])?1:0,
													'wts'=>(int)isset($_agendamentosConfirmacaoWts[$x->id])?$_agendamentosConfirmacaoWts[$x->id]:0,
													'mais24'=>(int)($dif>=24?1:0), // se possui mais de 24h que foi feito o agendamento
													'profissionais'=>$profissionais,
													'lembrete'=>isset($_agendamentosLembretes[$x->id])?1:0,
													'fichaCompleta'=>$paciente->fichaCompleta,
													'pProx'=>$pacienteProximaConsulta,
													'pHist'=>$pacienteHistorico,
													'pPer'=>$pacientePeriodicidade,
													'pPron'=>$pacienteProntuario,
													'futuro'=>isset($agendaFuturo[$x->id_paciente])?1:0
												);
					}
				}


				$this->kanban=$agenda;
				return true;

			} else {

				$this->erro='Data inválida!';
				return false;

			}


		}

	}
?>