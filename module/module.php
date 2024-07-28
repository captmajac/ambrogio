<?php
class Ambrogio extends IPSModule
{
    // erstellung
    public function Create()
    {
        // Never delete this line!
        parent::Create();
        $this->RegisterPropertyString("User", "");
        $this->RegisterPropertyString("Pass", "");
        $this->RegisterPropertyString("ThingKey", "");
        //$this->RegisterPropertyString ( "SessionID", "" );
        $this->SetBuffer("sessionid", "");
        $this->RegisterPropertyString("Interval", "300");
        $this->RegisterPropertyInteger("MapID", "");

        $Module = json_decode(
            file_get_contents(__DIR__ . "/module.json"),
            true
        )["prefix"];
        $this->RegisterTimer(
            "UpdateTimer",
            0,
            $Module . "_TimerEvent(\$_IPS['TARGET']);"
        );

        $this->CreateVarProfileModus();
    }

    // changes der instanz
    public function ApplyChanges()
    {
        // Never delete this line!
        parent::ApplyChanges();

        $this->RegisterVariableBoolean(
            "CloudConnected",
            "Cloud Verbindung",
            "Ambrogio.Online",
            10
        );
        $this->RegisterVariableString("LastSeen", "Letzter Kontakt", "", 20);
        $this->RegisterVariableInteger("State", "Status", "Ambrogio.State", 30);
        $this->RegisterVariableInteger(
            "Message",
            "Meldung",
            "Ambrogio.Msg",
            40
        );
        $this->RegisterVariableFloat("lat", "lat", "", 50);
        $this->RegisterVariableFloat("lgn", "lgn", "", 60);
        $this->RegisterVariableString("Map", "Letzte Position", "~HTMLBox", 70);

        // update timer
        @$Interval = (int) $this->ReadPropertyString("Interval") * 1000;
        if ($Interval == 0) {
            $Interval = 5 * 60 * 1000;
        }
        $this->SetTimerInterval("UpdateTimer", $Interval);

	//    echo ($this->ReadPropertyString("MapID"));
    	if ($this->ReadPropertyString("MapID") != 0)
	{
		IPS_SetHidden($this->GetIDForIdent("Map"),false);
	}
	else
	{
		IPS_SetHidden($this->GetIDForIdent("Map"),true);
	}
    }

    // public update status
    public function updateAmbrogioStatus()
    {
        $this->loginCloud();
        $result = $this->getRobotStatus();

        return $result;
    }

    // public update status
    private function decodeAmbrogioStatus(string $json)
    {
        $result = json_decode($json);
        //cloud state
        $online = $result->thing_find->params->connected;
        // message
        $msg = $result->thing_find->params->alarms->robot_state->msg;
        // state
        $state = $result->thing_find->params->alarms->connection_state->state;
        // since
        $since = $result->thing_find->params->alarms->robot_state->since;
        // lat, lgn
        $lat = 0; // todo: alten wert holen
        $lgn = 0; // todo: alten wert holen
        if (
            property_exists(
                $result->thing_find->params->alarms->robot_state,
                "lat"
            ) == true
        ) {
            $lat = $jar->thing_find->params->alarms->robot_state->lat;
        }
        if (
            property_exists(
                $result->thing_find->params->alarms->robot_state,
                "lgn"
            ) == true
        ) {
            $lgn = $jar->thing_find->params->alarms->robot_state->lgn;
        }

        // set vars
        SetValue($this->GetIDForIdent("CloudConnected"), $online);

        $dt = new DateTime($since);
        $tz = new DateTimeZone("Europe/Berlin");
        $dt->setTimezone($tz);
        SetValue($this->GetIDForIdent("LastSeen"), $dt->format("d.m.Y H:i:s"));

        SetValue($this->GetIDForIdent("State"), $state);
        SetValue($this->GetIDForIdent("Message"), $msg);
        SetValue($this->GetIDForIdent("lat"), $lat);
        SetValue($this->GetIDForIdent("lgn"), $lgn);

				if ($this->ReadPropertyString("MapID") != "")
				{
						$this->updateMap();
				}
    }

    // login cloud
    private function loginCloud()
    {
        $jsonDataEncoded =
            '{"auth":{"command":"api.authenticate","params":{"appId":"3c1Pt1We9dT3qBAlL7nxAcDERC82","thingKey":"3c1Pt1We9dT3qBAlL7nxAcDERC82","appToken":"DJMYYngGNEit40vA"}}}';
        $result = $this->sendCloudMessage($jsonDataEncoded);
        $obj = json_decode($result);
        // store sessionid
        $this->SetBuffer(
            "sessionid",
            $obj->{'auth'}->{'params'}->{'sessionId'}
        );
    }

    // send go online message
    public function goOnline()
    {
        // noch prüfen ob login erforderlich, session id vorliegt oder ob der user sich selbst drum kümmern soll
        $key = $this->ReadPropertyString("ThingKey");
        $jsonDataEncoded =
            '{"0" : {"params" : {"coding" : "SEVEN_BIT", "imei" : "' .
            $key .
            '","message" : "UP"},"command" : "sms.send"}}';
        $result = $this->sendCloudMessage($jsonDataEncoded);
        return $result;
    }

    // send getStatus message
    private function getRobotStatus()
    {
        $key = $this->ReadPropertyString("ThingKey");
        $jsonDataEncoded =
            '{"state_history":{"command":"alarm.history","params":{"thingKey":"' .
            $key .
            '","key":"robot_state","last":"24h"}},"thing_find":{"command":"thing.find","params":{"key":"' .
            $key .
            '"}}}';
        $obj = $this->sendCloudMessage($jsonDataEncoded);

        return $obj;
    }

    // send cloud message
    private function sendCloudMessage($data)
    {
        $user = $this->ReadPropertyString("User");
        $pass = $this->ReadPropertyString("Pass");
        $key = $this->ReadPropertyString("ThingKey");
        $sessionid = $this->GetBuffer("sessionid");
        $jsonDataEncoded = $data;

        //The URL you're sending the request to.
        $url = "http://api-de.devicewise.com/api";

        //Create a cURL handle.
        $ch = curl_init($url);

        //Create an array of custom headers.
        $customHeaders = [
            "Host: api-de.devicewise.com",
            "Connection: keep-alive",
            "Content-Type: application/json",
            "Accept: application/json",
            "User-Agent: Ambrogio%20Remote/56970 CFNetwork/1331.0.7 Darwin/21.4.0",
            "Accept-Language: de-DE,de;q=0.9",
            "Accept-Encoding: gzip, deflate",
        ];

        // Auth session ID oder user/pass
        if ($sessionid != "") {
            array_push($customHeaders, "sessionId:" . $sessionid);
        } else {
            // auth header
            curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $pass);
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

        //echo $result."\n";
        //$obj = json_decode($result);
        /* auf erfolgreich bzw. auth header prüfen
								if ($obj->{'errorCodes'}[0] = -99999 )
								{
								echo "Authentication header Fehler" ; // Login und dann Session ID notwendig.
								}*/

        return $result;
    }

    protected function SendDebug($Message, $Data, $Format)
    {
        if (is_array($Data)) {
            foreach ($Data as $Key => $DebugData) {
                $this->SendDebug($Message . ":" . $Key, $DebugData, 0);
            }
        } elseif (is_object($Data)) {
            foreach ($Data as $Key => $DebugData) {
                $this->SendDebug($Message . "." . $Key, $DebugData, 0);
            }
        } else {
            parent::SendDebug($Message, $Data, $Format);
        }
    }

    // timer aufruf
    public function TimerEvent()
    {
        $return = $this->updateAmbrogioStatus();
        $this->decodeAmbrogioStatus($return);

        echo "timer ambrogio";

        // neu setzen
        $Interval = (int) $this->ReadPropertyString("Interval") * 1000;
        if ($Interval == 0) {
            $Interval = 5 * 60 * 1000;
        }
        $this->SetTimerInterval("UpdateTimer", $Interval);
    }

    public function updateMap()
    {
        //declare(strict_types=1);
        // koordinaten aus robot_state
        $lat = GetValueFloat($this->GetIDForIdent("lat"));
        $lng = GetValueFloat($this->GetIDForIdent("lgn"));

        $points = [["lat" => $lat, "lng" => $lng]];

        // allgemeine Angaben zur Karte
        $map = [];

        $map["zoom"] = 20;
        $map["size"] = "896x384";
        $map["scale"] = 1;
        $map["maptype"] = "satellite";

        $map["restrict_points"] = false; // Anzahl der Punkte beschränken auf die zulässige Größe der URL
        $map["skip_points"] = 1; // nur jeden x'ten Punkt ausgeben, GoogleMap interpoliert

        $lat = GetValueFloat($this->GetIDForIdent("lat")); // static 52.37022400954993;
        $lng = GetValueFloat($this->GetIDForIdent("lgn")); // static 9.965405454556125;

        $center = [["lat" => $lat, "lng" => $lng]];

        // Mittelpunkt der Karte
        $map["center"] = $center[0];

        $styles = [];
        $styles[] = [
            "feature" => "road.local",
            "color" => "0xff00ff",
        ];
        $styles[] = [
            "feature" => "poi.park",
            "color" => "0x00ff00",
        ];
        $map["styles"] = $styles;

        $markers = [];

        $marker_points = [];
        $marker_points[0] = $points[0];

        $markers[] = [
            "color" => "green",
            "label" => "P",
            "points" => $marker_points,
        ];

        $markers[] = [
            "color" => "0x0000ff",
            "size" => "tiny",
            "points" => $marker_points,
        ];

        $map["markers"] = $markers;

        $paths = [];
        $paths[] = [
            "color" => "0xff0000ff", // 0xhhhhhhoo oo=opacity
            "weight" => 2,
            "points" => $points,
        ];

        $map["paths"] = $paths;
        //echo $this->GetIDForIdent("Map");
        $url = GoogleMaps_GenerateStaticMap(
            $this->ReadPropertyString("MapID"),
            json_encode($map)
        );

        $html = '<img width="1024", height="500" src="' . $url . '" />';

        SetValue($this->GetIDForIdent("Map"), $html);
    }

    private function CreateVarProfileModus()
    {
        if (!IPS_VariableProfileExists("Ambrogio.Online")) {
            IPS_CreateVariableProfile("Ambrogio.Online", 0);
            IPS_SetVariableProfileText("Ambrogio.Online", "", "");
            //IPS_SetVariableProfileIcon("Ambrogio.Online", "Information");
            IPS_SetVariableProfileAssociation(
                "Ambrogio.Online",
                0,
                "nicht verbunden",
                "",
                0xff2600
            );
            IPS_SetVariableProfileAssociation(
                "Ambrogio.Online",
                1,
                "online",
                "",
                0x00f900
            );
        }
        if (!IPS_VariableProfileExists("Ambrogio.State")) {
            IPS_CreateVariableProfile("Ambrogio.State", 1);
            IPS_SetVariableProfileText("Ambrogio.State", "", "");
            //IPS_SetVariableProfileIcon("Ambrogio.State", "Information");
            IPS_SetVariableProfileAssociation("Ambrogio.State", 0, "0", "", -1);
            IPS_SetVariableProfileAssociation(
                "Ambrogio.State",
                1,
                "Ladung",
                "",
                -1
            );
            IPS_SetVariableProfileAssociation(
                "Ambrogio.State",
                2,
                "Mähvorgang",
                "",
                -1
            );
            IPS_SetVariableProfileAssociation(
                "Ambrogio.State",
                4,
                "Fehler",
                "",
                -1
            );
        }
        if (!IPS_VariableProfileExists("Ambrogio.Msg")) {
            IPS_CreateVariableProfile("Ambrogio.Msg", 1);
            IPS_SetVariableProfileText("Ambrogio.Msg", "", "");
            //IPS_SetVariableProfileIcon("Ambrogio.Msg", "Information");
            IPS_SetVariableProfileAssociation(
                "Ambrogio.Msg",
                5,
                "Blockiert",
                "",
                -1
            );
            IPS_SetVariableProfileAssociation(
                "Ambrogio.Msg",
                51,
                "Leere Batterie",
                "",
                -1
            );
            IPS_SetVariableProfileAssociation(
                "Ambrogio.Msg",
                9,
                "Äußerer Umfang",
                "",
                -1
            );
            IPS_SetVariableProfileAssociation(
                "Ambrogio.Msg",
                6,
                "Hindernis",
                "",
                -1
            );
        }
    }
}
?>
