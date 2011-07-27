<?php
class antispamMail {

	private $id = 0;
	private $secret = '';
	private $to;
	public $client;
	public $error = '';

	public function __construct(&$client, $id, $secret, $to) {
		$this->id = str_replace('_', '+', $id);
		$this->secret = str_replace('_', '+', $secret);
		$this->client = $client;
		$this->to = $to;
	}

	public function release() {
		try {
			$this->client->releaseMail(array('mail_id'=> $this->id,
				'secret_id'=> $this->secret,
				'to'=> $this->to));
			return true;
		}
		catch (Exception $e) {
			$this->error = 'releaseMail exception: '.  $e->getMessage(). "\n";
			print_r($this->error);
			return false;
		}
	}

	public function delete() {
		try {
			$this->client->deleteMail(array('mail_id'=> $this->id,
				'secret_id'=> $this->secret,
				'to'=> $this->to));
			return true;
		}
		catch (Exception $e) {
			$this->error = 'releaseMail exception: '.  $e->getMessage(). "\n";
			print_r($this->error);
			return false;
		}
	}
}
