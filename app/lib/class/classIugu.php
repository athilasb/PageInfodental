<?php
	class Iugu {
		private 
			$prefixo = "",
			$iuguID="26CC7D7DF11A49998FFD2EAFE7FECDB9",
			//$token="8330F109B6BD34DAEC888D399CBA879138096816AF1A5CBEA4B605EB10500D0E", //-> teste
			$token="8FAA6A3AC20EFE18BF22465BCD0531700D98129043A07BDA3940A6EA27189B91", // -> producao
			$baseURL = "https://api.iugu.com/v1/";

		public 
			$ambienteChat=0; // se tiver no chatpro.com.br instancia MysqlAPI.php	

		function customersDetail($customer_id) {

			$attr=array('method'=>'customers/'.$customer_id,
						'type'=>'GET');
			if($this->endpoint($attr)) {

				return true;
			} else {
				$this->erro=$this->response->error;
				return false;
			}

		}
		function paymentMethodRemove($customer_id,$default_payment_method_id) { 
			$attr=array('method'=>'customers/'.$customer_id.'/payment_methods/'.$default_payment_method_id,
						'type'=>'DELETE',
						'pagamento'=>true);
			if($this->endpoint($attr)) {
				//var_dump($this->response);
				return true;
			} else {
				$this->erro=$this->response->error;
				return false;
			}
		}

		function paymentMethodSetDefault($customer_id,$default_payment_method_id) {
			$attr=array('method'=>'customers/'.$customer_id,
						'type'=>'PUT',
						'fields'=>array('default_payment_method_id'=>$default_payment_method_id),
						'pagamento'=>true);
			
			if($this->endpoint($attr)) {

				return true;
			} else {
				$this->erro=$this->response->error;
				return false;
			}
		}

		function formaDePagamentoListar($customer_id) {

		
			if(isset($customer_id) and !empty($customer_id)) {
				$attr=array('method'=>'customers/'.$customer_id.'/payment_methods',
							'type'=>'GET');
				if($this->endpoint($attr)) {
					return true;
				} else {
					$this->erro="Algum erro ocorreu durante o pagamento";
					return false;
				}
			}  else {
				$this->erro="Fatura não encontrada!";
				return false;
			}
		}

		function formaDePagamentoCriar($customer_id,$fields) {

			if(empty($customer_id)) {
				$this->erro="Usuário não encontrado para criação de Forma de Pagamento";
				return false;
			} else {

				$attr=array('method'=>'customers/'.$customer_id.'/payment_methods',
							'type'=>'POST',
							'pagamento'=>true,
							'fields'=>($fields));

				//var_dump($attr);
				if($this->endpoint($attr)) {

					//echo json_encode($this->info);
					if($this->info['http_code'] == 200) {

						return true;
					} else  {
						$this->erro="Algum erro ocorreu. Por favor tente novamente.";
						return false;
					} 

				} else {
					$this->erro="Algum erro ocorreu durante o pagamento";
					return false;
				}
			}
		}

		function planosListar() {
			$attr=array('method'=>'plans',
						'type'=>'GET');

			if($this->endpoint($attr)) {
				
				return true;
			} else {
				$this->erro="Algum erro ocorreu durante o pagamento";
				return false;
			}
		}

		function assinaturaAlterarPlano($fields,$id_assinatura) {
			$attr=array('method'=>'subscriptions/'.$id_assinatura,
						'type'=>'PUT',
						'fields'=>($fields));


			if($this->endpoint($attr)) {

				if($this->info['http_code'] == 500) {
					$this->erro="Algum erro ocorreu. Por favor tente novamente";
					return false;
				} else if($this->info['http_code'] == 400) {
					$this->erro="Algum erro ocorreu. Por favor tente novamente.";
					return false;
				} else {
					return true;
				}

			} else {
				$this->erro="Algum erro ocorreu durante o pagamento";
				return false;
			}
		}

		function assinaturaAtivar($id_assinatura) {
			$attr=array('method'=>'subscriptions/'.$id_assinatura.'/activate/',
						'type'=>'POST');

			if($this->endpoint($attr)) {

				//var_dump($this->response);die();

				if($this->info['http_code'] == 500) {
					$this->erro="Algum erro ocorreu. Por favor tente novamente";
					return false;
				} else if($this->info['http_code'] == 400) {
					$this->erro="Algum erro ocorreu. Por favor tente novamente.";
					return false;
				} else {
					return true;
				}

			} else {
				$this->erro="Algum erro ocorreu durante o pagamento";
				return false;
			}
		}

		function assinaturaCancelar($id_assinatura) {
			$attr=array('method'=>'subscriptions/'.$id_assinatura.'/suspend/',
						'type'=>'POST');

			if($this->endpoint($attr)) {

				if($this->info['http_code'] == 500) {
					$this->erro="Algum erro ocorreu. Por favor tente novamente";
					return false;
				} else if($this->info['http_code'] == 400) {
					$this->erro="Algum erro ocorreu. Por favor tente novamente.";
					return false;
				} else {
					return true;
				}

			} else {
				$this->erro="Algum erro ocorreu durante o pagamento";
				return false;
			}
		}

		function assinaturaAlterarPlanoDaAssinatura($id_assinatura,$id_plano) {
			$attr=array('method'=>'subscriptions/'.$id_assinatura.'/change_plan/'.$id_plano,
						'type'=>'POST');

			if($this->endpoint($attr)) {

				//var_dump($this->response);die();

				if($this->info['http_code'] == 500) {
					$this->erro="Algum erro ocorreu. Por favor tente novamente";
					return false;
				} else if($this->info['http_code'] == 400) {
					$this->erro="Algum erro ocorreu. Por favor tente novamente.";
					return false;
				} else {
					return true;
				}

			} else {
				$this->erro="Algum erro ocorreu durante o pagamento";
				return false;
			}
		}

		function assinaturaCriar($fields) {
			$attr=array('method'=>'subscriptions',
						'type'=>'POST',
						'pagamento'=>true,
						'fields'=>($fields));
			if($this->endpoint($attr)) {

				//echo json_encode($this->response);
				if($this->info['http_code'] == 200) {

					return true;
				} else  {
					$this->erro="Algum erro ocorreu. Por favor tente novamente.";
					return false;
				} 

			} else {
				$this->erro="Algum erro ocorreu durante o pagamento";
				return false;
			}
		}

		function assinaturaConsultar($id_assinatura) {

			$attr=array('method'=>'subscriptions/'.$id_assinatura.'/',
						'type'=>'GET',
						'fields'=>array()); //var_dump($attr);
			if($this->endpoint($attr)) {
				//echo json_encode($this->response);
				//var_dump($this->response);
				//var_dump($this->info);
				if($this->info['http_code'] == 500) {
					$this->erro="Algum erro ocorreu. Por favor tente novamente";
					return false;
				} else if($this->info['http_code'] == 400) {
					$this->erro="Algum erro ocorreu. Por favor tente novamente.";
					return false;
				} else {
					return true;
				}

			} else {
				$this->erro="Algum erro ocorreu durante o pagamento";
				return false;
			}
		}

		function assinaturaListar($customer_id) {

			$attr=array('method'=>'subscriptions?customer_id='.$customer_id,
						'type'=>'GET',
						'fields'=>array()); //var_dump($attr);
			if($this->endpoint($attr)) {
				//echo json_encode($this->response);
				//var_dump($this->response);
				//var_dump($this->info);
				if($this->info['http_code'] == 500) {
					$this->erro="Algum erro ocorreu. Por favor tente novamente";
					return false;
				} else if($this->info['http_code'] == 400) {
					$this->erro="Algum erro ocorreu. Por favor tente novamente.";
					return false;
				} else {
					return true;
				}

			} else {
				$this->erro="Algum erro ocorreu durante o pagamento";
				return false;
			}
		}

		function pagamentoCreditoToken($fields) {

			foreach($fields as $k=>$v) {
				if(empty($v)) {
					$this->erro="Preencha o campo $k";
					return false;
				}
			}

			$data=array("account_id"=>$this->iuguID,
							"method"=>"credit_card",
							"test"=>false,
							"data"=>$fields);

			$attr=array('method'=>'payment_token',
						'type'=>'POST',
						'pagamento'=>true,
						'fields'=>$data);

			if($this->endpoint($attr)) {
				//var_dump($this->response);die();
				if(isset($this->info)) {
					if($this->info['http_code']=="400") {
						$errors='';
						$this->erro="Dados do cartão incorreto(s)";
						return false;
					} else if($this->info['http_code']=="422") {
						$this->erro="Cartão de crédito não válido!";
						return false;
					} else if($this->info['http_code']=="200") {
						return true;
					}
				} else {
					$this->erro="Falha de conexão";
					return false;
				}
			} else {
				$this->erro="Algum erro ocorreu durante o pagamento.<br />Por favor entre em contato com nosso suporte";
				return false;
			}
		}

		function pagamentoCredito($fields) {
			foreach($fields as $k=>$v) {
				if(empty($v)) {
					$this->erro="Preencha o campo $k";
					return false;
				}
			}

			$attr=array('method'=>'charge',
						'type'=>'POST',
						'pagamento'=>true,
						'fields'=>$fields);

			if($this->endpoint($attr)) {
				return true;
			} else {
				$this->erro="Algum erro ocorreu durante o pagamento";
				return false;
			}

		}

		function pagamentoBoleto($fields) {

			foreach($fields as $k=>$v) {
				if(empty($v)) {
					$this->erro="Preencha o campo $k";
					return false;
				}
			}

			$attr=array('method'=>'charge',
						'type'=>'POST',
						'pagamento'=>true,
						'fields'=>$fields);
			if($this->endpoint($attr)) {
				return true;
			} else {
				$this->erro="Algum erro ocorreu durante o pagamento";
				return false;
			}
		}

		function faturaCriar($fields) {

		

			$attr=array('method'=>'invoices',
						'type'=>'POST',
						'pagamento'=>true,
						'fields'=>$fields);
			if($this->endpoint($attr)) {
				return true;
			} else {
				$this->erro="Algum erro ocorreu durante o pagamento";
				return false;
			}
		}

		function faturaListar($customer_id) {

		
			if(isset($customer_id) and !empty($customer_id)) {
				$attr=array('method'=>'invoices/?limit=100&customer_id='.$customer_id,
							'type'=>'GET');
				if($this->endpoint($attr)) {
					return true;
				} else {
					$this->erro="Algum erro ocorreu durante o pagamento";
					return false;
				}
			}  else {
				$this->erro="Fatura não encontrada!";
				return false;
			}
		}

		function faturaConsultar($id_invoice) {

		
			if(isset($id_invoice) and !empty($id_invoice)) {
				$attr=array('method'=>'invoices/'.$id_invoice,
							'type'=>'GET',
							'pagamento'=>true,
							'fields'=>'');

				if($this->endpoint($attr)) {
					return true;
				} else {
					$this->erro="Algum erro ocorreu durante o pagamento";
					return false;
				}
			}  else {
				$this->erro="Fatura não encontrada!";
				return false;
			}
		}

		function clientesBuscar($id_cliente) {

		
			if(isset($id_cliente) and !empty($id_cliente)) {
				$attr=array('method'=>'customers/'.$id_cliente,
							'type'=>'GET',
							'pagamento'=>true,
							'fields'=>'');

				if($this->endpoint($attr)) {
					return true;
				} else {
					$this->erro="Algum erro ocorreu durante o pagamento";
					return false;
				}
			}  else {
				$this->erro="Fatura não encontrada!";
				return false;
			}
		}

		function clientesCriar($fields) {


			foreach($fields as $k=>$v) {
				if(empty($v)) {
					$this->erro="Preencha o campo $k.";
					return false;
				}
			}
			$attr=array('method'=>'customers',
						'type'=>'POST',
						'fields'=>($fields));
			if($this->endpoint($attr)) {

				return true;
			} else {
				return false;
			}
		}	


		function clientesAlterar($id_cliente,$fields) {

			foreach($fields as $k=>$v) {
				/*if(empty($v)) {
					$this->erro="Preencha o campo $k.";
					echo "erro $k";
					return false;
				}*/
			}


			$attr=array('method'=>'customers/'.$id_cliente,
						'type'=>'PUT',
						'fields'=>($fields));

		//	var_dump($attr);die();

			if($this->endpoint($attr)) {

				return true;
			} else {
				return false;
			}
		}	

		function clientesLista() {

			$attr=array('method'=>'customers',
						'type'=>'GET');
			if($this->endpoint($attr)) {

				return true;
			} else {
				$this->erro=$this->response->error;
				return false;
			}

		}

		// executa a requisicao via curl
		function endpoint($attr) {

			$sql=$this->ambienteChat==1?new MysqlAPI():new Mysql();
			
			$erro='';
			$endpoint=$type=$fields='';

			if(isset($attr['method']) and !empty($attr['method'])) $endpoint=$this->baseURL.$attr['method'];
			if(isset($attr['type']) and ($attr['type']=="POST" or 
											$attr['type']=="GET" or 
											$attr['type']=="PATCH" or 
											$attr['type']=="DELETE"  or 
											$attr['type']=="PUT")) $type=$attr['type'];

			if(isset($attr['fields'])) $fields=$attr['fields'];
			else $fields=array();

			if(empty($endpoint)) $erro='Método não encontrado!';
			else if(empty($type)) $erro='Tipo de requisição não definida!';
			
		
			if(empty($erro)) {
				$curl = curl_init();

				$access_token=$this->token;
				$header=array();
		        $header[] = 'Accept: application/json';

				if(isset($attr['pagamento'])) { 
				
					curl_setopt_array($curl, [
						CURLOPT_URL => $endpoint."?api_token=".$this->token,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => "",
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 30,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => $type,
						CURLOPT_POSTFIELDS => json_encode($fields),
						CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
					]);
				} else { 
					$header[] = 'Authorization: Basic '.base64_encode($access_token.':');
			        $header[] = 'Accept-Charset: utf-8';
			        $header[] = 'User-Agent: Info Dental';
			        $header[] = 'Accept-Language: pt-br;q=0.9,pt-BR';

			       // var_dump($header);
			        curl_setopt_array($curl, [
					  CURLOPT_URL => $endpoint,
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => $type,
					  CURLOPT_POSTFIELDS => ($fields),
					  CURLOPT_HTTPHEADER => $header,
					]);	
			    }

				$response = curl_exec($curl);
				$err = curl_error($curl);
				$info = curl_getinfo($curl);

				$this->response=json_decode($response);
				$this->info=($info);

				
				//$sql->add("apictp_usuarios_iugu_api_log","data=now(),endpoint='$endpoint',entrada='".addslashes(json_encode($fields))."',response='".addslashes(utf8_decode($response))."',http_code='".$info['http_code']."'");
				

				curl_close($curl);
				return true;
			} else {
				$this->erro=$erro;
				return false;
			}
		}
	}

?>