<?php	
	if(isset($_POST['ajax'])) {
		$dir="../../";
		require_once("../../lib/conf.php");
		require_once("../../usuarios/checa.php");


		$attr=array('prefixo'=>$_p,'usr'=>$usr);
		$infozap = new Whatsapp($attr);

		$rtn = array();

		# Arquivos
			if($_POST['ajax']=="enviaArquivo") {
				var_dump($_FILES);
				if(isset($_FILES['file']['tmp_name'])) {

				}
			}
			else if($_POST['ajax']=="asEspecialidadesListar") {

				$regs=array();
				$sql->consult($_tableEspecialidades,"*","where lixo=0 order by titulo asc") ;
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$regs[]=array('id'=>$x->id,
											'titulo'=>utf8_encode($x->titulo));
				}

				$rtn=array('success'=>true,
							'regs'=>$regs);
			} 

			else if($_POST['ajax']=="asEspecialidadesEditar") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableEspecialidades,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$x=mysqli_fetch_object($sql->mysqry);
						$cnt=(object)array('id' =>$x->id,'titulo' =>utf8_encode($x->titulo));
					}
				}

				if(is_object($cnt)) {
					$rtn=array('success'=>true,
								'id'=>$cnt->id,
								'cnt'=>$cnt);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}	
			} 

			else if($_POST['ajax']=="asEspecialidadesPersistir") {

				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableEspecialidades,"*","where id='".addslashes($_POST['id'])."' and lixo=0");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				$titulo=isset($_POST['titulo'])?addslashes(utf8_decode($_POST['titulo'])):'';

				if(empty($titulo)) $rtn=array('success'=>false,'error'=>'Título não preenchido!');
				else {


					$vSQL="titulo='$titulo'";

					if(is_object($cnt)) {
						$vWHERE="where id=$cnt->id";
						//$vSQL.=",alteracao_data=now(),id_alteracao=$usr->id";
						$sql->update($_tableEspecialidades,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableEspecialidades."',id_reg='$cnt->id'");
					} else {
						$vSQL.=",data=now(),id_usuario=$usr->id";
						$sql->add($_tableEspecialidades,$vSQL);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='',tabela='".$_tableEspecialidades."',id_reg='$sql->ulid'");

					}

					$rtn=array('success'=>true);
				}
			} 

			else if($_POST['ajax']=="asEspecialidadesRemover") { 
				$cnt='';
				if(isset($_POST['id']) and is_numeric($_POST['id'])) {
					$sql->consult($_tableEspecialidades,"*","where id='".$_POST['id']."'");
					if($sql->rows) {
						$cnt=mysqli_fetch_object($sql->mysqry);
					}
				}

				if(is_object($cnt)) {
					$vSQL="lixo=$usr->id";
					$vWHERE="where id=$cnt->id";
					$sql->update($_tableEspecialidades,$vSQL,$vWHERE);
						$sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_tableEspecialidades."',id_reg='$cnt->id'");

					$rtn=array('success'=>true);
				} else {
					$rtn=array('success'=>false,'error'=>'Registro não encontrado!');
				}
			}

		header("Content-type: application/json");
		echo json_encode($rtn);
		die();
	} 

	# JS All Asides
?>
	<script type="text/javascript" src="js/aside.funcoes.js"></script>
	<script type="text/javascript">
		$(function(){
			$('.js-btn-asideArquivo').click(function(){
				$("#js-aside-asArquivos").fadeIn(100, function() {
					$("#js-aside-asArquivos  .aside__inner1").addClass("active");
				});
			});
		});
	</script>
<?php

	# Asides

		// Arquivos
?>
				<script type="text/javascript">

					function removeFile(index){
					    var attachments = document.getElementById("js-asArquivos-arquivos").files; // <-- reference your file input here
					    var fileBuffer = new DataTransfer();

					    // append the file list to an array iteratively
					    for (let i = 0; i < attachments.length; i++) {
					        // Exclude file in specified index
					        if (index !== i)
					            fileBuffer.items.add(attachments[i]);
					    }
					    
					    // Assign buffer to file input
					    document.getElementById("js-asArquivos-arquivos").files = fileBuffer.files; // <-- according to your file input reference
					}

					function enviaArquivo(file,index,obj,objHTMLAntigo) {
						return new Promise(resolve => {

							console.log(file.name+'....');

							const data = new FormData();
							data.append("act", "upload");
							data.append("token","ZDNudDRsaW5mMDo4ZTMwM2I1ZDVjMTJkNjg0ZjBjN2VhZGZmNjVkMDg5Yzk3OTM4YWZj");
							data.append("instancia", "<?php echo $infoConta->instancia;?>");
							data.append("file", file);
							data.append("id_paciente", "<?php echo $paciente->id;?>");
							data.append("tipo", "outros");
							data.append("obs", "observação vem aqui");
							data.append("id_colaborador", "<?php echo $usr->id;?>");

							// XMLHttpRequest
								/*const xhr = new XMLHttpRequest();
								xhr.withCredentials = true;

								xhr.addEventListener("readystatechange", function () {
								  if (this.readyState === this.DONE) {
								    console.log(this.responseText);
								    resolve();
								  }
								});

								xhr.addEventListener("progress", function (evt) {
								 var percentComplete = (evt.loaded / evt.total) * 100;
						                console.log(`${index} => ${percentComplete}`)
						                $(`.js-asArquivos-lista div.arquivo-item:eq(${index}) .progress`).css('width',`${percentComplete}%`);
						            
								});

								xhr.open("POST", "https://upload.infodental.dental/api/");
								xhr.setRequestHeader("Authorization", "Basic ZDNudDRsaW5mMDo4ZTMwM2I1ZDVjMTJkNjg0ZjBjN2VhZGZmNjVkMDg5Yzk3OTM4YWZj");

								xhr.send(data);*/


							// jQuery
								$.ajax({
									type:"POST",
									url:"https://upload.infodental.dental/api/",
									data:data,
									contentType:false,
									processData:false,
									 xhr: function() {
								        var xhr = new window.XMLHttpRequest();
								        xhr.upload.addEventListener("progress", function(evt) {
								            if (evt.lengthComputable) {
								                var percentComplete = (evt.loaded / evt.total) * 100;
								                //console.log(`${index} => ${percentComplete}`)
								                $(`.js-asArquivos-lista div.arquivo-item:eq(${index}) .progress`).css('width',`${percentComplete}%`);
								            }
								        }, false);
								        return xhr;
								    },
									success: function(rtn) {
										console.log(rtn);
										if(rtn.success) {
											$(`.arquivo-item:eq(${index})`).find('.progress').remove();
											$(`.arquivo-item:eq(${index})`).append(` <span class="iconify" data-icon="ep:success-filled" style="color:var(--verde)"></span>`);

											if((index+1)==document.getElementById('js-asArquivos-arquivos').files.length) {
												swal({title: "Sucesso", text: "Arquivo(s) enviado(s) com sucesso!", type:"success", confirmButtonColor: "#424242"},function(){
													document.location.reload();
												});
											}
										} else {
											$(`.arquivo-item:eq(${index})`).find('.progress').remove();
											$(`.arquivo-item:eq(${index})`).append(` <span class="iconify" data-icon="ic:round-cancel" style="color:var(--vermelho)"></span>`);
										}
										resolve();
									},
									error:function(a,b,c){
										alert('erro');
										resolve();
									}
								});
						})
					}
					$(function(){

						$('#js-asArquivos-arquivos').change(function(){
							$('.js-asArquivos-lista').html('');
							var totalfiles = document.getElementById('js-asArquivos-arquivos').files.length;
							for (var index = 0; index < totalfiles; index++) {
								let item = `<div class="arquivo-item">${document.getElementById('js-asArquivos-arquivos').files[index].name}<div class="progress" style="background:var(--cinza5);height:10px;width:0%"></div></div>`;
								$('.js-asArquivos-lista').append(item);
							}
						})

						$('.js-asArquivos-carregarArquivos').click(function(){
							$('#js-asArquivos-arquivos').click();
						});

						$('.js-asArquivos-submit').click(async function(){

							let obj = $(this);
							let objHTMLAntigo = $(this).html()
							if(obj.attr('data-loading')==0) {

								obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span> Enviando... Por favor aguarde!`);
								obj.attr('data-loading',1);

								var totalfiles = document.getElementById('js-asArquivos-arquivos').files.length;
								for (var index = 0; index < totalfiles; index++) {
									let file = document.getElementById('js-asArquivos-arquivos').files[index];
									await enviaArquivo(file,index,obj,objHTMLAntigo);
								}
							}
						});

						$('.aside-especialidade').on('click','.js-asEspecialidades-remover',function(){
							let obj = $(this);

							if(obj.attr('data-loading')==0) {

								let id = $('.js-asEspecialidades-id').val();
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

											obj.html(`<span class="iconify" data-icon="eos-icons:loading"></span>`);
											obj.attr('data-loading',1);
											let data = `ajax=asEspecialidadesRemover&id=${id}`; 
											$.ajax({
												type:"POST",
												data:data,
												url:baseURLApiAside,
												success:function(rtn) {
													if(rtn.success) {
														$(`.js-asEspecialidades-id`).val(0);
														$(`.js-asEspecialidades-titulo`).val('');
														asEspecialidadesAtualizar();
														swal.close();   
													} else if(rtn.error) {
														swal({title: "Erro!", text: rtn.error, type:"error", confirmButtonColor: "#424242"});
													} else {
														swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
													}
												},
												error:function(){
													swal({title: "Erro!", text: "Algum erro ocorreu durante a remoção deste horário!", type:"error", confirmButtonColor: "#424242"});
												}
											}).done(function(){
												$('.js-asEspecialidades-remover').hide();
												obj.html('<i class="iconify" data-icon="fluent:delete-24-regular"></i>');
												obj.attr('data-loading',0);
												$(`.js-asEspecialidades-submit`).html(`<i class="iconify" data-icon="fluent:add-circle-24-regular"></i>`);
											});
										} else {   
											swal.close();   
										} 
									});
							}
						});

					});
				</script>

				<style type="text/css">
					.js-asArquivos-lista {
						display:block !important;
					}
					.arquivo-item {
						width: 100%;
						border: solid 1px #CCC;
						padding:5px;
						margin-top:10px;
						overflow: hidden;
					}
				</style>
				<section class="aside aside-arquivos" id="js-aside-asArquivos">
					<div class="aside__inner1">

						<header class="aside-header">
							<h1>Arquivos</h1>
							<a href="javascript:;" class="aside-header__fechar aside-close"><i class="iconify" data-icon="fluent:dismiss-24-filled"></i></a>
						</header>

						<form method="post" class="aside-content form js-asArquivos-form">

							<section class="filter">
								<div class="filter-group"></div>
								<div class="filter-group">
									<div class="filter-form form">
										<dl>
											<dd><button type="button" class="button button_main js-asArquivos-submit" data-loading="0"><i class="iconify" data-icon="fluent:checkmark-12-filled"></i> <span>Enviar</span></button></dd>
										</dl>
									</div>								
								</div>
							</section>

							<dl>
								<dd>
									<div style="width:100%;border: 1px dashed var(--cinza3);background:var(--cinza1);color:var(--cinza4);padding: 40px;line-height: 30px;cursor: pointer;" class="js-asArquivos-carregarArquivos">
										<center>
											<span class="iconify" data-icon="ic:baseline-cloud-upload" data-height="30"></span>
											<br />
											Carregar Arquivo(s)
										</center>
									</div>
								</dd>
								<dd class="js-asArquivos-lista">
								</dd>
								<dd style="display:none">
									<input type="file" name="arquivos[]" id="js-asArquivos-arquivos" multiple />
								</dd>
							</dl>

							
						</form>
					</div>
				</section>
				