<?php


//file di test
$lat=$_GET["lat"];
$lon=$_GET["lon"];
$r=$_GET["r"];

$url ="https://docs.google.com/spreadsheets/d/1zXkOd5V4AADdotnfwSU5MKVchZSkbcCILTNB4lci9gg/pub?gid=45872635&single=true&output=csv";
$inizio=1;
$homepage ="";
//  echo $url;
$csv = array_map('str_getcsv', file($url));
$latidudine="";
$longitudine="";
$data=0.0;
$data1=0.0;
$count = 0;
$dist=0.0;
  $paline=[];
  $distanza=[];
//  $distanza[0]['distanza'] ="distanza";
//  $distanza[0]['palina'] ="palina";
//  $distanza[0]['lat'] ="lat";
//  $distanza[0]['lon'] ="lon";
//  $distanza[0]['nome'] ="nome";
foreach($csv as $data=>$csv1){
  $count = $count+1;
}

//$count=5;

//  echo $count;
for ($i=$inizio;$i<$count;$i++){

  $homepage .="\n";
//  $homepage .="Lat:".$csv[$i][5]."\n";
//  $homepage .="Long:".$csv[$i][6]."\n";

  $lat10=floatval($csv[$i][5]);
  $long10=floatval($csv[$i][6]);
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
  $csv[$i][7]= array("distance" => "value");

  $csv[$i][7]= $data1;
  $t=floatval($r*1000)/2;


  //    $reply ="Dista: ".$data."\n";
//echo $reply;
      if ($data < $t && $miles<1)
      {

        $homepage .="codicePalina: ".$csv[$i][0]."\n</br>";
        $homepage .="nomePalina: ".$csv[$i][1]."\n</br>";
      //  $homepage .="Distanza: ".$data."\n</br>";
        $homepage .="Dista: ".$csv[$i][7]."\n</br>";
        $distanza[$i]['distanza'] =$csv[$i][7];
      //  echo $distanza[$i]['distanza'];
        $distanza[$i]['palina'] =$csv[$i][0];
        $distanza[$i]['lat'] =$csv[$i][5];
        $distanza[$i]['lon'] =$csv[$i][6];
        $distanza[$i]['nome'] =$csv[$i][1];
    //    array_push($distanza['distanza'],$data);
    //    array_push($distanza['paline'],$csv[$i][0]);

        //array_push($distanza[$i][1],$csv[$i][0]);
      }


}
//echo $homepage;

sort($distanza);
//var_dump($distanza);
//for ($tt=0;$tt<5;$tt++){
//echo $distanza[$tt];
//  echo $distanza[$tt]['distanza'].",".$distanza[$tt]['palina'].",".$distanza[$tt]['lat'].",".$distanza[$tt]['lon'].",".$distanza[$tt]['nome']."\n</br>";
//}

$file1 = "mappaf.json";
$original_data="";

//$allfeatures = array('type' => 'FeatureCollection', 'features' => $features);

//var_dump($distanza['distanza']);


//$original_json_string = $distanza[$i];
//for ($tt=0;$tt<5;$tt++){


$dest1 = fopen($file1, 'w');

//$geostring=geoJson($original_json_string);

$original_data = json_decode($distanza[$tt], true);
if(empty($distanza))
{

  echo "<script type='text/javascript'>alert('Non ci sono fermate vicino alla tua posizione');</script>";

}
$features = array();

foreach($distanza as $key => $value) {
//  var_dump($value);
    $features[] = array(
            'type' => 'Feature',
            'geometry' => array('type' => 'Point', 'coordinates' => array((float)$value['lon'],(float)$value['lat'])),
            'properties' => array('nomePalina' => $value['nome'], 'codicePalina' => $value['palina'],'distanza' => $value['distanza']),
            );
    };

  $allfeatures = array('type' => 'FeatureCollection', 'features' => $features);

$geostring =json_encode($allfeatures, JSON_PRETTY_PRINT);


fputs($dest1, $geostring);

//echo stream_copy_to_stream($src, $dest) . "";
//sleep(1);
//header("Location:http://www.apposta.biz/prove/mappacqualta.html");

?>

<!DOCTYPE html>
<html lang="it">
  <head>
  <title>Trasporti Reggio Calabria</title>
  <link rel="stylesheet" href="http://necolas.github.io/normalize.css/2.1.3/normalize.css" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
  <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7.5/leaflet.css" />
  <link rel="stylesheet" href="MarkerCluster.css" />
  <link rel="stylesheet" href="MarkerCluster.Default.css" />
  <meta property="og:image" content="http://www.piersoft.it/baritrasportibot/bus_.png"/>
  <script src="http://cdn.leafletjs.com/leaflet-0.7.5/leaflet.js"></script>
  <script src="leaflet.markercluster.js"></script>
  <script type="text/javascript" src="csvjson.js" ></script>
<script type="text/javascript">

function microAjax(B,A){this.bindFunction=function(E,D){return function(){return E.apply(D,[D])}};this.stateChange=function(D){if(this.request.readyState==4 ){this.callbackFunction(this.request.responseText)}};this.getRequest=function(){if(window.ActiveXObject){return new ActiveXObject("Microsoft.XMLHTTP")}else { if(window.XMLHttpRequest){return new XMLHttpRequest()}}return false};this.postBody=(arguments[2]||"");this.callbackFunction=A;this.url=B;this.request=this.getRequest();if(this.request){var C=this.request;C.onreadystatechange=this.bindFunction(this.stateChange,this);if(this.postBody!==""){C.open("POST",B,true);C.setRequestHeader("X-Requested-With","XMLHttpRequest");C.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=UTF-8");C.setRequestHeader("Connection","close")}else{C.open("GET",B,true)}C.send(this.postBody)}};

</script>
  <style>
  #mapdiv{
        position:fixed;
        top:0;
        right:0;
        left:0;
        bottom:0;
}
#infodiv{
background-color: rgba(255, 255, 255, 0.95);

font-family: Helvetica, Arial, Sans-Serif;
padding: 2px;


font-size: 10px;
bottom: 13px;
left:0px;


max-height: 50px;

position: fixed;

overflow-y: auto;
overflow-x: hidden;
}
#loader {
    position:absolute; top:0; bottom:0; width:100%;
    background:rgba(255, 255, 255, 1);
    transition:background 1s ease-out;
    -webkit-transition:background 1s ease-out;
}
#loader.done {
    background:rgba(255, 255, 255, 0);
}
#loader.hide {
    display:none;
}
#loader .message {
    position:absolute;
    left:50%;
    top:50%;
}
</style>
  </head>

<body>

  <div data-tap-disabled="true">

  <div id="mapdiv"></div>
<div id="infodiv" style="leaflet-popup-content-wrapper">
  <p><b>Fermate e orario Bus Reggio Calabria<br></b>
  Mappa con fermate entro i 500mt dalla tua posizione, linee e orarie dei Bus di Reggio Calabria by @piersoft. Fonte dati Lic. IoDL2.0 <a href="http://ckan.reggiocal.it/dataset/atam-paline">openData Reggio Calabria</a></p>
</div>
<div id='loader'><span class='message'>loading</span></div>
</div>
  <script type="text/javascript">
  function convert() {
      var input = document.getElementById("input").innerHTML;

      var output_json = csvjson.csv2json(input, {
        delim: ",",
        textdelim: "\""
      });
      console.log("Converted CSV to JSON:", output_json);

      var output_csv = csvjson.json2csv(output_json, {
        delim: ",",
        textdelim: "\""
      });
      console.log("Converted JSON to CSV:", output_csv);

      document.getElementById("output").innerHTML = output_csv;
    }
		var lat=38.0907,
        lon=15.7207,
        zoom=12;
        var osm = new L.TileLayer('http://{s}.tile.thunderforest.com/transport/{z}/{x}/{y}.png', {minZoom: 0, maxZoom: 20, attribution: 'Map Data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'});

  //      var osm = new L.TileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {maxZoom: 20, attribution: 'Map Data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'});
		var mapquest = new L.TileLayer('http://otile{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png', {subdomains: '1234', maxZoom: 18, attribution: 'Map Data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'});
    var realvista = L.tileLayer.wms("http://213.215.135.196/reflector/open/service?", {
        layers: 'rv1',
        format: 'image/jpeg',attribution: '<a href="http://www.realvista.it/website/Joomla/" target="_blank">RealVista &copy; CC-BY Tiles</a> | <a href="http://openstreetmap.org">OpenStreetMap</a> contributors'
      });


        var map = new L.Map('mapdiv', {
                    editInOSMControl: true,
            editInOSMControlOptions: {
                position: "topright"
            },
            center: new L.LatLng(lat, lon),
            zoom: zoom,
            layers: [osm]
        });

        var baseMaps = {
    "Satellite": realvista,
    "Trasporti": osm,
    "Mapquest Open": mapquest
        };
        L.control.layers(baseMaps).addTo(map);

       var ico=L.icon({iconUrl:'circle.png', iconSize:[20,20],iconAnchor:[10,0]});
    //   var markers = L.markerClusterGroup({spiderfyOnMaxZoom: true, showCoverageOnHover: true,zoomToBoundsOnClick: true});
var markers=L.featureGroup();
        function loadLayer(url)
        {
                var myLayer = L.geoJson(url,{
                        onEachFeature:function onEachFeature(feature, layer) {
                                if (feature.properties && feature.properties.id) {
                                }

                        },
                        pointToLayer: function (feature, latlng) {
                        var marker = new L.Marker(latlng, { icon: ico });

                        markers[feature.properties.id] = marker;
                        marker.bindPopup('<img src="http://www.piersoft.it/reggiocaltrasporti/ajax-loader.gif">',{maxWidth:50, autoPan:true});

                      //  marker.on('click',showMarker());
                        return marker;
                        }
                });
                //.addTo(map);

                markers.addLayer(myLayer);
                map.addLayer(markers);
                markers.on('click',showMarker);
                map.fitBounds(markers.getBounds());
        }

microAjax('mappaf.json',function (res) {
var feat=JSON.parse(res);
loadLayer(feat);
  finishedLoading();
} );
function convertTimestamp(timestamp) {
  var d = new Date(timestamp * 1000),	// Convert the passed timestamp to milliseconds
		yyyy = d.getFullYear(),
		mm = ('0' + (d.getMonth() + 1)).slice(-2),	// Months are zero based. Add leading 0.
		dd = ('0' + d.getDate()).slice(-2),			// Add leading 0.
		hh = d.getHours(),
		h = hh,
		min = ('0' + d.getMinutes()).slice(-2),		// Add leading 0.
		ampm = 'AM',
		time;

	if (hh > 12) {
	//	h = hh - 12;
		ampm = 'PM';
	} else if (hh === 12) {
	//	h = 12;
		ampm = 'PM';
	} else if (hh == 0) {
		h = 12;
	}

	// ie: 2013-02-18, 8:35 AM
	time = h + ':' + min;

	return time;
}

 function showMarker(marker) {

   var jsonref=marker.layer.feature;
//   microAjax('http://servizi.recasi.it/AtamOpenData/opendata/GetPalina.ashx?id_palina='+jsonref.properties.codicePalina+'&type=json/', function (res) {
microAjax('http://servizi.recasi.it/AtamOpenData/opendata/GetPalina.ashx?id_palina='+jsonref.properties.codicePalina+'&type=json', function (res) {
      console.log("Palina: "+jsonref.properties.codicePalina);
//   console.log(res);

   var feat=JSON.parse(res);
   var index;
  // console.log(res);
   console.log("Capolinea: "+feat[0]['Capolinea']);
//  alert (feat.length);
    var text;
    var i = 0;

if(feat[0]['CodiceLinea'].length != "undefined")
{
  if(feat[0] === "")
  {
  text ="Non ci sono linee in arrivo nelle prossime ore";
  marker.layer.closePopup();
  marker.layer.bindPopup(text);
  marker.layer.openPopup();
  console.log("Feat lenght: "+feat['CodiceLinea'].length);
}else{

  text ="Prossimo arrivo:";
  console.log("lunghezza array linee: "+feat[0]['CodiceLinea'].length);
//console.log("Feat lenght: "+feat['PrevisioniLinee'].length);
for (i=0;i<feat[0]['CodiceLinea'].length;i++){
    //   // when the tiles load, remove the screen
    var last=feat[i];
  //  var text ="Linee servite: "+last['IdLinea']+"<br>";
    text +="<br />Linea:"+last['CodiceLinea']+"</br>Capolinea: "+last['Capolinea']+"</br>Ritardo: "+last['MinutiScostamento'];
    var orario =last['DataOraPassaggioPalina'];
  //  orario= orario.replace("/Date(","");
  //  orario=orario.replace("000+0200)/","");
    orario=orario.replace("T"," ");
  //  var date=convertTimestamp(orario);
    //var date = new Date(orario*1000);
  //  var iso = date.toISOString().match(/(\d{2}:\d{2}:\d{2})/);
    text+="</br><b>Arrivo: "+orario;
    marker.layer.closePopup();
    marker.layer.bindPopup(text);
    marker.layer.openPopup();
  }
}
}

}
  );

}
function startLoading() {
    loader.className = '';
}

function finishedLoading() {
    // first, toggle the class 'done', which makes the loading screen
    // fade out
    loader.className = 'done';
    setTimeout(function() {
        // then, after a half-second, add the class 'hide', which hides
        // it completely and ensures that the user can interact with the
        // map again.
        loader.className = 'hide';
    }, 500);
}
</script>

</body>
</html>
