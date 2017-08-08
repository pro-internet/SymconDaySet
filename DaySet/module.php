<?

class DaySet extends IPSModule
{

	public function Create()
	{
		//Never delete this line!
		parent::Create();

		if(@$this->RegisterPropertyString("DaySet") !== false){
			$this->RegisterPropertyString("DaySet","");
		}

		// to get our parent
		$parent = $this->InstanceID;

		// Create Instance Profies
		// CreateProfile($profile, $type, $min, $max, $steps, $digits = 0, $prefix = "DMX", $suffix = "", $icon = "")
		if(!IPS_VariableProfileExists("DaySet")){
			$this->CreateProfile("DaySet", 1, 0, 6, 0, 0, "", "", "");
		}
		if(!IPS_VariableProfileExists("Dammerung")){
			$this->CreateProfile("Dammerung", 1, 10, 880, 10, 0, "", "lx", "Moon");
		}




}

public function ApplyChanges()
{

	//Never delete this line!
	parent::ApplyChanges();

	$parent = $this->InstanceID;

	//Create our trigger
  $DaemmerungsVar = json_decode($this->ReadPropertyString("DaemmerungsVar"));


	// Create Instance Vars (RGBW & FadeWert)
	// CreateVariable($type, $name, $ident, $parent, $position, $initVal, $profile, $action, $hide)
	$DaySetID = @IPS_GetVariableIDByName("DaySet", $parent);
	if (!IPS_VariableExists($DaySetID)){
		$vid = $this->CreateVariable(1,"DaySet", "DaySet", $parent, 1, 0, "DaySet", "", false);
	}

	$AbendID = @IPS_GetVariableIDByName("DaySet Abend ab", $parent);
	if (!IPS_VariableExists($AbendID)){
		$vid = $this->CreateVariable(1,"DaySet Abend ab", "DaySetAbendAb", $parent, 1, 20, "Dammerung", "", false);
		$AbendID = @IPS_GetVariableIDByName("DaySet Abend ab", $parent);
	}

	$DaemmerungID = @IPS_GetVariableIDByName("DaySet Dämmerung ab", $parent);
	if (!IPS_VariableExists($DaemmerungID)){
		$vid = $this->CreateVariable(1,"DaySet Dämmerung ab", "DaySetDaemmerungAb", $parent, 1, 450, "Dammerung", "", false);
		$DaemmerungID = @IPS_GetVariableIDByName("DaySet Dämmerung ab", $parent);
	}

	$FruehID = @IPS_GetVariableIDByName("DaySet Früh ab", $parent);
	if (!IPS_VariableExists($FruehID)){
		$vid = $this->CreateVariable(1,"DaySet Früh ab", "DaySetFruehAb", $parent, 1, 20, "Dammerung", "", false);
		$FruehID = @IPS_GetVariableIDByName("DaySet Früh ab", $parent);
	}



	// Create DaySet Script
	if(@IPS_GetObjectIDByIdent("DaySetScript", $this->InstanceID) === false){
	$sid = IPS_CreateScript(0 /* PHP Script */);
	IPS_SetParent($sid, $this->InstanceID);
	IPS_SetName($sid, "DaySet");
	IPS_SetIdent($sid, "DaySetScript");
	IPS_SetHidden($sid, true);
	IPS_SetScriptContent($sid, $Script = '<?

	echo IPS_GetName($_IPS["SELF"])." \n";

	$dayset = 6;	// Nacht

	$daysetNamen = array(
		"1" => "Früh",
		"2" => "Morgen",
		"3" => "Tag",
		"4" => "Dämmerung",
		"5" => "Abend",
		"6" => "Nacht"
	);

	$hour = date("H");
	$minute = date("i");

	$time = intval($hour.$minute);

	$nachtTime = IPS_GetEvent(57506 /*[Zentrale\DaySet\DaySet\Nacht]*/)["CyclicTimeFrom"];
	$nacht = intval(($nachtTime["Hour"] < 10 ? "0" : "").$nachtTime["Hour"].($nachtTime["Minute"] < 10 ? "0" : "").$nachtTime["Minute"]);

	$morgenTime = IPS_GetEvent(35721 /*[Zentrale\DaySet\DaySet\Morgen]*/)["CyclicTimeFrom"];
	$morgen = intval(($morgenTime["Hour"] < 10 ? "0" : "").$morgenTime["Hour"].($morgenTime["Minute"] < 10 ? "0" : "").$morgenTime["Minute"]);

	$tagTime = IPS_GetEvent(23800 /*[Zentrale\DaySet\DaySet\Tag]*/)["CyclicTimeFrom"];
	$tag = intval(($tagTime["Hour"] < 10 ? "0" : "").$tagTime["Hour"].($tagTime["Minute"] < 10 ? "0" : "").$tagTime["Minute"]);

$lux = GetValue('.$DaemmerungsVar.');
$luxFrueh = GetValue('.$FruehID.');
$luxDaemmerung = GetValue('.$DaemmerungID.');
$luxAbend = GetValue('.$AbendID.');

if($time >= 0 && $time < $morgen) {

// Früh
if ($lux >= $luxFrueh) {

	$dayset = 1;
	#SMTP_SendMail(31819, "DaySet Früh", "");

}

} else if ($time >= $morgen && $time < $tag) {

// Morgen
$dayset = 2;
#SMTP_SendMail(31819, "DaySet Morgen", "");

} else if ($time >= $tag) {

// Tag
$dayset = 3;
#SMTP_SendMail(31819, "DaySet Tag", "");

// Dämmerung
if ($lux <= $luxDaemmerung && $hour > 12) {

	$dayset = 4;
	#SMTP_SendMail(31819, "DaySet Dämmerung", "");

	// Abend
	if ($lux <= $luxAbend) {

		$dayset = 5;
		#SMTP_SendMail(31819, "DaySet Abend", "");
	}

}

// Nacht
if($time >= $nacht) {
	$dayset = 6;
	#SMTP_SendMail(31819, "DaySet Nacht", "");
}

}

SetValue(59623 /*[Zentrale\DaySet\DaySet\DaySet]*/, $dayset);

#echo $dayset." ";
echo $daysetNamen[$dayset];
#SMTP_SendMail(31819, "SkyVilla DaySet ".$daysetNamen[$dayset], " ");


?>');
} else {
$sid = IPS_GetObjectIDByIdent("DaySetScript", $this->InstanceID);
IPS_SetScriptContent($sid, $Script);
}

$svs = IPS_GetObjectIDByIdent("DaySetScript", $this->InstanceID);


// Trigger on Change
$vid = $this->CreateEventTrigger($FruehID, "Frueh");
$vid = $this->CreateEventTrigger($AbendID, "Abend");
$vid = $this->CreateEventTrigger($DaemmerungID, "Daemmerung");

// Trigger on Time
// Script, Name, Stunden, Minuten
$vid = $this ->CreateTimeTrigger($svs, "tag", 7, 50);
$vid = $this ->CreateTimeTrigger($svs, "Morgen", 7, 0);
$vid = $this ->CreateTimeTrigger($svs, "Nacht", 23, 0);

}



// to Create our Variables
protected function CreateVariable($type, $name, $ident, $parent, $position, $initVal, $profile, $action, $hide){
	$vid = IPS_CreateVariable($type);

	IPS_SetName($vid,$name);                            // set Name
	IPS_SetParent($vid,$parent);                        // Parent
	IPS_SetIdent($vid,$ident);                          // ident halt :D
	IPS_SetPosition($vid,$position);                    // List Position
	SetValue($vid,$initVal);                            // init value
	IPS_SetHidden($vid, $hide);                         // Objekt verstecken

	if(!empty($profile)){
		IPS_SetVariableCustomProfile($vid,$profile);    	// Set custom profile on Variable
	}
	if(!empty($action)){
		IPS_SetVariableCustomAction($vid,$action);      	// Set custom action on Variable
	}

	return $vid;                                        // Return Variable
}

protected function CreateProfile($profile, $type, $min, $max, $steps, $digits = 0, $prefix = "DMX", $suffix, $icon){
	IPS_CreateVariableProfile($profile, $type);
	IPS_SetVariableProfileValues($profile, $min, $max, $steps);
	IPS_SetVariableProfileText($profile, $prefix, $suffix);
	IPS_SetVariableProfileDigits($profile, $digits);
	IPS_SetVariableProfileIcon($profile, $icon);
}

protected function CreateEventTrigger($triggerID, $name){
	$Instance = $this->InstanceID;

	// 0 = ausgelöstes; 1 = zyklisches; 2 = Wochenplan;
	$eid = IPS_CreateEvent(0);
	// Set Parent
	IPS_SetParent($eid, $Instance);
	// Set Name
	IPS_SetName($eid, "TriggerOnChange".$name);
	IPS_SetIdent($eid, "TriggerOnChange".$name);
	// Set Script
	IPS_SetEventScript($eid, "DS_callScript();");
	// OnUpdate für Variable 12345
	IPS_SetEventTrigger($eid, 0, $triggerID);
	IPS_SetEventActive($eid, true);

	return $eid;
}

protected function CreateTimeTrigger($svs, $name, $stunden, $minuten){
	$Instance = $this->InstanceID;

	// 0 = ausgelöstes; 1 = zyklisches; 2 = Wochenplan;
	$eid = IPS_CreateEvent(1);

	IPS_SetEventCyclicTimeFrom($eid, $stunden, $minuten, 0);
	// Set Parent
	IPS_SetParent($eid, $Instance);
	// Set Name
	IPS_SetName($eid, $name);
	IPS_SetIdent($eid, $name);

	// Set Script
	IPS_SetEventScript($eid, "DS_callScript();");

	IPS_SetEventActive($eid, true);

	return $eid;
}

public function callScript(){
	IPS_RunScript($svs);
}

}

?>
