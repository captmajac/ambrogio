<?php
class Ambrogio extends IPSModule{
	
	// erstellung
	public function Create() {
		// Never delete this line!
		parent::Create ();
		$this->RegisterPropertyString ( "User", "" );
    		$this->RegisterPropertyString ( "Pass", "" );
		$this->RegisterPropertyString ( "ThingKey", "" );
    		//$this->RegisterPropertyString ( "SessionID", "" );
		$this->SetBuffer("sessionid", "");
		
	}
	
	// changes der instanz
	public function ApplyChanges() {
		// Never delete this line!
		parent::ApplyChanges ();
		
	}

	// public update status
	public function updateAmbrogioStatus() { 
		$this->loginCloud();
		$result = $this->getRobotStatus();
		
		return $result;
	}

	
	// login cloud
	private function loginCloud() { 
		$jsonDataEncoded = '{"auth":{"command":"api.authenticate","params":{"appId":"3c1Pt1We9dT3qBAlL7nxAcDERC82","thingKey":"3c1Pt1We9dT3qBAlL7nxAcDERC82","appToken":"DJMYYngGNEit40vA"}}}';
		$result = $this->sendCloudMessage($jsonDataEncoded);  
		$obj = json_decode($result);
		// store sessionid 
		$this->SetBuffer("sessionid", $obj->{'auth'}->{'params'}->{'sessionId'});
		
	}
  
  // send go online message
	public function goOnline() { 
		// noch prüfen ob login erforderlich, session id vorliegt oder ob der user sich selbst drum kümmern soll
		$key = $this->ReadPropertyString("ThingKey");
    		$jsonDataEncoded = '{"0" : {"params" : {"coding" : "SEVEN_BIT", "imei" : "'.$key.'","message" : "UP"},"command" : "sms.send"}}';
    		$result = sendCloudMessage($jsonDataEncoded);  
		return $result;
	}
	
   	// send getStatus message
	private function getRobotStatus() {  
		$key = $this->ReadPropertyString("ThingKey");
		$jsonDataEncoded = '{"state_history":{"command":"alarm.history","params":{"thingKey":"'.$key.'","key":"robot_state","last":"24h"}},"thing_find":{"command":"thing.find","params":{"key":"'.$key.'"}}}';
		$obj = $this->sendCloudMessage($jsonDataEncoded);
		
		return $obj;
	}
	
  // send cloud message
	private function sendCloudMessage($data) { 
		$user = $this->ReadPropertyString("User");
		$pass = $this->ReadPropertyString("Pass");
		$key = $this->ReadPropertyString("ThingKey");
		$sessionid = $this->GetBuffer("sessionid");
		$jsonDataEncoded = $data;
		
		
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
    if ($sessionid<>"")
    {
        array_push($customHeaders, "sessionId:".$sessionid);
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
    //$obj = json_decode($result);

/* auf erfolgreich bzw. auth header prüfen
    if ($obj->{'errorCodes'}[0] = -99999 )
    {
        echo "Authentication header Fehler" ; // Login und dann Session ID notwendig.
    }*/

    return $result;		
	                                      
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
