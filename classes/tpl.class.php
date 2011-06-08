<?php
class tpl {

	private $body = '';

	public function __construct($naam) {
		$this->load($naam);
	}

	private function load($naam) {
		$this->body = file_get_contents($naam);
	}

	public function assign($wat, $door) {
		$this->body = str_replace('{'. strtoupper($wat) . '}', $door, $this->body);
	}

	public function get() {
		return $this->body;
	}
}
