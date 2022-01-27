<?php

	class Input {
		
		private
			$formulario="",
			$lista="",
			$visu="";
		public
			$campos="";
		
		
		function url($urlOut,$retira="") {
			if(is_array($urlOut)) {
				return $this->urlConsult($urlOut,$retira);	
			}
		}

		
		function urlConsult($urlOut,$retira="") {
			$url="";
			if(is_array($retira)) {
				foreach($urlOut as $v) {
					if($v) if(!in_array($v['chave'],$retira) && $v['valor']) $url.="&".$v['chave']."=".$v['valor'];
				}
			} else {
				foreach($urlOut as $v) {
				//	echo $v['chave']; 
					if($v) if($v['valor']) $url.="&".$v['chave']."=".$v['valor'];
				}
			}
			return $url;
		}
		
		function urlConsultArray($urlOut,$retira="") {
			$url=array();
			if(is_array($urlOut)) {
				if(is_array($retira)) {
					foreach($urlOut as $v) {
						if($v) if(!in_array($v['chave'],$retira) && $v['valor']) array_push($url,array($v['chave']=>$v['valor']));
					}
				} else {
					foreach($urlOut as $v) {
					//	echo $v['chave']; 
						if($v) if($v['valor']) array_push($url,array($v['chave']=>$v['valor']));
					}
				}
			}
			return $url;
		}
		function filtra($url) {
			$where="where lixo='0' ";
			$str=new StringW();
			if(is_array($url)) {
				foreach($url as $v) {
					if($v['type']=="ref" and $v['valor']) {
						$where.="and ".$v['chave']."='".$v['valor']."' ";
					}
					else if($v['chave']=="tipo") $tipo=$v['valor'];
					else if($v['chave']=="busca") $busca=$v['valor'];
					else if($v['chave']=="data_inicio") $data_inicio=$v['valor'];
					else if($v['chave']=="data_fim") $data_fim=$v['valor'];
				}
				
				if($tipo and $busca) $where.=" and ".$tipo." like CONVERT(_utf8 '%".$busca."%' USING latin1) COLLATE latin1_swedish_ci";
				
				if($data_inicio) {
					list($d,$m,$a)=explode("/",$data_inicio);
					if(checkdate($m,$d,$a)) {
						$where.=" and data>='".$a."-".$m."-".$d."'";
					}
				}
				if($data_fim) {
					list($d,$m,$a)=explode("/",$data_fim);
					if(checkdate($m,$d,$a)) {
						$where.=" and data<='".$a."-".$m."-".$d."'";
					}
				}
			}
			
			return $where;
		}
		function getQuery($url,$chave) {
			if(is_array($url)) {
				foreach($url as $v) {
					if($v) {
						if($v['chave']==$chave) return $v['valor']; 
					}
				}
			}
		}
		
		
		
		function setCampos($campos) {
			$this->campos=$campos;
		}
		
		function setVisualizacao($visu) {
			$this->visu=$visu;
		}
		
		function setLista($lista) {
			$this->lista=$lista;
		}
		
		function exibirVisualizacao() {
			return $this->formulario;
		}
		
		function exibirLista() {
			return $this->formulario;
		}
		
		
		function exibirFormulario() {
			return $this->formulario;
		}
		
		function carregarVisualizacao($attr) {//$tabela,$comPublicado="",$condicoes,$urlOut,$comFiltro="") {
			$tabela=$attr['table'];
			$comPublicado=$attr['pub'];
			$condicoes=$attr['condicao'];
			$urlOut=$attr['url'];
			$comFiltro=$attr['filtro'];
			$type=$attr['type'];
			if(is_array($this->visu)) {
				$sql=new Mysql();
				$sql2=new Mysql();
				$sql->consult($tabela,"*","where id='".$_GET['id']."'");
				if($sql->rows) {
					$x=mysqli_fetch_object($sql->mysqry);// echo 
					if(!$attr['print']) {
						$this->formulario="<a href=\"?go=print&id=".$attr['id']."\" class=\"botao botao-imprimir\" target=\"_blank\">Imprimir</a><table class=\"exibe-solicitacoes\">";
						//if($attr['csv']) $this->formulario.="<a href=\"#\" class=\"botao botao-exportar\">Exportar</a><table class=\"exibe-solicitacoes\">";
					} else {
						if($attr['print']) {
						$this->formulario="<table class=\"exibe-solicitacoes\">";// <a href=\"#\" class=\"botao botao-exportar\">Exportar</a><table class=\"exibe-solicitacoes\">";
					}
				}
					foreach($this->visu as $indice) {
						$this->formulario.="<tr><th colspan=\"2\" class=\"titulo\">".$indice['show']."</th></tr>";
						foreach($indice['campos'] as $comps) {
							$sai=""; //echo $comps['type']."<br>";
							if($comps['type']=="data") {
								list($ano,$mes,$dia)=explode("-",$x->$comps['id']);
								$sai=$dia."/".$mes."/".$ano;
							} else if($comps['id']=="whatsapp" or $comps['id']=="sms") { //echo 'aa'; die();
								$sai=$x->$comps['id']?"Sim":"Não";
							}else if($comps['id']=="escolaridade") { //echo 'aa'; die();
								$escolaridades['medio']="Ensino Médio";
								$escolaridades['superior']="Ensino Superior";
								$escolaridades['nenhum']="Nenhum";
								$sai=$escolaridades[$x->$comps['id']];
							} else if($comps['id']=="id_vendedor") { //echo 'aa'; die();
								$sql->consult("viadp_colaboradores","nome","where id='".$x->$comps['id']."'");
								if($sql->rows==0) $sai="-";
								else {
									$__y=mysqli_fetch_object($sql->mysqry);
									$sai=utf8_encode($__y->nome);
								}
								
							}  else if($comps['id']=="id_auditor") { //echo 'aa'; die();
								$sql->consult("viamais_auditores","nome","where id='".$x->$comps['id']."'");
								if($sql->rows==0) $sai="-";
								else {
									$__y=mysqli_fetch_object($sql->mysqry);
									$sai=utf8_encode($__y->nome);
								}
								
							} else if($comps['type']=="datahora") {
								list($dt,$hr)=explode(" ",$x->$comps['id']);
								list($ano,$mes,$dia)=explode("-",$dt);
								list($hora,$min,$seg)=explode(":",$hr);
								$sai=$dia."/".$mes."/".$ano." ás ".$hora.":".$min;
							} else if($comps['type']=="arq") {
								$ft=$comps['dir'].$x->id.".".$x->$comps['id'];
								
								if(file_exists($ft)) { //echo $comps['titulo'];die();
									$sai="<a href=\"lib/download.php?nome=curriculo-".outUrl($x->$comps['titulo']).".".$x->$comps['id']."&arq=".$ft."\" target=\"_blank\"><u>Clique aqui para fazer o download</u></a>";
								} else {
									$sai="<font color=#cc3300>Nenhum arquivo anexado</font>";
								} 
							} else if($comps['type']=="referencia") {
								$sql2->consult($comps['campos']['table'],$comps['campos']['campShow'],$comps['campos']['condicao']." and id='".$x->$comps['id']."'");
								if($sql2->rows==0) $sai="<font color=#cc3300>Vaga não encontrada!</font>";
								else {
									$y=mysqli_fetch_object($sql2->mysqry);
									$sai=utf8_encode($y->$comps['campos']['campShow']);
								}
							} else if($field['type']=="money") {
								$dataValue=$field['value'];
								$this->formulario.="<input type=\"text\" name=\"".$field['id']."\" maxlength=\"".($field['maxlength']?$field['maxlength']:140)."\" id=\"".$field['id']."\" class=\"money input1".($field['obg']?" obg":"").($field['class']?" ".$field['class']:"")."\" value=\"".$dataValue."\" style=\"width:180px;\" />";
							} else if($comps['type']=="referencia2") {
								$sql2->consult($comps['campos']['table'],"id,".$comps['cat']['id_ref'].",".$comps['campos']['campShow'],$comps['campos']['condicao']." and id='".$x->$comps['id']."'");
								if($sql2->rows==0) $sai="<font color=#cc3300>Vaga não encontrada!</font>";
								else {
									$y=mysqli_fetch_object($sql2->mysqry); ;
									$sql2->consult($comps['cat']['table'],$comps['cat']['campShow'],$comps['cat']['condicao']." and id='".$y->$comps['cat']['id_ref']."'");
									
									if($sql2->rows) {
										$z=mysqli_fetch_object($sql2->mysqry); 
										$sai=utf8_encode($z->$comps['cat']['campShow'])." / ";
									} else $sai="";
									$sai.=utf8_encode($y->$comps['campos']['campShow']);
								}
							} else {
								if($comps['id']!="estado") $sai=utf8_encode($x->$comps['id']);
								else $sai=strtoupper($x->$comps['id']);
							}
							$this->formulario.="<tr><th>".$comps['show']."</th><td>".$sai."</td></tr>";
						}
					}
					$this->formulario.="</table>";

				}
			}
			
			return ($this->formulario) ? true : false;
			
		}
		
		
		
		function carregarLista($attr) {//$tabela,$comPublicado="",$condicoes,$urlOut,$comFiltro="") {
			$tabela=$attr['table'];
			$comPublicado=$attr['pub'];
			$condicoes=$attr['condicao'];
			$urlOut=$attr['url'];
			//echo $attr['url'];
			$comFiltro=$attr['filtro'];
			$type=$attr['type'];
			$csv=$attr['csv'];
				//echo $csv."a";		
			//echo print_r($urlOut);
			if(is_array($this->lista)) {
				//echo $this->urlConsult($urlOut)."<br>";
				if($this->getQuery($urlOut,'ordem')) $ordemAtual="order by ".$this->getQuery($urlOut,'ordem');
				if($this->getQuery($urlOut,'desc')) $ordemAtual.=" desc";
				//echo $ordemAtual;die();
				$campos="id,";
				$registros="";
				$filtro=array();
				$this->listaID=array();
				foreach($this->lista as $value) {
					if($this->getQuery($urlOut,'desc')==1 or $this->getQuery($urlOut,'desc')==0) { 
						$orderNow=$this->getQuery($urlOut,'desc')==1?0:1;
						
					} else {
						$orderNow=1;
					}
					
					if($value['type']=="data") {
						$registros.="<th style=\"width:70px;\"><a href=\"?go=list&ordem=".$value['id']."&desc=".$orderNow."".$this->urlConsult($urlOut,array('ordem','desc'))."\" class=\"tip\" title=\"Ordenar por ".$value['show']."\">".$value['show']."".($this->getQuery($urlOut,'ordem')==$value['id']?($orderNow?" &#9650;":"&#9660;"):"")."</a></th>";
						array_push($this->listaID,$value['id']."f");
						$campos.="date_format(".$value['id'].", '%d/%m/%Y') as ".$value['id']."f,";
					} else {
						$registros.="<th><a href=\"?go=list&ordem=".$value['id']."&desc=".$orderNow."".$this->urlConsult($urlOut,array('ordem','desc'))."\" class=\"tip\" title=\"Ordenar por ".$value['show']."\">".$value['show']."".($this->getQuery($urlOut,'ordem')==$value['id']?($orderNow?" &#9650;":"&#9660;"):"")."</a></th>";
						array_push($filtro,array('id'=>$value['id'],'show'=>$value['show']));
						$campos.=$value['id'].",";
						array_push($this->listaID,$value['id']);
					}
				}
				$this->formulario="";
				//var_dump($attr);
				if($attr['csv']) $this->formulario.="<a href=\"?go=csv\" class=\"botao botao-exportar\">Exportar CSV</a><table class=\"exibe-solicitacoes\">";
				// com filtro comum
				if($comFiltro=="comFiltro") {
					foreach($urlOut as $v) {
						if($v['chave']=="busca") $buscaIn=$v['valor'];
						if($v['chave']=="tipo") $tipoIn=$v['valor'];
						if($v['chave']=="data_inicio") $dIIn=$v['valor'];  
						if($v['chave']=="data_fim") $dFIn=$v['valor'];  
					}
					$this->formulario.="<div class=\"filtros\">";
  					$this->formulario.="<div class=\"busca\"><label>Buscar por</label> <select name=\"tipo\" id=\"tipo\">";
					
					foreach($filtro as $v) {
						$this->formulario.="<option value=\"".$v['id']."\"".($tipoIn==$v['id']?" selected":"").">".$v['show']."</option>";
					}
  					$this->formulario.="</select> ";
					$this->formulario.="<input type=\"text\" name=\"busca\" id=\"busca\" class=\"input\" value=\"".$buscaIn."\" /> ";
      				$this->formulario.="<input type=\"submit\" value=\"Buscar\" onClick=\"filtro('".$url."');\" />";
  					$this->formulario.="</div>";
  
					 $this->formulario.="<div class=\"periodo\">
						  <label>Período</label> de <input type=\"text\" name=\"data_inicio\" id=\"data_inicio\" class=\"data datepicker\" value=\"".$dIIn."\" /> a <input type=\"text\" name=\"data_fim\" id=\"data_fim\" class=\"data datepicker\" value=\"".$dFIn."\" />
						  <input type=\"submit\" value=\"Buscar\" onClick=\"filtro('".$url."');\" /></div>";
					$this->formulario.="</div>";
					$btnBusca="<input type=\"submit\" value=\"Buscar\" onClick=\"document.location.href='?go=list&".$idC."='+$('#".$idC."').val()\" />";
				} 
				
				// com filtro de categoria
				else if($comFiltro=="comFiltroCategoria") {
					foreach($this->campos as $v) {
						
						$show=$v['campos']['show'];
						$idC=$v['id']; 
						
						// referencia de 1 nivel
						if($v['type']=="referencia") {
							$select="<select name=\"".$v['id']."\" id=\"".$v['id']."\" onchange=\"document.location.href='?go=list&".$idC."='+$('#".$idC."').val()+'".$this->urlConsult($urlOut,array($idC,"pagina"))."'\">";
							$select.="<option value=\"\">- Todos -</option>"; 
							$sql=new Mysql();
							$sql->consult($v['campos']['table'],"id,".$v['campos']['campShow'],$v['campos']['condicao']);
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$select.="<option value=\"".$x->id."\"".($_GET[$v['id']]==$x->id?" selected":"").">".utf8_encode($x->$v['campos']['campShow'])."</option>";
							}
							$select.="</select>";
							break;
						} 
						
						// referencia de 2 niveis
						else if($v['type']=="referencia2") {
							$select="<select name=\"".$v['id']."\" id=\"".$v['id']."\" onchange=\"document.location.href='?go=list&".$idC."='+$('#".$idC."').val()+'".$this->urlConsult($urlOut,array($idC,"pagina"))."'\">";
							$select.="<option value=\"\">- Todos -</option>"; 
							$sql=new Mysql();
							$sql2=new Mysql();
							$sql2->consult($v['campos2']['table'],"id,".$v['campos2']['campShow'],$v['campos2']['condicao']);
							while($y=mysqli_fetch_object($sql2->mysqry)) {
								$select.="<optgroup label=\"".utf8_encode($y->$v['campos2']['campShow'])."\">";
								$sql->consult($v['campos']['table'],"id,".$v['campos']['campShow'],$v['campos']['condicao']." and ".$v['campos2']['id_ref']."='".$y->id."'");
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$select.="<option value=\"".$x->id."\"".($_GET[$v['id']]==$x->id?" selected":"").">".utf8_encode($x->$v['campos']['campShow'])."</option>";
								}//"<input type=\"submit\" value=\"Buscar\" onClick=\"document.location.href='?go=list&".$idC."='+$('#".$idC."').val()\" />"
								$select.="</optgroup>";
							}
							$select.="</select>";
							break;
						}
						else if($v['type']=="select") {
							
							$select=$v['show'].": <select name=\"".$v['id']."\" id=\"".$v['id']."\" onchange=\"document.location.href='?go=list&".$idC."='+$('#".$idC."').val()+'".$this->urlConsult($urlOut,array($idC,"pagina"))."'\">";
							$select.="<option value=\"\">- Todos -</option>"; 
							foreach($v['options'] as $va) {
								$select.="<option value=\"".$va['id']."\"".($_GET[$v['id']]==$va['id']?" selected":"").">".($va['value'])."</option>";
							}
							$select.="</select>";
							break;
						} 
						else if($v['type']=="estado") {
							
							$estados="Acre=ac|Alagoas=al|Amazonas=am|Amapá=ap|Bahia=ba|Ceará=ce|Distrito Federal=df|Espírito Santo=es|Goiás=go|Maranhão=ma|Mato Grosso=mt|Mato Grosso do Sul=ms|Minas Gerais=mg|Pará=pa|Paraíba=pb|Paraná=pr|Pernambuco=pe|Piauí=pi|Rio de Janeiro=rj|Rio Grande do Norte=rn|Rondônia=ro|Rio Grande do Sul=rs|Roraima=rr|Santa Catarina=sc|Sergipe=se|São Paulo=sp|Tocantins=to";
							$estados=explode("|",$estados);
							$select=$v['show'].": <select name=\"".$v['id']."\" id=\"".$v['id']."\" onchange=\"document.location.href='?go=list&".$idC."='+$('#".$idC."').val()+'".$this->urlConsult($urlOut,array($idC,"pagina"))."'\">";
							//$select="<select name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1".($field['obg']?" obg":"")."\" style=\"width:auto;\">";
							$select.="<option value=\"\">- Todos -</option>";
							
							if(is_array($estados)) {
								foreach($estados as $vv) {
									if($v) {
										list($_val,$_id)=explode("=",$vv);
										$select.="<option value=\"".$_id."\"".($_GET[$v['id']]==$_id?" selected":"").">".($_val)."</option>";
										//$this->formulario.="<option value=\"".$_id."\"".($_id==$dataValue?" selected":"").">".$_val."</option>";
									}
								}
							}
							
							$select.="</select>";
							break;
						} 
					}
					
					$this->formulario.="<div class=\"filtros\">";
  					$this->formulario.="<div class=\"busca\"><label>".$show."</label>";
					
					$this->formulario.=$select;
      				//$this->formulario.="<input type=\"submit\" value=\"Buscar\" onClick=\"document.location.href='?go=list&data_inicio='+$('#data_inicio').val()+'&data_fim='+$('#data_inicio').val()+'&".$idC."='+$('#".$idC."').val()\" />";
					$this->formulario.=$btnBusca;
  					$this->formulario.="</div>";
  
					
					$this->formulario.="</div>";
				}
				
				$this->formulario.="<table class=\"registros\">";
				$this->formulario.="<tr>";
				$this->formulario.=$registros;
				if($campos) $campos=substr($campos,0,strlen($campos)-1);
				if($comPublicado) {
					if($this->getQuery($urlOut,'desc')==1 or $this->getQuery($urlOut,'desc')==0) { 
						$orderNow=$this->getQuery($urlOut,'desc')==1?0:1;
						
					} else {
						$orderNow=1;
					}
					$this->formulario.="<th style=\"width:75px;\"><a href=\"?go=list&ordem=pub&desc=".$orderNow.$this->urlConsult($urlOut,array('ordem','desc'))."\" class=\"tip\" title=\"Ordenar por Publicado\">Publicado".($this->getQuery($urlOut,'ordem')=="pub"?($orderNow?" &#9650;":"&#9660;"):"")."</a></th>";
					$campos.=",pub";
				}
				
				if($type=="exibicao") {
					$this->formulario.="<th style=\"width:70px;\">Visualizar</th>";
				} else if ($type=="soapaga") {
				//	$this->formulario.="<th style=\"width:70px;\">Editar</th>";
				}else {
					$this->formulario.="<th style=\"width:70px;\">Editar</th>";
				}
				if ($type!="soedita") {		
				$this->formulario.="<th style=\"width:70px;\">Apagar</th>";
				}
				$this->formulario.="</tr>";
				$sql=new Mysql();
				//echo $campos;
				
				//urlOut$url=$this->urlConsult($urlOut);
				
				$sql->consultPagMto($tabela,str_replace("datareq","data",$campos),50,$condicoes,$this->urlConsult($urlOut,array('pagina')),10,"pagina");
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$this->formulario.="<tr>";
						foreach($this->listaID as $v) {
							if($v=="id_categoria") {
								$sql2=new Mysql();
								$sql2->consult("viamais_docs_categorias","*","where id='".$x->$v."'");
								if($sql2->rows) {
									$_cat=mysqli_fetch_object($sql2->mysqry);
									$saiOut=utf8_encode($_cat->titulo);
								} else {
									$saiOut="<font color=red>-</font>";
								}
								$this->formulario.="<td><strong>".$saiOut."</strong></td>";
							} if($v=="vendedor" or $v=="id_vendedor" or $v=="supervisor" or $v=="id_supervisor") {
								$sql2=new Mysql();
								$sql2->consult("viadp_colaboradores","*","where id='".$x->$v."'");
								if($sql2->rows) {
									$_cat=mysqli_fetch_object($sql2->mysqry);
									$saiOut=utf8_encode($_cat->nome);
								} else {
									$saiOut="<font color=red>-</font>";
								}
								$this->formulario.="<td><strong>".$saiOut."</strong></td>";
							} else  if($v=="id_auditor") {
								$sql2=new Mysql();
								$sql2->consult("viamais_auditores","*","where id='".$x->$v."'");
								if($sql2->rows) {
									$_cat=mysqli_fetch_object($sql2->mysqry);
									$saiOut=utf8_encode($_cat->nome);
								} else {
									$saiOut="<font color=red>-</font>";
								}
								$this->formulario.="<td><strong>".$saiOut."</strong></td>";
							}  else if($v=="prazof") {
								list($_dia,$_mes,$_ano)=explode("/",$x->prazof);
								
								$prazo=strtotime($_ano."-".$_mes."-".$_dia);
								$hoje=strtotime(date('Y-m-d'));
								if($hoje>=$prazo and $x->status<>'concluido') {
									$this->formulario.="<td style=\"background:#cc3300;color:#ffffff;\"><strong>".$x->prazof."</strong></td>";
								} else {
									$this->formulario.="<td><strong>".$x->prazof."</strong></td>";
								}
							}else if($v=="nivel") {
								$_nivel['leve']="<font color=green>Leve</font>";
								$_nivel['grave']="<font color=ff6600>Grave</font>";
								$_nivel['gravissimo']="<font color=cc3300>Gravíssimo</font>";
								$this->formulario.="<td>".$_nivel[$x->nivel]."</td>";
							}else if($v=="id") {
								$this->formulario.="<td><input type=\"button\" value=\"Horários\" class=\"botao botao-buscar\" onclick=\"document.location.href='pg_atividades_horarios.php?id_atividade=".$x->id."'\" /></td>";
							}else if($v=="urgente" or $v=="concluido") {
								$this->formulario.=$x->$v?"<td class=\"green\">Sim</td>":"<td class=\"red\">Não</td>";
							} else if($v=="datareq") {
								list($_pri,$_seg)=explode(" ",$x->data);
								list($_ano,$_mes,$_dia)=explode("-",$_pri);
								list($_hra,$_min)=explode(":",$_seg);
								$this->formulario.="<td><strong>".$_dia."/".$_mes." ".$_hra."h ".$_min."m</strong></td>";
							} else if($v=="obs") {
								if($x->obs) {
									$this->formulario.="<td><a href=\"javascript://\" title=\"".utf8_encode($x->obs)."\" class=\"tiptip\">Ver</a></td>";
								} else {
									$this->formulario.="<td>-</td>";
								}
							} else {
								$this->formulario.="<td><strong>".utf8_encode($x->$v)."</strong></td>";
							}
						}
						if($comPublicado) {
							$this->formulario.=$x->pub?"<td class=\"green\">Sim</td>":"<td class=\"red\">Não</td>";
						}
						if($type=="exibicao") { //echo print_r($urlOut);
							$this->formulario.="<td><a href=\"?go=view&id=".$x->id.$this->url($urlOut)."\" class=\"botao botao-ver\">Visualizar</a></td>";
						} else if($type=="soapaga") { //echo print_r($urlOut); 
						
							//$this->formulario.="<td><a href=\"?go=view&id=".$x->id.$this->url($urlOut)."\" class=\"botao botao-ver\">Visualizar</a></td>";
						} else {
							$this->formulario.="<td><a href=\"?go=edit&id=".$x->id.$this->url($urlOut)."\" class=\"botao botao-editar\">Editar</a></td>";
						}
						if($type!="soedita") {
							$this->formulario.="<td><a href=\"javascript:del('?go=list&del=".$x->id.$this->url($urlOut)."');\" class=\"botao botao-apagar\">Apagar</a></td>";
						}
						$this->formulario.="</tr>";
					}
					
				}
				
				
				$this->formulario.="</table>";
				
				if($sql->rows and $sql->myspaginacao) {
					//<p class="paginacao">Página: <a href="#" class="active">1</a> <a href="#">2</a> <a href="#">3</a> <a href="#">4</a></p>
					$this->formulario.="<p class=\"paginacao\">Página: ".$sql->myspaginacao."</p>";
				}
				
			}
			
		}
		
		function carregarListaImg($attr) {//$tabela,$comPublicado="",$condicoes,$urlOut,$comFiltro="") {
			$tabela=$attr['table'];
			$comPublicado=$attr['pub'];
			$condicoes=$attr['condicao'];
			$urlOut=$attr['url'];
			//echo $attr['url'];
			$comFiltro=$attr['filtro'];
			$type=$attr['type'];
			$dir=$attr['dir'];
						
			//echo print_r($urlOut);
			if(is_array($this->lista)) {
				//echo $this->urlConsult($urlOut)."<br>";
				if($this->getQuery($urlOut,'ordem')) $ordemAtual="order by ".$this->getQuery($urlOut,'ordem');
				if($this->getQuery($urlOut,'desc')) $ordemAtual.=" desc";
				//echo $ordemAtual;die();
				$campos="id,";
				$registros="";
				$filtro=array();
				$this->listaID=array();
				foreach($this->lista as $value) {
					if($this->getQuery($urlOut,'desc')==1 or $this->getQuery($urlOut,'desc')==0) { 
						$orderNow=$this->getQuery($urlOut,'desc')==1?0:1;
						
					} else {
						$orderNow=1;
					}
					
						
						array_push($this->listaID,$value['id']);
					
				}
				$this->formulario="";
				
				
				$this->formulario.=$registros;
				if($campos) $campos=substr($campos,0,strlen($campos)-1);
				
				
				
				$sql=new Mysql();
				//echo $campos;
				if($comFiltro=="comFiltro") {
					foreach($urlOut as $v) {
						if($v['chave']=="busca") $buscaIn=$v['valor'];
						if($v['chave']=="tipo") $tipoIn=$v['valor'];
						if($v['chave']=="data_inicio") $dIIn=$v['valor'];  
						if($v['chave']=="data_fim") $dFIn=$v['valor'];  
					}
					$this->formulario.="<div class=\"filtros\">";
  					$this->formulario.="<div class=\"busca\"><label>Buscar por</label><select name=\"tipo\" id=\"tipo\">";
					
					foreach($filtro as $v) {
						$this->formulario.="<option value=\"".$v['id']."\"".($tipoIn==$v['id']?" selected":"").">".$v['show']."</option>";
					}
					$this->formulario.="<input type=\"text\" name=\"busca\" id=\"busca\" class=\"input\" value=\"".$buscaIn."\" />";
      				$this->formulario.="<input type=\"submit\" value=\"Buscar\" onClick=\"filtro('".$url."');\" />";
  					$this->formulario.="</div>";
  
					 $this->formulario.="<div class=\"periodo\">
						  <label>Período</label> de <input type=\"text\" name=\"data_inicio\" id=\"data_inicio\" class=\"data datepicker\" value=\"".$dIIn."\" /> a <input type=\"text\" name=\"data_fim\" id=\"data_fim\" class=\"data datepicker\" value=\"".$dFIn."\" />
						  <input type=\"submit\" value=\"Buscar\" onClick=\"filtro('".$url."');\" /></div>";
					$this->formulario.="</div>";
					$btnBusca="<input type=\"submit\" value=\"Buscar\" onClick=\"document.location.href='?go=list&".$idC."='+$('#".$idC."').val()\" />";
				} 
				
				// com filtro de categoria
				else if($comFiltro=="comFiltroCategoria") {
					foreach($this->campos as $v) {
						
						$show=$v['campos']['show'];
						$idC=$v['id']; 
						
						// referencia de 1 nivel
						if($v['type']=="referencia") {
							$select="<select name=\"".$v['id']."\" id=\"".$v['id']."\" onchange=\"document.location.href='?go=list&".$idC."='+$('#".$idC."').val()+'".$this->urlConsult($urlOut,array($idC,"pagina"))."'\">";
							$select.="<option value=\"\">- Todos -</option>"; 
							$sql=new Mysql();
							$sql->consult($v['campos']['table'],"id,".$v['campos']['campShow'],$v['campos']['condicao']);
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$select.="<option value=\"".$x->id."\"".($_GET[$v['id']]==$x->id?" selected":"").">".utf8_encode($x->$v['campos']['campShow'])."</option>";
							}
							$select.="</select>";
							break;
						} 
						
						// referencia de 2 niveis
						else if($v['type']=="referencia2") {
							$select="<select name=\"".$v['id']."\" id=\"".$v['id']."\" onchange=\"document.location.href='?go=list&".$idC."='+$('#".$idC."').val()+'".$this->urlConsult($urlOut,array($idC,"pagina"))."'\">";
							$select.="<option value=\"\">- Todos -</option>"; 
							$sql=new Mysql();
							$sql2=new Mysql();
							$sql2->consult($v['campos2']['table'],"id,".$v['campos2']['campShow'],$v['campos2']['condicao']);
							while($y=mysqli_fetch_object($sql2->mysqry)) {
								$select.="<optgroup label=\"".utf8_encode($y->$v['campos2']['campShow'])."\">";
								$sql->consult($v['campos']['table'],"id,".$v['campos']['campShow'],$v['campos']['condicao']." and ".$v['campos2']['id_ref']."='".$y->id."'");
								while($x=mysqli_fetch_object($sql->mysqry)) {
									$select.="<option value=\"".$x->id."\"".($_GET[$v['id']]==$x->id?" selected":"").">".utf8_encode($x->$v['campos']['campShow'])."</option>";
								}//"<input type=\"submit\" value=\"Buscar\" onClick=\"document.location.href='?go=list&".$idC."='+$('#".$idC."').val()\" />"
								$select.="</optgroup>";
							}
							$select.="</select>";
							break;
						}
					}
					
					$this->formulario.="<div class=\"filtros\">";
  					$this->formulario.="<div class=\"busca\"><label>".$show."</label>";
					
					$this->formulario.=$select;
      				//$this->formulario.="<input type=\"submit\" value=\"Buscar\" onClick=\"document.location.href='?go=list&data_inicio='+$('#data_inicio').val()+'&data_fim='+$('#data_inicio').val()+'&".$idC."='+$('#".$idC."').val()\" />";
					$this->formulario.=$btnBusca;
  					$this->formulario.="</div>";
  
					
					$this->formulario.="</div>";
				}
				//urlOut$url=$this->urlConsult($urlOut);
				
				$sql->consultPagMto($tabela,"*",50,$condicoes,$this->urlConsult($urlOut,array('pagina')),10,"pagina");
				if($sql->rows) {
					$this->formulario.="<table class=\"lista-fotos\">";
					$this->formulario.="<tr>";
					$cont=0;
					$img=new Img();
					while($x=mysqli_fetch_object($sql->mysqry)) {
						$cont++;
						//echo $dir.$x->id.".".$x->foto;
						if(file_exists($dir.$x->id.".".$x->foto)) {
							$img->po($x->id.".".$x->foto,$dir,100,100,"","","");
							$ftSai=$img->show2;
						} else {
							$ftSai="<img src=\"tools/noimg.php?w=100&h=100\"/>";
						}
						$this->formulario.="<td>".$ftSai;
						
						
						
						$this->formulario.="<br /><a href=\"?go=edit&id=".$x->id.$this->url($urlOut)."\" class=\"botao botao-editar\">Editar</a><a href=\"javascript:del('?go=list&del=".$x->id.$this->url($urlOut)."');\" class=\"botao botao-apagar\">Apagar</a></td>";
						if($cont==3) {
							$this->formulario.="</tr>";
							$cont=0;
						}
					}
					$this->formulario.=$cont?"</tr>":"";
					
					$this->formulario.="</table>";
				}
				
				
				
				if($sql->rows and $sql->myspaginacao) {
					//<p class="paginacao">Página: <a href="#" class="active">1</a> <a href="#">2</a> <a href="#">3</a> <a href="#">4</a></p>
					$this->formulario.="<p class=\"paginacao\">Página: ".$sql->myspaginacao."</p>";
				}
				
			}
			
		}
		
		function carregarCampos($atr) {//$id,$go) {
			
			
			$id=isset($atr['id'])?$atr['id']:"";
			$go=isset($atr['go'])?$atr['go']:"";
			$url=isset($atr['url'])?$atr['url']:"";
			$hiddens=isset($atr['hiddens'])?$atr['hiddens']:"";
			$hiddensForm="";
			if(is_array($hiddens)) {
				foreach($hiddens as $v) {
					if($v) {
						//var_dump($v);
						$hiddensForm.="<input type=\"hidden\" name=\"".$v['id']."\" value=\"".$v['value']."\" />";
					}
				}
			}
			echo $hiddensForm;
			if(is_array($this->campos)) {
				$this->formulario="<form method=\"post\" name=\"gerenciador\" id=\"gerenciador\" action=\"\" onsubmit=\"return valida(this);\" enctype=\"multipart/form-data\">";
				$this->formulario.="<input type=\"hidden\" name=\"acao\" value=\"".($id?$id:1)."\" />";
				if($hiddensForm) $this->formulario.=$hiddensForm;
				$this->formulario.="<div class=\"campos\">";
				$a=0;
				foreach($this->campos as $field) {
					//$this->formulario.="<div class=\"item\"><label>".$field['show'].($field['obg']?" <font color=\"#cc3300\">*</font>":"")."</label>";
					if($field['type']!="checkbox")$this->formulario.="<div class=\"item\"><label>".$field['show'].($field['obg']?" <font color=\"#cc3300\">*</font>":"")."</label>";
					if($field['type']=="textock") { 
					//	require_once("ckeditor/ckeditor.php");
						$ckeditor = new CKEditor();
						$ckeditor->basePath	= 'ckeditor/';
						$this->formulario.=$ckeditor->editor($field['id'], $this->campos[$a]['value'], "","", $obg);
					} else if($field['type']=="textockFinder") {
						//require_once("ckeditor/ckeditor.php");
						//require_once("ckfinder/ckfinder.php");
						
						$this->formulario.="<textarea id=\"".$field['id']."\" name=\"".$field['id']."\">".$this->campos[$a]['value']."</textarea>
						<script>
						var ".$field['id']." = CKEDITOR.replace('".$field['id']."',{customConfig: 'config.js',height:300,basicEntities:false});
						
						CKFinder.setupCKEditor( ".$field['id'].", 'ckfinder/' );
						
						</script>";
						
						
						//$ckeditor = new CKEditor();
						//$ckeditor->basePath	= 'ckeditor/';
						//CKFinder::SetupCKEditor( $ckeditor, 'ckfinder/' ) ;
						//$this->formulario.="<br />".$ckeditor->editor($field['id'], $this->campos[$a]['value'], "","", $obg);
						
					} else if($field['type']=="data") {
						$dataValue=$field['value'];
						$this->formulario.="<input type=\"text\" name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1 datepicker".($field['obg']?" obg":"")."\" style=\"width:100px\" value=\"".$dataValue."\" />";
					}  else if($field['type']=="datahora") {
						$dataValue=$field['value'];
						$this->formulario.="<input type=\"text\" name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1 datepicker".($field['obg']?" obg":"")."\" style=\"width:100px\" value=\"".$dataValue."\" />";
					} else if($field['type']=="text") {
						$dataValue=$field['value'];
						$this->formulario.="<input type=\"text\" name=\"".$field['id']."\" maxlength=\"".($field['maxlength']?$field['maxlength']:140)."\" id=\"".$field['id']."\" class=\"input1".($field['obg']?" obg":"").($field['class']?" ".$field['class']:"")."\" value=\"".$dataValue."\" style=\"".($field['width']?"width:".$field['width'].";":"").($field['height']?"height:".$field['height'].";":"")."\"".($field['disabled']?" disabled=\"disabled\"":"")." />";
					}  else if($field['type']=="textarea") {
						$dataValue=$field['value'];
						
						$this->formulario.="<textarea name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1".($field['obg']?" obg":"").($field['class']?" ".$field['class']:"")."\" style=\"".($field['width']?"width:".$field['width'].";":"").($field['height']?"height:".$field['height'].";":"")."\"".($field['disabled']?" disabled=\"disabled\"":"").">".$dataValue."</textarea>";
					} else if($field['type']=="checkbox") {
						$dataValue=$field['value'];
						$this->formulario.="<div class=\"item\"><label>";
						$this->formulario.="<input type=\"checkbox\" name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"\" value=\"1\" ".($dataValue==1?" checked":"")." /> ".$field['show']. "</label>";
						
					} else if($field['type']=="estado") {
						$dataValue=$field['value'];
						
						$estados="Acre=ac|Alagoas=al|Amazonas=am|Amapá=ap|Bahia=ba|Ceará=ce|Distrito Federal=df|Espírito Santo=es|Goiás=go|Maranhão=ma|Mato Grosso=mt|Mato Grosso do Sul=ms|Minas Gerais=mg|Pará=pa|Paraíba=pb|Paraná=pr|Pernambuco=pe|Piauí=pi|Rio de Janeiro=rj|Rio Grande do Norte=rn|Rondônia=ro|Rio Grande do Sul=rs|Roraima=rr|Santa Catarina=sc|Sergipe=se|São Paulo=sp|Tocantins=to";
						$estados=explode("|",$estados);
						$this->formulario.="<select name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1".($field['obg']?" obg":"")."\" style=\"width:auto;\">";
						$this->formulario.="<option value=\"\">-</option>";
						if(is_array($estados)) {
							foreach($estados as $v) {
								if($v) {
									list($_val,$_id)=explode("=",$v);
									$this->formulario.="<option value=\"".$_id."\"".($_id==$dataValue?" selected":"").">".$_val."</option>";
								}
							}
						}
						$this->formulario.="</select>";
						
					}  else if($field['type']=="select") {
						$dataValue=$field['value'];
						//echo $dataValue."a"; print_r($field);
						$this->formulario.="<select name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1".($field['obg']?" obg":"")."\" style=\"width:auto;\">";
						$this->formulario.="<option value=\"\">-</option>";
						if(is_array($field['options'])) {
							foreach($field['options'] as $v) {
								if($v) {
									$this->formulario.="<option value=\"".$v['id']."\"".($v['id']==$dataValue?" selected":"").">".$v['value']."</option>";
								}
							}
						}
						$this->formulario.="</select>";
					} else if($field['type']=="arq") {
						
						if(is_array($field['exts'])) {
							$exts=implode(",",$field['exts']);
						} else {
							$exts="TODAS";
						}
						$dataValue=$field['value'];
						$this->formulario.="<input type=\"file\" name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1".(($field['obg'] and $go=="add")?" obg":"")."\" /><p class=\"obs\">".(($field['width'] and $field['height'])?"Dimensão: ".$field['width']."x".$field['height']." - ":"").(($field['width'] and !$field['height'])?"Largura: ".$field['width']."px - ":"")."Tamanho: até ".(round((($field['size']/1024)/1024)))."MB - Extensão(s) Permitida(s): ".strtoupper($exts)."</p>"; 
					} else if($field['type']=="referencia") {
						$camp=$field['campos']; 
						$sql=new Mysql(); 
						$sql->consult($camp['table'],"*",$camp['condicao']);
						$this->formulario.="<select name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1".(($field['class'])?" ".$field['class']:"")."".(($field['obg'])?" obg":"")."\" style=\"width:600px;\"".($field['disabled']?" disabled=\"disabled\"":"")."><option value=\"\">-</option>";
						while($x=mysqli_fetch_object($sql->mysqry)) {
							if($field['id']=="id_desconto") {
								$this->formulario.="<option value=\"".$x->id."\"".($x->id==$field['value']?" selected":"").">".number_format($x->$camp['campShow'],0,",",".")." pts - Desconto: ".$x->desconto."</option>";
							} else {
								$this->formulario.="<option value=\"".$x->id."\"".($x->id==$field['value']?" selected":"").">".utf8_encode($x->$camp['campShow'])."</option>";
							}
							
						}
						$this->formulario.="</select>";
					} else if($field['type']=="password") {
						$this->formulario.="<input type=\"password\" name=\"".$field['id']."\" maxlength=\"20\" id=\"".$field['id']."\" class=\"input1".($field['obg']?" obg":"")."\" style=\"width:150px;\" />".($field['description']?"<p class=\"obs\">".$field['description']."</p>":"");;
					} else if($field['type']=="selectmultipleStatic") {
						$dataValue=$field['value'];
						if(!is_array($dataValue)) $dataValue=array();
						//var_dump($dataValue);
						$this->formulario.="<select multiple=\"true\" name=\"".$field['id']."[]\" id=\"".$field['id']."\" class=\"multiselect ".($field['obg']?" obg":"")."\" style=\"".$field['style']."\"/>".($field['description']?"<p class=\"obs\">".$field['description']."</p>":"");;
						foreach($field['options'] as $vi) { 
							$this->formulario.="<option value=\"".$vi['id']."\"".(in_array($vi['id'],$dataValue)?" selected":"").">".$vi['value']."</option>";
						}
						$this->formulario.="</select>".($field['description']?"<p class=\"obs\">".$field['description']."</p>":"");
					} else if($field['type']=="selectmultipleStaticADM") {
						$dataValue=$field['value'];
						if(!is_array($dataValue)) $dataValue=array();
						//var_dump($dataValue);
						$this->formulario.="<select multiple=\"true\" name=\"".$field['id']."[]\" id=\"".$field['id']."\" class=\"multiselect ".($field['obg']?" obg":"")."\" style=\"".$field['style']."\"/>".($field['description']?"<p class=\"obs\">".$field['description']."</p>":"");;
						foreach($field['options'] as $vi) { 
							$this->formulario.="<option value=\"".$vi['sessaoID']."\"".(in_array($vi['sessaoID'],$dataValue)?" selected":"").">".$vi['sessaoTitle']."</option>";
						}
						$this->formulario.="</select>".($field['description']?"<p class=\"obs\">".$field['description']."</p>":"");
					} else if($field['type']=="selectmultipleDinamic") {
						$dataValue=$field['value'];
						if(!is_array($dataValue)) $dataValue=array();
						$this->formulario.="<select multiple=\"true\" name=\"".$field['id']."[]\" id=\"".$field['id']."\" class=\"chosen ".($field['obg']?" obg":"")."\" style=\"".$field['style']."\"/>";
						$sql=new Mysql();
						$sql->consult($field['attr']['table'],"id,".$field['attr']['campShowCategoria'],"where lixo='0'");
						
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$this->formulario.="<option value=\"".$x->$field['attr']['id']."\"".(in_array($x->$field['attr']['id'],$dataValue)?" selected":"").">".utf8_encode($x->$field['attr']['show'])."</option>";
						}
						$this->formulario.="</select>".($field['description']?"<p class=\"obs\">".$field['description']."</p>":"");;
					}  else if($field['type']=="pub") {
						$dataValue=$field['value'];
						$this->formulario.="<select name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1".($field['obg']?" obg":"")."\" style=\"width:auto\"><option value=\"1\"".($field['value']==1?" selected":"").">Sim</option><option value=\"0\"".($field['value']==0?" selected":"").">Não</option></select>";
					} else if($field['type']=="logomarca") {
						$dataValue=$field['value'];
						$this->formulario.="<input type=\"file\" name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1".(($field['obg'] and $go=="add")?" obg":"")."\" /><p class=\"obs\">Dimensão: ".$field['width']."x".$field['height'].($field['legend']?$field['legend']:" - Extensão permitida: JPG")."</p>";
					} else if($field['type']=="alturaMinima") {
						$dataValue=$field['value'];
						$this->formulario.="<input type=\"file\" name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1".(($field['obg'] and $go=="add")?" obg":"")."\" /><p class=\"obs\">Altura mínima: ".$field['height']."px - Extensão permitida: JPG</p>";
					} else if($field['type']=="money") {
						$dataValue=$field['value'];
						$this->formulario.="<input type=\"text\" name=\"".$field['id']."\" maxlength=\"".($field['maxlength']?$field['maxlength']:140)."\" id=\"".$field['id']."\" class=\"money input1".($field['obg']?" obg":"").($field['class']?" ".$field['class']:"")."\" value=\"".$dataValue."\" style=\"width:180px;\" />";
					} else if($field['type']=="foto") {
						$dataValue=$field['value'];
						if($field['width']  and $field['height']) {
							$obs="Dimensão: ".$field['width']."x".$field['height']." - Extensão permitida: JPG";
						} else if($field['width']) {
							$obs="Mínimo de largura: ".$field['width']."px - Extensão permitida: JPG";
						
						} else if($field['height']) {
							$obs="Mínimo de altura: ".$field['height']."px - Extensão permitida: JPG";
						
						} else $obs= "";
						$this->formulario.="<input type=\"file\" name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1".(($field['obg'] and $go=="add")?" obg":"")."\" /><p class=\"obs\">".$obs."</p>";
					}else if($field['type']=="referencia") {
						$camp=$field['campos'];
						$sql=new Mysql();
						$sql->consult($camp['table'],"id,".$camp['campShow'],$camp['condicao']);
						$this->formulario.="<select name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1".(($field['obg'])?" obg":"")."\" style=\"width:auto\"><option value=\"\">-</option>";
						while($x=mysqli_fetch_object($sql->mysqry)) {
							$this->formulario.="<option value=\"".$x->id."\"".($x->id==$field['value']?" selected":"").">".utf8_encode($x->$camp['campShow'])."</option>";
						}
						$this->formulario.="</select>";
					}else if($field['type']=="referencia2") {
						$camp=$field['campos'];
						$camp2=$field['campos2'];
						$sql2=new Mysql();
						$sql=new Mysql();
						$sql2->consult($camp2['table'],"id,".$camp2['campShow'],$camp2['condicao']);
						$this->formulario.="<select name=\"".$field['id']."\" id=\"".$field['id']."\" class=\"input1".(($field['obg'])?" obg":"")."\" style=\"width:auto\"><option value=\"\">-</option>";
						while($y=mysqli_fetch_object($sql2->mysqry)) {
							$sql->consult($camp['table'],"id,".$camp['campShow'],$camp['condicao']." and ".$camp2['id_ref']."='".$y->id."'");
							$this->formulario.="<optgroup label=\"".utf8_encode($y->$camp2['campShow'])."\">";
							while($x=mysqli_fetch_object($sql->mysqry)) {
								$this->formulario.="<option value=\"".$x->id."\"".($x->id==$field['value']?" selected":"").">".utf8_encode($x->$camp['campShow'])."</option>";
							}
						$this->formulario.="</optgroup>";
						}
						$this->formulario.="</select>";
					}
					$a++;
					$this->formulario.="</div>";
				}
				$this->formulario.="<div class=\"item\"><input type=\"submit\" class=\"botao botao-editar\" value=\"Concluir\" /></div>";
				$this->formulario.="</div>";
				$this->formulario.="</form>";
			}
		
		}
		
		function includeValores($x) {	
			$a=0;
			$types=array("text","textarea","textock","textockFinder","pub","referencia","referencia2","select","estado","checkbox","money");
			foreach($this->campos as $field) { 
				if($this->campos[$a]['type']=="data") { 
					list($ano,$mes,$dia)=explode("-",$x->$field['id']);
					$this->campos[$a]['value']=$dia."/".$mes."/".$ano;
				}  else if($this->campos[$a]['type']=="money") { 
					$this->campos[$a]['value']="R$ ".number_format($x->$field['id'],2,",",".");
				} else if($field['type']=="selectmultipleStatic" or $field['type']=="selectmultipleStaticADM" or $field['type']=="selectmultipleDinamic") { 
					$this->campos[$a]['value']=explode($field['separator'],$x->$field['id']);
				} else if($field['type']=="textockFinder") { 
					$this->campos[$a]['value']=utf8_encode($x->$field['id']);
				} else if(in_array($field['type'],$types)) { 
					$this->campos[$a]['value']=utf8_encode($x->$field['id']);
				} 
				$a++;
			}
			
		}
		
		function includeValoresPOST($post) {	
		//var_dump($post);
			$a=0;
			$types=array("text","textarea","textock","textockFinder","pub","referencia","referencia2","select","estado","selectmultipleDinamic","selectmultipleStatic","selectmultipleStaticADM","data","checkbox","money");
			foreach($this->campos as $field) { 
				if(in_array($field['type'],$types)) { 
				
					$this->campos[$a]['value']=($post[$field['id']]);
					//echo $this->campos[$a]['id']." ".$this->campos[$a]['value']."<br>";
				}
				$a++;
			}
			
		}
		
		function retornarValores($post) { 
			$values="";
			$types=array("text","textarea","textock","textockFinder","pub","referencia","referencia2","select","estado","checkbox","selectmultipleStaticADM","money");
			foreach($this->campos as $field) { // echo $field['type']." ";
				if($filed['disabled']) continue;
				if($field['type']=="data") {
					list($dia,$mes,$ano)=explode("/",$post[$field['id']]);
					$values.=$field['id']."='".$ano."-".$mes."-".$dia."',";
				} if($field['type']=="password") {
					continue;
				} elseif($field['type']=="money") { 
					if($post[$field['id']]) {
						$auxSai=number_format(str_replace("R$","",trim(str_replace(",",".",str_replace(".","",$post[$field['id']])))),2,".","");
					} else {
						$auxSai=0;
					}
					$values.=$field['id']."='".$auxSai."',";
					//echo $values;die();
				} else if($field['type']=="s" or $field['type']=="selectmultipleDinamic" or $field['type']=="selectmultipleStaticADM") { //echo "aaa";
					if(is_array($post[$field['id']])) {
						$values.="`".$field['id']."`=',".implode($field['separator'],$post[$field['id']]).",',";
					} else {
						$values.=$field['id']."='',";
					}
					
				} else if(in_array($field['type'],$types)) { 
					$values.="`".$field['id']."`='".addslashes(utf8_decode($post[$field['id']]))."',";
				}
			}
			//echo $values;die();
			return substr($values,0,strlen($values)-1);
		}
		
		
	}

?>