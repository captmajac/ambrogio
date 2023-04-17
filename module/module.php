<?php
class Ambrogio {
	
	// erstellung
	public function Create() {
		// Never delete this line!
		parent::Create ();
		$this->RegisterPropertyString ( "User", "" );
    $this->RegisterPropertyString ( "Pass", "" );
    $this->RegisterPropertyString ( "SessionID", "" );
		
	}
	
	// changes der instanz
	public function ApplyChanges() {
		// Never delete this line!
		parent::ApplyChanges ();
		
	}


	
	// login cloud
	private function loginCloud($data) { 
	                                      
	}
  
  // send go online message
	private function goOnline($data) { 
	                                      
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
