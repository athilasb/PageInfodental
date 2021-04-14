<?php
	if(isset($_POST['ajax'])) {
		require_once("lib/conf.php");	
		require_once("usuarios/checa.php");

		$sql = new Mysql();

		$rtn = array();

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}


	$_laboratorios=array();
	$sql->consult($_p."parametros_fornecedores","*","where tipo='LABORATORIO' and lixo=0 order by razao_social asc, nome asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_laboratorios[$x->id]=$x;
	}

	$values=$adm->get($_GET);

	$_table=$_p."parametros_servicosdelaboratorio";
	$_page=basename($_SERVER['PHP_SELF']);


?>

<section class="content">

	<?php /*<header class="caminho">
		<h1 class="caminho__titulo">Configurações <i class="iconify" data-icon="bx-bx-chevron-right"></i> <strong>Procedimentos</strong></h1>
		<a href="javascript:;" class="caminho__tutorial button button__sec"><i class="iconify" data-icon="ic-baseline-slow-motion-video"></i> ASSISTIR TUTORIAL</a>
	</header>*/?>
	<?php
	require_once("includes/abaConfiguracoes.php");
	?>

		<section style="padding:2rem 0;">


			<section class="content__item">
			
				<?php
				if(isset($_GET['form'])) {
					$cnt='';
					$campos=explode(",","titulo,id_regiao");
					
					foreach($campos as $v) $values[$v]='';
					
					
					?>
					<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
						
						<input type="hidden" name="acao" value="wlib" />
						<fieldset>
							<legend>Dados do Serviço</legend>

							
							<div class="colunas4">
								<dl class="dl2">
									<dt>Nome do Serviço</dt>
									<dd>
										<input type="text" name="titulo" value="<?php echo $values['titulo'];?>"  class="obg" />
									</dd>
								</dl>
								<dl>
									<dt>Região</dt>
									<dd>
										<select name="id_regiao">
											<option value="">-</option>
											<?php
											foreach($_regioes as $v) {
												echo  '<option value="'.$v->id.'" data-face="'.$v->face.'" data-quantitativo="'.$v->quantitativo.'"'.($v->id==$values['id_regiao']?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
							</div>
						</fieldset>

						<fieldset>
							<legend>Serviços por Laboratório</legend>
						
							<input type="text" name="laboratorios" />
							<div class="colunas4">
								<dl>
									<dt>Laboratório</dt>
									<dd>
										<select class="js-id_laboratorio">
											<option value="">-</option>
											<?php
											foreach($_laboratorios as $p) {
												$laboratorioTitulo='';
												if($p->tipo_pessoa=='PJ') $laboratorioTitulo=utf8_encode($p->razao_social);
												else $laboratorioTitulo=utf8_encode($p->nome);
												echo '<option value="'.$p->id.'">'.$laboratorioTitulo.'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Valor</dt>
									<dd><input type="text" class="js-valor money" /></dd>
								</dl>
								
								<dl>
									<dt>&nbsp;</dt>
									<dd>
										<a href="javascript:;" class="button button__sec js-laboratorio-salvar"><i class="iconify" data-icon="bx-bx-check"></i></a>
										
										<a href="javascript:;" class="js-laboratorio-cancelar tooltip" style="display: none;color:red" title="Cancelar edição"><span class="iconify" data-icon="icons8:cancel"></span> cancelar edição</a>
									</dd>
								</dl>
							</div>
							<script type="text/javascript">
								var laboratorios = [];
								const laboratoriosListar = () => {
									$('.js-laboratorios-table tbody tr').remove();
									laboratorios.forEach(x => {
										$('.js-id_laboratorio').find(`option[value=${x.id_laboratorio}]`).remove();

										let tr = `<tr>
														<td>${x.laboratorio}</td>
														<td>${x.valor}</td>
														<td>
															<a href="javascript:;" data-id="${x.id}" class="js-editar registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a><a href="javascript:;" data-id="${x.id}" class="js-remover registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
														</td>
													</tr>`;
										$('.js-laboratorios-table tbody').append(tr);
										
									});

									$('input[name=laboratorios]').val(JSON.stringify(laboratorios));
								};
								const laboratoriosRemover = (index) => {

									let idPlano = planos[index].id_plano;
									let plano = planos[index].plano;

									$('.js-laboratorio-id_fornecedor').append(`<option value="${idPlano}">${plano}</option>`);
									planos.splice(index,1);
									
									planosListar();
								};
								
								const laboratorioAdicionar = () => {

									let id_laboratorio = $('select.js-id_laboratorio').val();
									let valor = $('input.js-valor').val();

									if(id_laboratorio.length==0) {
										swal({title: "Erro!", text: "Selecione o Laboratório!", type:"error", confirmButtonColor: "#424242"});
									} else if(valor.length==0) {
										swal({title: "Erro!", text: "Digite o Valor!", type:"error", confirmButtonColor: "#424242"});
									}  else {

										let laboratorio = $('select.js-id_laboratorio option:selected').text();
										let item = {};
										item.id_laboratorio=id_laboratorio;
										item.laboratorio=laboratorio;
										item.valor=valor;


										
										laboratoriosListar();
										
										$('select.js-id_laboratorio').val('');
										$('input.js-valor').val('');

									}
								}; 	
								$(function(){

									
									$('input.money').maskMoney({symbol:'', allowZero:false, showSymbol:true, thousands:'.', decimal:',', symbolStay: true});
									planosListar();

									$('.js-laboratorio-salvar').click(function(){
										fornecedorAdicionar();
									});

									$('.js-planos-table').on('click','.js-remover',function(){
										let obj = $(this);
										swal({
											title: "Atenção",
											text: "Você tem certeza que deseja remover este registro?",
											type: "warning",
											showCancelButton: true,
											confirmButtonColor: "#DD6B55",
											confirmButtonText: "Sim!",
											cancelButtonText: "Não",
											closeOnConfirm:false,
											closeOnCancel: false }, 
											function(isConfirm){   
												if (isConfirm) {   
													let index = $(obj).index('.js-planos-table .js-remover');
													planoRemover(index);
													swal.close(); 
												} else {   
													swal.close();   
												} 
											});
									});
									$('.js-laboratorio-cancelar').click(function(){
										$('select.js-laboratorio-id_fornecedor').find(`option:selected`).remove();
										$('select.js-laboratorio-id_fornecedor').val(``);
										$('input.js-laboratorio-valor').val(``);
										$('input.js-laboratorio-custo').val(``);
										$('input.js-laboratorio-comissionamento').val(``);
										$('input.js-laboratorio-obs').val(``);
										$('input.js-laboratorio-id').val(0);
										

										$(this).hide();
									});

									$('table.js-planos-table').on('click','.js-editar',function(){
										let index = $(this).index('table.js-planos-table .js-editar');
										planoEditar(index);
									});
								});
							</script>

							<div class="registros">
								<table class="tablesorter js-laboratorios-table">
									<thead>
										<tr>
											<th>Laboratório</th>
											<th>Valor</th>
											<th style="width:120px"></th>
										</tr>
									</thead>
									<tbody>
										
									</tbody>
								</table>
							</div>
						<?php	
						}
						?>
						</fieldset>	
					</form>

					<div class="acoes">
						<a href="<?php echo $_page."?".$url;?>" class="button button__lg button__ter"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						<?php
						if(is_object($cnt)) {
						?>
						
						<a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;" class="button button__lg button__ter"><span class="iconify" data-icon="fa:history" data-inline="false"></span> Logs</a>
						<?php	
						}
						?>
						<a href="javascript:;" class="button button__lg btn-submit"><i class="iconify" data-icon="bx-bx-check"></i> Salvar</a>			
					</div>
					<?php
				} else {
				

					?>
					
					<section class="filtros">
						<form method="get" class="filtros-form form">
							<input type="hidden" name="csv" value="0" />
							<div class="colunas4">
								<dl class="">
									<dt>Laboratório</dt>
									<dd>
										<select name="id_laboratorio">
											<option value="">-</option>
											<?php
											foreach($_laboratorios as $p) {
												$laboratorioTitulo='';
												if($p->tipo_pessoa=='PJ') $laboratorioTitulo=utf8_encode($p->razao_social);
												else $laboratorioTitulo=utf8_encode($p->nome);
												echo '<option value="'.$p->id.'">'.$laboratorioTitulo.'</option>';
											}
											?>
										</select>
									</dd>
								</dl>
								<dl>		
									<dt>&nbsp;</dt>			
									<dd><button type="submit" class="button button__sec"><i class="iconify" data-icon="bx-bx-search" data-inline="false"></i></button></dd>
								</dl>
							</div>
						</form>
						<div class="filtros-acoes">
							<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="filtros-acoes__button tooltip" title="Adicionar"><i class="iconify" data-icon="ic-baseline-add"></i></a>
						</div>
					</section>

					<?php
					if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usr->tipo=="admin") {
						$vSQL="lixo='1'";
						$vWHERE="where id='".$_GET['deleta']."'";
						$sql->update($_table,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
						$jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
						die();
					}
					
					$where="WHERE lixo='0'";

					if(isset($values['id_especialidade']) and is_numeric($values['id_especialidade'])) $where.=" and id_especialidade='".$values['id_especialidade']."'";
					if(isset($values['id_subcategoria']) and is_numeric($values['id_subcategoria'])) $where.=" and id_subcategoria='".$values['id_subcategoria']."'";
					
					$sql->consult($_table,"*",$where." order by id");
					
					?>
					<div class="registros-qtd">
						<p class="registros-qtd__item"><?php echo $sql->rows;?> registros</p>
					</div>

					<div class="registros">

						<table class="tablesorter">
							<thead>
								<tr>
									<th>Nome do Serviço</th>
									<th style="width:270px;">Região</th>
									<th style="width:120px;">Ações</th>
								</tr>
							</thead>
							<tbody>
							<?php
							$registros=$procediemntosIDs=array();
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$registros[$x->id]=$x;
								$procediemntosIDs[]=$x->id;
							} 

							$_procedimentosPlanos=array();
							if(count($procediemntosIDs)>0) {
								$sql->consult($_table."_fornecedores","*","where id_servico IN (".implode(",",$procediemntosIDs).")");
								if($sql->rows) {
									while($x=mysqli_fetch_object($sql->mysqry)) {
										$_procedimentosPlanos[$x->id_servico][]=$x;
									}
								}
							}
				 
							foreach($registros as $x) {
							?>
							<tr>
								<td><?php echo utf8_encode($x->titulo);?></td>
								<td><?php echo isset($_regioes[$x->id_regiao])?utf8_encode($_regioes[$x->id_regiao]->titulo):"-";?></td>
								<td>
									<a href="<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>" class="registros__acao"><i class="iconify" data-icon="bx:bxs-edit-alt"></i></a>
									<?php if($usr->tipo=="admin") { ?><a href="<?php echo $_page;?>?deleta=<?php echo $x->id."&".$url;?>" class="registros__acao registros__acao_sec js-deletar"><i class="iconify" data-icon="bx:bxs-trash"></i></a><?php } ?>
								</td>
							</tr>
							<?php
							}
							?>
							</tbody>
						</table>
					</div>
				
				<?php
				}
				?>

				
			</section>
		</section>
	</section>
</section>

<?php
	include "includes/footer.php";
?>