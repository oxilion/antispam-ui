<?php

class user {

	private $userId = '';
	private $pass = '';

	public function __construct($id = 0) {
		global $db;
		if ($id != 0) {
			$sql = "SELECT *
				FROM ". USER_TABLE . "
				WHERE ". USER_USERFIELD ." = '$id';";
			$q = $db->query($sql) or die($db->error);
			$r = $q->fetch_assoc();

			$this->userId = $r[USER_USERFIELD];
			$this->pass 	= $r[USER_PASSFIELD];
		}
	}

	/**
	 * Lists the domains the user has access to
	 *
	 * @return array
	 */
	public function getAccess() {
		global $db;
		$sql = "SELECT *
			FROM ". ACCOUNT_TABLE . "
			WHERE userId = $this->userId
			GROUP BY domain
			ORDER BY domain;";

		$q = $db->query($sql) or die($db->error);
		$toRe = array();
		while ($r = $q->fetch_assoc()) {
			$toRe[] = $r;
		}
		return $toRe;
	}

	/**
	 * Returns true if access should be granted and otherwise false.
	 * @return bool
	 */
	public function hasAccess($address) {
		global $db;
		$domain = substr($address, strpos($address, '@') +1);

		$sql = "SELECT *
			FROM ". ACCOUNT_TABLE . "
			WHERE userId = $this->userId
			AND (domain = '$domain' AND address = '')
			OR (domain = '$domain' AND address = '$address');";

		$q = $db->query($sql) or die($db->error);
		return $q->num_rows > 0;
	}

	/**
	 * Returns whether the user is logged in
	 * @return bool
	 */
	static function isLoggedIn() {
		return isset($_SESSION['uid']) && $_SESSION['uid'] != 0;
	}

	static function tryLogin($user, $pass) {
		global $db;
		$user = $db->escape_string($user);
		$pass = $db->escape_string($pass);
		$sql = "SELECT ". USER_USERFIELD ."
			FROM ". USER_TABLE . "
			WHERE ". USER_USERFIELD ." = '$user'
			AND ". USER_PASSFIELD ."= '$pass';";
		$r = $db->query($sql) or die('Problems');
		if ($r->num_rows > 0) {
			$_SESSION['uid'] = $user;
			return true;
		}
		else {
			return false;
		}
	}

}
