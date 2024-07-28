# ambrogio
IPSymcon Module für Ambrogio Mähroboter Cloud

Eine schnelle Implementierung für ein IPSymcon Modul um den Status einen Ambrogio Mähroboter abzufragen. Es gibt keine offizielle API Beschreibung. 

- Installation über Module
- Anlegen eines Moduls "Ambrogio"
- Konfiguration 3 Parameter aus der Ambrogio Handy App (IMEI des Roboters, User und Passwort wie in App genutzt. Alle drei Informationen bitte nie öffentlich bekannt geben)
- Zyklus der Prüfung in Sekunden (300 Sek als Empfehlung damit nicht zu viel Traffik entsteht)
- Wenn das IPS Modul googleMap genutzt und eingerichtet ist, kann diese als ID Referenz angegeben werden und es wird eine Karte mit Roboter Position angezeigt.

Man kann das Modul auch nutzen um weitere Parameter abzufragen über ein Script mit Funktionsaufruf über AMR_updateAmbrogioStatus(MODUL ID);
Man bekommt den JSON Antwort String zurück. Wenn man diesen mit einem JSON Decoder und einem VirtualIO verbindet werden automatisch Variablen angelegt. Wenn man dies in IPS mit einem PHP Scipt macht, kann man dies auch über ein Ereignis zyklisch starten lassen.

<code>$Text= AMR_updateAmbrogioStatus(MODUL ID AMBROGIO);<br>
VIO_PushText (MODUL ID VIRTUAL_IO,  $Text);</code> 

<b>Hintergrund Infos:</b>

Der Roboter ist nicht permanent Online erreichbar. Generell fragt man die Cloud Daten ab die vom Roboter gesendet werden. Über eine Funktion AMR_goOnline() kann man den Roboter zwingen eine Cloud Verbindung herzustellen.
Allerdings ist es aktuell noch nicht möglich diesen zu steuern oder zu konfigirieren.
