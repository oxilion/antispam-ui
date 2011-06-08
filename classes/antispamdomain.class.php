<?php
class antispamDomain {

	private $client;
	private $domain = '';
	public $error = '';

	public function __construct(&$client, $domain) {
		$this->client = $client;
		$this->domain = $domain;
	}

	public function getAddresses() {
		try {
			return $this->client->getAddresses(array('domain'=>$this->domain));
		}
		catch (Exception $e) {
			$this->error = 'getAddresses exception: '.  $e->getMessage() . "\n";
			print_r($this->error);
		}
	}

	public function getQuarantaine($address = '') {
		try {
			$account = !empty($address) ? $address : $this->domain;
			return $this->client->getQuarantaine(array('account'=>$account));
		}
		catch (Exception $e) {
			$this->error = 'getQuarantaine exception: '.  $e->getMessage(). "\n";
			print_r($this->error);
		}
	}
}

