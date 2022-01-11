<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);

	$_table=$_p."financeiro_extrato";
	$_dirofx="arqs/tempf/";

?>
<section class="content">

	<?php
	require_once("includes/asideFinanceiro.php");
	?>

	<?php
	$_page=basename($_SERVER['PHP_SELF']);

	$conta='';
	if(isset($_GET['id_conta']) and is_numeric($_GET['id_conta'])) {
		$sql->consult($_p."financeiro_bancosecontas","*,date_format(data,'%d/%m/%Y %H:%i') as dataf","where id='".$_GET['id_conta']."'");
		if($sql->rows) {
			$conta=mysqli_fetch_object($sql->mysqry);
			
			if($conta->tipo!="contacorrente") {
				$jsc->jAlert("Este cadastro não é uma Conta Corrente","erro","document.location.href='pg_financeiro_movimentacao_saldo.php'");
				die();
			}

		} 
	}

	if(empty($conta)) {
		$jsc->jAlert("Banco/Conta não encontrada!","erro","document.location.href='pg_financeiro_movimentacao_saldo.php'");
		die();
	}

	if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
		$sql->add($_p."financeiro_extrato_ofx","data=now(),id_conta='".$conta->id."'");
		$id_reg=$sql->ulid;
		
		$erro='';
		$ext=explode(".",$_FILES['ofx']['name']);
		$ext=strtolower($ext[count($ext)-1]);
		if($ext!="ofx") $erro="Só é permitido importação de arquivos OFX";
		//else if(!copy($_FILES['ofx']['tmp_name'],$_dirofx.$id_reg.".ofx")) $erro="Algum erro ocorreu durante o envio do OFX. Tente novamente!";
		else { 

			$uploadFile=$_FILES['ofx']['tmp_name'];
			/*$uploadPathFile=$_wasabiPathRoot.$_dirofx.$id_reg.".ofx";
			$uploadType=$_FILES['ofx']['type'];
			$uploaded=$wasabiS3->putObject(S3::inputFile($uploadFile,false),$_wasabiBucket,$uploadPathFile,S3::ACL_PUBLIC_READ);
			if(!$uploaded) {
				$erro="Algum erro ocorreu durante o envio do OFX. Tente novamente!";
			} else {*/
			
				$ofx = new OFX($_FILES['ofx']['tmp_name']);

				$agenciaconta=preg_replace('/[^0-9]/', '', $conta->agencia.$conta->conta);
				//echo " strpos($agenciaconta,$ofx->acctId)<BR>";

				$mystring=(string)$agenciaconta;
				$findme=(string)$ofx->acctId;

				//echo strpos($mystring,$findme) === false ? 0 : 1;
				//die();
				//if($agenciaconta!=$ofx->acctId) {

				//echo $mystring." ".$findme;die();
				if(strpos($mystring,$findme) === false ){
					$jsc->jAlert("Este OFX não pertence a esta conta!","erro","");
					$sql->update($_table,"lixo=1","where id=$id_reg");
				} else {
					$saldo=0;
					foreach($ofx->bankTranList as $v) {
						$data=substr($v->DTPOSTED,0,4)."-".substr($v->DTPOSTED,4,2)."-".substr($v->DTPOSTED,6,2);
						$tipo=$v->TRNTYPE;

						$num=$v->TRNAMT;
						$val=strpos($num,",")!==false?valor($num):$num;
						$valor=number_format((float)$val,2,".","");
						$uniqueId=$v->FITID;
						$checknumber=$v->CHECKNUM;
						$descricao=$v->MEMO;
						//if($valor!=-13676.14) continue;
						
						$vSQL="data_extrato='".$data."',
								tipo='".$tipo."',
								valor='".$valor."',
								descricao='".utf8_decode($descricao)."',
								id_ofx='".$id_reg."',
								id_conta='".$conta->id."',
								uniqueid='".$uniqueId."',
								checknumber='".$checknumber."'";

						$saldo+=$valor;
						$saldo=number_format($saldo,2,".","");
						//echo $data."-> $v->TRNAMT ---> ".$valor."--->".$saldo."<BR>";continue;
						//echo $vSQL;die();
						$where="where id_conta='".$conta->id."' and data_extrato='".$data."' and valor='".$valor."' and descricao='".utf8_decode($descricao)."' and lixo=0";
						$sql->consult($_table,"*",$where); 

						//echo $where."->".$sql->rows. "<br />";
						if($sql->rows==0) {
							$sql->add($_table,$vSQL);
						} else {
							$x=mysqli_fetch_object($sql->mysqry);
							//echo $x->id."<BR>";
							$sql->update($_table,$vSQL,"where id=$x->id");
						}
					}
					$saldo=number_format($saldo,2,".","");
					$sql->update($_table."_ofx","saldo='".$saldo."'","where id='".$id_reg."'");
					
					
					//echo $saldo;die();
				}
			//}
			
			if(!empty($erro)) {
				$jsc->jAlert($erro,"erro","");
			}
		}
		
		//die();
	}

	?>

	<section class="grid">
		<div class="box">

			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				<input type="hidden" name="acao" value="wlib" />

				<div class="filter">
					<div class="filter-group">
						<div class="filter-button">
							<a href="pg_financeiro_movimentacao_saldo.php"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						</div>
					</div>
					<div class="filter-group filter-group_right">
						<div class="filter-button">
							<a href="javascript:;" class="azul btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
						</div>
					</div>
				</div>

				<div class="grid grid_auto" style="flex:1;">
					<fieldset style="margin:0;">
						<legend>Importar OFX</legend>
						<dl>
							<dt>Conta</dt>
							<dd><?php echo utf8_encode($conta->titulo);?></dd>
						</dl>
						<dl class="dl3">
							<dt>OFX</dt>
							<dd><input type="file" name="ofx" accept=".ofx" class="obg" /></dd>
						</dl>

						
					</fieldset>												
					
					
				</div>


				
			</form>
		</div>
	</section>
	

</section>

<?php
	include "includes/footer.php";
?>