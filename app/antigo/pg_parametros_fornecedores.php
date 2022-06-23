<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}

	$values=$adm->get($_GET);

	$_table=$_p."parametros_fornecedores";
	$_page=basename($_SERVER['PHP_SELF']);

	$_bancos=array();
	$sql->consult($_p."parametros_bancos","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_bancos[$x->id]=$x;
	}
?>

<section class="content">

	<?php
	require_once("includes/abaConfiguracoes.php");
	?>

	
	<?php
	if(isset($_GET['form'])) {
		$cnt='';
		$campos=explode(",","tipo,tipo_pessoa,nome,cpf,razao_social,cnpj,responsavel,telefone1,telefone2,email,cep,logradouro,numero,complemento,bairro,id_cidade,estado");
		
		foreach($campos as $v) $values[$v]='';
		$values['tipo_pessoa']='PF';
		
		if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
			$sql->consult($_table,"*","where id='".$_GET['edita']."'");
			if($sql->rows) {
				$cnt=mysqli_fetch_object($sql->mysqry);
				
				$values=$adm->values($campos,$cnt);
			} else {
				$jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
				die();
			}
		}

		if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
			$vSQL=$adm->vSQL($campos,$_POST);
			$values=$adm->values;
			$processa=true;

			$dadosBancario=utf8_decode($_POST['dados_bancario']);
			$vSQL.="dados_bancario='".$dadosBancario."',";
			//echo $vSQL;die();
			if($processa===true) {	
				if(is_object($cnt)) {
					$vSQL=substr($vSQL,0,strlen($vSQL)-1);
					$vWHERE="where id='".$cnt->id."'";
					$sql->update($_table,$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
					$id_reg=$cnt->id;
				} else {
					$vSQL=substr($vSQL,0,strlen($vSQL)-1);
					//echo $vSQL;die();
					$sql->add($_table,$vSQL);
					$id_reg=$sql->ulid;
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");


					$id_procedimento=$id_reg;
					
				}

				$msgErro='';
				if(!empty($msgErro)) {
					$jsc->jAlert($msgErro,"erro","");
				} else {
					$jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
					die();
				}
			}
		}
	?>
	<script type="text/javascript">

		var _cidade='';
		var _cidadeID='<?php echo empty($values['id_cidade'])?0:$values['id_cidade'];?>';
		const tipoPessoa = () => {
			let tipo_pessoa = $('input[name=tipo_pessoa]:checked').val();
			if(tipo_pessoa=="PJ") {
				$('.js-box-pf').hide().find('input').removeClass('obg');
				$('.js-box-pj').show().find('input').addClass('obg');;
			} else {
				$('.js-box-pf').show().find('input').addClass('obg');;
				$('.js-box-pj').hide().find('input').removeClass('obg');;
			}
		}
		$(function(){
			$('input[name=tipo_pessoa]').click(function(){tipoPessoa()});
			tipoPessoa();
		});
	</script>

	<div class="filtros">
		<h1 class="filtros__titulo">Fornecedores</h1>
		<div class="filtros-acoes">
			<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
			<?php
			if(is_object($cnt)) {
			?>		
			<a data-fancybox data-type="ajax" data-src="ajax/log.php?table=<?php echo $_table;?>&id=<?php echo $cnt->id;?>" href="javascript:;"><i class="iconify" data-icon="bx-bx-history"></i></a>
			<?php	
			}
			?>
			<a href="javascript:;" class="principal btn-submit"><i class="iconify" data-icon="bx-bx-check"></i></a>
			<?php if(is_object($cnt) and $usr->tipo=="admin") { ?>
			<a class="sec js-deletar" href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>"><i class="iconify" data-icon="bx-bx-trash"></i></a>
			<?php } ?>
		</div>
	</div>

	<section class="grid">
		<div class="box">
			<form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
				
				<input type="hidden" name="acao" value="wlib" />
				</fieldset>
				<fieldset>
					<legend>Dados do Fornecedor</legend>

					<div class="colunas7">
						<dl class="dl2">
							<dt>Tipo</dt>
							<dd>
								<select name="tipo" class="obg">
									<option value="">-</option>
									<?php
									foreach($_tiposFornecedores as $k=>$v) echo '<option value="'.$k.'"'.($values['tipo']==$k?' selected':'').'>'.$v.'</option>';
									?>
								</select>
							</dd>
						</dl>

						<dl class="dl2">
							<dd>
								<label><input type="radio" name="tipo_pessoa" value="PJ"<?php echo $values['tipo_pessoa']=="PJ"?" checked":"";?> /> Pessoa Jurídica</label>
								<label><input type="radio" name="tipo_pessoa" value="PF"<?php echo $values['tipo_pessoa']=="PF"?" checked":"";?> /> Pessoa Física</label>
							</dd>
						</dl>
					</div>
						
					<div class="colunas4 js-box-pf">
						<dl class="dl2">
							<dt>Nome</dt>
							<dd><input type="text" name="nome" value="<?php echo $values['nome'];?>" /></dd>
						</dl>
						<dl>
							<dt>CPF</dt>
							<dd><input type="text" name="cpf" value="<?php echo $values['cpf'];?>" class="cpf" /></dd>
						</dl>
					</div>

					<div class="colunas4 js-box-pj">
						<dl class="dl2">
							<dt>Razão Social</dt>
							<dd><input type="text" name="razao_social" value="<?php echo $values['razao_social'];?>" /></dd>
						</dl>
						<dl>
							<dt>CNPJ</dt>
							<dd><input type="text" name="cnpj" value="<?php echo $values['cnpj'];?>" class="cnpj" /></dd>
						</dl>
						<dl>
							<dt>Responsável</dt>
							<dd><input type="text" name="responsavel" value="<?php echo $values['responsavel'];?>" /></dd>
						</dl>
					</div>
				
				</fieldset>

				<fieldset>
					<legend>Dados de Contato</legend>

					<div class="colunas4">
						<dl>
							<dt>Telefone 1</dt>
							<dd><input type="text" name="telefone1" value="<?php echo $values['telefone1'];?>" class="celular obg" /></dd>
						</dl>
						<dl>
							<dt>Telefone 2</dt>
							<dd><input type="text" name="telefone2" value="<?php echo $values['telefone2'];?>" class="celular" /></dd>
						</dl>
						<dl class="dl2">
							<dt>E-mail</dt>
							<dd><input type="text" name="email" value="<?php echo $values['email'];?>" class="noupper" /></dd>
						</dl>
					</div>
				</fieldset>

				<fieldset>
					<legend>Dados de Endereço</legend>
					<div class="colunas5">
						<dl>
							<dt>CEP</dt>
							<dd><input type="text" name="cep" value="<?php echo $values['cep'];?>" class="cep" /></dd>
						</dl>
						<dl>
							<dt>&nbsp;</dt>
							<dd><a href="javascript:;" class="botao js-cep"><i class="icon-search"></i></a></dd>
						</dl>
					</div>
					<div class="colunas4">
						<dl class="dl2">
							<dt>Logradouro</dt>
							<dd><input type="text" name="logradouro" value="<?php echo $values['logradouro'];?>" class="" /></dd>
						</dl>
						<dl>
							<dt>Número</dt>
							<dd><input type="text" name="numero" value="<?php echo $values['numero'];?>" class="" /></dd>
						</dl>
						<dl>
							<dt>Complemento</dt>
							<dd><input type="text" name="complemento" value="<?php echo $values['complemento'];?>" class="" /></dd>
						</dl>
					</div>
					<div class="colunas4">
						<dl>
							<dt>Bairro</dt>
							<dd><input type="text" name="bairro" value="<?php echo $values['bairro'];?>" class="" /></dd>
						</dl>
						<dl>
							<dt>Estado</dt>
							<dd>
								<?php $inEstado=strtoupperWLIB($values['estado']);?><select name="estado" class="js-estado"><option value="">SELECIONE</option><option value="AC"<?php echo $inEstado=="AC"?" selected":"";?>>ACRE</option><option value="AL"<?php echo $inEstado=="AL"?" selected":"";?>>ALAGOAS</option><option value="AM"<?php echo $inEstado=="AM"?" selected":"";?>>AMAZONAS</option><option value="AP"<?php echo $inEstado=="AP"?" selected":"";?>>AMAPÁ</option><option value="BA"<?php echo $inEstado=="BA"?" selected":"";?>>BAHIA</option><option value="CE"<?php echo $inEstado=="CE"?" selected":"";?>>CEARÁ</option><option value="DF"<?php echo $inEstado=="DF"?" selected":"";?>>DISTRITO FEDERAL</option><option value="ES"<?php echo $inEstado=="ES"?" selected":"";?>>ESPÍRITO SANTO</option><option value="GO"<?php echo $inEstado=="GO"?" selected":"";?>>GOIÁS</option><option value="MA"<?php echo $inEstado=="MA"?" selected":"";?>>MARANHÃO</option><option value="MT"<?php echo $inEstado=="MT"?" selected":"";?>>MATO GROSSO</option><option value="MS"<?php echo $inEstado=="MS"?" selected":"";?>>MATO GROSSO DO SUL</option><option value="MG"<?php echo $inEstado=="MG"?" selected":"";?>>MINAS GERAIS</option><option value="PA"<?php echo $inEstado=="PA"?" selected":"";?>>PARÁ</option><option value="PB"<?php echo $inEstado=="PB"?" selected":"";?>>PARAÍBA</option><option value="PR"<?php echo $inEstado=="PR"?" selected":"";?>>PARANÁ</option><option value="PE"<?php echo $inEstado=="PE"?" selected":"";?>>PERNANBUMCO</option><option value="PI"<?php echo $inEstado=="PI"?" selected":"";?>>PIAUÍ</option><option value="RJ"<?php echo $inEstado=="RJ"?" selected":"";?>>RIO DE JANEIRO</option><option value="RN"<?php echo $inEstado=="RN"?" selected":"";?>>RIO GRANDE DO NORTE</option><option value="RO"<?php echo $inEstado=="RO"?" selected":"";?>>RONDÔNIA</option><option value="RS"<?php echo $inEstado=="RS"?" selected":"";?>>RIO GRANDE DO SUL</option><option value="RR"<?php echo $inEstado=="RR"?" selected":"";?>>RORAIMA</option><option value="SC"<?php echo $inEstado=="SC"?" selected":"";?>>SANTA CATARINA</option><option value="SE"<?php echo $inEstado=="SE"?" selected":"";?>>SERGIPE</option><option value="SP"<?php echo $inEstado=="SP"?" selected":"";?>>SÃO PAULO</option><option value="TO"<?php echo $inEstado=="TO"?" selected":"";?>>TOCANTINS</option></select>
							</dd>
						</dl>
						<dl>
							<dt>Cidade</dt>
							<dd>
								<select name="id_cidade" class="js-cidade">
									<option value="">-</option>
								</select>
							</dd>
						</dl>
					</div>
				</fieldset>

				<fieldset>
					<legend>Dados Bancários</legend>

					<input type="hidden" name="dados_bancario" value="<?php echo isset($values['dados_bancario'])?$values['dados_bancario']:'';?>" />
					<script>
						var dadosBancario = [];

						const dadosBancarioRemover = (index) => {
							dadosBancario.splice(index,1);
							dadosBancarioListar();
						};
						const dadosBancarioListar = () => {
							$('table.js-dadosBancario .js-tr').remove();

							html = `<tr class="js-tr">
										<td class="js-tr-banco"></td>
										<td class="js-tr-agencia"></td>
										<td class="js-tr-conta"></td>
										<td>
											<a href="javascript:;" class="js-tr-deleta registros__acao registros__acao_sec"><i class="iconify" data-icon="bx:bxs-trash"></i></a>
										</td>
									</tr>`;

							dadosBancario.forEach(x => {
								$('table.js-dadosBancario').append(html);

								$('table.js-dadosBancario .js-tr-banco:last').html(x.banco);
								$('table.js-dadosBancario .js-tr-agencia:last').html(x.agencia);
								$('table.js-dadosBancario .js-tr-conta:last').html(x.conta);
								$('table.js-dadosBancario .js-tr-deleta:last').click(function() {
									let index = $(this).index('table.js-dadosBancario .js-tr-deleta');
									swal({   title: "Atenção",   text: "Você tem certeza que deseja remover este registro?",   type: "warning",   showCancelButton: true,   confirmButtonColor: "#DD6B55",   confirmButtonText: "Sim!",   cancelButtonText: "Não",   closeOnConfirm: false,   closeOnCancel: false }, function(isConfirm){   if (isConfirm) {  dadosBancarioRemover(index); swal.close();   } else {   swal.close();   } });
								});
							});

							let json = JSON.stringify(dadosBancario);
							$('[name=dados_bancario]').val(json);
						}

						$(function(){
							<?php
							if(is_object($cnt) and !empty($cnt->dados_bancario)) {
								echo "dadosBancario=JSON.parse('".utf8_encode($cnt->dados_bancario)."');";
								echo "dadosBancarioListar();";
							} 
							?>
							

	      					$('.js-btn-add').click(function(){
	      						let banco = $('select.js-inpt-banco option:selected').text();
	      						let id_banco = $('select.js-inpt-banco').val();
	      						let agencia = $('input.js-inpt-agencia').val();
	      						let conta = $('input.js-inpt-conta').val();
	      						
	      						if(id_banco.length==0) {
	      							swal({title: "Erro!", text: "Selecione o banco!", type:"error", confirmButtonColor: "#424242"});
	      							$('select.js-inpt-banco').addClass('erro');
	      						} else if(agencia.length==0) {
	      							swal({title: "Erro!", text: "Informe a Agência", type:"error", confirmButtonColor: "#424242"});
	      							$('input.js-inpt-agencia').addClass('erro');
	      						} else if(conta.length==0) {
	      							swal({title: "Erro!", text: "Informe a Conta", type:"error", confirmButtonColor: "#424242"});
	      							$('input.js-inpt-conta').addClass('erro');
	      						} else {
	      							let item = {};
	      							item.banco = banco;
	      							item.id_banco = id_banco;
	      							item.agencia = agencia;
	      							item.conta = conta;
	      							dadosBancario.push(item);
	      							dadosBancarioListar();
	      							$('select.js-inpt-banco option:selected').prop('selected',false);
	      							$('input.js-inpt-agencia,input.js-inpt-conta').val('');
	      						}

	      					});
						});
					</script>	
					<div class="colunas4">
						<dl>
							<dt>Banco</dt>
							<dd>
								<select class="js-inpt-banco">
									<option value=""></option>
									<?php
									foreach($_bancos as $v) {
										echo '<option value="'.$v->id.'" data-titulo="'.utf8_encode($v->titulo).'">'.utf8_encode($v->titulo).'</option>';
									}
									?>
								</select>	
							</dd>
						</dl>
						<dl>
							<dt>Agência</dt>
							<dd><input type="text" class="js-inpt-agencia" maxlength="" /></dd>
						</dl>
						<dl>
							<dt>Conta</dt>
							<dd><input type="text" class="js-inpt-conta" maxlength="" /></dd>
						</dl>
						<dl>
							<dt>&nbsp;</dt>
							<dd><a href="javascript:;" class="button button__sec js-btn-add"><i class="iconify" data-icon="bx-bx-check"></i></a></dd>
						</dl>	
					</div>
					<div class="registros">
						<table class="js-dadosBancario">
							<tr>
								<th>Banco</th>
								<th>Agência</th>
								<th>Conta</th>
								<th style="width:20px;"></th>
							</tr>

						</table>
					</div>
				</fieldset>
			</form>
		</div>
	</section>

	<?php
	} else {
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
	
	<section class="filtros">
		<h1 class="filtros__titulo">Fornecedores</h1>
		<form method="get" class="filtros-form">
			<input type="hidden" name="csv" value="0" />
			<dl>
				<dt>Tipo</dt>
				<dd>
					<select name="id_especialidade">
						<option value="">-</option>
						<?php
						foreach($_especialidades as $v) echo '<option value="'.$v->id.'"'.($values['id_especialidade']==$v->id?' selected':'').'>'.utf8_encode($v->titulo).'</option>';
						?>
					</select>
				</dd>
			</dl>
			<button type="submit" class="filtros-form__button"><i class="iconify" data-icon="bx-bx-search"></i></button>
		</form>
		<div class="filtros-acoes">
			<a href="<?php echo $_page."?form=1&$url";?>" data-padding="0" class="adicionar tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>
		</div>
	</section>

	<section class="grid">
		<div class="box">

			<div class="registros-qtd">
				<p class="registros-qtd__item"><?php echo $sql->rows;?> registros</p>
			</div>
			<div class="registros">
				
				<table class="tablesorter">
					<thead>
						<tr>
							<th>Fornecedor</th>
							<th>Telefone 1</th>
						</tr>
					</thead>
					<tbody>
					<?php
					while($x=mysqli_fetch_object($sql->mysqry)) {
					?>
					<tr onclick="document.location.href='<?php echo $_page;?>?form=1&edita=<?php echo $x->id."&".$url;?>'">
						<td>
							<?php
							if($x->tipo_pessoa=="PJ") echo utf8_encode($x->razao_social)."<br />".$x->cnpj;
							else echo utf8_encode($x->nome)."<br />".$x->cpf;
							?>
						</td>
						<td><?php echo utf8_encode($x->telefone1);?></td>						
					</tr>
					<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	</section>
				
	<?php
	}
	?>

</section>

<?php
	include "includes/footer.php";
?>