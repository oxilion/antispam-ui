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
			echo "Couldn't login";
		}
	}
	$main = new tpl('tpl/login.htm');
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
			foreach ($access As $key => $item) {
				$tmp = new tpl('tpl/home_access_line.htm');

				$ad = new antispamDomain($as->client, $item['domain']);
				$addr = $ad->getAddresses();

				$nrAdr = count($addr->addresses);

				// Per user at this domain, show the dirty work
				$users = '';
				if ($nrAdr > 0) {
					foreach ($addr->addresses As $id => $address) {
						if ($u->hasAccess($address)) {
							if (count($ad->getQuarantaine($address)->quarantaine) == 0) {
								$tmp2 = new tpl('tpl/home_access_nospam.html');
							}
							else {
								$tmp2 = new tpl('tpl/home_access_spam.htm');

								$lines = '';
								foreach ($ad->getQuarantaine($address)->quarantaine As $obj => $s) {
									$tmp3 = new tpl('tpl/home_access_spam_line.htm');
									$from = substr($s->from, 0, strpos($s->from, '<')); // Since not everybody has the PECL lib
									$tmp3->assign('from', htmlspecialchars($from, ENT_QUOTES));
									$tmp3->assign('subject', htmlspecialchars($s->subject, ENT_QUOTES));
									$tmp3->assign('type', $s->type);
									$tmp3->assign('time', substr($s->time,0,10));
									$tmp3->assign('reason', $s->smtp_resp);

									// Javascript doesn't support a plus sign in the id.
									$tmp3->assign('key', str_replace('+', '_', $s->secret_id));
									$tmp3->assign('id', str_replace('+', '_', $s->mail_id));
									$lines .= $tmp3->get();
								}
							}
							$tmp2->assign('lines', $lines);
							$tmp2->assign('address', $address);
							$users .= $tmp2->get();
						}
					}
				}
				$tmp->assign('users', $users);
				$tmp->assign('aantal', $nrAdr);
				$tmp->assign('domain', $item['domain']);
				$sAccess .= $tmp->get();
			}
		} else {
			$sAccess = 'Geen domeinen';
		}
		$main->assign('access', $sAccess);

		$body->assign('main', $main->get());
		echo $body->get();
	}
}
