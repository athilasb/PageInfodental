<?php
	require_once("../lib/conf.php");
	$dir="../";
	require_once("../usuarios/checa.php");

	if(isset($_POST['ajax'])) {
		$sql = new Mysql();

		$rtn = array();
		if($_POST['ajax']=="bandeiraPersistir") {

			$bandeira='';
			if(isset($_POST['id_bandeira']) and is_numeric($_POST['id_bandeira'])) {
				$sql->consult($_p."parametros_cartoes_bandeiras","*","where id='".$_POST['id_bandeira']."'");
				if($sql->rows) {
					$bandeira=mysqli_fetch_object($sql->mysqry);
				}
			}

			$vSQL="titulo='".addslashes(utf8_decode($_POST['titulo']))."'";

			if(is_object($bandeira)) {
				$vWHERE="where id=$bandeira->id";
				$sql->update($_p."parametros_cartoes_bandeiras",$vSQL,$vWHERE);
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."parametros_cartoes_bandeiras',id_reg='".$bandeira->id."'");
				$id_reg=$bandeira->id;
			} else {
				$sql->add($_p."parametros_cartoes_bandeiras",$vSQL);
				$id_reg=$sql->ulid;
				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_cartoes_bandeiras"."',id_reg='".$sql->ulid."'");
			}
			$rtn=array('success'=>true);
		} else if($_POST['ajax']=="bandeiraRemover") {
			if(isset($_POST['id_bandeira']) and is_numeric($_POST['id_bandeira'])) {
				$sql->consult($_p."parametros_cartoes_bandeiras","*","where id='".$_POST['id_bandeira']."'");
				if($sql->rows) {
					$bandeira=mysqli_fetch_object($sql->mysqry);
				}
			}

			if(isset($bandeira) and is_object($bandeira)) {
				$vSQL="lixo=1";
				$vWHERE="where id=$bandeira->id";
				$sql->update($_p."parametros_cartoes_bandeiras",$vSQL,$vWHERE);

				$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vwhere='".addslashes($vWHERE)."',vsql='".addslashes($vSQL)."',tabela='".$_p."parametros_cartoes_bandeiras',id_reg='".$bandeira->id."'");

				$rtn=array('success'=>true);

			} else {
				$rtn=array('success'=>false,'error'=>'Bandeira não encontrada');
			}
		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}

	$campos=explode(",","titulo");
		
	foreach($campos as $v) $values[$v]='';

	$jsc = new Js();
	$bandeira='';
	if(isset($_GET['id_bandeira']) and is_numeric($_GET['id_bandeira'])) {
		$sql->consult($_p."parametros_cartoes_bandeiras","*","where id='".$_GET['id_bandeira']."'");
		if($sql->rows) {
			$bandeira=mysqli_fetch_object($sql->mysqry);

			foreach($campos as $v) {
				$values[$v]=utf8_encode($bandeira->$v);
			}
		}
	}
?>
<script>
	var id_bandeira = '<?php echo is_object($bandeira)?$bandeira->id:'';?>';
	$(function(){
		$('.js-remover').click(function(){

			swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   
				if (isConfirm) { 

					let data = `ajax=bandeiraRemover&id_bandeira=${id_bandeira}`;   
					$.ajax({
						type:"POST",
						url:'box/boxBandeiraCartao.php',
						data:data,
						success:function(rtn){
							swal.close();  
							if(rtn.success) {
								$.fancybox.close();
							} else if(rtn.error) {
								swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
							} else {
								swal({title: "Erro!", text: "Bandeira não removida. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
							}
						},
						error:function(){
							swal.close();  
							swal({title: "Erro!", text: "Bandeira não removida. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
						}
					})
				} else {   
					swal.close();   
				} 
			});
		});
		$('.js-salvar').click(function(){

			let erro=false;
			$('form .obg').each(function(index,elem){
				if($(this).attr('name')!==undefined && $(this).val().length==0) {
					$(elem).addClass('erro');
					erro=true;
				}
			});

			if(erro===true) {
				swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
				
			} else {
				
				let campos = $('form.js-form-bandeira').serialize();
				let data = `ajax=bandeiraPersistir&id_bandeira=${id_bandeira}&${campos}`;

				$.ajax({
					type:'POST',
					url:'box/boxBandeiraCartao.php',
					data:data,
					success:function(rtn) {
						if(rtn.success) {
							$.fancybox.close();
						} else if(rtn.error) {
							swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
						} else {
							swal({title: "Erro!", text: "Bandeira não salva. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
						}
					},
					error:function(){
						swal({title: "Erro!", text: "Bandeira não salva. Por favor tente novamente!", type:"error", confirmButtonColor: "#424242"});
					}
				})

			}
			return false;
		});
	});
</script>
<section class="modal" style="height:auto; width:950px;">

	<header class="modal-header">
		<div class="filtros">

			<?php
				if(empty($bandeira)) {
			?>
			<h1 class="filtros__titulo"></h1>
			<div class="filtros-acoes filter-button">
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
			</div>
			<?php
				} else {
			?>
			<h1 class="filtros__titulo">Editar</h1>
			
			<div class="filtros-acoes filter-button">
				<a href="javascript:;" class="js-remover"><i class="iconify" data-icon="bx-bx-trash"></i></a>
				<a href="javascript:;" class="azul js-salvar"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
			</div>
			<?php
				}
			?>
		
		</div>
	</header>
	
	<article class="modal-conteudo">

		<form method="post" class="form js-form-bandeira">
			<fieldset>
				<legend>Informações</legend>
				<div>
					<dl>
						<dt>Bandeira</dt>
						<dd>
							<input type="text" name="titulo" value="<?php echo $values['titulo'];?>"  class="obg" />
						</dd>
					</dl>
				</div>
			</fieldset>
				
		</form>
	</article>

</section>