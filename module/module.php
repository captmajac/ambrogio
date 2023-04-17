<?php
class Ambrogio {
	
	// erstellung
	public function Create() {
		// Never delete this line!
		parent::Create ();
		$this->RegisterPropertyString ( "User", "" );
    $this->RegisterPropertyString ( "Pass", "" );
	$this->RegisterPropertyString ( "ThingKey", "" );
    $this->RegisterPropertyString ( "SessionID", "" );
		
	}
	
	// changes der instanz
	public function ApplyChanges() {
		// Never delete this line!
		parent::ApplyChanges ();
		
	}


	
	// login cloud
	private function loginCloud($data) { 
		$jsonDataEncoded = '{"auth":{"command":"api.authenticate","params":{"appId":"3c1Pt1We9dT3qBAlL7nxAcDERC82","thingKey":"3c1Pt1We9dT3qBAlL7nxAcDERC82","appToken":"DJMYYngGNEit40vA"}}}';
		$obj = sendCloudMessage($jsonDataEncoded);                              
	}
  
  // send go online message
	private function goOnline($data) { 
	$key = $this->ReadPropertyString("ThingKey");
    $jsonDataEncoded = '{"0" : {"params" : {"coding" : "SEVEN_BIT", "imei" : "'.$key.'","message" : "UP"},"command" : "sms.send"}}';
    obj = sendCloudMessage($jsonDataEncoded);                        
	}
	
   	// send getStatus message
	private function getStatus($data) {  
		$key = $this->ReadPropertyString("ThingKey");
		$jsonDataEncoded = '{"state_history":{"command":"alarm.history","params":{"thingKey":"'.$key.'","key":"robot_state","last":"24h"}},"thing_find":{"command":"thing.find","params":{"key":"'.$key.'"}}}';
		$obj = sendCloudMessage($jsonDataEncoded);
	}
	
  // send cloud message
	private function sendCloudMessage($data) { 
		$user = $this->ReadPropertyString("User");
		$pass = $this->ReadPropertyString("Pass");
		$key = $this->ReadPropertyString("ThingKey");
		$sessionid = $this->ReadPropertyString("SessionID");
		
		
	//The URL you're sending the request to.
    $url = 'http://api-de.devicewise.com/api';

    //Create a cURL handle.
    $ch = curl_init($url);

    //Create an array of custom headers.
    $customHeaders = array(
        'Host: api-de.devicewise.com',
        'Connection: keep-alive',
        'Content-Type: application/json',
        'Accept: application/json',
        'User-Agent: Ambrogio%20Remote/56970 CFNetwork/1331.0.7 Darwin/21.4.0',
        'Accept-Language: de-DE,de;q=0.9',
        'Accept-Encoding: gzip, deflate'
        );

    // Auth session ID oder user/pass
    if ($sessionID<>"")
    {
        array_push($customHeaders, "sessionId:".$sessionID);
    }
    else
    {
        // auth header
        curl_setopt($ch, CURLOPT_USERPWD, $user.":".$pass);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    }

    //Use the CURLOPT_HTTPHEADER option to use our
    //custom headers.
    curl_setopt($ch, CURLOPT_HTTPHEADER, $customHeaders);

    //Attach our encoded JSON string to the POST fields.
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonDataEncoded);

    // returns the result - very important
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //Tell cURL that we want to send a POST request.
    curl_setopt($ch, CURLOPT_POST, 1);
    //Execute the request.
    $result = curl_exec($ch);
    curl_close($ch);

    echo $result."\n";
    $obj = json_decode($result);

    if ($obj->{'errorCodes'}[0] = -99999 )
    {
        echo "Authentication header Fehler" ; // Login und dann Session ID notwendig.
    }

    return $obj;		
	                                      
	}

	protected function SendDebug($Message, $Data, $Format) {
		if (is_array ( $Data )) {
			foreach ( $Data as $Key => $DebugData ) {
				$this->SendDebug ( $Message . ":" . $Key, $DebugData, 0 );
			}
		} else if (is_object ( $Data )) {
			foreach ( $Data as $Key => $DebugData ) {
				$this->SendDebug ( $Message . "." . $Key, $DebugData, 0 );
			}
		} else {
			parent::SendDebug ( $Message, $Data, $Format );
		}
	}
	
	
}
?>
