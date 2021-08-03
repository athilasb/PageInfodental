<?php

	class BI {


		function __construct($attr) {
			if(isset($attr['prefixo'])) $this->prefixo=$attr['prefixo'];
		}

		function classificaTodos() {
			$sql = new Mysql();
			$_p=$this->prefixo;

			// Pacientes
			$_pacientes=array();
			$pacientesIds=array();
			$sql->consult($_p."pacientes","*","limit 10");
			echo $sql->rows." pacientes <br >";
			while($x=mysqli_fetch_object($sql->mysqry)) {

				$diasDeCadastro = floor((strtotime(date('Y-m-d H:i:s')) - strtotime(date($x->data)))/(60*60*24));

				echo $x->nome."->".$diasDeCadastro."<br>";

				$_pacientes[$x->id]=array('id'=>$x->id,
											'nome'=>$x->nome,
											'novo'=>$diasDeCadastro<=60?1:0);
				$pacientesIds[]=$x->id;
			}

			// Agendamentos
			$_agendas=array();
			$sql->consult($_p."agenda","*","where lixo=0");
			while($x=mysqli_fetch_object($sql->mysqry)) {

			}




		}

		function classificaPaciente($id_paciente) {

		}
	}

?>