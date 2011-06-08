<?php

include_once('config.php');
include_once('init.php');

$body = new tpl('tpl/index.htm');

if (!user::isLoggedIn()) {
	// Not logged in, but perhaps he tries to
	if (isset($_POST['user'])) {
		if(user::tryLogin($_POST['user'], $_POST['pass'])) {
			header("Location: ". ABSOLUTE_URL);
			die();
		} else {
			$error_tpl = new tpl('tpl/error.htm');
			$error_tpl->assign('error', 'Ongeldige gebruikersnaam en/of wachtwoord.');
			$error_msg = $error_tpl->get();
		}
	}

	$main = new tpl('tpl/login.htm');
	$main->assign('error', isset($error_msg) ? $error_msg : '');
	$body->assign('main', $main->get());
	echo $body->get();
} else {
	// User is logged in.
	if (isset($_GET['action'])) {
		$asm = new antispamMail($as->client, $_GET['id'], $_GET['key']);
		$main = new tpl('tpl/respons.htm');

		// Security consideration: In this application we consider the combination 
		// id and key strong enough as authentication.
		switch ($_GET['action']) {

		case 'release' :
			if ($asm->release($_GET['to']) && $asm->delete()) {
				$main->assign('result', 'ack');
				$main->assign('msg', 'Het bericht wordt afgeleverd en is verwijderd.');
			} else {
				$main->assign('result', 'nack');
				$main->assign('msg', 'Sorry, er is iets fout gegaan.');
			}
			break;

		case 'delete' :
			if ($asm->delete()) {
				$main->assign('result', 'ack');
				$main->assign('msg', 'Het bericht is verwijderd.');
			} else {
				$main->assign('result', 'nack');
				$main->assign('msg', 'Sorry, er is iets fout gegaan.');
			}
			break;

		default:
			$main->assign('result', 'nack');
			$main->assign('msg', 'Onbekend actie.');
			break;
		}
		echo $main->get();
	} else {
		$main = new tpl('tpl/home.htm');
		$u = new user($_SESSION['uid']);

		// Generate an accesslist
		$sAccess = '';
		$access = $u->getAccess();
		if (count($access) > 0) {
			foreach ($access As $item) {
				$domain_tpl = new tpl('tpl/home_access_line.htm');

				$ad = new antispamDomain($as->client, $item['domain']);
				$addresses = $ad->getAddresses()->addresses;

				// Per user at this domain, show the dirty work
				$users = '';
				foreach ($addresses as $address) {
					if ($u->hasAccess($address)) {
						if (count($ad->getQuarantaine($address)->quarantaine) == 0) {
							$addr_tpl = new tpl('tpl/home_access_nospam.html');
						}
						else {
							$addr_tpl = new tpl('tpl/home_access_spam.htm');

							$lines = '';
							foreach ($ad->getQuarantaine($address)->quarantaine As $obj => $s) {
								$line_tpl = new tpl('tpl/home_access_spam_line.htm');
								$from = substr($s->from, 0, strpos($s->from, '<')); // Since not everybody has the PECL lib
								$line_tpl->assign('from', htmlspecialchars($from, ENT_QUOTES));
								$line_tpl->assign('subject', htmlspecialchars($s->subject, ENT_QUOTES));
								$line_tpl->assign('type', $s->type);
								$line_tpl->assign('time', substr($s->time,0,10));
								$line_tpl->assign('reason', $s->smtp_resp);

								// Javascript doesn't support a plus sign in the id.
								$line_tpl->assign('key', str_replace('+', '_', $s->secret_id));
								$line_tpl->assign('id', str_replace('+', '_', $s->mail_id));
								$lines .= $line_tpl->get();
							}
							$addr_tpl->assign('lines', $lines);
						}
						$addr_tpl->assign('address', $address);
						$users .= $addr_tpl->get();
					}
				}

				$domain_tpl->assign('users', $users);
				$domain_tpl->assign('aantal', count($addresses));
				$domain_tpl->assign('domain', $item['domain']);
				$sAccess .= $domain_tpl->get();
			}
		} else {
			$sAccess = 'Geen domeinen';
		}
		$main->assign('access', $sAccess);

		$body->assign('main', $main->get());
		echo $body->get();
	}
}
