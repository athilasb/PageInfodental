<?php
	$title="";
	include "includes/header.php";
	include "includes/nav.php";
	
	if($usr->tipo!="admin" and !in_array("relatorios",$_usuariosPermissoes)) {
		$jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
		die();
	}
	$_usuarios=array();
	$sql->consult($_p."usuarios","*","where lixo=0 order by nome");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_usuarios[$x->id]=$x;
	}

	$_produtos=array();
	$sql->consult($_p."produtos","*","where lixo=0");
	if($sql->rows) {
		while($x=mysqli_fetch_object(($sql->mysqry))) {
			$_produtos[$x->id]=$x;
		}
	}
	$_servicos=array();
	$sql->consult($_p."produtos","*","where lixo=0");
	if($sql->rows) {
		while($x=mysqli_fetch_object(($sql->mysqry))) {
			$_servicos[$x->id]=$x;
		}
	}
	
	$_title='Relatórios <i class="icon-angle-right"></i> Comandas Fechadas';
	$_table=$_p."clientes_comandas";
	$_page=basename($_SERVER['PHP_SELF']);
	
	$campos=explode(",","titulo,id_unidade");


	$values=$adm->get($_GET);

	if(!isset($values['data_inicio'])) {
		$values['data_inicio']=date('d/m/Y',strtotime("-15 day"));
		$values['data_inicioWH']=date('Y-m-d',strtotime("-15 day"));
		$values['mes_inicio']=date('m',strtotime("-15 day"));
	}
	if(!isset($values['data_fim'])) {
		$values['data_fim']=date('d/m/Y',strtotime("+15 day"));
		$values['data_fimWH']=date('Y-m-d',strtotime("+15 day"));
		$values['mes_fim']=date('m',strtotime("+15 day"));
	}
	$values['data_de']="fechada_data";

	$dataDe=array('data'=>'Data de Entrada',
					'data_saida'=>'Data de Saída',
			);
	

?>
<script>
	$(function(){
		$('.m-relatorios').next().show();		
	});
</script>
<section id="conteudo">
	
	<div class="box-caminho">
		<a href="javascript" class="js-collapse"><span></span></a>
		<h1><?php echo $_title;?></h1>
	</div>
	
	
	<div class="box-filtros clearfix">
		<form method="get" class="formulario-validacao js-filtro">
			<div class="colunas5">
				<dl>
					<dt>De</dt>
					<dd><input type="text" name="data_inicio" value="<?php echo $values['data_inicio'];?>" class="data datecalendar" /></dd>
				</dl>
				<dl>
					<dt>Até</dt>
					<dd><input type="text" name="data_fim" value="<?php echo $values['data_fim'];?>" class="data datecalendar" /></dd>
				</dl>
			
				<dl>		
					<dt>&nbsp;</dt>			
					<dd><button type="submit"><i class="icon-search"></i> Filtrar</button></dd>
				</dl>
			</div>
		</form>
	</div>

	<div class="box-registros">
		<?php
		
		
		$_pagamentos=array('credito'=>'Crédito',
							'debito'=>'Débito',
							'convenio'=>'Convênio',
							'isencao'=>'Isenção por erro',
							'cortesia'=>'Cortesia',
							'dinheiro'=>'Dinheiro',
							'desconto'=>'Desconto');
		$where="WHERE ".$values['data_de'].">='".$values['data_inicioWH']." 00:00:00' and ".$values['data_de']."<='".$values['data_fimWH']." 23:59:59' and lixo='0' and fechada=1";

		//if(isset($values['data_inicio']) and !empty($values['data_inicio'])) $where.=" and ".$values['data_de'].">='".$values['data_inicioWH']." 00:00:00'";
		//if(isset($values['data_fim']) and !empty($values['data_fim'])) $where.=" and ".$values['data_de']."<='".$values['data_fimWH']." 23:59:59'";
		

		if(isset($values['turno']) and !empty($values['turno'])) {
			$colaboradoresTurnosID=array();
			$sql->consult($_p."colaboradores","id","where turno='".addslashes($values['turno'])."'");
			if($sql->rows) {
				while($x=mysqli_fetch_object($sql->mysqry)) {
					$colaboradoresTurnosID[]=$x->id;
				}
			}
			if(count($colaboradoresTurnosID)>0) {
				$where.=" and ((id_usuario_caixa IN (".implode(",",$colaboradoresTurnosID).")) or (id_usuario_caixa=0 and fechada_usuario IN (".implode(",",$colaboradoresTurnosID).")))";
			} else $where.=" and 1=2";
		}

		if(isset($values['tipo']) and !empty($values['tipo'])) $where.=" and tipo='".$values['tipo']."'";
		if(isset($values['id_usuario_caixa']) and !empty($values['id_usuario_caixa']) and is_numeric($values['id_usuario_caixa'])) $where.=" and (id_usuario_caixa='".$values['id_usuario_caixa']."' or (id_usuario_caixa=0 and fechada_usuario='".$values['id_usuario_caixa']."'))";

		$where.=" order by ".$values['data_de']." desc";
		//echo $where;

		$sql->consult($_table,"*,date_format(data,'%d/%m/%Y %H:%i') as dataf,
									date_format(retorno_data,'%d/%m/%Y %H:%i') as data_entregaf,
									date_format(fechada_data,'%d/%m/%Y %H:%i') as fechada_dataf",$where);
		//echo $where." -> ".$sql->rows;;
		$comandas=array();
		$comandasID=array();
		if($sql->rows) {
			while ($x=mysqli_fetch_object($sql->mysqry)) {
				$comandas[$x->id]=$x;
				$comandasID[]=$x->id;
			}
		}

		$pagamentos=array();
		if(count($comandas)>0) {
			$sql->consult($_table."_pagamentos","*","where id_comanda IN (".implode(",",$comandasID).") and lixo=0");
			if($sql->rows) {
				while ($x=mysqli_fetch_object($sql->mysqry))  {
					$pagamentos[$x->id_comanda][]=$x;
				}
			}
		}
		?>
		<div class="opcoes clearfix">
			<div class="qtd"><i class="icon-user"></i><?php echo $sql->rows;?> registros</div>
			<?php /*<div class="link"><a href="javascript://" id="btn-csv"><i class="icon-doc-text"></i>csv</a></div>
			<div class="link"><a href="javascript://" id="btn-gmail"><i class="icon-users"></i>gmail</a></div>*/?>
		</div>
		<style type="text/css">
			.box-resultados div {
				font-size:9px;
				width:7%;
				float:left;
				margin:3px;
				padding:10px;
				border-radius: 15px;
			}
			.box-resultados div span {
				font-size: 12px;
			}
		</style><br />
		<div style="width:100%;margin-top:10px;margin-bottom: 10px;overflow: hidden" class="box-resultados">
			<div style="background:#000;color:#FFF;font-size: 15px;text-align: center;">
				DIA
			</div>
			<div style="border:solid 2px green;">
				Dinheiro:<br /><span class="js-dia-dinheiro"></span>
			</div>
			<div style="border:solid 2px green;">
				Débito:<br /><span class="js-dia-debito"></span>
			</div>
			<div style="border:solid 2px green;">
				Crédito:<br /><span class="js-dia-credito"></span>
			</div>
			<div style="border:solid 2px green;">
				Convênio:<br /><span class="js-dia-convenio"></span>
			</div>
			<div style="border:solid 2px orange;">
				Desconto:<br /><span class="js-dia-desconto"></span>
			</div>
			<div style="border:solid 2px orange;">
				Cortesia:<br /><span class="js-dia-cortesia"></span>
			</div>
			<div style="border:solid 2px red;">
				Isenção:<br /><span class="js-dia-isencao"></span>
			</div>
			<div style="border:solid 2px green;font-weight: bold;font-size:16px;">
				Total:<br /><span class="js-dia-total"></span>
			</div>
		</div>
		<div style="width:100%;margin-top:10px;margin-bottom: 10px;overflow: hidden" class="box-resultados">
			<div style="background:#000;color:#FFF;font-size: 15px;text-align: center;">
				NOITE
			</div>
			<div style="border:solid 2px green;">
				Dinheiro:<br /><span class="js-noite-dinheiro"></span>
			</div>
			<div style="border:solid 2px green;">
				Débito:<br /><span class="js-noite-debito"></span>
			</div>
			<div style="border:solid 2px green;">
				Crédito:<br /><span class="js-noite-credito"></span>
			</div>
			<div style="border:solid 2px green;">
				Convênio:<br /><span class="js-noite-convenio"></span>
			</div>
			<div style="border:solid 2px orange;">
				Desconto:<br /><span class="js-noite-desconto"></span>
			</div>
			<div style="border:solid 2px orange;">
				Cortesia:<br /><span class="js-noite-cortesia"></span>
			</div>
			<div style="border:solid 2px red;">
				Isenção:<br /><span class="js-noite-isencao"></span>
			</div>
			<div style="border:solid 2px green;font-weight: bold;font-size:16px;">
				Total:<br /><span class="js-noite-total"></span>
			</div>
		</div>
		<div style="width:100%;margin-top:10px;margin-bottom: 10px;overflow: hidden" class="box-resultados">
			<div style="background:#000;color:#FFF;font-size: 15px;text-align: center;">
				TOTAL
			</div>
			<div style="border:solid 2px green;">
				Dinheiro:<br /><span class="js-total-dinheiro"></span>
			</div>
			<div style="border:solid 2px green;">
				Débito:<br /><span class="js-total-debito"></span>
			</div>
			<div style="border:solid 2px green;">
				Crédito:<br /><span class="js-total-credito"></span>
			</div>
			<div style="border:solid 2px green;">
				Convênio:<br /><span class="js-total-convenio"></span>
			</div>
			<div style="border:solid 2px orange;">
				Desconto:<br /><span class="js-total-desconto"></span>
			</div>
			<div style="border:solid 2px orange;">
				Cortesia:<br /><span class="js-total-cortesia"></span>
			</div>
			<div style="border:solid 2px red;">
				Isenção:<br /><span class="js-total-isencao"></span>
			</div>
			<div style="border:solid 2px green;font-weight: bold;font-size:16px;">
				Total:<br /><span class="js-total-total"></span>
			</div>
		</div>
		<br /><br />
		<table class="tablesorter">
			<thead>
				<tr>
					<th style="width:150px">Fechamento</th>
					<th style="width:150px">Entrada</th>
					<th>Colaborador</th>
					<th>Cliente</th>
					<th>Veículo</th>
					<th>Lavajato</th>
					<th>Serviços</th>
					<th>Produtos</th>
					<th>Estacionamento</th>
					<th style="width: 300px;">Valor</th>
					<?php /*<th style="width:30px;">Ações</th>*/ ?>
				</tr>
			</thead>
			<tbody>
			<?php
			$WLIB=new WLIB();
			$total=array('dinheiro'=>0,
							'debito'=>0,
							'credito'=>0,
							'cortesia'=>0,
							'convenio'=>0,
							'desconto'=>0,
							'total'=>0,
							'isencao'=>0);

			$dia=array('dinheiro'=>0,
							'debito'=>0,
							'credito'=>0,
							'cortesia'=>0,
							'convenio'=>0,
							'desconto'=>0,
							'total'=>0,
							'isencao'=>0);

			$noite=array('dinheiro'=>0,
							'debito'=>0,
							'credito'=>0,
							'convenio'=>0,
							'cortesia'=>0,
							'desconto'=>0,
							'total'=>0,
							'isencao'=>0);

			foreach($comandas as $x) {
				$u='';
				if(isset($_usuarios[$x->id_usuario])) $u=$_usuarios[$x->id_usuario];
				if(empty($u)) { 

					if(isset($_usuarios[$x->fechada_usuario])) $u=$_usuarios[$x->fechada_usuario];
				}
				$cli=$WLIB->buscaCliente($x->id_cliente,array('rtnObj'=>true));
				$veiculo=$WLIB->buscaVeiculoCliente($x->id_cliente_veiculo);
				$veiculoCliente=$WLIB->buscaVeiculoCliente($x->id_cliente_veiculo,array('rtnObj'=>true));

				$cProdutos=$cServicos=0;
				$cObj=json_decode($x->ps);
				if(is_array($cObj)) {
					foreach($cObj as $v) {
						if($v->tipo=="produto") $cProdutos++;
						else if($v->tipo=="servico") $cServicos++;
					}
				}
			
			?>
			<tr>
				<td><?php echo $x->fechada_dataf;?></td>
				<td><?php echo $x->dataf;?></td>
				<td><?php echo is_object($u)?utf8_encode($u->nome):"-";?></td>
				<td><?php echo utf8_encode($cli->nome);?></td>
				<td><?php echo $veiculo."<br />".$x->placa;?></td>
				<td><?php echo $x->lavajato==1?'<i class="icon-ok" style="color:green"></i>':'<i class="icon-cancel" style="color:red"></i>';?></td>
				
				
				<td style="font-size: 11px;width:150px;">
					<?php
					echo $cServicos==0?"-":$cServicos;
					?>
				</td>
				<td style="font-size: 11px;width:150px;">
					<?php
					echo $cProdutos==0?"-":$cProdutos;
					?>
				</td>
				<td style="font-size: 11px;width:150px;">
					<?php
					$dtHj=strtotime($x->fechada_data);
					$dtEntrada=strtotime($x->data);

					$dtDif=$dtHj-$dtEntrada;
					$_tempo=secondsToTime($dtDif);
					echo $_tempo;
					?>
				</td>
				<td>
					<?php 
					if(isset($pagamentos[$x->id])) {
						foreach($pagamentos[$x->id] as $p) {
							$pindex=strtolower(tirarAcentos(utf8_encode($p->forma)));
							$total[$pindex]+=$p->valor;
							if($pindex=="dinheiro" or $pindex=="credito" or $pindex=="debito") {
								$total['total']+=$p->valor;
							} 


							echo utf8_encode($p->forma).": ".number_format($p->valor,2,",",".")."<br />";
							//var_dump($p); 
						}
					} else {
						//var_dump($vsqlpag);
						if(isset($vsqlpag) and is_array($vsqlpag) and count($vsqlpag)>0) {
							foreach($vsqlpag as $v) {
								$vsql="valor='$v->valor',pagamento='$v->pagamento',id_comanda=$x->id,data='".$x->data."'";
								$sql->consult($_table."_pagamentos","*","where id_comanda=$x->id and valor='".$v->valor."' and pagamento='".$v->pagamento."' and lixo=0");
								if($sql->rows==0) {
									echo $vsql."<br>";
									//$sql->add($_table."_pagamentos",$vsql);
								}
							}
						}
					}
					?>
				</td>
				<?php /*<td>
					<a href="pg_colaboradores.php?form=1&edita=<?php echo $x->id."&".$url;?>" class="tooltip botao botao-principal" title="editar" target="_blank"><i class="icon-pencil"></i></a>
				</td>*/?>
			</tr>
			<?php
			}
			?>
			</tbody>
		</table>
	</div>
	<script>
		$(function(){
			$('.js-total-dinheiro').html('<?php echo number_format($total['dinheiro'],2,",",".");?>');
			$('.js-total-cortesia').html('<?php echo number_format($total['cortesia'],2,",",".");?>');
			$('.js-total-debito').html('<?php echo number_format($total['debito'],2,",",".");?>');
			$('.js-total-credito').html('<?php echo number_format($total['credito'],2,",",".");?>');
			$('.js-total-convenio').html('<?php echo number_format($total['convenio'],2,",",".");?>');
			$('.js-total-isencao').html('<?php echo number_format($total['isencao'],2,",",".");?>');
			$('.js-total-desconto').html('<?php echo number_format($total['desconto'],2,",",".");?>');
			$('.js-total-total').html('<?php echo number_format($total['total'],2,",",".");?>');


			$('.js-dia-dinheiro').html('<?php echo number_format($dia['dinheiro'],2,",",".");?>');
			$('.js-dia-cortesia').html('<?php echo number_format($dia['cortesia'],2,",",".");?>');
			$('.js-dia-debito').html('<?php echo number_format($dia['debito'],2,",",".");?>');
			$('.js-dia-credito').html('<?php echo number_format($dia['credito'],2,",",".");?>');
			$('.js-dia-convenio').html('<?php echo number_format($dia['convenio'],2,",",".");?>');
			$('.js-dia-isencao').html('<?php echo number_format($dia['isencao'],2,",",".");?>');
			$('.js-dia-desconto').html('<?php echo number_format($dia['desconto'],2,",",".");?>');
			$('.js-dia-total').html('<?php echo number_format($dia['total'],2,",",".");?>');


			$('.js-noite-dinheiro').html('<?php echo number_format($noite['dinheiro'],2,",",".");?>');
			$('.js-noite-cortesia').html('<?php echo number_format($noite['cortesia'],2,",",".");?>');
			$('.js-noite-debito').html('<?php echo number_format($noite['debito'],2,",",".");?>');
			$('.js-noite-credito').html('<?php echo number_format($noite['credito'],2,",",".");?>');
			$('.js-noite-convenio').html('<?php echo number_format($noite['convenio'],2,",",".");?>');
			$('.js-noite-isencao').html('<?php echo number_format($noite['isencao'],2,",",".");?>');
			$('.js-noite-desconto').html('<?php echo number_format($noite['desconto'],2,",",".");?>');
			$('.js-noite-total').html('<?php echo number_format($noite['total'],2,",",".");?>');
		});
	</script>


</section>

<?php
	include "includes/footer.php";
?>