<?php
class antispam {
	public $client;
	public $error = '';

	public function __construct($wsdl, $user, $pass) {
		$header = new SoapHeader('urn:as', 'authHeader', array(
			'Clientid' => $user,
			'Password'=> $pass
		));

		try {
			$this->client = new SoapClient($wsdl, array('trace'=>true));
		}
		catch (Exception $e) {
			$this->error = 'newClient exception: '.  $e->getMessage() . "\n";
			return false;
		}
		$this->client->__setSoapHeaders(array($header));
		return true;
	}

	/**
	 * Adds a domain to the Oxilion antispam
	 * @param domain The domain to add
	 * @returns bool
	 */
	public function addDomain($domain) {
		try {
			$this->client->configureDomain(array(
				'domain'=>$domain,
				'spam'=>true,
				'badheader'=>true,
				'virus'=>true,
				'badfile'=>false,
			));
		}
		catch (Exception $e) {
			$this->error = 'addDomain exception: '.  $e->getMessage() . "\n";
			return false;
		}
		return true;
	}

	/**
	 * Deletes a domain from the Oxilion antispam
	 * @return bool
	 */
	public function removeDomain($domain) {
		try {
			$this->client->removeDomain(array('domain'=> $domain));
		}
		catch (Exception $e) {
			$this->error = 'removeDomain exception: '.  $e->getMessage() . "\n";
			return false;
		}
		return true;
	}

	/**
	 * Gets a full list of domains in Oxilion antispam
	 * @return bool
	 */
	public function getDomains() {
		return $this->client->getDomains();
	}
}
