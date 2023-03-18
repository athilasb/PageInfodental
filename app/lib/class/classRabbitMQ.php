<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ
{
	/**
	 * @var AMQPStreamConnection $connection
	 */
	private AMQPStreamConnection $connection;

	/**
	 * @var AMQPChannel $channel
	 */
	private AMQPChannel $channel;

	/**
	 * @var array
	 */
	private array $authData;

	/**
	 * @var string
	 */
	private string $queue;

	/**
	 * RabbitMQ constructor.
	 * @param array $connection
	 */
	public function __construct(array $connection)
	{
		$this->authData = $connection;
	}


	public function sendTest(string $message,string $rabbitmqFila): bool
	{	
		if(empty($this->authData)) return false;
		else if(empty($this->queue)) return false;


		try {
			

			
			$AMQPMessage = new AMQPMessage($message, array(
				'expiration' => '3600000'
			));
			$this->channel->basic_publish($AMQPMessage, '', $this->queue);
			$this->channel->wait_for_pending_acks(100000);
		

		

			$this->channel->set_nack_handler(
				function (AMQPMessage $message){
					$body = json_decode($message->body);
					var_dump($body);
				}
			);
		

			

			return true;
		} catch (Exception $exception) {

			$this->erro=$exception->getMessage();
			return false;
		}
	}
	/**
	 * @param string $message
	 * @return bool
	 */
	public function sendMessageToQueueWts($message,$rabbitmqFila): bool
	{	
		if(empty($this->authData)) return false;
		else if(empty($this->queue)) return false;

		try {
			


			$wtsMsg='';
			///if(isset($id_whatsapp) and is_numeric($id_whatsapp) and !empty($message)) {
				//$sql->consult("ident_whatsapp_mensagens","*","where id=$id_whatsapp");
		
				//if($sql->rows) {
					//$wtsMsg=mysqli_fetch_object($sql->mysqry);


					$expiration=1800000;
					/*$sql->consult("lbox_whatsapp_mensagens_tipos","*","where id=$wtsMsg->id_tipo");
					if($sql->rows) {
						$wtsMsgTipo=mysqli_fetch_object($sql->mysqry);

						if($wtsMsgTipo->rabbitmq_expiration>0) $expiration=$wtsMsgTipo->rabbitmq_expiration;
					}

					$this->id_unidade=$wtsMsg->id_unidade;*/
					
					

					$AMQPMessage = new AMQPMessage($message, array('expiration' => $expiration));
					$this->channel->basic_publish($AMQPMessage, '', $this->queue);
					$this->channel->wait_for_pending_acks(100000);
					//$sql->update("lbox_whatsapp_mensagens","rabbitmq_enviado=now()","WHERE id='".$id_whatsapp."'");
			
					$this->channel->set_nack_handler(
						function (AMQPMessage $message){
							$body = json_decode($message->body);
							var_dump($body);
							
							//$sql->update("lbox_whatsapp_mensagens","rabbitmq_enviado='0000-00-00 00:00:00'","WHERE id='".$body->impressoes[0]->id."'");
							//$vsql="fila='".addslashes($message->delivery_info['routing_key'])."',json_envio='".addslashes(json_encode($body->impressoes[0]))."',id_impressao='". $body->impressoes[0]->id."',erro=1";
							
							//$sql->update($vucaName."impressoras_fila_rabbitmq",$vsql,"WHERE id='".$body->impressoes[0]->id."'");
						}
					);
					
				//}
			//}

			

			return true;
		} catch (Exception $exception) {
			return false;
		}
	}

	/**
	 * @param string $message
	 * @return bool
	 */
	public function sendMessageToQueue(string $message,int $id_impressao,string $rabbitmqFila): bool
	{	
		if(empty($this->authData)) return false;
		else if(empty($this->queue)) return false;

		try {
			

			$sql = new Mysql();

			$result=$sql->add("lbox_impressoras_fila_rabbitmq","fila='".addslashes($rabbitmqFila)."',json_envio='".addslashes($message)."',id_impressao='".$id_impressao."'");	


			// se adicionou (nao teve duplicado)
			if($result===true) {
				$AMQPMessage = new AMQPMessage($message, array(
					'expiration' => '3600000'
				));
				$this->channel->basic_publish($AMQPMessage, '', $this->queue);
				$this->channel->wait_for_pending_acks(100000);
				$sql->update("lbox_impressoras_fila","impressao_enviada=now()","WHERE id='".$id_impressao."'");
			

				//$this->channel->set_ack_handler(
				$this->channel->set_nack_handler(
					function (AMQPMessage $message){
						$body = json_decode($message->body);
						$sql = new Mysql();

						//var_dump($this->channel);
						$sql->update("lbox_impressoras_fila","impressao_enviada='0000-00-00 00:00:00'","WHERE id='".$body->impressoes[0]->id."'");
						$vsql="fila='".addslashes($message->delivery_info['routing_key'])."',json_envio='".addslashes(json_encode($body->impressoes[0]))."',id_impressao='". $body->impressoes[0]->id."',erro=1";

						$sql->update("lbox_impressoras_fila_rabbitmq",$vsql,"where id='".$body->impressoes[0]->id."'");
					}
				);
			}

			

			return true;
		} catch (Exception $exception) {
			 $this->erro=$exception->getMessage();

			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function createConnection(): bool
	{
		if(empty($this->authData)) return false;

		try {
			$this->connection = new AMQPStreamConnection($this->authData['host'], $this->authData['port'], $this->authData['username'], $this->authData['password']);
			$this->channel = $this->connection->channel();
			$this->channel->confirm_select();

			if(!empty($this->queue)) {
				$this->channel->queue_declare($this->queue, false, false, false, false);
			}

			return true;
		} catch (Exception $exception) {

			//echo "Connection: ".$exception->getMessage();

			//var_dump($exception);
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function closeConnection(): bool
	{
		try {
			$this->channel->close();
			$this->connection->close();
			return true;
		} catch (Exception $exception) {
			return false;
		}
	}

	/**
	 * @param string $queue
	 * @return string
	 */
	public function setQueue(string $queue): string
	{
		$this->queue = $queue;
		return $this->queue;
	}

	/**
	 * @param string $queue
	 * @return string
	 */
	public function declareQueue(string $queue): bool
	{	
		try {
			$this->channel->queue_declare($queue, false, false, false, false);
			return true;
		} catch (Exception $exception) {
			return false;
		}
	}
}