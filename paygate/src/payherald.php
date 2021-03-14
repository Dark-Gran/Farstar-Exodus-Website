<?php
declare(strict_types=1);

namespace PayGate;

class PayHerald
{

	public function RUN()
	{
		
		$logloc = 'C:\Apache24\htdocs\hooklog.log';
		//TODO: PayPal SDK Verification 
		//IP CHECK
		//$plist = include "/Apache24/htdocs/paygate_plist.php";
		$plist = $this->getPList();
		$ip = $this->getUserIpAddr();
		//$ip = $_SERVER['REMOTE_ADDR'];
		
		//PROCESS POST
		if($json = json_decode(file_get_contents("php://input"), true)) {
	 		$data = $json;
 		} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	 		$data = $_POST;
 		}
		
		if (isset($data)) {
		
			$tim = time();
			$dat = date("Y-m-d H:i:s", $tim);
			$timestamp = $tim . " - " . $dat;
			//save me the data
			error_log("::::::::::::::::::::::::::::::::::::::::::\r\n" . "-----------(" . $timestamp . " (UTC+0))-----------\r\n" . print_r($data, true) . "\r\n", 3, $logloc);
			
			if (isset($ip) && in_array($ip, $plist)) {
								
				//DB Connection
				//$configs = include "/Apache24/htdocs/paygate_config.php";
				//$conn = new \mysqli($configs['host'], $configs['username'], $configs['password'], $configs['dbname']);
				$conn = new \mysqli('127.0.0.1', 'herald', 'W3bh00k3r', 'fs_main');
				if ($conn->connect_error || mysqli_connect_error()) {
					error_log("-----------HERALD ERROR: COULD NOT REACH THE ARCHIVES!-----------\r\n::::::::::::::::::::::::::::::::::::::::::\r\n", 3, $logloc);
					http_response_code(500);
					die();
				}
				//handle data
				if (isset($data['resource']['purchase_units'][0]['custom_id']) && isset($data['id']) && isset($data['event_type']) && isset($data['resource']['status']) && isset($data['resource']['purchase_units'][0]['amount']['value']) && isset($data['create_time']) && isset($data['resource']['payer']['email_address']) && isset($data['resource']['id'])) {
					$hook_type = $data['event_type'];
					$userstr = $data['resource']['purchase_units'][0]['custom_id'];
					$userarr = explode("&", $userstr);
					if (isset($userarr[0]) && isset($userarr[1])) {
						$username = $userarr[0];
						$userid = $userarr[1];
					}
					$status = $data['resource']['status'];
					//check for duplicit webhook
					$hook_id = $data['id'];
					$pay_id = $data['resource']['id'];
					$query = "SELECT * FROM hooklogs WHERE hook_id='$hook_id' AND pay_id='$pay_id'";
					$result = $conn->query($query);
					if ($result->num_rows > 0) {
						error_log("-----------HERALD ERROR: DETECTED DUPLICITY!-----------\r\n::::::::::::::::::::::::::::::::::::::::::\r\n", 3, $logloc);
						http_response_code(200);
						die();
					} else if (!isset($username) || !isset($userid) || $userid == "0" || !($status == "COMPLETED" || $status == "APPROVED") || !($hook_type == "CHECKOUT.ORDER.COMPLETED" || $hook_type == "CHECKOUT.ORDER.APPROVED")) {
						error_log("-----------HERALD ERROR: FOUND INVALID VARIABLES!-----------\r\n::::::::::::::::::::::::::::::::::::::::::\r\n", 3, $logloc);
						http_response_code(500);
						die();
					} else {
						//prepare vals
						$usd = $data['resource']['purchase_units'][0]['amount']['value'];
						$mail = $data['resource']['payer']['email_address'];
						$time_creation = $data['create_time'];
						$time_reception = $timestamp;
						//get info from db
						$query = "SELECT * FROM users WHERE userID='$userid'";
						$result = $conn->query($query);
						error_log(var_dump($result) . "\r\n", 3, $logloc);
						if ($result->num_rows <= 0) {
							error_log("-----------HERALD ERROR: USER NOT FOUND!-----------\r\n::::::::::::::::::::::::::::::::::::::::::\r\n", 3, $logloc);
							http_response_code(500);
							die();
						} else {
							$row = $result->fetch_assoc();
							//prepare update
							switch ($usd) {
								case '2.00':
									$cval = 400; 
									break;
								case '5.00':
									$cval = 1200; 
									break;
								case '10.00':
									$cval = 3000; 
									break;
								case '60.00':
									$cval = 20000; 
									break;
							}
							if (!isset($cval)) {
								error_log("-----------HERALD ERROR: COULD NOT TRANSLATE VALUE!-----------\r\n::::::::::::::::::::::::::::::::::::::::::\r\n", 3, $logloc);
								http_response_code(500);
								die();							
							}
							if ($row['credits'] == null) {
								$oldcredits = 0;
								$newcredits = $cval;
							} else {
								$oldcredits = $row['credits'];
								$newcredits = $oldcredits + $cval;
							}
							//perform update
							$query = "UPDATE users SET credits = '$newcredits' WHERE userID = '$userid'";
							if ($conn->query($query) === true) {
								//save move
								$query = "INSERT INTO hooklogs (time_creation, time_reception, hook_id, pay_id, hook_type, status, userID, username, usermail, usd, credits, oldcredits) VALUES ('$time_creation', '$time_reception', '$hook_id', '$pay_id', '$hook_type', '$status', '$userid', '$username','$mail', '$usd', '$cval', '$oldcredits')";
								if ($conn->query($query) === true) {
									error_log("-----------(hook processing successful)-----------\r\n", 3, $logloc);
								} else {
									error_log("-----------HERALD ERROR: COULD NOT SAVE THE MOVE!-----------\r\n::::::::::::::::::::::::::::::::::::::::::\r\n", 3, $logloc);
									http_response_code(500);
									die();
								}
							} else {
								error_log("-----------HERALD ERROR: COULD NOT UPDATE DATABASE!-----------\r\n::::::::::::::::::::::::::::::::::::::::::\r\n", 3, $logloc);
								http_response_code(500);
								die();
							}
						}
					}
				} else {
					http_response_code(500);
					error_log("-----------HERALD ERROR: COULD NOT ASSORT THE DATA!-----------\r\n::::::::::::::::::::::::::::::::::::::::::\r\n", 3, $logloc);
					die();
				}
				//finish
				http_response_code(200);
				$conn->close();
				if (isset($userid) && $userid != "0" && isset($newcredits)) {
					$this->askRefresh($userid, $newcredits);
				}
				error_log("::::::::::::::::::::::::::::::::::::::::::\r\n", 3, $logloc);
			} else {
				if (!isset($ip)) {
					error_log("-----------(ACCESS DENIED - UNKNOWN)-----------\r\n", 3, $logloc);
				} else {
					error_log("-----------(ACCESS DENIED - " . strval($ip) . ")-----------\r\n", 3, $logloc);
				}	
				http_response_code(500);
				echo("(denied)");
				die();
			}
		}
		echo "OK.";
	}

	private function getUserIpAddr() { #AlixAxel
    	foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){ // O.O
        	if (array_key_exists($key, $_SERVER) === true){
	            foreach (explode(',', $_SERVER[$key]) as $ip){
                	$ip = trim($ip); // "just to be safe"
                	if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
	                    return $ip;
                	}
            	}
	        }
    	}
	}
	
	private function askRefresh($who, $newcredits) {
		$host="127.0.0.1" ;
		$port=9121;
		$timeout = 30;
		$sk = fsockopen($host, $port, $errnum, $errstr, $timeout);
		if (!is_resource($sk)) {
			exit("connection fail: " . $errnum . " " . $errstr);
		} else {
			fwrite($sk, $who . "&" . $newcredits);
			fclose($sk);
    	}
	}
	
	private function getPList() {
		return array(
			'64.4.248.23',
			'64.4.249.23',
			'66.211.168.91',
			'66.211.168.123',
			'173.0.84.66',
			'173.0.84.98',
			'173.0.88.66',
			'173.0.88.98',
			'173.0.92.23',
			'173.0.93.23',
			'64.4.248.21',
			'64.4.249.21',
			'66.211.168.126',
			'173.0.84.101',
			'173.0.84.69',
			'173.0.88.101',
			'173.0.88.69',
			'173.0.92.21',
			'173.0.93.21',
			'64.4.248.23',
			'64.4.249.23',
			'66.211.168.124',
			'173.0.84.67',
			'173.0.84.99',
			'173.0.88.67',
			'173.0.88.99',
			'173.0.92.23',
			'173.0.93.23',
			'173.0.88.68',
			'173.0.88.100',
			'173.0.84.68',
			'173.0.84.100',
			'66.211.168.125',
			'173.0.92.20',
			'173.0.93.20',
			'64.4.249.20',
			'64.4.248.20',
			'173.0.84.108',
			'173.0.84.76',
			'173.0.88.108',
			'173.0.88.76',
			'173.0.92.36',
			'64.4.248.36',
			'64.4.249.36',
			'66.211.168.180',
			'173.0.84.139',
			'173.0.88.139',
			'173.0.84.139',
			'173.0.88.139',
			'66.211.168.93',
			'173.0.84.139',
			'173.0.88.139',
			'66.211.168.93',
			'66.211.170.66',
			'173.0.81.1',
			'173.0.81.0/24',
			'173.0.81.33',
			'173.0.84.139',
			'173.0.88.139',
			'66.211.168.93',
			'64.4.248.8',
			'64.4.249.8',
			'66.211.169.17',
			'173.0.84.40',
			'173.0.84.8',
			'173.0.88.40',
			'173.0.88.8',
			'173.0.92.8',
			'173.0.93.8',
			'173.0.84.178',
			'173.0.84.212',
			'173.0.88.178',
			'173.0.88.212',
			'173.0.88.203',
			'173.0.84.171',
			'173.0.84.203',
			'173.0.88.171',
			'66.211.168.131',
			'66.211.168.171',
			'66.211.168.176',
			'66.211.171.197',
			'173.0.84.102',
			'173.0.84.104',
			'173.0.84.104',
			'173.0.84.70',
			'173.0.84.72',
			'173.0.88.102',
			'173.0.88.104',
			'173.0.88.70',
			'173.0.88.72'
			);
		}
	
}
?>