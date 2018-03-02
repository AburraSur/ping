<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PingMessage {
    private $connection;
    private $channel;
    private $callback_queue;
    private $response;
    private $corr_id;
	private $varios;

    public function __construct() {
		
		/*
		*Se instancian las variables de conexión a nuestro message broker
		*y se establece un canal mediante el cual se enviara el mensaje PING_MESSAGE a la aplicacion pong.php
		*/
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
		
		/*
		*Se define el callback para procesar la respuesta del aplicacion pong 
		*/
        list($this->callback_queue, ,) = $this->channel->queue_declare("", false, false, true, false);
        $this->channel->basic_consume($this->callback_queue, '', false, false, false, false,array($this, 'on_response'));
    }
	
	/*
	*con esta función se procesa la respuesta para mostrar al cliente HTTP
	*/
    public function on_response($rep) {
		/*
		*se evalua que el correlation_id sea el mismo que fue creado en la peticion de la funcion call quien es la encargada de comunicarse con el broker
		*/
        if($rep->get('correlation_id') == $this->corr_id) {
            $this->response = $rep->body;
        }
    }

    /*
	*En esta función se crea el mensaje PING_MESSAGE para enviar al broker
	*/	
	public function call($n) {
        $this->response = null;
		
		/*Se crea un identificador del mensaje para luego ser comparado en la respuesta recibida*/
        $this->corr_id = uniqid();
		
		/*Se parametriza el mensaje que se va a enviar por medio del AMQPMessage*/
        $msg = new AMQPMessage((string) $n,array('correlation_id' => $this->corr_id,'reply_to' => $this->callback_queue));
		/*Se publica la peticion al broker*/
        $this->channel->basic_publish($msg, '', 'rpc_queue');
		
        while(!$this->response) {
            $this->channel->wait();
        }
		
        return $this->response;
    }
};
/*Se recibe el commando del cliente HTTP*/
$command = $_GET['command'];
/*Se crea una nueva instancia de la clase PingMessage y se realiza el llamado con la funcion call*/
$ping_rpc = new PingMessage();
$response = $ping_rpc->call($command);
echo " [.] Got ", $response, "\n";
//var_dump($response);

?>