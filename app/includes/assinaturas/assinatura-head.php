<?php
$doc_status = "0";

//verificando se o documento já foi assinado previamente
$sql->consult($_p . "pacientes_assinaturas", "id_evolucao", "where  md5(id_evolucao)='".$_GET['id_evolucao']."'");
if ($sql->rows) {
	$doc_status = "2";
}

if (isset($_POST['conf']) && $_POST['conf'] == true) {
	$rtn;
	if ($_POST['cpf_ent'] != $paciente->cpf || $_POST['data'] != $paciente->data_nascimento) {
		$rtn = array(
			"status" => "error",
			"message" => "CPF ou Data de nascimento errada"
		);
		echo json_encode($rtn);
		die();
	} else {
		$qry = "INSERT INTO " . $_p . "pacientes_assinaturas" . " (id_evolucao, id_tipo_evolucao, id_paciente, data, png_url, latitude, longitude, aprox, user_agent) VALUES (" . $evolucao->id . ", " . $evolucao->id_tipo . ", " . $evolucao->id_paciente . ", now(), '" . $_POST['canvas-url'] . "', " . $_POST['latitude'] . ", " . $_POST['longitude'] . ", " . $_POST['aprox'] . ", '" . addslashes($_POST['user_agent']) . "')";
		$sql->sintax($qry);
		$rtn = array(
			"status" => "success",
			"message" => "Assinatura realizada"
		);
		echo json_encode($rtn);
		die();
	}
}

?>