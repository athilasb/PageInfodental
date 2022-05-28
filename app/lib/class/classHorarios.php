<?php
	
	class Horarios {



		function __construct($attr) {


			$this->prefixo = $attr['prefixo'];
		}


		// verifica intercecao de horarios de cadeira
		function cadeiraHorariosIntercecao($attr) {
			$_p=$this->prefixo;
			$sql = new Mysql();


			$_table=$_p."parametros_cadeiras_horarios";

			$cadeira = '';
			if(isset($attr['id_cadeira']) and is_numeric($attr['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id=".$attr['id_cadeira']);
				if($sql->rows) $cadeira=mysqli_fetch_object($sql->mysqry);
			}

			$colaborador = '';
			if(isset($attr['id_colaborador']) and is_numeric($attr['id_colaborador'])) {
				$sql->consult($_p."colaboradores","*","where id=".$attr['id_colaborador']);
				if($sql->rows) $colaborador=mysqli_fetch_object($sql->mysqry);
				$_table=$_p."profissionais_horarios";
			}

			$horario = '';
			if(is_object($cadeira) and isset($attr['id_horario']) and is_numeric($attr['id_horario'])) {

				$sql->consult($_table,"*","where id=".$attr['id_horario']);
				if($sql->rows) $horario=mysqli_fetch_object($sql->mysqry);
			}

			$diaSemana = (isset($attr['diaSemana']) and is_numeric($attr['diaSemana']) and $attr['diaSemana']>=0 and $attr['diaSemana']<=6) ? $attr['diaSemana'] : '';
			$inputHoraInicio = (isset($attr['inputHoraInicio']) and strlen($attr['inputHoraInicio'])==5) ? $attr['inputHoraInicio'] : '';
			$inputHoraFim = (isset($attr['inputHoraFim']) and strlen($attr['inputHoraFim'])==5) ? $attr['inputHoraFim'] : '';

			$err='';
			if(empty($cadeira)) $err='Cadeira não encontrada!';
			else if(empty($diaSemana) and $diaSemana!=0) $err='Dia da semana não definido!';
			else if(empty($inputHoraInicio)) $err='Horário de início não definido!';
			else if(empty($inputHoraFim)) $err='Horário de fim não definido!';
			else if(strtotime($inputHoraInicio)>=strtotime($inputHoraFim)) $err='Horário fim deve ser maior que o Horário início';


			if(empty($err)) {
				//echo $diaSemana." -> ".$inputHoraInicio." ".$inputHoraFim." -> \n\n";


				if(is_object($colaborador)) {
					$whereCadeira="where id_profissional=$colaborador->id and id_cadeira=$cadeira->id and dia=$diaSemana and lixo=0";
					if(is_object($horario)) $whereCadeira.=" and id<>$horario->id";
					$whereCadeira.=" order by inicio asc";

				} else {
					$whereCadeira="where id_cadeira=$cadeira->id and dia=$diaSemana and lixo=0";
					if(is_object($horario)) $whereCadeira.=" and id<>$horario->id";
					$whereCadeira.=" order by inicio asc";
				}
				$sql->consult($_table,"*",$whereCadeira);
				//echo $whereCadeira."\n".$sql->rows;die();

				if($sql->rows) {

					$inpInicio=strtotime($inputHoraInicio);
					$inpFim=strtotime($inputHoraFim);



					while($x=mysqli_fetch_object($sql->mysqry)) {

						$hInicio=strtotime($x->inicio);
						$hFim=strtotime($x->fim);
						//echo $x->inicio."-".$x->fim."->\n";

						$intercede=false;
						$intercedeHorario="";
						
						if($inpInicio<$hInicio and $inpInicio<$hFim and $inpFim>$hInicio and $inpFim<=$hFim) { 
							//echo 1;
							$intercede=true;
						} else if($inpInicio>=$hInicio and $inpInicio<$hFim and $inpFim>$hInicio and $inpFim<=$hFim) {
							//echo 2;
							$intercede=true;
						} else if($inpInicio>=$hInicio and $inpInicio<$hFim and $inpFim>$hInicio and $inpFim>$hFim) { 
							//echo 3;
							$intercede=true;
						} else if($inpInicio<$hInicio and $inpInicio<$hFim and $inpFim>$hInicio and $inpFim>$hFim) { 
						//	echo 4;
							$intercede=true;
						}
						// echo "\n\n ";

						if($intercede===true) {
							$intercedeHorario = date('H:i',strtotime($x->inicio))." - ".date('H:i',strtotime($x->fim));
							break;
						}


					}
					//echo "fim";die();
					if($intercede===true) {
						$err='Este horário intercede com o horário das: '.$intercedeHorario;
					} 
				}
				
			} 


			if(!empty($err)) {
				$this->erro=$err;
				return false;
			} else {
				return true;
			}

		}

		// calcula carga horaria
		function colaboradorCargaHoraria($id_colaborador) {
			$_p=$this->prefixo;
			$sql = new Mysql();

			$colaborador = '';
			if(isset($id_colaborador) and is_numeric($id_colaborador)) {
				$sql->consult($_p."colaboradores","id","where id=$id_colaborador and lixo=0");
				if($sql->rows) {
					$colaborador=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($colaborador)) {

				$carga=0;
				$sql->consult($_p."profissionais_horarios","*","where id_profissional=$colaborador->id and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$dif=(strtotime($x->fim)-strtotime($x->inicio));
						$carga+=$dif;

						//echo $x->inicio." ".$x->fim." = ".$dif."<BR>";
					}
				}

				$this->carga=$carga;
				return true;

			} else {
				$this->erro="Colaborador não encontrado!";
				return false;
			}

		}


		// calcula carga horaria
		function cadeiraCargaHoraria($id_cadeira) {
			$_p=$this->prefixo;
			$sql = new Mysql();

			$cadeira = '';
			if(isset($id_cadeira) and is_numeric($id_cadeira)) {
				$sql->consult($_p."parametros_cadeiras","id","where id=$id_cadeira and lixo=0");
				if($sql->rows) {
					$cadeira=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(is_object($cadeira)) {

				$carga=0;
				$sql->consult($_p."parametros_cadeiras_horarios","*","where id_cadeira=$cadeira->id and lixo=0");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$dif=(strtotime($x->fim)-strtotime($x->inicio));
						$carga+=$dif;

						//echo $x->inicio." ".$x->fim." = ".$dif."<BR>";
					}
				}

				$this->carga=$carga;
				return true;

			} else {
				$this->erro="Cadeira não encontrado!";
				return false;
			}

		}

	}

?>