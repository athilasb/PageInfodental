<?php
	include "includes/header.php";
	include "includes/nav.php";

	$_table=$_p."pacientes";
	$_page=basename($_SERVER['PHP_SELF']);

	$_width=400;
	$_height=400;
	$_dir="arqs/pacientes/";

	$_cidades=array();
	$sql->consult($_p."cidades","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) $_cidades[$x->id]=$x;

	$_profissoes=array();
	$sql->consult($_p."parametros_profissoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_profissoes[$x->id]=$x;
	}


	$_pacienteIndicacoes=array();
	$sql->consult($_p."parametros_indicacoes","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteIndicacoes[$x->id]=$x;
	}

	$_pacienteGrauDeParentesco=array();
	$sql->consult($_p."parametros_grauparentesco","*","where lixo=0 order by titulo asc");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_pacienteGrauDeParentesco[$x->id]=$x;
	}

	$paciente=$cnt='';
	if(isset($_GET['id_paciente']) and is_numeric($_GET['id_paciente'])) {
		$sql->consult($_p."pacientes","*","where id='".$_GET['id_paciente']."'");
		if($sql->rows) {
			$paciente=mysqli_fetch_object($sql->mysqry);
			$cnt=$paciente;
		}
	}

    $laboratorios = [];
    $sql->consult(
    $_p."parametros_fornecedores",
    "id,nome,tipo"," WHERE tipo ='LABORATORIO' AND lixo=0 ORDER BY nome ASC");
    while($x=mysqli_fetch_object($sql->mysqry)) {
        $laboratorios[$x->id]=$x;
    }

    $dentistas = [];
    $sql->consult(
        $_p."colaboradores",
        "id,nome"," WHERE tipo_cro ='CD' AND lixo=0 ORDER BY nome ASC");
    while($x=mysqli_fetch_object($sql->mysqry)) {
        $dentistas[]=$x;
    }

    $servicosLaboratorios = [];
    $idsLaboratorios = implode(',', array_keys($laboratorios));
    $sql->consult("
                {$_p}parametros_servicosdelaboratorio ips
                    INNER JOIN
                {$_p}parametros_servicosdelaboratorio_laboratorios ipsl
                ON
                  ips.id = ipsl.id_servicodelaboratorio
                INNER JOIN
                {$_p}parametros_fornecedores ipf
                ON
                   ipf.id = ipsl.id_fornecedor
        ",
        "ips.id, ips.titulo, ipsl.id_fornecedor"," WHERE ipf.id IN ({$idsLaboratorios}) AND ipf.lixo=0 ORDER BY ipf.nome ASC");
    while($x=mysqli_fetch_object($sql->mysqry)) {
        $x->titulo = utf8_encode($x->titulo);
        $servicosLaboratorios[]=$x;
    }

    $listaDeServicos = [];
    foreach ($servicosLaboratorios as $servico){
        if(in_array($servico->id_fornecedor, array_keys($laboratorios))){
            $listaDeServicos[$servico->id_fornecedor][] = $servico;
        }
    }

    function objectToArray ($object) {
    if(!is_object($object) && !is_array($object))
        return $object;

    return array_map('objectToArray', (array) $object);
}
//echo json_encode($listaDeServicos);
//var_dump(json_last_error());
//var_dump(json_last_error_msg());
   // var_dump($listaDeServicos);die;
//echo '<pre>';
//var_dump( array_keys($laboratorios), $listaDeServicos);die;

//SELECT
//*
//FROM
//ident_parametros_servicosdelaboratorio ips
//INNER JOIN
//ident_parametros_servicosdelaboratorio_laboratorios ipsl
//ON
//ips.id = ipsl.id_servicodelaboratorio
//INNER JOIN
//ident_parametros_fornecedores ipf
//ON
//ipf.id = ipsl.id_fornecedor
//WHERE
//ipf.id IN (
//    407
//    -- SELECT id FROM ident_parametros_fornecedores a WHERE a.tipo ='LABORATORIO' AND a.lixo = 0
//);

//
//    echo '<pre>';
//    var_dump($destistas);die;

	$campos=explode(",","nome,situacao,noem,sexo,foto,rg,rg_orgaoemissor,rg_estado,cpf,data_nascimento,profissao,estado_civil,telefone1,telefone1_whatsapp,telefone1_whatsapp_permissao,telefone2,email,instagram,instagram_naopossui,musica,indicacao_tipo,indicacao,cep,endereco,numero,complemento,bairro,estado,cidade,id_cidade,responsavel_possui,responsavel_nome,responsavel_sexo,responsavel_rg,responsavel_rg_orgaoemissor,responsavel_rg_estado,responsavel_datanascimento,responsavel_estadocivil,responsavel_cpf,responsavel_profissao,responsavel_grauparentesco,preferencia_contato");
	
	foreach($campos as $v) $values[$v]='';
	$values['data']=date('d/m/Y H:i');
	$values['sexo']='M';


	if(is_object($paciente)) {
		$values=$adm->values($campos,$cnt);
		$values['data']=date('d/m/Y H:i',strtotime($cnt->data));
	}

    $values = [];
    $values['titulo'] = 'Ordem de serviço X';
//var_dump($_GET);die;
//if(isset($_GET['form'])) {
//
//    $campos=explode(",","titulo");
//
//    foreach($campos as $v) $values[$v]='';
//    $values['procedimentos']="[]";
//    $values['pagamentos']="[]";
//
//    $sql->consult($_table,"id","where id_paciente=$paciente->id");
//    $values['titulo']="Plano de tratamento ".($sql->rows+1);
//
//    $cnt='';
//    if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
//        $sql->consult($_table,"*","where id='".$_GET['edita']."' and lixo=0");
//        if($sql->rows) {
//            $cnt=mysqli_fetch_object($sql->mysqry);
//            $values=$adm->values($campos,$cnt);
//
//            // Procedimentos
//            $procedimentos=array();
//            $where="where id_tratamento=$cnt->id and id_paciente=$paciente->id and id_unidade=$usrUnidade->id and lixo=0";
//            $sql->consult($_table."_procedimentos","*",$where);
//            while($x=mysqli_fetch_object($sql->mysqry)) {
//
//                $profissional=isset($_profissionais[$x->id_profissional])?$_profissionais[$x->id_profissional]:'';
//                $iniciaisCor='';
//                $iniciais='?';
//                if(is_object($profissional)) {
//                    $iniciais=$profissional->calendario_iniciais;
//
//                    $iniciaisCor=$profissional->calendario_cor;
//                }
//
//                $valor=$x->valorSemDesconto;
//                if($x->quantitativo==1) $valor*=$x->quantidade;
//
//                $procedimentos[]=array('id'=>$x->id,
//                    'id_procedimento'=>(int)$x->id_procedimento,
//                    'procedimento'=>utf8_encode($x->procedimento),
//                    'id_profissional'=>(int)$x->id_profissional,
//                    'profissional'=>utf8_encode($x->profissional),
//                    'id_plano'=>(int)$x->id_plano,
//                    'plano'=>utf8_encode($x->plano),
//                    'quantitativo'=>(int)$x->quantitativo,
//                    'quantidade'=>(int)$x->quantidade,
//                    'id_opcao'=>(int)$x->id_opcao,
//                    'opcao'=>utf8_encode($x->opcao),
//                    'valorCorrigido'=>(float)$x->valor,
//                    'valor'=>(float)$valor,
//                    'desconto'=>(float)$x->desconto,
//                    'obs'=>utf8_encode($x->obs),
//                    'situacao'=>$x->situacao,
//                    'iniciais'=>$iniciais,
//                    'iniciaisCor'=>$iniciaisCor);
//            }
//            if($cnt->status=="APROVADO") {
//                $values['procedimentos']=json_encode($procedimentos);
//            } else {
//                $values['procedimentos']=empty($cnt->procedimentos)?"[]":utf8_encode($cnt->procedimentos);
//            }
//
//            // Pagamentos
//            $pagamentos=array();
//            $where="where id_tratamento=$cnt->id and id_paciente=$paciente->id and id_unidade=$usrUnidade->id and lixo=0";
//            $sql->consult($_table."_pagamentos","*",$where);
//            while($x=mysqli_fetch_object($sql->mysqry)) {
//
//                $pagamentos[]=array('id'=>$x->id,
//                    'vencimento'=>date('d/m/Y',strtotime($x->data_vencimento)),
//                    'valor'=>(float)$x->valor);
//            }
//
//            if($cnt->status=="APROVADO") {
//                $values['pagamentos']=json_encode($pagamentos);
//            } else {
//                $values['pagamentos']=empty($cnt->pagamentos)?"[]":utf8_encode($cnt->pagamentos);
//            }
//
//            $values['pagamentos']=empty($cnt->pagamentos)?"[]":utf8_encode($cnt->pagamentos);
//
//        } else {
//            $jsc->jAlert("Plano de Tratamento não encontrado!","erro","document.location.href='$_page?$url'");
//            die();
//        }
//    }
	?>
	<section class="content">
		
		<?php
		require_once("includes/abaPaciente.php");
		?>

		<script type="text/javascript">
			$(function(){
				$('input[name=tipo]').click(function(){
					let tipo = $(this).val();

					$(`.js-box`).hide();
					$(`.js-box-${tipo}`).show();
				})
                const listServicos = JSON.parse('<?= json_encode($listaDeServicos) ?>');
                const procedimentoSelect = $('select[name="procedimento"]');
                const procedimentoOption = (value, label) => `<option value=${value}>${label}</option>`
                const resetSelectOptions = (el) => {
                    el.empty()
                    el.append(procedimentoOption('', 'Selecione uma procedimento'))
                }
                $('select[name="laboratorio"]').on('change',function(){
                    resetSelectOptions(procedimentoSelect)
                    $(listServicos[this.value]).each(function(idx, item){
                        procedimentoSelect.append(procedimentoOption(item.id, item.titulo))
                        procedimentoSelect.val('').trigger('chosen:updated');
                    })
                })
			});
		</script>

		<section class="grid">
			<div class="box">

				<?php
				
				$exibirEvolucaoNav=1;
				require_once("includes/evolucaoMenu.php");
				?>

				<div class="filter">
					<div class="filter-group">
						<div class="filter-button">
							<a href="<?php echo $_page."?".$url;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
						</div>
					</div>
					<div class="filter-group">
						<div class="filter-input">
							<input type="text" name="" placeholder="título do serviço" value="<?= $values['titulo'] ?>"/>
						</div>
					</div>
					<div class="filter-group">
						<div class="filter-input">
							<select name="laboratorio">
                                <option value="">laboratório</option>
                                <?php foreach($laboratorios as $laboratorio): ?>
                                    <option value="<?= $laboratorio->id ?>"> <?= utf8_encode($laboratorio->nome) ?> </option>
                                <?php endforeach; ?>
                            </select>
						</div>
					</div>
					<div class="filter-group">
						<div class="filter-input">
							<select name="">
                                <option value="">cirurgião dentista</option>
                                <?php foreach($dentistas as $dentista): ?>
                                    <option value="<?= $dentista->id ?>"> <?= utf8_encode($dentista->nome) ?> </option>
                                <?php endforeach; ?>
                            </select>
						</div>
					</div>
					<div class="filter-group filter-group_right">
						<div class="filter-data">
							<h1>Valor Total</h1>
							<h2>R$ 3.540,00</h2>
						</div>					
					</div>		
					<div class="filter-group">
						<div class="filter-button">
							<a href="javascript:;"><i class="iconify" data-icon="bx-bx-trash"></i></a>
							<a href="javascript:;"><i class="iconify" data-icon="bx-bx-printer"></i></a>
							<a href="javascript:;" class="azul"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
							<a href="javascript:;" class="verde"><i class="iconify" data-icon="bx-bxs-paper-plane"></i><span>enviar para laboratório</span></a>
						</div>
					</div>
				</div>

				<form class="form">
					<div class="grid grid_2">
						<fieldset>
							<legend><span class="badge">1</span>Selecione os serviços</legend>
							<dl>
								<dt>Procedimento</dt>
								<dd>
									<select name="procedimento" class="chosen">
										<option value="">Selecione um procedimento</option>
									</select>
								</dd>
							</dl>
							<dl>
								<dt>Dente(s)</dt>
								<dd>
									<select name="" class="chosen">
										<option value="">21</option>
										<option value="">22</option>
										<option value="">23</option>
									</select>
								</dd>
							</dl>
							<dl>
								<dt>Descrição</dt>
								<dd>
									<input type="text" name="" />
									<button type="submit" class="button">adicionar</button>
								</dd>
							</dl>

							<div class="reg" style="margin-top:2rem;">

								<a href="javascript:;" class="reg-group">
									<div class="reg-color" style="background-color:palegreen"></div>
									<div class="reg-data" style="flex:0 1 300px">
										<h1>PORCELANA INJETADA</h1>
										<p>21, 22, 24</p>
									</div>
									<div class="reg-data">
										<p>R$ 1.022,00</p>
									</div>										
								</a>

								<a href="javascript:;" class="reg-group">
									<div class="reg-color" style="background-color:palegreen"></div>
									<div class="reg-data" style="flex:0 1 300px">
										<h1>SCAN SERVICE</h1>
										<p>Enviar com urgência</p>
									</div>
									<div class="reg-data">
										<p>R$ 600,00</p>
									</div>										
								</a>
							</div>

						</fieldset>
						<fieldset>
							<legend><span class="badge">2</span>Descrição Geral</legend>
							<dl style="height:100%;">
								<dd style="height:100%;"><textarea name="" style="height:100%;" class="noupper"></textarea></dd>
							</dl>
						</fieldset>
						<fieldset>
							<legend><span class="badge">3</span>Adicione arquivos</legend>
							<div class="colunas">
								<dl>
									<dt>Localizar</dt>
									<dd><input type="file" name="" /></dd>
								</dl>
								<dl>
									<dt>Conteúdo</dt>
									<dd>
										<select name="">
											<optgroup label="Modelos Digitais">
												<option value="">Modelo de Trabalho</option>
												<option value="">Modelo de Referência</option>
												<option value="">Modelo Antagonista</option>
												<option value="">Outro modelo digital...</option>
											</optgroup>
											<optgroup label="Fotos">
												<option value="">Sorriso</option>
												<option value="">Cor do Substrato</option>
												<option value="">Cor final</option>												
											</optgroup>
											<optgroup label="Outros arquivos">
												<option value="">Outro...</option>										
											</optgroup>
										</select>
										<button type="submit" class="button">enviar</button>
									</dd>
								</dl>
							</div>
							<div class="reg" style="margin-top:2rem;">

								<div class="reg-group">
									<div class="reg-color" style="background-color:palegreen"></div>
									<div class="reg-data" style="flex:0 1 300px">
										<h1>fotos.zip</h1>
									</div>
									<div class="reg-data">
										<p>Fotos / Sorriso</p>
									</div>
									<div class="reg-icon">
										<a href=""><i class="iconify" data-icon="bx-bx-download"></i></a>
										<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
									</div>
								</div>
								<div class="reg-group">
									<div class="reg-color" style="background-color:palegreen"></div>
									<div class="reg-data" style="flex:0 1 300px">
										<h1>modelo_antagonista.crc</h1>
									</div>
									<div class="reg-data">
										<p>Modelos / Modelo Antagonista</p>
									</div>
									<div class="reg-icon">
										<a href=""><i class="iconify" data-icon="bx-bx-download"></i></a>
										<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
									</div>
								</div>

							</div>
						</fieldset>
						<fieldset>
							<legend><span class="badge">4</span>Checklist</legend>

							<div class="colunas">
								<dl>
									<dt>Adicionar</dt>
									<dd>
										<select name="">
											<optgroup label="Modelos">
												<option value="">Modelo de Trabalho</option>
												<option value="">Modelo de Referência</option>
												<option value="">Modelo Antagonista</option>
												<option value="">Outro...</option>
											</optgroup>
											<optgroup label="Componentes">
												<option value="">Análogos</option>
												<option value="">Parafusos</option>
												<option value="">Links</option>												
												<option value="">Transfer</option>												
											</optgroup>
											<optgroup label="Fotos">
												<option value="">Sorriso</option>
												<option value="">Cor do Substrato</option>
												<option value="">Cor final</option>												
											</optgroup>
											
										</select>
									</dd>
								</dl>
								<dl>
									<dt>Tipo</dt>
									<dd>
										<label><input type="radio" name="tipo" value="digital" checked />digital</label>
										<label><input type="radio" name="tipo" value="analógico" />analógico</label>
										<button type="submit" class="button">adicionar</button>
									</dd>
								</dl>
							</div>

							<div class="reg" style="margin-top:2rem;">

								<div class="reg-group">
									<div class="reg-color" style="background-color:red"></div>
									<div class="reg-data" style="flex:0 1 300px">
										<h1>Fotos / Sorriso</h1>
										<p>Digital</p>
									</div>
									<div class="reg-icon">
										<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
									</div>
								</div>
								<div class="reg-group">
									<div class="reg-color" style="background-color:red"></div>
									<div class="reg-data" style="flex:0 1 300px">
										<h1>Modelos / Modelo Antagonista</h1>
										<p>Digital</p>
									</div>
									<div class="reg-icon">
										<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
									</div>
								</div>
								<div class="reg-group">
									<div class="reg-color" style="background-color:blue"></div>
									<div class="reg-data" style="flex:0 1 300px">
										<h1>Componentes / Análogos</h1>
										<p>Analógico</p>
									</div>
									<div class="reg-icon">
										<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
									</div>
								</div>
								<div class="reg-group">
									<div class="reg-color" style="background-color:blue"></div>
									<div class="reg-data" style="flex:0 1 300px">
										<h1>Componentes / Parafusos</h1>
										<p>Analógico</p>
									</div>
									<div class="reg-icon">
										<a href=""><i class="iconify" data-icon="bx-bx-trash"></i></a>
									</div>
								</div>
							</div>

						</fieldset>
					</div>		
				</form>
			
			</div>
		</section>


				
		</section>
		
<?php
include "includes/footer.php";
?>