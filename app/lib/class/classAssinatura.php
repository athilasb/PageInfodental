<?php
	class Assinatura {

		function validarAssinatura($attr) {

			$sql = new Mysql();
			$iugu = new Iugu();

			$conta = '';
			if(isset($attr['instancia']) and !empty($attr['instancia'])) {
				$sql->consult("infodentalADM.infod_contas","*","where instancia='".addslashes($attr['instancia'])."'");
				if($sql->rows) {
					$conta=mysqli_fetch_object($sql->mysqry);
				}
			}

			$subscription = isset($attr['subscription']) ? $attr['subscription'] : '';


			if(is_object($conta)) {

				if(isset($subscription->id)) {

					// se a assinatura consultada na iugu estiver suspensa
					if($subscription->suspended===false) {
						if($conta->iugu_subscription_suspended==1) {
							$sql->update("infodentalADM.infod_contas","iugu_subscription_suspended=0,iugu_subscription_suspended_data=now()","where instancia='".$conta->instancia."'");
						}
					} else {
						if($conta->iugu_subscription_suspended==0) {
							$sql->update("infodentalADM.infod_contas","iugu_subscription_suspended=1,iugu_subscription_suspended_data=now()","where instancia='".$conta->instancia."'");
						}
					}

				}


			} else {
				$this->erro="Conta não encontrada!";
				return false;
			}

		}
	}
?>