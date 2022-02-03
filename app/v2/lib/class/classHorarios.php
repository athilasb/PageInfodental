<?php
	
	class Horarios {



		function __construct($attr) {


			$this->prefixo = $attr['prefixo'];
		}


		// verifica intercecao de horarios de cadeira
		function cadeiraHorariosIntercecao($attr) {
			$_p=$this->prefixo;
			$sql = new Mysql();

			$cadeira = '';
			if(isset($attr['id_cadeira']) and is_numeric($attr['id_cadeira'])) {
				$sql->consult($_p."parametros_cadeiras","*","where id=".$attr['id_cadeira']);
				if($sql->rows) $cadeira=mysqli_fetch_object($sql->mysqry);
			}

			$horario = '';
			if(is_object($cadeira) and isset($attr['id_horario']) and is_numeric($attr['id_horario'])) {
				$sql->consult($_p."parametros_cadeiras_horarios","*","where id=".$attr['id_horario']);
				if($sql->rows) $horario=mysqli_fetch_object($sql->mysqry);
			}

			$diaSemana = (isset($attr['diaSemana']) and is_numeric($attr['diaSemana']) and $attr['diaSemana']>=0 and $attr['diaSemana']<=6) ? $attr['diaSemana'] : '';
			$inputHoraInicio = (isset($attr['inputHoraInicio']) and strlen($attr['inputHoraInicio'])==5) ? $attr['inputHoraInicio'] : '';
			$inputHoraFim = (isset($attr['inputHoraFim']) and strlen($attr['inputHoraFim'])==5) ? $attr['inputHoraFim'] : '';

			$err='';
			if(empty($cadeira)) $err='Cadeira não encontrada!';
			else if(empty($diaSemana)) $err='Dia da semana não definido!';
			else if(empty($inputHoraInicio)) $err='Horário de início não definido!';
			else if(empty($inputHoraFim)) $err='Horário de fim não definido!';
			else if(strtotime($inputHoraInicio)>=strtotime($inputHoraFim)) $err='Horário fim deve ser maior que o Horário início';


			if(empty($err)) {
				//echo $diaSemana." -> ".$inputHoraInicio." ".$inputHoraFim." -> \n\n";

				$whereCadeira="where id_cadeira=$cadeira->id and dia=$diaSemana and lixo=0";
				if(is_object($horario)) $whereCadeira.=" and id<>$horario->id";
				$whereCadeira.=" order by inicio asc";
				$sql->consult($_p."parametros_cadeiras_horarios","*",$whereCadeira);
				

				if($sql->rows) {

					$inpInicio=strtotime($inputHoraInicio);
					$inpFim=strtotime($inputHoraFim);



					while($x=mysqli_fetch_object($sql->mysqry)) {

						$hInicio=strtotime($x->inicio);
						$hFim=strtotime($x->fim);
						//echo $x->inicio."-".$x->fim."->";

						$intercede=false;
						$intercedeHorario="";
						
						if($inpInicio<$hInicio and $inpInicio<$hFim and $inpFim>$hInicio and $inpFim<$hFim) { 
							//echo 1
							$intercede=true;
						} else if($inpInicio>=$hInicio and $inpInicio<$hFim and $inpFim>$hInicio and $inpFim<=$hFim) {
							//echo 2;
							$intercede=true;
						} else if($inpInicio>=$hInicio and $inpInicio<$hFim and $inpFim>$hInicio and $inpFim>$hFim) { 
							//echo 3;
							$intercede=true;
						}


						if($intercede===true) {
							$intercedeHorario = date('H:i',strtotime($x->inicio))." - ".date('H:i',strtotime($x->fim));
							break;
						}


					}

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

	}

?>