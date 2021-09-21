<?php
	
	class Adm {
		
		private 
			$pre,
			$_p="wtalk_",
			$emp,
			$dinheiro=array('price','valor','instrumentacao','teleconsulta_valor','teleconsulta_desconto','consultapresencial_valor','consultapresencial_desconto','salario'),
			$bool=array('instrumentacao_pago','credito','efetivado'),
			$datahora=array('data','data_cirurgia'),
			$checkbox=array('pub','destaque','face','quantitativo','dia_inteiro','responsavel_possui','atendimentopersonalizado','permitir_acesso','estrangeiro','custo_recorrente','custo_fuxo'),
			$noupper=array('email','instagram','tipo','legenda','codigo_conversao','codigo_head','codigo_body','instagram'),
			$multi=array('permissoes','unidade','camposEvolucao'),
			$telefones=array('telefone','telefone1','telefone2','telefone3'),
			$unmask=array('cpf','cnpj'),
			$datas=array('data_nascimento','data_modelo','data_noticia','data_versao','data_reg','data_catalogo','vencimento','efetivado_data','responsavel_datanascimento','data_vencimento','data_emissao','data_extrato');
		
		
		
		function __construct($prefixo) {
			$this->pre=$prefixo;
		}

		function biCategorizacao() {
			$curl = curl_init();

			curl_setopt_array($curl, [
			  CURLOPT_URL => "https://studiodental.dental/infodental/cmd/bi.php",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => "",
			]);

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			/*if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  echo $response;
			}*/
		}
		
		function values($campos,$cnt) {
			$values=array();;
			
			
			$_data=$this->datas;
			$_datahora=$this->datahora;
			$_multi=$this->multi;
			$_multiEsp=array('closer_campaigns');
			$_dinheiro=$this->dinheiro;
			$_checkbox=$this->checkbox;
			$_noupper=$this->noupper;
			
			if(is_array($campos)) {
				foreach($campos as $v) {
					if(in_array($v,$_multiEsp)) { 
						$values[$v]=explode(" ",$cnt->$v);
					} else if(isset($cnt->$v)) {
						if(in_array($v,$_data)) {
							if(!empty($cnt->$v) and strpos($cnt->$v,"-")>0) {
								list($_a,$_m,$_d)=@explode("-",$cnt->$v);
								if(checkdate($_m,$_d,$_a)) {
									$values[$v]=$_d."/".$_m."/".$_a;
								} else {
									$values[$v]="";
								}
							} else {
								$values[$v]="";
							}
						} else if(in_array($v,$_datahora)) {
							if(!empty($cnt->$v) and strpos($cnt->$v,"-")>0) {
								list($_dt,$_hr)=@explode(" ",$cnt->$v);
								list($_a,$_m,$_d)=@explode("-",$_dt);
								list($_h,$_min)=@explode(":",$_hr);
								if(checkdate($_m,$_d,$_a)) {
									$values[$v]=$_d."/".$_m."/".$_a." ".$_h.":".$_min;
								} else {
									$values[$v]="";
								}
							} else {
								$values[$v]="";
							}
						} else if(in_array($v,$_multi)) { 
							$values[$v]=explode(",",$cnt->$v);
						} else if(in_array($v,$_dinheiro)) {
							$values[$v]=number_format($cnt->$v,2,",",".");
						} else {
							$values[$v]=utf8_encode($cnt->$v);
						}
					} else
						$values[$v]="";
				}
			}
		
			return $values;
		}
		
		function vSQL($campos,$post) {
			$rtn='';
			$values=array();
			
			$_data=$this->datas;
			$_datahora=$this->datahora;
			$_dinheiro=$this->dinheiro;
			$_checkbox=$this->checkbox;
			$_noupper=$this->noupper;
			$_multi=$this->multi;
			$_multiEsp=array('closer_campaigns');
			$_telefones=$this->telefones;
			$_unmask=$this->unmask;
			
			if(is_array($campos)) {
				foreach($campos as $v) {
					if(isset($post[$v])) $values[$v]=$post[$v];
					
					if(in_array($v,$_checkbox)) { 
						$rtn.=$v."='".((isset($post[$v]) and $post[$v]==1)?1:0)."',";
					} else if(in_array($v,$_multiEsp)) { 
						if(isset($post[$v]) and is_array($post[$v]) and count($post[$v])>0) $rtn.=$v."='".implode(" ",$post[$v])." -',";
						else $rtn.=$v."=NULL,";
					} else if($v=="active") {
						$rtn.=$v."='".((isset($post[$v]) and $post[$v]=='Y')?'Y':'N')."',";
					} else if(isset($post[$v])) { 
						if(in_array($v,$_data)) {
							if(!empty($post[$v]) and strpos($post[$v],"/")>0) {
								list($_d,$_m,$_a)=@explode("/",$post[$v]);
								if(checkdate($_m,$_d,$_a)) {
									$rtn.=$v."='".$_a."-".$_m."-".$_d."',";
								} else {
									$rtn.=$v."='0000-00-00',";
								}
							} else {
								$rtn.=$v."='0000-00-00',";
							}
						} else if(in_array($v,$_datahora)) {
							if(!empty($post[$v]) and strpos($post[$v],"/")>0) {
								list($_dt,$_hr)=@explode(" ",$post[$v]);
								list($_d,$_m,$_a)=@explode("/",$_dt);
								list($_h,$_min)=@explode(":",$_hr);
								if(checkdate($_m,$_d,$_a)) {
									$rtn.=$v."='".$_a."-".$_m."-".$_d." ".$_h.":".$_min.":00',";
								} else {
									$rtn.=$v."='0000-00-00 00:00:00',";
								}
							} else {
								$rtn.=$v."='0000-00-00 00:00:00',";
							}
						} else if(in_array($v,$_dinheiro)) {
							$rtn.=$v."='".str_replace(",",".",str_replace(".","",$post[$v]))."',";
						} else if(in_array($v,$_noupper)) { 
							$rtn.=$v."='".utf8_decode(($post[$v]))."',";
						} else if(in_array($v,$_telefones)) { 
							$rtn.=$v."='".telefone($post[$v])."',";
						} else if(in_array($v,$_unmask)) { 
							$rtn.=$v."='".str_replace(".","",str_replace("-","",str_replace("/","",str_replace("_","",$post[$v]))))."',";
						} else if(in_array($v,$_multi)) { 
							if(is_array($post[$v])) $rtn.=$v."=',".implode(",",$post[$v]).",',";
							else $rtn.=$v."='',";
						} else {
							
							$rtn.=$v."='".addslashes(utf8_decode(($post[$v])))."',";
						}
					}
				}
			}
		
			$this->values=$values;
			return $rtn;
		}
		
		function csv($tabela,$sql,$where,$colunas=array()) {
			$del=";";
			
			
			$_data=$_data=$this->datas;
			$_datahora=$this->datahora;
			
			if(count($colunas)==0) {
				$sql->colunas($tabela);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					var_dump($x);
				}
			} else {
				$_cols="";
				foreach($colunas as $k=>$v) {
					$_cols.='"'.$v.'"'.$del;
				}
				$_cols.="\r\n";
				
				$sql->consult($tabela,"*",$where);
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						foreach($colunas as $k=>$v) {
							if(in_array($k,$_data)) {
								list($_a,$_m,$_d)=explode("-",$x->$k);
								$_cols.='"'.$_d.'/'.$_m.'/'.$_a.'"'.$del;
							} elseif(in_array($k,$_datahora)) {
								list($_dts,$_hrs)=explode(" ",$x->$k);
								list($_a,$_m,$_d)=explode("-",$_dts);
								list($_h,$_min,$_seg)=explode(":",$_hrs);
								
								$_cols.='"'.$_d.'/'.$_m.'/'.$_a.' '.$_h.':'.$_min.':'.$_seg.'"'.$del;
							} else {
								$_cols.='"'.$x->$k.'"'.$del;
							}
						}
						$_cols.="\r\n";
					}
				}
				
				
				
			}
			
			
			$arq=fopen("arqs/csv/csv.csv","w");
			fputs($arq,$_cols);
			fclose($arq);
			
			return "arqs/csv/csv.csv";
		}

		function csv2($tabela,$sql,$where,$colunas=array(),$especificacoes=array()) {
			$del=";";
			$_p=$this->_p;
			$_data=$this->datas;
			$_datahora=$this->datahora;
			$_dinheiro=$this->dinheiro;
			$_bool=$this->bool;
			$_restrito=array('credito_cartao','credito_codigo_seguranca','adesao_cartao','adesao_codigo_seguranca');
			$_multi=array('claro_id_pacote_alacarte','net_id_pacote_alacarte');
			
			if(count($colunas)==0) {
				$sql->colunas($tabela);
				while($x=mysqli_fetch_object($sql->mysqry)) {
					var_dump($x);
				}
			} else {
				
				$_cols="";
				foreach($colunas as $v) {
					$k=$v;
					if(isset($especificacoes[$v])) {
						$_cols.='"'.($especificacoes[$v]['title']).'"'.$del;
					} else {
						$_cols.='"'.str_replace("_"," ",($v)).'"'.$del;	
					}
				}
				$_cols.="\r\n";
				
				
										$sql2=new Mysql();
				$sql->consult($tabela,"*",$where);
				if($sql->rows) {
					while($x=mysqli_fetch_object($sql->mysqry)) {
						foreach($colunas as $k) {
							$v=$k;
							if(in_array($k,$_data)) {
								list($_a,$_m,$_d)=explode("-",$x->$k);
								if($_d.'/'.$_m.'/'.$_a!="00/00/0000")
									$_cols.='"'.$_d.'/'.$_m.'/'.$_a.'"'.$del;
								else 
									$_cols.='"-"'.$del;
							} else if(in_array($k,$_dinheiro)) {
								$_cols.='"'.number_format($x->$v,2,",",".").'"'.$del;
							} else if(in_array($k,$_multi)) {
								$multiOut='';
								$multiAux=explode(",",$x->$k);
								if(is_array($multiAux) and count($multiAux)>0) {
									foreach($multiAux as $mc) {
										if(isset($_pacotes[$mc])) {
											$multiOut.=$_pacotes[$mc]->titulo.', ';
										}
									}
								}
								$_cols.='"'.(empty($multiOut)?"-":substr($multiOut,0,strlen($multiOut)-2)).'"'.$del;
							} else if(in_array($k,$_restrito)) {
								$_cols.='"***"'.$del;
							} else if(in_array($k,$_bool)) {
								$_cols.='"'.($x->$v==1?"SIM":"NAO").'"'.$del;
							} elseif(in_array($k,$_datahora)) {
								list($_dts,$_hrs)=explode(" ",$x->$k);
								list($_a,$_m,$_d)=explode("-",$_dts);
								list($_h,$_min,$_seg)=explode(":",$_hrs);
								if($_d.'/'.$_m.'/'.$_a.' '.$_h.':'.$_min.':'.$_seg!="00/00/0000 00:00:00")
									$_cols.='"'.$_d.'/'.$_m.'/'.$_a.' '.$_h.':'.$_min.':'.$_seg.'"'.$del;
								else 
									$_cols.='"-"'.$del;
							} else if(isset($especificacoes[$v])) {
								$e=$especificacoes[$v]; //echo $e['table'];
								if($e['table']=="vicidial_users") {
									$ql->consult($e['table'],$e['field'].",".$e['id'],"where ".$e['id']."='".$x->$v."'");
									if($ql->rows) {
										$y=mysqli_fetch_object($ql->mysqry);
										$esp=($y->$e['field']);
									} else $esp='-';
								} else  {
									if($e['table']==$_p."parametros_pacotes") {
										if(isset($_pacotes[$x->$v])) {
											$esp=$_pacotes[$x->$v]->titulo;
										} else {
											$esp='-';
										}
									} else {
										$sql2->consult($e['table'],$e['field'].",".$e['id'],"where ".$e['id']."='".$x->$v."'");
										if($sql2->rows) {
											$y=mysqli_fetch_object($sql2->mysqry);
											$esp=($y->$e['field']);
										} else $esp='-';
									}
								}
								$_cols.='"'.$esp.'"'.$del;
							} else{
								$_cols.='"'.($x->$k).'"'.$del;
							}
						}
						$_cols.="\r\n";
					}
				}
				
				
				
			}
			
			
			$arq=fopen("arqs/csv/csv.csv","w");
			fputs($arq,$_cols);
			fclose($arq);
			
			return "arqs/csv/csv.csv";
		}
		
		function get($get) {
			
			$str = new String();
			
			$datas=array('data','data_inicio','data_fim');
			$multi=array('status_multi','status_produto_multi','status_ged_multi','status_entrega_multi');
			
			$values=array();
			if(isset($get) and is_array($get)) {
				foreach($get as $k=>$v) {
					if(in_array($k,$datas)) {
						if(!empty($v)) {
							list($dia,$mes,$ano)=explode("/",$v);
							if(checkdate($mes,$dia,$ano)) {
								$values[$k]=$v;
								$values[$k.'WH']=$ano."-".$mes."-".$dia;
							} else {
								$values[$k]=date('d/m/Y');
								$values[$k.'WH']=date('Y-m-d');
							}
						}
					} else if(in_array($k,$multi)) {
						if(is_array($v)) $values[$k]=$v;
						else $values[$k]=array();
					} else {
						$values[$k]=$str->protege($v);
					}
				}
			}
			
			return $values;
		}
		
		function url($get) {

			$noURL=explode(",","fichadocliente,edita,pagina,ancoraanexo,csv,deleta,desconciliar,form,pagina,alterarUnidade,deletaPaciente,unirPagamentos,abrirAnamnese");

			$url="";
			if(is_array($get)) {
				foreach($get as $k=>$v) {
					if($v and !in_array($k, $noURL)) {
						if(is_array($v)) {
							$url.=http_build_query(array($k=>$v))."&";
						} else {
							$url.=$k."=".$v."&";
						}
					}
				}
			}
			
			return $url;
		}
		
		
	}


?>