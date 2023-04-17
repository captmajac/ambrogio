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
	                                      
	}
	
   	// send getStatus message
	private function getStatus($data) {  
		$key = $this->ReadPropertyString("ThingKey");
		$jsonDataEncoded = '{"state_history":{"command":"alarm.history","params":{"thingKey":"'.$key.'","key":"robot_state","last":"24h"}},"thing_find":{"command":"thing.find","params":{"key":"'.$key.'"}}}';
		$obj = sendCloudMessage($jsonDataEncoded);
	}
	
  // send cloud message
	private function sendCloudMessage($data) { 
	                                      
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
