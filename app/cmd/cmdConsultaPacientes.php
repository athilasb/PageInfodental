<?php 
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");
	
	$sql = new Mysql();

	$_page=basename($_SERVER['PHP_SELF']);

	$where="WHERE lixo='0'";

	if(isset($values['busca']) and !empty($values['busca'])) {
		//$where.=" and (nome like '%".utf8_decode($values['busca'])."%' or cpf like '%".cpf($values['busca'])."%' or id = '".addslashes($values['busca'])."')";
		$wh="";
		$aux = explode(" ",$_GET['busca']);
		$primeiraLetra='';
		foreach($aux as $v) {
			if(empty($v)) continue;

			if(empty($primeiraLetra)) $primeiraLetra=substr($v,0,1);
			$wh.="nome REGEXP '$v' and ";
		}
		$wh=substr($wh,0,strlen($wh)-5);
		$where="where (($wh) or nome like '%".$_GET['busca']."%' or telefone1 like '%".$_GET['busca']."%' or cpf like '%".$_GET['busca']."%') and lixo=0";
		
		
	}


	$colabs = array();	
	if(isset($_POST['consultar']) && ($_POST['consultar'] == "1")){

		$sql->sintax("SELECT *, `ident_colaboradores`.`nome` as nome_colaborador FROM `ident_pacientes_evolucoes` INNER JOIN `ident_colaboradores` on `ident_colaboradores`.`id` = `ident_pacientes_evolucoes`.`id_profissional` ORDER BY `ident_pacientes_evolucoes`.`id` DESC;");

		while($x = mysqli_fetch_object($sql->mysqry)){
			$colabs[$x->id] = $x;
		}
		
		foreach($colabs as $e ){
			print_r($e);
			echo("<br/>\n");
		}

	}

?>

<link rel="stylesheet" type="text/css" href="../assinaturas/css/print.css" />
<link rel="stylesheet" type="text/css" href="../assinaturas/css/assinatura-css.css" />
<link rel="stylesheet" type="text/css" href="../assinaturas/css/assinatura-css2.css" />

<script src="../js/jquery.js"></script>
<script defer type="text/javascript" src="../js/jquery.select2.js"></script>
<script defer type="text/javascript" src="../js/jquery.slick.js"></script>
<script defer type="text/javascript" src="../js/jquery.datetimepicker.js"></script>
<script defer type="text/javascript" src="../js/jquery.chosen.js"></script>
<script defer type="text/javascript" src="../js/jquery.fancybox.js"></script>
<script defer type="text/javascript" src="../js/jquery.chart.js"></script>
<script defer type="text/javascript" src="../js/jquery.chart-utils.js"></script>
<script defer type="text/javascript" src="../js/jquery.tablesorter.js"></script>
<script defer type="text/javascript" src="../js/jquery.inputmask.js"></script>
<script defer type="text/javascript" src="../js/jquery.dad.js"></script>
<script defer type="text/javascript" src="../js/jquery.money.js"></script>
<script defer type="text/javascript" src="../js/jquery.tooltipster.js"></script>
<script defer type="text/javascript" src="../js/jquery.autocomplete.js"></script>
<script defer type="text/javascript" src="../js/jquery.caret.js"></script>
<script defer type="text/javascript" src="../js/jquery.mobilePhoneNumber.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script type="text/javascript" src="../js/jquery.sweetalert.js"></script>
<script type="text/javascript" src="../js/jquery.validacao.js"></script>
<script type="text/javascript" src="../js/jquery.funcoes.js?v1"></script>
<script defer src="https://code.iconify.design/1/1.0.3/iconify.min.js"></script>
<script type="text/javascript" src="../js/moment.js"></script>
<script type="text/javascript" src="../js/jquery.daterangepicker.js"></script>


<!--
<div class="box">

	
	<div class="list1">
		<table>
			<?php
/*			while($x=mysqli_fetch_object($sql->mysqry)) {
				$cor="var(--cinza3)";
				if(isset($_codigoBICores[$x->codigo_bi])) $cor=$_codigoBICores[$x->codigo_bi];
			/*?>
			<tr class="js-item" data-id="<?php echo $x->id;?>">
				<td style="width:20px;"><i class="iconify" data-icon="fluent:chevron-up-down-24-regular"></i></td>
				<td><h1><strong><?php echo utf8_encode($x->titulo);?></strong></h1></td>
			</tr>*/
			?>
			<tr class="js-item" data-id="<?php//echo $x->id;?>">
				<td class="list1__border" style="color:<?php //echo $cor;?>"></td>
				<td>
					<h1><//?php echo utf8_encode($x->nome);
					?></h1>
					<p>#<?php 
					//echo utf8_encode($x->id);
					?></p>
				</td>
				<td><?php 
				//echo isset($_codigoBI[$x->codigo_bi])?$_codigoBI[$x->codigo_bi]:"";
				?></td>
				<td><?php 
				//echo $x->data_nascimento!="0000-00-00"?idade($x->data_nascimento)." anos":"";
				?></td>
				<td><?php //echo !empty($x->telefone1)?mask($x->telefone1):"-";
				?></td>
			</tr>
			<?php
			//}
			?>
		</table>
	</div>
	<?php
		//if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
	?>
	
	<div class="pagination">						
		<?php //echo $sql->myspaginacao;?>
	</div>
	<?php
	//}

# LISTAGEM #
?>*/


</div>
-->




<h1> Pagina de consultas </h1>

<form method="post" class="formulario-validacao" id="classe">
	<input type="hidden" name="acao" value="conf" />
	<div style="border: " ><label>Profissional </label><input id="cpf" type="text" name="cpf"></input> </div>
	<div><label>Data Inicio </label><input class="data" id="data_inicio" type="text" name="data_inicio" /> </div>
	<div><label>Data Fim </label><input class="data" id="data_fim" type="text" name="data_fim" /> </div>
</form>

<?php


if(isset($_GET['consultar'])){
	$data_inicio = $data_fim = "";
	$lista = array(); 

	if(isset($_GET['data_inicio']) && $_GET['data_inicio'] != ""){
		$data_inicio = 	addslashes(converteData($_GET['data_inicio']));
	}if(isset($_GET['data_fim']) && isset($_GET['data_fim'])){
		$data_fim    = 	addslashes(converteData($_GET['data_fim']));
	}								//$where.=" and (nome like '%".utf8_decode($values['busca'])."%' or cpf like '%".cpf($values['busca'])."%' or id = '".addslashes($values['busca'])."')";
		$wh="";
		$aux = explode(" ",$_GET['busca']);
		$primeiraLetra='';
		foreach($aux as $v) {
			if(empty($v)) continue;

			if(empty($primeiraLetra)) $primeiraLetra=substr($v,0,1);
			$wh.="nome REGEXP '$v' and ";
		}
		$wh=substr($wh,0,strlen($wh)-5);
		$where="where  ($wh) or nome like '%".$_GET['busca']."%' or telefone1 like '%".$_GET['busca']."%' or cpf like ('%".$_GET['busca']."%') and   lixo=0";
		             //data  >= '$data_inicio' and data <= '$data_fim'		
/*					 echo ("<br><h1>$where</h1><br>");
	die();*/
	$sql->consult($_p."pacientes_evolucoes", "*", $where);



	/*if($sql->rows==0) {
		if(isset($values['busca'])) $msg="Nenhum Resultado encontrado";
		else $msg="Nenhum colaborador cadastrado";
		echo "<center>";
		var_dump($_ENV);		
		echo "</center>";
	} else{		
		while($x=mysqli_fetch_object($sql->mysqry)) {
			$cor="var(--cinza3)";
			$lista[] = "<tr class=\"js-item\" data-id=$x->id>
							<td class=\"list1__border\"></td>
							<td>
								<h1>".(utf8_encode($x->nome))."</h1>
								<p>#".(utf8_encode($x->id))."</p>
							</td>
						</tr>";

		}
	}*/


}
?>


<div class="filter-group">
                    <div class="filter-form form">
                        <dl>
                            <dd>
                                <a href="javascript:see();" data-loading="0" data-aside="prontuario-opcoes" class="button button_main">
                                    <i class="iconify"
                                        data-icon="line-md:circle-to-confirm-circle-transition"></i><span>Enviar</span>
                                </a>
                            </dd>
                        </dl>
                    </div>
                </div>
				<div class="list1">
							<table>
<?php


 
		echo "<center>";
		//		var_dump($lista);		
			//	echo(count($lista));
			echo(count($lista));
				echo "</center>";
	if(isset($_GET['busca']) && (count($lista) != 0)){
		echo "<center>";
		//		var_dump($lista);		
			//	echo(count($lista));
			echo("aqui");
				echo "</center>";

		foreach($lista as $e ){
			echo ("<h1>--------------------------------------------------------------------------------------------------------------</h1>");
			print_r($e);
			echo ("<h1>--------------------------------------------------------------------------------------------------------------</h1>");
			echo("<br/>\n");
		}
	}

?>
							</table>
						</div>



<script>
	function see(){
	 var dt_inicio = $("#data_fim")[0].value;
	 var dt_fim = $("#data_fim")[0].value;

	 console.log(`${dt_inicio}, ${dt_fim}`);
	 $.ajax({
		type: 'POST',
		data:{
			'consultar': 1,
			'data_inicio': $('#data_inicio').value(),
			'data_fim': $('#data_fim').value()
		},
		dataType: 'JSON',
		success: function(rnt) {
			alert(rnt);
		}
		});

	}


</script>