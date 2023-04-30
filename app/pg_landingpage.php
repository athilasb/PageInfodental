<?php
	if(isset($_POST['ajax'])) {

		require_once("lib/conf.php");
		require_once("usuarios/checa.php");

		$rtn = array();
		if($_POST['ajax']=="verificaCode") {

			$sql->consult($_p."landingpage_temas","*","WHERE code='".addslashes($_POST['code'])."' and lixo=0");
			if($sql->rows==0) {
				$rtn=array('success'=>true);
			} else {
				$rtn=array('success'=>false,'error'=>'Já existe tema com o endereço '.addslashes($_POST['code']).'');
			}
		} 

		header("Content-type: application/json");
		echo json_encode($rtn);

		die();
	}
	include "includes/header.php";
	include "includes/nav.php";
    if($usr->tipo!="admin" and !in_array("landingpage",$_usuariosPermissoes)) {
        $jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
        die();
    }

	$_table=$_p."landingpage_temas";
	$cnt="";

	$campos=explode(",","titulo,code,cor_primaria,cor_secundaria,codigo_head,codigo_body");
	
	foreach($campos as $v) $values[$v]='';
	$values['code']= '';
?>

	<header class="header">
		<div class="header__content content">
			<div class="header__inner1">
				<section class="header-title">
					<h1>Landing Pages</h1>
				</section>
			</div>
		</div>
	</header>
	
	<script src="js/jquery.colorpicker.js"></script>
	<script type="text/javascript">
		
		$(function(){
			
			$('.js-openAside').click(function(){
				$('#js-aside form.formulario-validacao').trigger('reset');
				openAside(0);
			})
			$('.list1').on('click','.js-item',function(){
				$('#js-aside form.formulario-validacao').trigger('reset');
				let id = $(this).attr('data-id');
				document.location.href=`pg_landingpage_configuracao.php?id_landingpage=${id}`;
			})

			$('input[name=cor_primaria]').ColorPicker({
				color: '<?php echo $values['cor_primaria'];?>',
				onShow: function (colpkr) {$(colpkr).fadeIn(500);return false;},
				onHide: function (colpkr) {$(colpkr).fadeOut(500);return false;},
				onChange: function (hsb, hex, rgb) {console.log(hex);$('input[name=cor_primaria]').css('backgroundColor', '#' + hex).val('#'+hex);}
			});
			$('input[name=cor_secundaria]').ColorPicker({
				color: '<?php echo $values['cor_secundaria'];?>',
				onShow: function (colpkr) {$(colpkr).fadeIn(500);return false;},
				onHide: function (colpkr) {$(colpkr).fadeOut(500);return false;},
				onChange: function (hsb, hex, rgb) {console.log(hex);$('input[name=cor_secundaria]').css('backgroundColor', '#' + hex).val('#'+hex);}
			});
			$('input[name=cor_primaria]').css('backgroundColor','<?php echo $values['cor_primaria'];?>');
			$('input[name=cor_secundaria]').css('backgroundColor','<?php echo $values['cor_secundaria'];?>');
			var input = $('input[name=code]');

			input.bind('keypress', function(e)
			{
			    if (((e.which < 65 || e.which > 122) && (e.which < 48 || e.which > 57)) && e.which != 45)
			    {
			        e.preventDefault();
			    } 
			});
			<?php
			if(empty($cnt)) {
			?>
			$('input[name=titulo]').keyup(function(){
				let code = removerAcentos($(this).val().toLowerCase().split(' ').join('-'));
				code = code.replace(/[,!/&'".%$#@*()_=+^~`´;:]/g, '');
				code.toLowerCase().split(' ').join('-');
				$('input[name=code]').val(code);
			});
			<?php
			}
			?>

			$('.js-salvar').click(function(){
				let alerta = false;
				let titulo = $('input[name=titulo]').val();
				let code = $('input[name=code]').val();
				let cor_primaria = $('input[name=cor_primaria]').val();
				let cor_secundaria = $('input[name=cor_secundaria]').val();

				if(!titulo) {
					alerta=true;
					$('input[name=titulo]').addClass('erro');
				}

				if(!code) {
					alerta=true;
					$('input[name=code]').addClass('erro');
				}

				if(!cor_primaria) {
					alerta=true;
					$('input[name=cor_primaria]').addClass('erro');
				}

				if(!cor_secundaria) {
					alerta=true;
					$('input[name=cor_secundaria]').addClass('erro');
				}

				if(alerta) {
					swal({title: "Erro!", text: "Complete os campos destacados", type:"error", confirmButtonColor: "#424242"});
				} else {
					$.ajax({
						type:'POST',
						data:`ajax=verificaCode&code=${code}`,
						success:function(rtn) {
							if(rtn.success===false) {
								swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
							} else {
								$('.js-form').submit();
							}
						},
						error:function() {
							
						}
					});
				}
			});
		})
	</script>

	<main class="main">
		<div class="main__content content">

 	<?php
 	if(isset($_GET['form'])) {

 		if(isset($_POST['acao'])) {

 			$vSQL=$adm->vSQL($campos,$_POST);
 			$processa=true;
		
 			if($processa===true) {	

				if(is_object($cnt)) {
					$vSQL=substr($vSQL,0,strlen($vSQL)-1);
					$vWHERE="where id='".$cnt->id."'";
					$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
					$id_reg=$cnt->id;
				} else {
					$sql->add($_table,$vSQL."data=now(),id_usuario='".$usr->id."'");
					$id_reg=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");
				}

				$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='pg_landingpage_configuracao.php?id_landingpage=".$id_reg."&".$url."'");
				die();
			}

		}
		?>	
			<section class="filter">
				
				<div class="filter-group">
				</div>

				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="pg_landingpage.php" class="button"><i class="iconify" data-icon="fluent:arrow-left-24-regular"></i></a></dd>
						</dl>
						<dl>
							<dd><a href="javascript:;" class="button button_main js-salvar"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span>Salvar</span></a></dd>
						</dl>
					</div>
				</div>
				
			</section>
			<section class="grid">

				<form method="post" class="form formulario-validacao js-form">
					<input type="hidden"  name="acao" value="wlib" />

					<fieldset>
						<legend>Informações</legend>

						<div class="grid grid_2">

							<div style="grid-column:span 2">
								<div class="colunas">
									<dl>
										<dt>Tema</dt>
										<dd><input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" /></dd>
									</dl>
									<dl>
										<dt>URL do Tema</dt>
										<dd><input type="text" name="code" value="<?php echo $values['code'];?>" class="obg" /></dd>
									</dl>
								</div>
								<div class="colunas">
									<dl>
										<dt>Cor Primária</dt>
										<dd><input type="text" name="cor_primaria" value="<?php echo $values['cor_primaria'];?>" class="obg" /></dd>
									</dl>
									<dl>
										<dt>Cor Secundária</dt>
										<dd><input type="text" name="cor_secundaria" value="<?php echo $values['cor_secundaria'];?>" class="obg" /></dd>
									</dl>
								</div>
								<dl>
									<dt>Código de Rastreamento Body</dt>
									<dd>
										<textarea name="codigo_body" style="height: 200px;" class="noupper"><?php echo $values['codigo_body'];?></textarea>
									</dd>
								</dl>
								<dl>
									<dt>Código de Rastreamento Head</dt>
									<dd>
										<textarea name="codigo_head" style="height: 200px;" class="noupper"><?php echo $values['codigo_head'];?></textarea>
									</dd>
								</dl>
							</div>
						</div>
					</fieldset>
		</form>

			</section>
		<?php
 	} else {

	?>		
 			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-form form">
						<dl>
							<dd><a href="pg_landingpage.php?form=1" class="button button_main"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Nova Landing Page</span></a></dd>
						</dl>
					</div>
				</div>

				<form method="get" class="js-filtro">
					<div class="filter-group">
						<div class="filter-form form">
							<script type="text/javascript">
								$(function(){
									$('input[name=busca]').keydown(function(e){
										if(e.which==13) {
											$('.js-btn-buscar').click();
										}
									});
								})
							</script>
							<dl>
								<dd class="form-comp form-comp_pos"><input type="text" name="busca" placeholder="Buscar..." value="<?php echo isset($_GET['busca'])?($_GET['busca']):"";?>" /><a href="javascript:;" class="js-btn-buscar" onclick="$('form.js-filtro').submit();"><i class="iconify" data-icon="fluent:search-12-filled"></i></a></dd>
							</dl>
						</div>					
					</div>
				</form>

			</section>

			<section class="grid" style="grid-template-columns:100% auto">


				<div class="box">

					<?php
					# LISTAGEM #

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
						$where="where (($wh) or titulo like '%".$_GET['busca']."%') and lixo=0";
					}

					$where.=" order by data desc";

					//echo $where;
					$sql->consultPagMto2($_table,"*",10,$where,"",15,"pagina",$_page."?".$url."&pagina=");
					if($sql->rows==0) {
						if(isset($values['busca'])) $msg="Nenhum Resultado encontrado";
						else $msg="Nenhum lading page cadastrada";

						echo "<center>$msg</center>";
					} else {
					?>	
						<div class="list1">
							<table>
								<?php
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$cor="var(--cinza3)";
								?>
								<tr class="js-item" data-id="<?php echo $x->id;?>">
									<td><h1><?php echo utf8_encode($x->titulo);?></h1></td>
								</tr>
								
								<?php
								}
								?>
							</table>
						</div>
						<?php
							if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
						?>
						
						<div class="pagination">						
							<?php echo $sql->myspaginacao;?>
						</div>
						<?php
						}
					}
					# LISTAGEM #
					?>

					
				</div>

			</section>
		<?php
	}
		?>
		
		</div>
	</main>

<?php
	$apiConfig=array('profissao'=>1);
	require_once("includes/api/apiAside.php");
	include "includes/footer.php";
?>	