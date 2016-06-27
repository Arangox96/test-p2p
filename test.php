<?php
date_default_timezone_set('America/Bogota');
ini_set('default_socket_timeout', 600);
session_start();

//Importa las clases transacción y persona.
require 'Transaction.php';
require 'Person.php';

try {
	$transaction = new Transaction(include('config.php'));	
    $person = new Person();
	
//Se define el pagador.
$payer = $person->createPerson('CC', '123456789', 'Prueba', 'Transacción', null,
								'soporte@placetopay.com', null, null, null, 'CO', null,
								null
							);
							
//Obtiene lista de bancos a usar.
$bancos = $transaction->getBankList(include('config.php'));

//Crear transacción en PSE.
	$response = $transaction->createTransaction('1022','1','201511251','pRUEBA-transacción ', 'ES', 
												'COP', 1000,0
												, 0, $payer, null,  null,
												'190.125.12.3',  null, 'https://www.google.com', include('config.php'));
												
		header ('Location:'.$response);
		
} catch (Exception $e) {
	print 'Error: ' . $e->getMessage() ;
}
