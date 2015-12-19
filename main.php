<?php
/**
* Telegram Bot example for Public Transport of Reggio Calabria (Italy)
* @author @Piersoft
Funzionamento
- invio location
- invio fermata più vicina come risposta


*/

include("Telegram.php");

class mainloop{
const MAX_LENGTH = 4096;
function start($telegram,$update)
{

	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	$db = new PDO(DB_NAME);

	$text = $update["message"] ["text"];
	$chat_id = $update["message"] ["chat"]["id"];
	$user_id=$update["message"]["from"]["id"];
	$location=$update["message"]["location"];
	$reply_to_msg=$update["message"]["reply_to_message"];

	$this->shell($telegram, $db,$text,$chat_id,$user_id,$location,$reply_to_msg);
	$db = NULL;

}

//gestisce l'interfaccia utente
 function shell($telegram,$db,$text,$chat_id,$user_id,$location,$reply_to_msg)
{
	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");

	if ($text == "/start" || $text == "Informazioni") {
		$img = curl_file_create('odreggio.png','image/png');
		$contentp = array('chat_id' => $chat_id, 'photo' => $img);
		$telegram->sendPhoto($contentp);
		$reply = "Benvenuto. Invia la tua posizione cliccando sulla graffetta (📎) e ti indicherò le fermate più vicine nel raggio di 500 metri e relative linee ed orari";
		$reply .= "\nI dati, in licenza opendata IoDL2.0, sono prelevabili realtime su http://dati.reggiocal.it/";
		$reply .= "\nProgetto sviluppato da @Piersoft. Si declina ogni responsabilità sulla veridicità dei dati.";

		$content = array('chat_id' => $chat_id, 'text' => $reply);
		$telegram->sendMessage($content);
	$log=$today. ";new chat started;" .$chat_id. "\n";
	exit;
	}elseif ($text == "/linee" || $text =="Linee") {
		$img = curl_file_create('odreggio.png','image/png');
		$contentp = array('chat_id' => $chat_id, 'photo' => $img);
		$telegram->sendPhoto($contentp);
	//$response=$telegram->getData();
		$temp_c1="";
	//	$bot_request_message_id=$response["message"]["message_id"];
		$json_string = file_get_contents("http://dati.reggiocal.it/opendata/TrasportiAtam/linee.json");
		$parsed_json = json_decode($json_string);
		$count =0;
		foreach($parsed_json as $data=>$csv1){
			 $count = $count+1;
		}
	//	echo 	"Linee: ".$count." ".$parsed_json[0]->{'IdLinea'};

		for ($i=0;$i<$count;$i++){

		$temp_c1 =$parsed_json[$i]->{'lineaNomeBreve'}." - ".$parsed_json[$i]->{'lineaNomeEsteso'};
		//$content = array('chat_id' => $chat_id, 'text' => $chunk, 'reply_to_message_id' =>$bot_request_message_id,'disable_web_page_preview'=>true);
		//$telegram->sendMessage($content);

		$chunks = str_split($temp_c1, self::MAX_LENGTH);
	  foreach($chunks as $chunk) {
	 	// $forcehide=$telegram->buildForceReply(true);
	 		 //chiedo cosa sta accadendo nel luogo
	 		 $content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
	 		 $telegram->sendMessage($content);

	  }
	}
	exit;

}elseif ($text == "/fermate" || $text =="Fermate") {
	$content = array('chat_id' => $chat_id, 'text' => "Invia la tua posizione cliccando sulla graffetta (📎) in basso e, se vuoi, puoi cliccare due volte sulla mappa e spostare il Pin Rosso in un luogo di cui vuoi conoscere le fermate più vicine.");
	$telegram->sendMessage($content);
		exit;
}elseif ($text == "01") {
//	$response=$telegram->getData();
	$temp_c1="";
	$h = "1";// Hour for time zone goes here e.g. +7 or -4, just remove the + or -
	$hm = $h * 60;
	$ms = $hm * 60;
//	$bot_request_message_id=$response["message"]["message_id"];
	$json_string = file_get_contents("http://bari.opendata.planetek.it/OrariBus/v2.1/OpenDataService.svc/REST/ServizioGiornaliero/".$text."/");
	$parsed_json = json_decode($json_string);
	$count =0;

	foreach($parsed_json as $data=>$csv1){
		 $count = $count+1;
	}

		$time =$parsed_json[0]->{'Orario'}; //registro nel DB anche il tempo unix
		$time =str_replace("/Date(","",$time);
			if (strpos($time,'0100') == false) {
				$h = "2";
			}
		$time =str_replace("000+0200)/","",$time);
	$time =str_replace("000+0100)/","",$time);
		$time =str_replace(" ","",$time);
		$time =str_replace("\n","",$time);
		$timef=floatval($time);
		$timeff = time();
		$timec =gmdate('H:i:s', $timef+$ms);

		$temp_c1 .="\narrivo: ".$timec;


	$fermataid =$parsed_json[0]->{'IdFermata'};
	echo $fermataid;
	$json_stringf = file_get_contents("http://bari.opendata.planetek.it/OrariBus/v2.1/OpenDataService.svc/REST/rete/Fermate/".$fermataid);
	$parsed_jsonf = json_decode($json_stringf);

  $fermata=$parsed_jsonf[0]->{'DescrizioneFermata'};

	echo "\nLinea: ".$text."\nFermata".$fermata."\norario: ".$timec;

	for ($i=0;$i<$count;$i++){


	$time =$parsed_json[$i]->{'Orario'}; //registro nel DB anche il tempo unix
	$time =str_replace("/Date(","",$time);
	$time =str_replace("000+0200)/","",$time);
	$time =str_replace("000+0100)/","",$time);
	if (strpos($time,'0100') == false) {
		$h = "2";
	}
	$time =str_replace(" ","",$time);
	$time =str_replace("\n","",$time);
	$timef=floatval($time);
	$timeff = time();
	$timec =gmdate('H:i:s', $timef+$ms);

	$temp_c1 .="\narrivo: ".$timec;

	//echo $temp_c1;
}
}
		//gestione segnalazioni georiferite
		elseif($location!=null)
		{

			$this->location_manager($db,$telegram,$user_id,$chat_id,$location);
			exit;

		}else{

$forcehide=$telegram->buildKeyBoardHide(true);
$content = array('chat_id' => $chat_id, 'text' => "Comando errato.\nInvia la tua posizione cliccando sulla graffetta (📎) in basso e, se vuoi, puoi cliccare due volte sulla mappa e spostare il Pin Rosso in un luogo di cui vuoi conoscere le fermate più vicine.");
$telegram->sendMessage($content);
	//		$this->create_keyboard($telegram,$chat_id);
			exit;

		}
	$this->create_keyboard_temp($telegram,$chat_id);
		file_put_contents(LOG_FILE, $log, FILE_APPEND | LOCK_EX);

}
function create_keyboard_temp($telegram, $chat_id)
 {
		 $option = array(["Linee","Fermate"],["Informazioni"]);
		 $keyb = $telegram->buildKeyBoard($option, $onetime=false);
		 $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Clicca su Linea oppure invia la tua posizione tramite la graffetta (📎)]");
		 $telegram->sendMessage($content);
 }



// Crea la tastiera
 function create_keyboard($telegram, $chat_id)
	{
		$forcehide=$telegram->buildKeyBoardHide(true);
		$content = array('chat_id' => $chat_id, 'text' => "Invia la tua posizione cliccando sulla graffetta (📎) in basso e, se vuoi, puoi cliccare due volte sulla mappa e spostare il Pin Rosso in un luogo di cui vuoi conoscere le fermate più vicine.", 'reply_markup' =>$forcehide);
		$telegram->sendMessage($content);

	}



function location_manager($db,$telegram,$user_id,$chat_id,$location)
	{

			$lon=$location["longitude"];
			$lat=$location["latitude"];
      $r=1;
			//rispondo
			$response=$telegram->getData();

			$bot_request_message_id=$response["message"]["message_id"];
			$time=$response["message"]["date"]; //registro nel DB anche il tempo unix
			$img = curl_file_create('odreggio.png','image/png');
			$contentp = array('chat_id' => $chat_id, 'photo' => $img);
			$telegram->sendPhoto($contentp);
			$content = array('chat_id' => $chat_id, 'text' => "Sto cercando le fermate attorno alla tua posizione nel raggio di 500mt..", 'reply_to_message_id' =>$bot_request_message_id,'disable_web_page_preview'=>true);
	 	 $telegram->sendMessage($content);

			$r=1;

			function decode_entities($text) {

											$text=htmlentities($text, ENT_COMPAT,'ISO-8859-1', true);
										$text= preg_replace('/&#(\d+);/me',"chr(\\1)",$text); #decimal notation
											$text= preg_replace('/&#x([a-f0-9]+);/mei',"chr(0x\\1)",$text);  #hex notation
										$text= html_entity_decode($text,ENT_COMPAT,"UTF-8"); #NOTE: UTF-8 does not work!

				return $text;
			}
			$json_string = file_get_contents("http://dati.reggiocal.it/opendata/TrasportiAtam/paline.json");
			$csv = json_decode($json_string);
			$count =0;

			foreach($csv as $data=>$csv1){
			   $count = $count+1;
			}

			//  $time =$csv[0]->{'codicePalina'}; //registro nel DB anche il tempo unix
			//  $fermataid =$csv[0]->{'nomePaline'};






			//$url ="https://docs.google.com/spreadsheets/d/1zXkOd5V4AADdotnfwSU5MKVchZSkbcCILTNB4lci9gg/pub?gid=45872635&single=true&output=csv";
			$inizio=1;
			$homepage ="";
			//  echo $url;
			//$csv = array_map('str_getcsv', file($url));
			$latidudine="";
			$longitudine="";
			$data=0.0;
			$data1=0.0;
			//$count = 0;
			$dist=0.0;
			  $paline=[];
			  $distanza=[];
			  $countf = 0 ;
			//  $distanza[0]['distanza'] ="distanza";
			//  $distanza[0]['palina'] ="palina";
			//  $distanza[0]['lat'] ="lat";
			//  $distanza[0]['lon'] ="lon";
			//  $distanza[0]['nome'] ="nome";
			//foreach($csv as $data=>$csv1){
			//  $count = $count+1;
			//}

			//$count=5;

			//  echo $count;
			for ($i=$inizio;$i<$count;$i++){

			  $homepage .="\n";
			//  $homepage .="Lat:".$csv[$i][5]."\n";
			//  $homepage .="Long:".$csv[$i][6]."\n";

			  $lat10=floatval($csv[$i]->{'lat'});
			  $long10=floatval($csv[$i]->{'lon'});
			  $theta = floatval($lon)-floatval($long10);
			  $dist =floatval( sin(deg2rad($lat)) * sin(deg2rad($lat10)) +  cos(deg2rad($lat)) * cos(deg2rad($lat10)) * cos(deg2rad($theta)));
			  $dist = floatval(acos($dist));
			  $dist = floatval(rad2deg($dist));
			  $miles = floatval($dist * 60 * 1.1515 * 1.609344);
			//echo $miles;

			  if ($miles >=1){
			$data1 =number_format($miles, 2, '.', '');
			    $data =number_format($miles, 2, '.', '')." Km";
			  } else {
			    $data =number_format(($miles*1000), 0, '.', '')." mt";
			    $data1 =number_format(($miles*1000), 0, '.', '');
			  }
			  $csv[$i]->{'distance'}= array("distance" => "value");

			  $csv[$i]->{'distance'}= $data1;
			  $t=floatval($r*1000);


			  //    $reply ="Dista: ".$data."\n";
			//echo $reply;
			      if ($data < $t && $miles<1)
			      {
			//echo $data1."</br>";
			        $homepage .="codicePalina: ".$csv[$i]->{'codicePalina'}."\n</br>";
			        $homepage .="Fermata: ".$csv[$i]->{'nomePaline'}."\n";
			      //  $homepage .="Distanza: ".$data."\n</br>";
			        $homepage .="Dista: ".$csv[$i]->{'distance'}."\n</br>";
			        $distanza[$i]['distanza'] =$csv[$i]->{'distance'};
			      //  echo $distanza[$i]['distanza'];
			        $distanza[$i]['palina'] =$csv[$i]->{'codicePalina'};
			        $distanza[$i]['lat'] =$csv[$i]->{'lat'};
			        $distanza[$i]['lon'] =$csv[$i]->{'lat'};
			        $distanza[$i]['nome'] =$csv[$i]->{'nomePaline'};
			    //    array_push($distanza['distanza'],$data);
			    //    array_push($distanza['paline'],$csv[$i][0]);
			  $countf++;
			        //array_push($distanza[$i][1],$csv[$i][0]);
			      }


			}
			//echo $homepage;
			$temp_c1="";
			sort($distanza);
			for ($f=0;$f<5;$f++){
			$json_stringf = file_get_contents("http://servizi.recasi.it/AtamOpenData/opendata/GetPalina.ashx?id_palina=".$distanza[$f]['palina']."&type=json");
			$json_stringf=decode_entities($json_stringf);
			$json_stringf=str_replace("T"," ",$json_stringf);
			//var_dump($json_stringf);
			$parsed_jsonf = json_decode($json_stringf);

			//for ($tt=0;$tt<5;$tt++){


			//	for ($f=0;$f<$countf;$f++){
$ritardo=$parsed_jsonf[0]->{'MinutiScostamento'};
if ($ritardo !== NULL) $ritardo="\nRitardo: ".$parsed_jsonf[0]->{'MinutiScostamento'};
			    $temp_c1 .="\nFermata: ".$distanza[$f]['nome']."\nDistanza: ".$distanza[$f]['distanza']."mt\nProssimo arrivo: linea ".$parsed_jsonf[0]->{'CodiceLinea'}."\nCapolinea: ".$parsed_jsonf[0]->{'CapolineaBreve'}."\nArrivo: ".substr($parsed_jsonf[0]->{'DataOraPassaggioPalina'}, -8).$ritardo;
	$temp_c1 .="\n_____________\n";
			//}

			}

		//	echo $temp_c1;

$longUrl="http://www.piersoft.it/reggiocaltrasporti/locator.php?lat=".$lat."&lon=".$lon."&r=1";
$apiKey = API;

$postData = array('longUrl' => $longUrl, 'key' => $apiKey);
$jsonData = json_encode($postData);

$curlObj = curl_init();

curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.$apiKey);
curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($curlObj, CURLOPT_HEADER, 0);
curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
curl_setopt($curlObj, CURLOPT_POST, 1);
curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);

$response = curl_exec($curlObj);

// Change the response json string to object
$json = json_decode($response);

curl_close($curlObj);
//  $reply="Puoi visualizzarlo su :\n".$json->id;
$shortLink = get_object_vars($json);
//return $json->id;

$temp_c1 .="\nVisualizza tutte le fermate su mappa :\n".$shortLink['id'];



 $chunks = str_split($temp_c1, self::MAX_LENGTH);
 foreach($chunks as $chunk) {
	// $forcehide=$telegram->buildForceReply(true);
		 //chiedo cosa sta accadendo nel luogo
		 $content = array('chat_id' => $chat_id, 'text' => $chunk, 'reply_to_message_id' =>$bot_request_message_id,'disable_web_page_preview'=>true);
		 $telegram->sendMessage($content);

 }
 //$telegram->sendMessage($content);
	echo $temp_l1;


	$today = date("Y-m-d H:i:s");

	$log=$today. ";fermatebari sent;" .$chat_id. "\n";
	$this->create_keyboard_temp($telegram,$chat_id);
	exit;

	}


}

?>
