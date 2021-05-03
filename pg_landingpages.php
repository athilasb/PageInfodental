<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";

	if($usr->tipo!="admin" and !in_array("clientes",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$values=$adm->get($_GET);
?>


<section class="content">

	<?php
	$_table=$_p."landingpage_temas";
	$_page=basename($_SERVER['PHP_SELF']);
	?>

		<section class="grid grid_2">
			<?php
			$sql->consult($_p."pacientes","*","where data>='".date('Y-m-d H:i:s',strtotime(date('Y-m-d H:i:s')." - 1 year"))."' and lixo=0");
			$total=$sql->rows;

			// Grafico 2: Idade
			$grafico2Labels=array();
			for($i=0;$i<=70;$i+=10) {
				if($i==70) {
					$grafico2Labels[]="+71";
				} else {
					$grafico2Labels[]=($i==0?$i:$i+1)."-".($i+10);
				}
			}

			$pacintesQuantidade=array();
			$pacientesIdade=array();
			while($x=mysqli_fetch_object($sql->mysqry)) {
				$mes=date('m',strtotime($x->data));
				$ano=date('y',strtotime($x->data));

				if(!isset($pacintesQuantidade[substr(mes($mes),0,3)."/".$ano])) $pacintesQuantidade[substr(mes($mes),0,3)."/".$ano]=0;
				$pacintesQuantidade[substr(mes($mes),0,3)."/".$ano]++;
				
				$idade=idade($x->data_nascimento);

				if($idade<=10) {
					if(!isset($pacientesIdade[0])) $pacientesIdade[0]=0;
					$pacientesIdade[0]++;
				} else if($idade<=20) {
					if(!isset($pacientesIdade[1])) $pacientesIdade[1]=0;
					$pacientesIdade[1]++;
				} else if($idade<=30) {
					if(!isset($pacientesIdade[2])) $pacientesIdade[2]=0;
					$pacientesIdade[2]++;
				} else if($idade<=40) {
					if(!isset($pacientesIdade[3])) $pacientesIdade[3]=0;
					$pacientesIdade[3]++;
				} else if($idade<=50) {
					if(!isset($pacientesIdade[4])) $pacientesIdade[4]=0;
					$pacientesIdade[4]++;
				} else if($idade<=60) {
					if(!isset($pacientesIdade[5])) $pacientesIdade[5]=0;
					$pacientesIdade[5]++;
				} else if($idade<=70) {
					if(!isset($pacientesIdade[6])) $pacientesIdade[6]=0;
					$pacientesIdade[6]++;
				} 
				if(!isset($grafico2[$idade])) $grafico2[$idade]=0;
				$grafico2[$idade]++;
			}	


			// Grafico 2: Idade
			$grafico2Data=array();
			foreach($grafico2Labels as $key=>$v) {
				$grafico2Data[$key]=isset($pacientesIdade[$key])?$pacientesIdade[$key]:0;
			}
			//echo json_encode($grafico2Data);

			// Grafico 1: Quantidade
			$grafico1Labels=array();
			$mes=date('m');
			$ano=date('y');
			for($i=1;$i<=12;$i++) {
				$grafico1Labels[]=substr(mes($mes),0,3)."/".$ano;
				$mes--;
				if($mes==0) {
					$ano--;
					$mes=12;
				}
			}

			$grafico1Labels=array_reverse($grafico1Labels);
			foreach($grafico1Labels as $key) { 
				if(!isset($pacintesQuantidade[$key])) $grafico1Data[]=0;
				else { //echo $key."->".$grafico1DataAux[$key]."<BR>";
					$grafico1Data[]=$pacintesQuantidade[$key];
				}
			}

			

			?>
			<section class="box">
				<div class="lista-botoes">
					<a href="javascript:;" class="lista-botoes__item js-grafico" data-grafico="1">
						<i class="iconify" data-icon="clarity-group-solid"></i>
						<div class="lista-botoes__inner1">
							<h1 class="lista-botoes__titulo">Total</h1>
							<h2 class="lista-botoes__valor"><?php echo $total;?></h2>
						</div>
					</a>
					<a href="javascript:;" class="lista-botoes__item js-grafico" data-grafico="2">
						<i class="iconify" data-icon="cil-birthday-cake"></i>
						<div class="lista-botoes__inner1">
							<h1 class="lista-botoes__titulo">Distribuição por</h1>
							<h2 class="lista-botoes__valor">Idade</h2>
						</div>
					</a>
					<a href="javascript:;" class="lista-botoes__item js-grafico" data-grafico="3">
						<i class="iconify" data-icon="mdi-gender-male-female"></i>
						<div class="lista-botoes__inner1">
							<h1 class="lista-botoes__titulo">Distribuição por</h1>
							<h2 class="lista-botoes__valor">Gênero</h2>
						</div>
					</a>
					<a href="javascript:;" class="lista-botoes__item js-grafico" data-grafico="4">
						<i class="iconify" data-icon="carbon-location"></i>
						<div class="lista-botoes__inner1">
							<h1 class="lista-botoes__titulo">Distribuição por</h1>
							<h2 class="lista-botoes__valor">Localização</h2>
						</div>
					</a>
					<a href="javascript:;" class="lista-botoes__item js-grafico" data-grafico="5">
						<i class="iconify" data-icon="tabler-user-plus"></i>
						<div class="lista-botoes__inner1">
							<h1 class="lista-botoes__titulo">Novos Pacientes</h1>
							<h2 class="lista-botoes__valor">9 / mês</h2>
						</div>
					</a>
				</div>
				<div class="grafico">
					<script>
					$(function() {
						
						$('.js-grafico').click(function(){
							let grafico = $(this).attr('data-grafico');

							$(`.box-grafico`).hide();
							$(`#grafico${grafico}`).show();
							$(`.js-grafico`).removeClass('active');
							$(this).addClass('active');
						});

						$('.js-grafico:eq(0)').trigger('click')

						var ctx = document.getElementById('grafico1').getContext('2d');
						var gradientStroke = ctx.createLinearGradient(0,230,0,50);
						gradientStroke.addColorStop(1, 'rgba(254,71,2,0.2)');
						gradientStroke.addColorStop(0.8, 'rgba(254,71,2,0.1)');
						gradientStroke.addColorStop(0, 'rgba(254,71,2,0)');
						var grafico1 = new Chart(ctx, {    
						    type: 'line',
						    data: {
						        labels: <?php echo json_encode($grafico1Labels);?>,
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: 'Pacientes',
						            data: <?php echo json_encode($grafico1Data);?>,
						            backgroundColor: gradientStroke,
						            borderColor:'rgba(254,71,2,0.3)',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						    options: {
						        scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero: true
						                },
						                gridLines: {
						                	drawBorder: false,
						                	color: 'transparent'
						                }
						            }],
						            xAxes: [{
							            gridLines: {
							            	drawBorder: false,
							                color: '#ebebeb',
							                zeroLineColor: "#ebebeb"
							            }	              
							        }]
						        }
						    }
						});

						var ctx = document.getElementById('grafico2').getContext('2d');
						var gradientStroke = ctx.createLinearGradient(0,230,0,50);
						gradientStroke.addColorStop(1, 'rgba(254,71,2,0.2)');
						gradientStroke.addColorStop(0.8, 'rgba(254,71,2,0.1)');
						gradientStroke.addColorStop(0, 'rgba(254,71,2,0)');
						var grafico2 = new Chart(ctx, {    
						    type: 'bar',
						    data: {
						        labels: <?php echo json_encode($grafico2Labels);?>,
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: 'Pacientes',
						            data: <?php echo json_encode($grafico2Data);?>,
						            backgroundColor: gradientStroke,
						            borderColor:'rgba(254,71,2,0.3)',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						    options: {
						        scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero: true
						                },
						                gridLines: {
						                	drawBorder: false,
						                	color: 'transparent'
						                }
						            }],
						            xAxes: [{
							            gridLines: {
							            	drawBorder: false,
							                color: '#ebebeb',
							                zeroLineColor: "#ebebeb"
							            }	              
							        }]
						        }
						    }
						});

						var ctx = document.getElementById('grafico3').getContext('2d');
						var gradientStroke = ctx.createLinearGradient(0,230,0,50);
						gradientStroke.addColorStop(1, 'rgba(254,71,2,0.2)');
						gradientStroke.addColorStop(0.8, 'rgba(254,71,2,0.1)');
						gradientStroke.addColorStop(0, 'rgba(254,71,2,0)');
						var grafico3 = new Chart(ctx, {    
						    type: 'line',
						    data: {
						        labels: ["1","2","3","4","5","6","7"],
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: '# visitas',
						            data: [1200,1100,1300,1300,500,1200,1345],
						            backgroundColor: gradientStroke,
						            borderColor:'rgba(254,71,2,0.3)',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						    options: {
						        scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero: true
						                },
						                gridLines: {
						                	drawBorder: false,
						                	color: 'transparent'
						                }
						            }],
						            xAxes: [{
							            gridLines: {
							            	drawBorder: false,
							                color: '#ebebeb',
							                zeroLineColor: "#ebebeb"
							            }	              
							        }]
						        }
						    }
						});

						var ctx = document.getElementById('grafico4').getContext('2d');
						var gradientStroke = ctx.createLinearGradient(0,230,0,50);
						gradientStroke.addColorStop(1, 'rgba(254,71,2,0.2)');
						gradientStroke.addColorStop(0.8, 'rgba(254,71,2,0.1)');
						gradientStroke.addColorStop(0, 'rgba(254,71,2,0)');
						var grafico4 = new Chart(ctx, {    
						    type: 'line',
						    data: {
						        labels: ["1","2","3","4","5","6","7"],
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: '# visitas',
						            data: [1200,1100,1300,1300,500,1200,1345],
						            backgroundColor: gradientStroke,
						            borderColor:'rgba(254,71,2,0.3)',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						    options: {
						        scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero: true
						                },
						                gridLines: {
						                	drawBorder: false,
						                	color: 'transparent'
						                }
						            }],
						            xAxes: [{
							            gridLines: {
							            	drawBorder: false,
							                color: '#ebebeb',
							                zeroLineColor: "#ebebeb"
							            }	              
							        }]
						        }
						    }
						});

						var ctx = document.getElementById('grafico5').getContext('2d');
						var gradientStroke = ctx.createLinearGradient(0,230,0,50);
						gradientStroke.addColorStop(1, 'rgba(254,71,2,0.2)');
						gradientStroke.addColorStop(0.8, 'rgba(254,71,2,0.1)');
						gradientStroke.addColorStop(0, 'rgba(254,71,2,0)');
						var grafico5 = new Chart(ctx, {    
						    type: 'line',
						    data: {
						        labels: ["1","2","3","4","5","6","7"],
						        datasets: [{
						            fill:true,
						            borderDashOffset: 0.0,
						            label: '# visitas',
						            data: [1200,1100,1300,1300,500,1200,1345],
						            backgroundColor: gradientStroke,
						            borderColor:'rgba(254,71,2,0.3)',
						            borderWidth: 1,
						            borderDash: [],
						            borderDashOffset: 0.0
						        }]
						    },
						    options: {
						        scales: {
						            yAxes: [{
						                ticks: {
						                    beginAtZero: true
						                },
						                gridLines: {
						                	drawBorder: false,
						                	color: 'transparent'
						                }
						            }],
						            xAxes: [{
							            gridLines: {
							            	drawBorder: false,
							                color: '#ebebeb',
							                zeroLineColor: "#ebebeb"
							            }	              
							        }]
						        }
						    }
						});
					});
					</script>
					<div class="grafico">
						<canvas id="grafico1" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
						<canvas id="grafico2" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
						<canvas id="grafico3" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
						<canvas id="grafico4" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
						<canvas id="grafico5" class="box-grafico" width="300px" height="150px" style="display: none;"></canvas>
					</div>

				</div>
			</section>

			<section class="grid">
				<div class="box">
					<section class="filtros">
					<h1 class="filtros__titulo">Landing Page</h1>
					<form method="get" class="filtros-form">
						<dl>
							<dt>Busca</dt>
							<dd><input type="text" name="busca" value="<?php echo isset($values['busca'])?$values['busca']:"";?>" /></dd>
						</dl>
						<button type="submit" class="filtros-form__button"><i class="iconify" data-icon="bx-bx-search"></i></button>
					</form>
					<div class="filtros-acoes">
						<a href="pg_landingpages_configuracao.php" data-padding="0" class="adicionar tooltip" title="Adicionar"><i class="iconify" data-icon="bx-bx-plus"></i></a>			
					</div>
				</section>
					<?php
					$where="WHERE lixo='0'";
					if(isset($values['busca']) and !empty($values['busca'])) $where.=" and (titulo like '%".utf8_decode($values['busca'])."%')";
					
					//echo $where;

					?>
					<div class="reg">
						<?php
						$sql->consultPagMto2($_table,"*",10,$where,"",15,"pagina",$_page."?".$url."&pagina=");
						if($sql->rows==0) {
							$msgSemResultado="Nenhuma Landing Page";
							if(isset($values['busca'])) $msgSemResultado="Nenhuma Landing Page encontrada";

							echo "<center>$msgSemResultado</center>";
						} else {
							while($x=mysqli_fetch_object($sql->mysqry)) {
						?>
						<a href="pg_landingpages_configuracao.php?id_landingpage=<?php echo $x->id?>" class="reg-group">
							<div class="reg-color" style="background-color:green;"></div>
							<div class="reg-data" style="flex:0 1 50%;">
								<h1><?php echo strtoupperWLIB(utf8_encode($x->titulo));?></h1>
								<p>studiodental.dental/<?php echo $x->code;?></p>
							</div>
							
						</a>
						<?php
							}

							if(isset($sql->myspaginacao) and !empty($sql->myspaginacao)) {
							?>	
						<div class="paginacao" style="margin-top: 30px;">
							<p class="paginacao__item"><span>Página</span><?php echo $sql->myspaginacao;?></p>
						</div>
							<?php
							}
						}
						?>
					</div>
					
				</div>
			</section>
		
		</section>

</section>

<?php
	include "includes/footer.php";
?>