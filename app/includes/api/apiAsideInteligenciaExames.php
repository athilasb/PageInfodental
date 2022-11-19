<?php
	$_dirPedidosDeExameDir="arqs/pacientes/exames/";
	if(isset($_POST['ajax'])) {



		$dir="../../";
		require_once("../../lib/conf.php");
		require_once("../../usuarios/checa.php");

		$rtn=array();

		if($_POST['ajax']=="pedidoDeExameConsulta") {
		

			$pedidoDeExame=$evolucao=$clinica=$profissional=$colaborador=$paciente='';
			$_exames=array();
			if(isset($_POST['id_evolucao_pedidodeexame']) and is_numeric($_POST['id_evolucao_pedidodeexame'])) {
				$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id=".$_POST['id_evolucao_pedidodeexame']." and lixo=0");
				if($sql->rows) {
					$pedidoDeExame=mysqli_fetch_object($sql->mysqry);

					$sql->consult($_p."pacientes_evolucoes","*","where id=$pedidoDeExame->id_evolucao and lixo=0");
					if($sql->rows) {
						$evolucao=mysqli_fetch_object($sql->mysqry);

						$sql->consult($_p."parametros_fornecedores","*","where id=$evolucao->id_clinica");
						if($sql->rows) {
							$clinica=mysqli_fetch_object($sql->mysqry);
						}

						$sql->consult($_p."colaboradores","id,nome","where id=$evolucao->id_profissional and lixo=0");
						if($sql->rows) {
							$profissional=mysqli_fetch_object($sql->mysqry);
						}

						$sql->consult($_p."colaboradores","id,nome","where id=$evolucao->id_usuario and lixo=0");
						if($sql->rows) {
							$colaborador=mysqli_fetch_object($sql->mysqry);
						}

					}

					$sql->consult($_p."parametros_examedeimagem","*","");
					if($sql->rows) {
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$_exames[$x->id]=$x;
						}
					}

				}
			}

			if(is_object($pedidoDeExame)) {
				if(is_object($evolucao)) {
					if(is_object($evolucao)) {

						$sql->consult($_p."pacientes","id,nome,data_nascimento,foto_cn,foto,codigo_bi,periodicidade","where id=$evolucao->id_paciente");
						if($sql->rows) {
							$paciente=mysqli_fetch_object($sql->mysqry);
						}

						$_colaboradores=array();
						$sql->consult($_p."colaboradores","id,nome","");
						while($x=mysqli_fetch_object($sql->mysqry)) $_colaboradores[$x->id]=$x;

						$regs=$pedidosIds=[];
						$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id_evolucao=$evolucao->id and lixo=0");
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if(isset($_exames[$x->id_exame])) {
								$pedidosIds[]=$x->id;
								$regs[]=$x;
							}
						}

						$pedidosAnexos=[];
						if(count($pedidosIds)>0) {
							$sql->consult($_p."pacientes_evolucoes_pedidosdeexames_anexos","*","where id_evolucao_pedidodeexame IN (".implode(",",$pedidosIds).") and lixo=0");
							if($sql->rows) {
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$pedidosAnexos[$x->id_evolucao_pedidodeexame][]=array('id_anexo'=>$x->id,
																							'data'=>date('d/m/Y H:i',strtotime($x->data)),
																							'colaborador'=>isset($_colaboradores[$x->id_colaborador])?encodingToJson($_colaboradores[$x->id_colaborador]->nome):'-',
																							'titulo'=>encodingToJson($x->titulo),
																							'arq'=>$_wasabiURL.$_dirPedidosDeExameDir.$x->id.".".$x->arq);
								}
							}
						}


						$exames=[];
						foreach($regs as $x) {			
							$e=$_exames[$x->id_exame];						
							$exames[]=array('id_evolucao_pedidodeexame'=>$x->id,
												'titulo'=>trim(encodingToJson($e->titulo)),
												'obs'=>trim(encodingToJson($e->obs)),
												'status'=>$x->status,
												'anexos'=>isset($pedidosAnexos[$x->id])?$pedidosAnexos[$x->id]:[],
												'statusTitulo'=>isset($_selectSituacaoOptions[$x->status])?$_selectSituacaoOptions[$x->status]['titulo']:'-',
												'opcao'=>encodingToJson($x->opcao)
											);
						}


						if($paciente->data_nascimento!="0000-00-00") {
							$dob = new DateTime($paciente->data_nascimento);
							$now = new DateTime();
							$idade = $now->diff($dob)->y;
						} else $idade=0;

						$ft='';
						if(!empty($paciente->foto_cn)) {
							$ft=$_cloudinaryURL.'c_thumb,w_100,h_100/'.$paciente->foto_cn;
						} else if(!empty($paciente->foto)) {
							$ft=$_wasabiURL."arqs/clientes/".$paciente->id.".jpg";
						}


						$pedidodeexame=[];
						$pedidodeexame['data']=date('d/m/Y',strtotime($evolucao->data_pedido));
						$pedidodeexame['clinica']=is_object($clinica)?encodingToJson($clinica->nome_fantasia):'-';
						$pedidodeexame['profissional']=is_object($profissional)?encodingToJson($profissional->nome):'-';
						$pedidodeexame['paciente']=is_object($paciente)?encodingToJson($paciente->nome):'-';
						$pedidodeexame['idade']=$idade;
						$pedidodeexame['ft']=$ft;
						$pedidodeexame['periodicidade']=isset($_pacientesPeriodicidade[$paciente->periodicidade])?$_pacientesPeriodicidade[$paciente->periodicidade]:$paciente->periodicidade;
						$pedidodeexame['statusBI']=isset($_codigoBI[$paciente->codigo_bi])?utf8_encode($_codigoBI[$paciente->codigo_bi]):"";
						$pedidodeexame['id_paciente']=is_object($paciente)?encodingToJson($paciente->id):0;
						$pedidodeexame['colaborador']=is_object($colaborador)?encodingToJson($colaborador->nome):'-';
						$pedidodeexame['exames']=$exames;


						$rtn=array('success'=>true,'pedidodeexame'=>$pedidodeexame);

						
					} else {
						$rtn=array('success'=>false,'error'=>'Clínica não encontrada!');
					}
				} else {
					$rtn=array('success'=>false,'error'=>'Evolução não encontrada!');
				}
			} else {
				$rtn=array('success'=>false,'error'=>'Pedido de exame não encontrado!');
			}
		}
		else if($_POST['ajax']=="statusPersistir") {

			$pedidoDeExame='';
			if(isset($_POST['id_evolucao_pedidodeexame']) and is_numeric($_POST['id_evolucao_pedidodeexame'])) {
				$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id=".$_POST['id_evolucao_pedidodeexame']." and lixo=0");
				if($sql->rows) {
					$pedidoDeExame=mysqli_fetch_object($sql->mysqry);
				}
			}

			$status=(isset($_POST['status']) and isset($_selectSituacaoOptions[$_POST['status']]))?$_POST['status']:'';

			if(is_object($pedidoDeExame)) {

				if(!empty($status)) {

					$vSQL="status='".$status."',data_atualizacao=now()";
					$vWHERE="where id=$pedidoDeExame->id";

					$sql->update($_p."pacientes_evolucoes_pedidosdeexames",$vSQL,$vWHERE);
					$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."pacientes_evolucoes_pedidosdeexames"."',id_reg='".$pedidoDeExame->id."'");

					$rtn=array('success'=>true);
				}  else {
					$rtn=array('success'=>false,'error'=>'Status não definido!');
				}

			} else {
				$rtn=array('success'=>false,'error'=>'Pedido de exame não encontrada!');
			}
		}
		else if($_POST['ajax']=="pedidosAtualiza") {

			$attr=array('_cloudinaryURL'=>$_cloudinaryURL,
				'prefixo'=>$_p,
				'_wasabiURL'=>$_wasabiURL);
			$inteligencia = new Inteligencia($attr);
			
			$inteligencia->controleDeExames();
			$pedidos = $inteligencia->pedidos;
			$_pedidosDeExames = $inteligencia->_pedidosDeExames;
			


			$rtn=array('success'=>true,
						'pedidosAguardando'=>$pedidos['aguardando'],
						'pedidosConcluido'=>$pedidos['concluido'],
					 	'pedidosNaoRealizado'=>$pedidos['naoRealizado'],
					 	'pedidosAguardandoQtd'=>count($_pedidosDeExames['aguardando']),
					 	'pedidosConcluidoQtd'=>count($_pedidosDeExames['concluido']),
					 	'pedidosNaoRealizadoQtd'=>count($_pedidosDeExames['naoRealizado']));
		}
		else if($_POST['ajax']=="examesAnexosPersistir") {

			$pedidoDeExame='';
			if(isset($_POST['id_evolucao_pedidodeexame']) and is_numeric($_POST['id_evolucao_pedidodeexame'])) {
				$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id=".$_POST['id_evolucao_pedidodeexame']);
				if($sql->rows) {
					$pedidoDeExame=mysqli_fetch_object($sql->mysqry);
				}
			}


			$erro = '';
			if(!isset($_FILES['anexos']) or !is_array($_FILES['anexos']) or count($_FILES['anexos'])==0) $erro='Selecione pelo menos um arquivo para anexar!';
			else if(empty($pedidoDeExame)) $erro='Pedido de exame não encontrado!';

			if(empty($erro)) {

				$cont=$arquivosUploads=0;
				foreach($_FILES['anexos']['tmp_name'] as $file) {

					$ext = explode(".",$_FILES['anexos']['name'][$cont]);
					$ext = strtolower($ext[count($ext)-1]);

					$vsql="data=now(),
							id_evolucao='$pedidoDeExame->id_evolucao',
							id_evolucao_pedidodeexame='$pedidoDeExame->id',
							id_paciente='$pedidoDeExame->id_paciente',
							id_colaborador='$usr->id',
							titulo='".utf8_decode(addslashes($_FILES['anexos']['name'][$cont]))."',
							arq='".$ext."'";

					$sql->add($_p."pacientes_evolucoes_pedidosdeexames_anexos",$vsql);
					$id_anexo=$sql->ulid;

					// upload da foto 
					$uploadFile=$file;
					$uploadType=filesize($file);
					$uploadPathFile=$_wasabiPathRoot.$_dirPedidosDeExameDir.$id_anexo.".".$ext;
					$uploaded=$wasabiS3->putObject(S3::inputFile($uploadFile,true),$_wasabiBucket,$uploadPathFile,S3::ACL_PUBLIC_READ);

					if($uploaded) {
						$arquivosUploads++;
					}
					$cont++;
				}

				$rtn=array('success'=>true,'arquivos'=>$arquivosUploads,'total'=>$cont);

			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}

		}
		else if($_POST['ajax']=="examesAnexosRemover") {
			
			$pedidoDeExame='';
			if(isset($_POST['id_evolucao_pedidodeexame']) and is_numeric($_POST['id_evolucao_pedidodeexame'])) {
				$sql->consult($_p."pacientes_evolucoes_pedidosdeexames","*","where id=".$_POST['id_evolucao_pedidodeexame']);
				if($sql->rows) $pedidoDeExame=mysqli_fetch_object($sql->mysqry);
			}

			$anexo='';
			if(is_object($pedidoDeExame) and isset($_POST['id_anexo']) and is_numeric($_POST['id_anexo'])) {
				$sql->consult($_p."pacientes_evolucoes_pedidosdeexames_anexos","*","where id=".$_POST['id_anexo']." and id_evolucao_pedidodeexame=$pedidoDeExame->id and lixo=0");
				if($sql->rows) $anexo=mysqli_fetch_object($sql->mysqry);
			}

			$erro='';
			if(empty($pedidoDeExame)) $erro='Pedido de exame não encontrado!';
			else if(empty($anexo)) $erro='Anexo não encontrado!';

			if(empty($erro)) {

				$uploadPathFile=$_wasabiPathRoot.$_dirPedidosDeExameDir.$anexo->id.".".$anexo->arq;
				$deleted = $wasabiS3->deleteObject($_wasabiBucket,$uploadPathFile);

				if($deleted) {

					$sql->update($_p."pacientes_evolucoes_pedidosdeexames_anexos","lixo=1,lixo_data=now(),lixo_id_colaborador=$usr->id","where id=$anexo->id");

					$rtn=array('success'=>true);

				} else {
					$rtn=array('success'=>false,'error'=>'Algum erro ocorreu durante a exclusão do anexo!');
				}

			} else {
				$rtn=array('success'=>false,'error'=>$erro);
			}

		}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	}
	$selectSituacaoOptions='<select class="js-select-status">';
	foreach($_selectSituacaoOptions as $key=>$value) {
		$selectSituacaoOptions.='<option value="'.$key.'">'.$value['titulo'].'</option>';
	}
	$selectSituacaoOptions.='</select>';
?>
<script type="text/javascript">

	var id_usuario = '<?php echo $usr->id;?>';
	var autor = '<?php echo utf8_encode($usr->nome);?>';
	var pedidodeexame = {};
	var formAnexo = new FormData();

	// abre aside de Exames
	const asideInteligenciaExames = () => {

		$('.js-asideInteligenciaExames-exame').val(pedidodeexame.exame?pedidodeexame.exame:'-');
		$('.js-asideInteligenciaExames-clinica').val(pedidodeexame.clinica?pedidodeexame.clinica:'-');
		$('.js-asideInteligenciaExames-data').val(pedidodeexame.data?pedidodeexame.data:'-');
		
		$('.js-asideInteligenciaExames-colaborador').val(pedidodeexame.colaborador?pedidodeexame.colaborador:'-');
		$('.js-asideInteligenciaExames-profissional').val(pedidodeexame.profissional?pedidodeexame.profissional:'-');

		// dados do paciente
			$('.aside-inteligencia-exames .js-nome').html(pedidodeexame.paciente?`${pedidodeexame.paciente} <a href="pg_pacientes_resumo.php?id_paciente=${pedidodeexame.id_paciente}" target="_blank"><i class="iconify" data-icon="fluent:share-screen-person-overlay-20-regular" style="color:var(--cinza4)"></i></a>`:'-');
			if(pedidodeexame.ft && pedidodeexame.ft.length>0) {
				$('.aside-inteligencia-exames .js-foto').attr('src',pedidodeexame.ft);
			} else {
				$('.aside-inteligencia-exames .js-foto').attr('src','img/ilustra-usuario.jpg');
			}

			if(pedidodeexame.idade && pedidodeexame.idade>0) {
				$('.aside-inteligencia-exames .js-idade').html(pedidodeexame.idade+(pedidodeexame.idade>=2?' anos':' ano'));
			} else {
				$('.aside-inteligencia-exames .js-idade').html(``);
			}

			if(pedidodeexame.periodicidade && pedidodeexame.periodicidade.length>0) {
				$('.aside-inteligencia-exames .js-periodicidade').html(`Periodicidade: ${pedidodeexame.periodicidade}`);
			} else {
				$('.aside-inteligencia-exames .js-periodicidade').html(`Periodicidade: -`);
			}

			if(pedidodeexame.statusBI && pedidodeexame.statusBI.length==0) {
				$('.aside-inteligencia-exames .js-statusBI').html(``).hide();
			} else {
				$('.aside-inteligencia-exames .js-statusBI').html(`${pedidodeexame.statusBI}`).show();
			}

			if(pedidodeexame.musica && pedidodeexame.musica.length>0) {
				$('.aside-inteligencia-exames .js-musica').html(`<i class="iconify" data-icon="bxs:music"></i> ${rtn.data.musica}`);
			} else {
				$('.aside-inteligencia-exames .js-musica').html(``);
			}


		$('.js-asideInteligenciaExames-exames').html('');
		if(pedidodeexame.exames.length>0) {
			pedidodeexame.exames.forEach(x=>{

				cor='';
				if(x.status=='aguardando') cor='var(--laranja)';
				else if(x.status=='naoRealizado') cor='var(--vermelho)';
				else if(x.status=='concluido') cor='var(--verde)';
				$('.js-asideInteligenciaExames-exames').append(`<tr class="js-tr-item" data-id_evolucao_pedidodeexame="${x.id_evolucao_pedidodeexame}">
																	<td class="list1__border" style="color:${cor}"></td>
																	<td class="js-titulo" style="width:66%">${x.titulo}<p style="font-size:11px;"><i>${x.opcao}</i></p></td>
																	<td>
																		<?php echo $selectSituacaoOptions;?>
																	</td>
																	<td style="text-align:right;width:50px;"><a href="javascript:;" class="button js-tr-item-anexo" style="${x.anexos.length>0?"color:#333":""}"><span class="iconify" data-icon="fluent:attach-12-filled" data-inline="true"></span> ${x.anexos.length}</a></td>
																</tr>`);
				$('.js-asideInteligenciaExames-exames .js-select-status:last').val(x.status);
			})
		}

		$(".aside-inteligencia-exames").fadeIn(100,function() {
			$(".aside-inteligencia-exames .aside__inner1").addClass("active");
		});
	}

	// atualiza informacoes do pedido de exame
	const pedidosDeExameAtualiza = (id_evolucao_pedidodeexame) => {
		let data = `ajax=pedidoDeExameConsulta&id_evolucao_pedidodeexame=${id_evolucao_pedidodeexame}`;
		$.ajax({
			type:"POST",
			data:data,
			url:'includes/api/apiAsideInteligenciaExames.php',
			success:function(rtn) {
				if(rtn.success) {
					pedidodeexame=rtn.pedidodeexame;
					asideInteligenciaExames();
					$('.js-ag:eq(0)').click();
				}
			}
		})
	}

	// atualiza todas solicitacoes de exame
	const pedidosAtualiza = () => {
		let data = `ajax=pedidosAtualiza`;
		$.ajax({
			type:"POST",
			data:data,
			url:'includes/api/apiAsideInteligenciaExames.php',
			success:function(rtn) {
				if(rtn.success) {
					pedidosAguardando = (rtn.pedidosAguardando);
					pedidosConcluido = (rtn.pedidosConcluido);
					pedidosNaoRealizado = (rtn.pedidosNaoRealizado);


					pedidosAguardandoQtd=rtn.pedidosAguardandoQtd;
					pedidosConcluidoQtd=rtn.pedidosConcluidoQtd;
					pedidosNaoRealizadoQtd=rtn.pedidosNaoRealizadoQtd;

					pedidosListar();
				}
			}
		})
	}

	$(function(){

		// atualiza status do exame
		$('.js-asideInteligenciaExames-exames').on('change','.js-select-status',function(){
			let id_evolucao_pedidodeexame = $(this).parent().parent().attr('data-id_evolucao_pedidodeexame');
			let status = $(this).val();
			let data = `ajax=statusPersistir&id_evolucao_pedidodeexame=${id_evolucao_pedidodeexame}&status=${status}`;
			$.ajax({
				type:'POST',
				data:data,
				url:'includes/api/apiAsideInteligenciaExames.php',
				success:function(rtn) {
					if(rtn.success===true) {
						pedidosDeExameAtualiza(id_evolucao_pedidodeexame);
						pedidosAtualiza();
					}
				}
			})
		});

		// clica nos exames do kanban para abrir aside
		$('.kanban-card').on('click','.js-exame-item',function(){
			let id_evolucao_pedidodeexame = $(this).attr('data-id_evolucao_pedidodeexame');
			pedidosDeExameAtualiza(id_evolucao_pedidodeexame);
		});

		// clica no botao de anexo do exame
		$('.js-asideInteligenciaExames-exames').on('click','.js-tr-item-anexo',function(){
			let index = $('.js-asideInteligenciaExames-exames .js-tr-item-anexo').index(this);
			if(pedidodeexame.exames[index]) {
				e=pedidodeexame.exames[index];
				$('.aside-inteligencia-exames-anexos .js-anexo-id_evolucao_pedidodeexame').val(e.id_evolucao_pedidodeexame);
				$('.aside-inteligencia-exames-anexos .js-exame').val(e.titulo);
				$('.aside-inteligencia-exames-anexos .js-opcao').val(e.opcao.length==0?'-':e.opcao);
				$('.aside-inteligencia-exames-anexos .js-obs').val(e.obs.length==0?'-':e.obs);
				$('.aside-inteligencia-exames-anexos .js-status').val(e.statusTitulo);

				$('.aside-inteligencia-exames-anexos .js-asideInteligenciaExames-exames-anexos').html('');

				e.anexos.forEach(x=>{
					$('.aside-inteligencia-exames-anexos .js-asideInteligenciaExames-exames-anexos').append(`<tr>
																						<td>${x.data}</td>
																						<td>${x.titulo}</td>
																						<td>${x.colaborador}</td>
																						<td style="width:90px;">
																							<a href="${x.arq}" class="button"><i class="iconify" data-icon="ant-design:download-outlined"></i></a>
																							<a href="javascript:;" class="button js-anexo-remover" data-id_anexo="${x.id_anexo}"  data-id_evolucao_pedidodeexame="${e.id_evolucao_pedidodeexame}" data-loading="0"><i class="iconify" data-icon="fluent:delete-24-regular"></i></a>
																						</td>
																					</tr>`);

				});

				$(".aside-inteligencia-exames-anexos").fadeIn(100,function() {
					$(".aside-inteligencia-exames-anexos .aside__inner1").addClass("active");
				});
			}
		});

		$('.js-asideInteligenciaExames-exames-anexos').on('click','.js-anexo-remover',function(){
			let id_anexo = $(this).attr('data-id_anexo');
			let id_evolucao_pedidodeexame = $(this).attr('data-id_evolucao_pedidodeexame');
			let obj = $(this);
			let objHTMLAntigo = obj.html();

			swal({   
					title: "Atenção",   
					text: "Tem certeza que deseja remover este anexo?",   
					type: "warning",   
					showCancelButton: true,   
					confirmButtonColor: "#DD6B55",   
					confirmButtonText: "Sim",   
					cancelButtonText: "Não",   
					closeOnConfirm: false,   
					closeOnCancel: false 
				}, function(isConfirm){   
					if (isConfirm) {    

						if(obj.attr('data-loading')==0) {

							swal.close(); 
							obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);

							let data=`ajax=examesAnexosRemover&id_anexo=${id_anexo}&id_evolucao_pedidodeexame=${id_evolucao_pedidodeexame}`;
							$.ajax({
								type:"POST",
								data:data,
								url:'includes/api/apiAsideInteligenciaExames.php',
								success:function(rtn) {
									swal({title: "Sucesso!", text: `Anexo removido com sucesso!`, type:"success", confirmButtonColor: "#424242",html:true});
									$('.aside-inteligencia-exames-anexos .aside-close').click();
									pedidosDeExameAtualiza(id_evolucao_pedidodeexame);
								},
								error:function(){

								}
							}).done(function(){
								obj.html(objHTMLAntigo);
								obj.attr('data-loading',0);
								
							});
						}
					} else {   
						swal.close();   
					} 
				});
		
		});


		$('.aside-inteligencia-exames-anexos .js-file-anexo-button').click(function(){
			$('.aside-inteligencia-exames-anexos .js-file-anexo').click();
		});

		$('.aside-inteligencia-exames-anexos .js-file-anexo').change(function(ev){

			let newFormData = new FormData();
			let max = 70;

			for(var i = 0;i<ev.target.files.length;i++) {
				file = ev.target.files[i];
				newFormData.append('anexos[]',file);

			}

			let fileDescription = ev.target.files.length>1 ? ev.target.files.length+' Arquivos' : ev.target.files.length+' Arquivo';
			$('.js-file-anexo-button').html('<span class="iconify" data-icon="ic:twotone-cloud-upload"></span> '+fileDescription);
			$('.js-file-anexo-button').css({'background':'var(--cinza2)'})

			formAnexo=newFormData;
		});

		// clica para cadastrar novo anexo
		$('.aside-inteligencia-exames-anexos .js-btn-adicionarAnexo').click(function(){

			let erro = '';

			if($('.js-file-anexo').val()=="") erro='Selecione pelo menos um arquivo para anexar!';

			if(erro.length==0) {


				let obj = $(this);
				let objHTMLAntigo = obj.html();

				if(obj.attr('data-loading')==0) {

					obj.attr('data-loading',1);
					obj.html('<span class="iconify" data-icon="eos-icons:loading"></span> Enviando...');


					let id_evolucao_pedidodeexame=$('.js-anexo-id_evolucao_pedidodeexame').val();
					formAnexo.append('ajax','examesAnexosPersistir');
					formAnexo.append('id_evolucao_pedidodeexame',id_evolucao_pedidodeexame);

					$.ajax({
						type:"POST",
						data:formAnexo,
				        cache: false,
				        contentType: false,
				        processData: false,
						url:'includes/api/apiAsideInteligenciaExames.php',
						success:function(rtn) {
							if(rtn.success) {
								swal({title: "Sucesso!", text: `Upload realizado com sucesso!<br /><br />Foram anexados ${rtn.arquivos} de ${rtn.total} arquivo(s)`, type:"success", confirmButtonColor: "#424242",html:true});
								$('.aside-inteligencia-exames-anexos .aside-close').click();
								pedidosDeExameAtualiza(id_evolucao_pedidodeexame);

							} else if(rtn.error) {
								swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
							} else {
								swal({title: "Erro!", text: 'Algum erro ocorreu durante o upload dos anexos', type:"error", confirmButtonColor: "#424242"});
							}
						},
						xhr: function() {
				            var myXhr = $.ajaxSettings.xhr();
				            if (myXhr.upload) { // Avalia se tem suporte a propriedade upload
				                myXhr.upload.addEventListener('progress', function(event) {

				                	loaded = event.loaded;
				                	total = event.total;

				                	percent = (loaded/total)*100;
				                	percent = Math.floor(percent);
				                	$('.js-progress').css('width',percent+'%');

				                	if(percent==100) {
										obj.html('<span class="iconify" data-icon="eos-icons:loading"></span> Salvando...');
				                	}
				                
				                }, false);
				            }
				            return myXhr;
				        }
					}).done(function(){
						obj.attr('data-loading',0);
						obj.html(objHTMLAntigo);
						$('.js-progress').css('width','0%');
						$('.js-file-anexo-button').html('<span class="iconify" data-icon="ic:outline-cloud-upload"></span> Anexar Arquivos').css({'background':''});
						$('.js-file-anexo').val('');
					});
				}
			} else {
				swal({title: "Erro!", text: erro, type:"error", confirmButtonColor: "#424242"});
			}

		});

	});
</script>

<section class="aside aside-inteligencia-exames" style="display: none;">
	<div class="aside__inner1" style="width:900px;">
		<header class="aside-header">
			<h1>Pedido de Exame</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-form-editar-procedimento">

			<section class="header-profile">
				<img src="img/ilustra-usuario.jpg" alt="" width="60" height="60" class="header-profile__foto js-foto" />
				<div class="header-profile__inner1">
					<h1><a href="" target="_blank" class="js-nome"></a></h1>
					<div>
						<p class="js-statusBI"></p>
						<p class="js-idade"></p>
						<p class="js-periodicidade">Periodicidade: 6 meses</p>
						<p class="js-musica"></p>
					</div>
				</div>
			</section>

			<script>
				$(function() {
					$('.js-tab a').click(function() {
						$(".js-tab a").removeClass("active");
						$(this).addClass("active");							
					});
				});
			</script>
			<section class="tab tab_alt js-tab">
				<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-solicitados').show();" class="active">Exames Solicitados</a>
				<a href="javascript:;" onclick="$('.js-ag').hide(); $('.js-ag-dados').show();">Dados da Evolução</a>			
			</section>



			<div class="js-ag js-ag-solicitados">
				<legend>Exames Solicitados</legend>

				<div class="list1">

					<table class="js-asideInteligenciaExames-exames">
						
					</table>
				</div>
			</div>

			<div class="js-ag js-ag-dados" style="display: none;">

				<div class="colunas4">

					<dl>
						<dt>Data do Pedido</dt>
						<dd><input type="text" class="js-asideInteligenciaExames-data" disabled /></dd> 
					</dl>
					<dl class="dl3">
						<dt>Cadastrado por</dt>
						<dd><input type="text" class="js-asideInteligenciaExames-colaborador" disabled /></dd> 
					</dl>
				</div>
				<dl>	
					<dt>Clínica</dt>
					<dd><input type="text" class="js-asideInteligenciaExames-clinica" disabled /></dd>
				</dl>

				<dl>
					<dt>Profissional</dt>
					<dd><input type="text" class="js-asideInteligenciaExames-profissional" disabled /></dd>
				</dl>	

			</div>


		</form>
	</div>
</section>



<section class="aside aside-inteligencia-exames-anexos" style="display: none;">
	<div class="aside__inner1" style="width:750px;">
		<header class="aside-header">
			<h1>Anexos</h1>
			<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
		</header>

		<form method="post" class="aside-content form js-form-editar-procedimento">
			<input type="hidden" class="js-anexo-id_evolucao_pedidodeexame" />
			<section class="filter">
				<div class="filter-group"></div>
				<div class="filter-group">
					<div class="filter-form form">

						
					</div>								
				</div>
			</section>

			<fieldset>
				<legend>Dados do Exame</legend>

			
				<dl>
					<dt>Exame</dt>
					<dd><input type="text" class="js-exame" disabled /></dd>
				</dl>

				<dl>
					<dt>Opções</dt>
					<dd><input type="text" class="js-opcao" disabled /></dd>
				</dl>


				<dl>
					<dt>Obs.:</dt>
					<dd class="js-obs"></dd>
				</dl>
			</fieldset>

			<fieldset>
				<legend>Anexos</legend>



				<dl>
					<dd>
						<button type="button" class="button js-file-anexo-button"><span class="iconify" data-icon="ic:outline-cloud-upload"></span> Anexar Arquivos</button>
						<input type="file" class="js-file-anexo" multiple style="display: none;" />
						<a href="javascript:;" class="button button_main js-btn-adicionarAnexo" data-loading="0"><span class="iconify" data-icon="akar-icons:plus"></span> Anexar</a>
					</dd>
				</dl>

				<div style="width:100%;">
					<div style="width: 0%;background:var(--cinza5);height:5px;border-radius: 5px;" class="js-progress"></div>
				</div>



				<div class="list1">

					<table class="js-asideInteligenciaExames-exames-anexos">
						
					</table>
				</div>
			</fieldset>


		</form>
	</div>
</section>
