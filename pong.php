<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

if($_GET['estadisticas']){
	
	$process = curl_init('http://localhost:15672/api/overview');
	curl_setopt($process, CURLOPT_USERPWD,  "guest:guest");
	curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
	$return = curl_exec($process);
	header('Content-type: application/json');
	echo $return;
	curl_close($process);
	
}else{



$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('rpc_queue', false, false, false, false);
/*$contReceived = 0;
$contResponsed = 0;*/
echo "Escuchando peticiones RPC...\n";
$callback = function($req) {
	
    echo "Mensaje recibido (", $req->body, ")\n";
		
	if($req->body==='PING_MESSAGE'){	
		sleep(2);
		$response = "PONG_MESSAGE";
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
}
?>