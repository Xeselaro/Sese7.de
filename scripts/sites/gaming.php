<?php
	function httpReq($url){
		// make request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$output = curl_exec($ch);   
		
		return $output;

		curl_close($ch);
	}
	function riot_request($userarray, $url, $maxsum, $api_key, $opt = ''){
		$return = "";
		for($x = 0; $x < (intval(sizeof($userarray)) / $maxsum); $x++){ //Max $maxsum summoners per request
			$users = "";
			for($i = 0; $i < $maxsum; $i++){
				if(isset($userarray[($maxsum * $x) + $i])){
					if($users != ""){
						$users .= ",";
					}
					$users .= $userarray[($maxsum * $x) + $i];
				}
				else{
					break;
				}
			}

			if($return != ""){
				$return .= ",";
			}

			$requrl = $url . $users . $opt . "?api_key=" . $api_key;
			$result = httpReq($requrl);
			$return .= $result;
		}
		$return = str_replace('},{', ',', $return); //incase of multiple request all resonses are in the same json.
		return $return;
	}

	require_once("../connect.php");
	require_once("../pw.php");
	require_once("../tools/ErrorReporter.php");

	$i = 0;

	$lol = mysqli_query($db, "SELECT * FROM user_data WHERE lol_username != ''");
	while($row = mysqli_fetch_assoc($lol)){
		$lol_username[$i] = urlencode(utf8_encode($row["lol_username"]));
		$i++;
	}

	$lol_users = riot_request($lol_username, "https://euw.api.pvp.net/api/lol/euw/v1.4/summoner/by-name/", 40, $lol_api_key);

	$lol_users_array = json_decode($lol_users, true);
	$lol_summoner = array();
	foreach ($lol_users_array as $user) {
		array_push($lol_summoner, $user['id']);
	}

	$lol_ranks = riot_request($lol_summoner, "https://euw.api.pvp.net/api/lol/euw/v2.5/league/by-summoner/", 10, $lol_api_key, "/entry");
	
	echo '{"lol": {"users":' . $lol_users . ',"ranks":' . $lol_ranks . '}}';

?>