<?php

require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('rpc_queue', false, false, false, false);
$contReceived = 0;
$contResponsed = 0;
echo " [x] Awaiting RPC requests\n";
$callback = function($req) {
	
    echo " [.] Receive(", $req->body, ")\n";
		
	if($req->body==='PING_MESSAGE'){	
		sleep(mt_rand(2, 5));
		$response = "PONG_MESSAGE";
	}elseif($req->body==='ESTADISTICAS'){
		//$response = 'SIN CALCULO POR AHORA';
		
	}
	
	$msg = new AMQPMessage((string) $response, array('correlation_id' => $req->get('correlation_id')) );

    $req->delivery_info['channel']->basic_publish($msg, '', $req->get('reply_to'));
    $req->delivery_info['channel']->basic_ack($req->delivery_info['delivery_tag']);
	
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

		
$channel->close();
$connection->close();

?>