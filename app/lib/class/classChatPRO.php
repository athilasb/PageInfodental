<?php

	class ChatPRO {

		public $prefixo,$whatsapp='';

		function __construct($attr) {
			if(isset($attr['prefixo'])) $this->prefixo=$attr['prefixo'];
			$id_whatsapp=(isset($attr['id_whatsapp']) and is_numeric($attr['id_whatsapp']))?$attr['id_whatsapp']:0;
			$sql = new Mysql();
			$_p=$this->prefixo;
			$sql->consult($_p."whatsapp_instancias","*","where id='".$id_whatsapp."' and lixo=0");
			if($sql->rows) {
				$this->whatsapp=mysqli_fetch_object($sql->mysqry);
			}
		}

		function endpoint($method) {
			$wts=$this->whatsapp;

			if(is_object($wts)) {

				$erro='';
				if(empty($wts->endpoint)) $erro="Endpoint não definido!";
				else if(empty($wts->token)) $erro="Token não definido!";
				else if(empty($method)) $erro="Método não definido!";

				if(empty($erro)) {
					$curl = curl_init();
					$endpoint=$wts->endpoint."/".$method;

					if(substr($endpoint,0,8)!="https://") $endpoint="https://".$endpoint;

					curl_setopt_array($curl, array(
					  CURLOPT_URL => $endpoint,
					  CURLOPT_RETURNTRANSFER => true,
					  CURLOPT_ENCODING => "",
					  CURLOPT_MAXREDIRS => 10,
					  CURLOPT_TIMEOUT => 30,
					  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					  CURLOPT_CUSTOMREQUEST => "GET",
					  CURLOPT_POSTFIELDS => "",
					  CURLOPT_COOKIE => "__cfduid=d9d1f58c808d70b7bdbced16eec6338b91592226351",
					  CURLOPT_HTTPHEADER => array(
					    "accept: application/json",
					    "authorization: ".$wts->token
					  ),
					));

					$response = curl_exec($curl);
					$err = curl_error($curl);

					curl_close($curl);

					if ($err) {
					  $this->erro="Erro na comunicação com o ChatPRO:<br />".$err;
					  return false;
					} else {
						$this->response=$response;
						return true;
					}
				} else {
					$this->erro=$erro;
					return false;
				}
			} else {
				$this->erro="Instância não definida!";
				return false;
			}
		}

		function status() {
			$_p=$this->prefixo;
			$wts=$this->whatsapp;

			if(is_object($wts)) {

				if($this->endpoint('status')) {

					$resp=json_decode($this->response);

					if(isset($resp->connected) and $resp->connected==true) {
						list($numero,)=explode("@",$resp->info->Wid);
						$this->marcamodelo=$resp->info->Phone->DeviceManufacturer."/".$resp->info->Phone->DeviceModel;
						$this->pushname=$resp->info->Pushname;
						$this->numero=substr($numero,2,strlen($numero));
						$this->bateria=$resp->info->Battery."%";
						$this->connected=true;
					} else {
						$this->connected=false;
					}

					return true;
				} else {
					return false;
				}
			} else {
				$this->erro="Instância não definida!";
				return false;
			}
		}

		function qrcode() {
			$_p=$this->prefixo;
			$wts=$this->whatsapp;

			if(is_object($wts)) {

				if($this->endpoint('generate_qrcode')) {

					$resp=json_decode($this->response);

					if(isset($resp->qr)) {
						$this->qrcode=$resp->qr;
						return true;
					} else {
						$this->erro="Algum erro ocorreu durante a geração do QR Code!";
						return false;
					}
				} else { 
					return false;
				}
			} else {
				$this->erro="Instância não definida!";
				return false;
			}
		}


	}
?>