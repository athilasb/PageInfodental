<?php
	/*
		Migração para odontosazaki realizada em 2023-01-28 por Luciano e acompanhamento de Kroner e Walker
	*/

	require_once("../../lib/conf.php");
	require_once("../../lib/classes.php");

	$sql = new Mysql();


	# Clientes

		if(1==2) {
			$arq = file("clientes.csv");

			//$sql->del($_p."pacientes","");//die();
			$values='';
			foreach($arq as $x) {
				list($id_paciente,$data_registro,$nome,$celular,$cpf,$datanascimento,$email,$bairro,$cep,$cidade,$logradouro,$numeroprontuario,$numeropaciente,$observacao,$rg,$sexo,$telefone,$uf,$complemento,$plano,$motivo_chegar_clinica,$titular_plano,$cpf_resposavel_plano,$numero_carteirinha,$excluido) = explode(",",$x);

				if($nome=="nome") continue;

				$endereco=$logradouro;
				if(!empty($complemento)) $endereco.=", ".$complemento;
				if(!empty($bairro)) $endereco.=", ".$bairro;
				if(!empty($cidade)) $endereco.=", ".$cidade;
				if(!empty($uf)) $endereco.=", ".$uf;
				if(!empty($cep)) $endereco.=", CEP: ".$cep;
				$dn='0000-00-00';
				if(!empty($datanascimento)) list($dn,)=explode(" ",str_replace("\"","",$datanascimento));

				$values.="('".str_replace("\"","",utf8_decode($nome))."',
						'$data_registro',
						'".str_replace("\"","",$sexo)."',
						'".str_replace("\"","",telefone($celular))."',
						'".str_replace("\"","",telefone($telefone))."',
						'".str_replace("\"","",$cpf)."',
						'$dn',
						'".str_replace("\"","",$email)."',
						'".utf8_decode($endereco)."',
						'".str_replace("\"","",$rg)."'),";
			}

			$values=substr($values,0,strlen($values)-1);

			$sql->insertMultiple($_p."pacientes","nome,data,sexo,telefone1,telefone2,cpf,data_nascimento,email,endereco,rg",$values);
		}

	# Profissionais
		if(1==2) {

			$arq = file("profissionais.csv");

			foreach($arq as $x) {
				list($id_profissional,$celular,$cidade,$cpf,$cro,$email,$logradouro,$nome,$rg,$sexo,$telefone,$uf) = explode(",",$x);
				
				if($celular=="celular") continue;

				$endereco=$logradouro;
				if(!empty($cidade)) $endereco.=", ".$cidade;
				if(!empty($uf)) $endereco.=", ".$uf;

				$vSQL="nome='".str_replace("\"","",utf8_decode($nome))."',
						telefone1='".str_replace("\"","",telefone($celular))."',
						endereco='".str_replace("\"","",utf8_decode($endereco))."',
						cpf='".str_replace("\"","",utf8_decode($cpf))."',
						rg='".str_replace("\"","",utf8_decode($rg))."',
						email='".str_replace("\"","",utf8_decode($email))."',
						cro='".str_replace("\"","",utf8_decode($cro))."',
						sexo='".str_replace("\"","",utf8_decode($sexo))."',
						contratacaoAtiva=1,
						check_agendamento=1";

				$sql->add($_p."colaboradores",$vSQL);
			}
		}

	# Consulta
		if(1==2) {

			$sql->del($_p."agenda","");//die();

			$_pacientes=array();
			$sql->consult($_p."pacientes","id,nome","");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_pacientes[trim(utf8_encode($x->nome))]=$x->id;
			}

			$_profissionais=array();
			$sql->consult($_p."colaboradores","id,nome","");
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$_profissionais[trim(utf8_encode($x->nome))]=$x->id;
			}

			$_statusID=array('AGENDADA'=>1,
								'FINALIZADA'=>5,
								'CONFIRMADA'=>2,
								'CONFIRMADA_SMS'=>2,
								'CANCELADA_PACIENTE'=>4,
								'EM_ATENDIMENTO'=>6,
								'FALTA'=>3,
								'PACIENTE_AGUARDANDO'=>7,
								'CANCELADA_PROFISSIONAL'=>4,
								'CANCELADA_SMS'=>4,
								'CONFIRMADA_ONLINE'=>2,
								'CANCELADA_ONLINE'=>4,
								);

			//var_dump($_pacientes);die();
			$linha=1;
			$values="";
			if (($handle = fopen("consultas.csv", "r")) !== FALSE) {
			    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			        $num = count($data);
			       	$linha++;
			        for ($c=0; $c < $num; $c++) {
			        	if($c==0) { 
			        		$datareg=date('Y-m-d H:i:s',strtotime($data[$c]." - 3 hour"));
			        	}
			        	if($c==1) {
			        		$paciente=trim($data[$c]);
			        		if(isset($_pacientes[($paciente)])) {
			        			$id_paciente=$_pacientes[$paciente];
			        		} else {
			        			echo $linha."  cliente=> ".$paciente."<BR>";;
			        			var_dump($data);die();
			        		}
			        	}
			        	if($c==2) {
			        		$profissional=trim($data[$c]);
			        		if(isset($_profissionais[$profissional])) {
			        			$profissionais=",".$_profissionais[$profissional].",";
			        		} else {
			        			echo $linha." profissional=> ".$profissional."<BR>";;
			        			var_dump($data);die();
			        		}
			        	}
			        	if($c==3) {
			        		$status=$data[$c];
			       			$_status[$status]=$status;

			       			if(isset($_statusID[$status])) {
			       				$id_status=$_statusID[$status];
			       			} 
			        	}
			        	if($c==4) $agenda_duracao=$data[$c];
			        	if($c==5) $obs=str_replace("\"","",utf8_decode(addslashes($data[$c])));
			        }

			        $agenda_data_final=date('Y-m-d H:i:s',strtotime($datareg." + ".$agenda_duracao." minutes"));

			        // (agenda_data,id_paciente,profissionais,id_status,agenda_duracao,agenda_data_final,obs)

					if($datareg=="data") continue;
					$v="('$datareg','$id_paciente','$profissionais','$id_status','$agenda_duracao','$agenda_data_final','$obs'),";
			        $values.=$v;
			       // echo $values;die();

			      
			    }
			    fclose($handle);
			}
			//echo $values;die();

			$values=substr($values,0,strlen($values)-1);
			$sql->insertMultiple($_p."agenda","agenda_data,id_paciente,profissionais,id_status,agenda_duracao,agenda_data_final,obs",$values);

		}
		echo "deu certo";
?>