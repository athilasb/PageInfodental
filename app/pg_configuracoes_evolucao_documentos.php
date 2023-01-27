<?php
	require_once("lib/conf.php");
	require_once("usuarios/checa.php");

	$_table=$_p."parametros_documentos";
	$_page=basename($_SERVER['PHP_SELF']);

	include "includes/header.php";
	include "includes/nav.php";

	$values=$adm->get($_GET);
	$campos=explode(",","titulo,texto");

	foreach($campos as $v) $values[$v]='';

?>

	<header class="header">
		<div class="header__content content">
			<div class="header__inner1">
				<section class="header-title">
					<h1>Configuração</h1>
				</section>
				<?php
				require_once("includes/menus/menuConfiguracoes.php");
				?>
			</div>
		</div>
	</header>

	<main class="main">
		<div class="main__content content">

			<section class="filter">
				
				<div class="filter-group">
					<div class="filter-title">
						<h1>Configure os modelos de documentos</h1>
					</div>
				</div>
			</section>
 	
			<section class="grid">

				<div class="box box-col">

					<?php
					require_once("includes/submenus/subConfiguracoesEvolucao.php");
					?>

					<div class="box-col__inner1">
					

					<?php
					if(isset($_GET['form'])) {

						$cnt='';
						if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
							$sql->consult($_table,"*","where id=".$_GET['edita']." and lixo=0");
							if($sql->rows) {
								$cnt=mysqli_fetch_object($sql->mysqry);


								$values=$adm->values($campos,$cnt);
							}
						}

						if(is_object($cnt) and isset($_GET['deleta']) and is_numeric($_GET['deleta'])) {
							$vSQL="lixo=1";
							$vWHERE="where id=".$cnt->id;

							$sql->update($_table,$vSQL,$vWHERE);
							$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='$_table',id_reg='$cnt->id'");


							$jsc->go($_page."?$url");
							die();

						}

						if(isset($_POST['acao'])) {
							$vSQL=$adm->vSQL($campos,$_POST);
							$values=$adm->values;

							if(is_object($cnt)) {
								$vWHERE="where id=$cnt->id";
								$vSQL=substr($vSQL,0,strlen($vSQL)-1);
								$sql->update($_table,$vSQL,$vWHERE);
								$id_reg=$cnt->id;
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='$_table',id_reg='$id_reg'");
							} else {
								$vSQL=substr($vSQL,0,strlen($vSQL)-1);
								$sql->add($_table,$vSQL);
								$id_reg=$sql->ulid;
								$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',vwhere='',tabela='$_table',id_reg='$id_reg'");
							}

							$jsc->go($_page."?$url");
							die();
						}
					?>
					<section class="filter">
				
						<div class="filter-group">
						</div>

						<div class="filter-group">
							<div class="filter-form form">
								<dl>
									<dd><a href="<?php echo $_page."?".$url;?>" class="button"><i class="iconify" data-icon="fluent:arrow-left-24-regular"></i></a></dd>
								</dl>
								<?php
								if(is_object($cnt)) {
								?>
								<dl>
									<dd><a href="<?php echo $_page."?form=1&edita=".$cnt->id."&deleta=1";?>" class="button js-confirmarDeletar"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a></dd>
								</dl>
								<?php
								}
								?>
								<dl>
									<dd><a href="javascript:;" class="button button_main js-submit" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i><span>Salvar</span></a></dd>
								</dl>
							</div>
						</div>
						
					</section>

					<script type="text/javascript">
						$(function(){
							var fck_texto = CKEDITOR.replace('js-texto',{
																		language: 'pt-br',
																		width:'100%',
																		height:500,
																		
																		});
						})
					</script>
					<section class="grid">

						<form method="post" class="form formulario-validacao">
							<input type="hidden"  name="acao" value="wlib" />
							<button style="display:none;"></button>

							<fieldset>
								<legend>Modelo de Documento</legend>
									<dl>
										<dt>Título</dt>
										<dd><input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg" /></dd>
									</dl>
									<dl>
										<dt>Texto</dt>
										<dd>
											<textarea name="texto" id="js-texto"><?php echo $values['texto'];?></textarea>
										</dd>
									</dl>
							</fieldset>

							<fieldset class="box-registros">
								<legend>Palavra Chaves</legend>

								<table class="table">
									<tr>
										<th style="width:200px;">Palavra Chave</th>
										<th>Descrição</th>
									</tr>
									<tr>
										<td>[nome]</td>
										<td>Nome do paciente</td>
									</tr>
									<tr>
										<td>[cpf]</td>
										<td>CPF do paciente</td>
									</tr>
									<tr>
										<td>[endereco]</td>
										<td>Endereço do paciente</td>
									</tr>
									<tr>
										<td>[dados_paciente]</td>
										<td>{Nome}, brasileiro, {Estado Civil}, inscrito no CPF de nº {CPF} e RG de n°. {RG} {RG Orgão Emissor}, com telefone de n° {Telefone 1} e email: {E-mail}, residente e domiciliado à {Endereço}</td>
									</tr>
									<tr>
										<td>[clinica_nome]</td>
										<td>Nome da Clínica</td>
									</tr>
									<tr>
										<td>[clinica_endereco]</td>
										<td>Endereço da Clínica</td>
									</tr>
									<tr>
										<td>[data]</td>
										<td>dd/mm/AAAA</td>
									</tr>
									<tr>
										<td>[data_leitura]</td>
										<td>{Dia da Semana}, {Dia do Mês} de {Mês Por Extenso} de {Ano}</td>
									</tr>
									<tr>
										<td>[procedimentos]</td>
										<td>Tabela de procedimentos aprovados do plano de tratamento selecionado</td>
									</tr>
									<tr>
										<td>[procedimentos_valor]</td>
										<td>Valor total dos procedimentos aprovados do plano de tratamento selecionado</td>
									</tr>
									<tr>
										<td>[procedimentos_tempo_estimado]</td>
										<td>Prazo estimado do plano de tratamento selecionado</td>
									</tr>
									<tr>
										<td>[pagamentos]</td>
										<td>Formas de pagamentos (vencimento, valor) do plano de tratamento selecionado</td>
									</tr>
								</table>
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
										<dd><a href="<?php echo $_page."?form=1&".$url;?>" class="button button_main js-openAside"><i class="iconify" data-icon="fluent:add-circle-24-regular"></i> <span>Novo Modelo</span></a></dd>
									</dl>
								</div>								
							</div>
							<form method="get" class="js-filtro">
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd class="form-comp form-comp_pos"><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:"";?>" placeholder="Buscar..." /><a href="javascript:;" onclick="$('form.js-filtro').submit();"><i class="iconify" data-icon="fluent:search-12-filled"></i></a></dd>
										</dl>
									</div>
								</div>
							</form>					
						</section>

					<?php
						
						$where="where lixo=0";
						if(isset($values['busca']) and !empty($values['busca'])) {
							$where.=" and titulo like '%".$values['busca']."%'";
						}
						$sql->consultPagMto2($_table,"*",10,$where." order by titulo asc","",15,"pagina",$_page."?".$url."&pagina=");
						if($sql->rows==0) {
							if(isset($values['busca'])) $msg="Nenhum Resultado encontrado";
							else $msg="Nenhum modelo de documento cadastrado";

							echo "<center>$msg</center>";
						} else {
						?>	
							<div class="list1">
								<table>
									<?php
									while($x=mysqli_fetch_object($sql->mysqry)) {
									?>
									<tr>
										<td><a href="<?php echo $_page."?form=1&edita=".$x->id."&".$url;?>"><h1><strong><?php echo utf8_encode($x->titulo);?></strong></h1></a></td>
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
					}
					?>

					</div>					
				</div>

			</section>
		
		</div>
	</main>

	
<?php 

	require_once("includes/footer.php");
?>	