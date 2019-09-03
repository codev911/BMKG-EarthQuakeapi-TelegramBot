<?php
    define('TOKEN','877851107:AAEpvRsCB61oyjwmqoq25VVvqkU3FZnFcKo');
    session_start();
    function BotKirim($perintah){
        return 'https://api.telegram.org/bot'.TOKEN.'/'.$perintah;
    }
    function KirimPerintahStream($perintah,$data){
         $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents(BotKirim($perintah), false, $context);
        return $result;
    }
    
    function KirimPerintahCurl($perintah,$data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,BotKirim($perintah));
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $kembali = curl_exec ($ch);
        curl_close ($ch);
        return $kembali;
    }
    function DapatkanUpdate($offset){
        $url = BotKirim("getUpdates")."?offset=".$offset;
        $kirim = file_get_contents($url);
        $hasil = json_decode($kirim, true);
        if ($hasil["ok"]==1){
            return $hasil["result"];
            }
        else{
             return array();
        }
    }
 
    function JalankanBot(){
            $update_id  = 0;
            $map_url = "http://data.bmkg.go.id/autogempa.xml";
            $response_xml_data = file_get_contents($map_url);
            $xml=simplexml_load_string($response_xml_data) or die("Error: Cannot create object");	
            $json  = json_encode($xml);
            $configData = json_decode($json, true);
                if($_SESSION['data'] != $response_xml_data){
                    $_SESSION['data'] = $response_xml_data;   
                    $data = array(
                        'chat_id' => '-379643094',
                        'text'=> '*⚠️Info Gempa⚠️*

*Waktu :* '. $configData['gempa']['Tanggal'] . ' ' . $configData['gempa']['Jam'] . '

*Coordinate :* ' . $configData['gempa']['point']['coordinates'] . '
*Lintang :* ' . $configData['gempa']['Lintang'] . '
*Bujur :* ' . $configData['gempa']['Bujur'] . '
*Kekuatan :* ' . $configData['gempa']['Magnitude'] . '
*Kedalaman :* ' . $configData['gempa']['Kedalaman'] . '

*Wilayah :* ' . $configData['gempa']['Wilayah1'] . ' ,' . $configData['gempa']['Wilayah2'] . ' ,' . $configData['gempa']['Wilayah3'] . ' ,' . $configData['gempa']['Wilayah4'] . ' ,' . $configData['gempa']['Wilayah5'] . '

*Potensi :* ' . $configData['gempa']['Potensi'],
                        'parse_mode' => 'markdown'
                    );
                    KirimPerintahCurl('sendMessage',$data);
                }
            file_put_contents("last_update_id", $update_id + 1);
        }
        
    while(true){
        sleep(2);
        JalankanBot();
    }
?>