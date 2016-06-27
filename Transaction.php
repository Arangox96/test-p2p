<?php
class Transaction
{
	/**
	 * Identificación del sitio para el recaudo.
	 * @var string
	 */
	protected $login;

	/**
	 *LLave transaccional definida en Place to Pay
	 * @var string
	 */
	protected $tranKey;

	/**
	 * URL para el consumo del servicio.
	 * @var string
	 */
	protected $wsdl;

	/**
	 * @param array $config
	 * @thows Exception
	 */
	public function __construct(array $config)
	{
		if (empty($config['x_login']))
			throw new Exception(__CLASS__ . '::' . __METHOD__ . ': No fue definido el x_login.', 1001);
		if (empty($config['tranKey']))
			throw new Exception(__CLASS__ . '::' . __METHOD__ . ': No fue definido el tranKey.', 1002);
	
		$this->login = $config['x_login'];
		$this->tranKey = $config['tranKey'];		
	}

	/**
	 * @return object Auth 
	 */
	protected function getAuthorization()
	{
		$auth = new stdClass();
		$auth->login = $this->login;
		$auth->seed = date('c');
		$auth->tranKey = sha1($auth->seed.$this->tranKey);		
		return $auth;
	}

	/**
	 * @return object SoapClient
	 *
	 */
	protected function getSoapClient()
	{
		$ws = new SoapClient($this->wsdl, array(
				'trace' => true,
				'exceptions' => true,
				'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
				'soap_version' => SOAP_1_1,
				'connection_timeout' => 5
			));

		return $ws;
	}
/**
*@param array $config
*@param getBankListResult
**/

	public function getBankList(array $config)
	{
		$this->wsdl = $config['pseWSDL'];	
		$ws = $this->getSoapClient();
		$request = new stdClass();
		$request->auth = $this->getAuthorization();
		$result = $ws->getBankList($request);				
		return $result->getBankListResult;
	}
	
	/**
	* @param string $bankCode
	* @param string $bankInterface
	* @param string $reference
	* @param string $description
	* @param string $language
	* @param string $franchise
	* @param string $currency
	* @param string $totalAmount
	* @param string $taxAmount
	* @param string $tipAmount
	* @param string $payer
	* @param string $buyer
	* @param string $shipping
	* @param string $ipAddress
	* @param string $userAgent
	* @param string $additionalData
	* @param string $returnURL
	* @param string $transactionType
	* @param array $config
	* @return item
	 */
	 
	public function createTransaction($bankCode,$bankInterface,$reference, $description, $language, 
										$currency, $totalAmount,
										$taxAmount, $tipAmount, $payer, $buyer = null, $shipping = null,
										$ipAddress = null, $userAgent = null, $returnURL, array $config)
	{
			if (empty($reference) || (strlen($reference) > 32))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se requiere una referencia de la transacción no superior a 32 caracteres', 1021);
			if (empty($language) || !in_array($language, array('ES','EN','FR','PT')))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se requiere de un idioma soportado [ES, EN, PT, FR]', 1022);
			if (empty($currency) || !in_array($currency, array('COP', 'USD', 'EUR')))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se requiere de una moneda soportada [COP, EUR, USD]', 1023);
			if (empty($totalAmount) || $totalAmount < 0)
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se espera un valor para la transacción', 1024);
			if (empty($payer))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se espera los datos del pagador', 1025);
			if (empty($returnURL) || !filter_var($returnURL, FILTER_VALIDATE_URL))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se requiere una URL valida', 1026);	
			if (!empty($ipAddress) && !filter_var($ipAddress, FILTER_VALIDATE_IP))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se requiere que la dirección IP sea válida', 1028);			
			if (empty($bankCode && $bankInterface  || $franchise  ))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se debe ingresar bankCode y bankInterface o franchise', 1029);		
			if(empty($config))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se requiere la configuración del wsdl', 1032);
			
			$tran = new stdClass();			
			$tran->reference = $reference;
			$tran->description = $description;
			$tran->language = $language;
			$tran->currency = $currency;
			$tran->totalAmount = $totalAmount;
			$tran->taxAmount = $taxAmount;
			$tran->tipAmount = $tipAmount;
			$tran->payer = $payer;
			$tran->buyer = $buyer;
			$tran->shipping = $shipping;
			$tran->ipAddress = $ipAddress;
			$tran->devolutionBase=0;
			$tran->userAgent = $userAgent;
			$tran->returnURL = $returnURL;
			
			
				$tran->bankCode=$bankCode;
				$tran->bankInterface=$bankInterface;

				$this->wsdl = $config['pseWSDL'];	
			
			$request = new stdClass();
			$request->auth = $this->getAuthorization();
			$request->transaction = $tran;	
			
		try {
			 
			$ws = $this->getSoapClient();

			$result = $ws->createTransaction($request);				
			return $result->createTransactionResult->bankURL;
			
		} catch (Exception $e) {
			throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Error en el consumo al servicio', 1050);
		}
		return null;
	}
	
	
	public function getTransactionInformation($transactionID, $wsdl)
	{
		if (empty($transactionID) || (strlen($transactionID) > 7))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se requiere un transactionID valido', 1040);			
		if (empty($wsdl))
				throw new Exception(__CLASS__ . '::' . __METHOD__ . ': Se requiere de un WSDL ', 1041);
			
		$this->wsdl = $wsdl;
		$ws = $this->getSoapClient();
		
		$request = new stdClass();
		$request->auth = $this->getAuthorization();
		$request->transactionID = $transactionID;	

       $response = $ws->getTransactionInformation($request);			
	   
	   return $response;		
	}
}
