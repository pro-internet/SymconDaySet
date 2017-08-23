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
			$this->CreateDaySetProfile("DaySet", 1, 0, 6, 0, 0, "", "", "");
		}
		if(!IPS_VariableProfileExists("Lux")){
			$this->CreateProfile("Lux", 1, 10, 880, 10, 0, "", " lx", "Sun");
		}
}

public function ApplyChanges()
{

	//Never delete this line!
	parent::ApplyChanges();

	$parent = $this->InstanceID;

}

protected function GetModulAC(){

	$moduleList = IPS_GetModuleList();
	$dummyGUID = ""; //init
	foreach($moduleList as $l)
	{
		if(IPS_GetModule($l)['ModuleName'] == "Archive Control")
		{
			$dummyGUID = $l;
			break;
		}
	}
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

	$svid = IPS_GetObjectIDByIdent("SetValueScript", $this->InstanceID);
	IPS_SetVariableCustomAction($vid,$svid);

	$archive = $this->GetModulAC();											// Startet die Get Archive Handler Funktion

	AC_SetLoggingStatus($archive, $vid, true);
	IPS_ApplyChanges($archive); 												// Activate Logging

	return $vid;                                        // Return Variable
}

protected function CreateProfile($profile, $type, $min, $max, $steps, $digits = 0, $prefix = "DMX", $suffix, $icon){
	IPS_CreateVariableProfile($profile, $type);
	IPS_SetVariableProfileValues($profile, $min, $max, $steps);
	IPS_SetVariableProfileText($profile, $prefix, $suffix);
	IPS_SetVariableProfileDigits($profile, $digits);
	IPS_SetVariableProfileIcon($profile, $icon);
}

protected function CreateDaySetProfile($profile, $type, $min, $max, $steps, $digits = 0, $prefix = "DMX", $suffix, $icon){
	IPS_CreateVariableProfile($profile, $type);
	IPS_SetVariableProfileValues($profile, $min, $max, $steps);
	IPS_SetVariableProfileText($profile, $prefix, $suffix);
	IPS_SetVariableProfileDigits($profile, $digits);
	IPS_SetVariableProfileIcon($profile, $icon);

	IPS_SetVariableProfileAssociation($profile, 1, "Früh", "", -1);
	IPS_SetVariableProfileAssociation($profile, 2, "Morgen", "", -1);
	IPS_SetVariableProfileAssociation($profile, 3, "Tag", "", -1);
	IPS_SetVariableProfileAssociation($profile, 4, "Dämmerung", "", -1);
	IPS_SetVariableProfileAssociation($profile, 5, "Abend", "", -1);
	IPS_SetVariableProfileAssociation($profile, 6, "Dämmerung", "", -1);
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


public function CreateModule($id, $dammValue){

	$parent = $this->InstanceID;
	$DaemmerungsVar = "Test";

	if ($dammValue != ""){
		$DaemmerungsVar = $dammValue;
	}

	if ($DaemmerungsVar != ""){
		//Create our trigger

		//SetValueScript erstellen
		if(@IPS_GetObjectIDByIdent("SetValueScript", $this->InstanceID) === false)
		{
			$vid = IPS_CreateScript(0 /* PHP Script */);
			IPS_SetParent($vid, $this->InstanceID);
			IPS_SetName($vid, "SetValue");
			IPS_SetIdent($vid, "SetValueScript");
			IPS_SetHidden($vid, true);
			IPS_SetScriptContent($vid, "<?
				if (\$IPS_SENDER == \"WebFront\")
				{
				SetValue(\$_IPS['VARIABLE'], \$_IPS['VALUE']);
				}
				?>");
		}

		// Create Instance Vars (RGBW & FadeWert)
		// CreateVariable($type, $name, $ident, $parent, $position, $initVal, $profile, $action, $hide)
		$DaySetID = @IPS_GetVariableIDByName("DaySet", $parent);
		if (!IPS_VariableExists($DaySetID)){
			$vid = $this->CreateVariable(1,"DaySet", "DaySet", $parent, 1, 0, "DaySet", "", false);
		}

		$AbendID = @IPS_GetVariableIDByName("DaySet Abend ab", $parent);
		if (!IPS_VariableExists($AbendID)){
			$vid = $this->CreateVariable(1,"DaySet Abend ab", "DaySetAbendAb", $parent, 1, 20, "Lux", "", false);
			$AbendID = @IPS_GetVariableIDByName("DaySet Abend ab", $parent);
		}

		$DaemmerungID = @IPS_GetVariableIDByName("DaySet Dämmerung ab", $parent);
		if (!IPS_VariableExists($DaemmerungID)){
			$vid = $this->CreateVariable(1,"DaySet Dämmerung ab", "DaySetDaemmerungAb", $parent, 1, 450, "Lux", "", false);
			$DaemmerungID = @IPS_GetVariableIDByName("DaySet Dämmerung ab", $parent);
		}

		$FruehID = @IPS_GetVariableIDByName("DaySet Früh ab", $parent);
		if (!IPS_VariableExists($FruehID)){
			$vid = $this->CreateVariable(1,"DaySet Früh ab", "DaySetFruehAb", $parent, 1, 20, "Lux", "", false);
			$FruehID = @IPS_GetVariableIDByName("DaySet Früh ab", $parent);
		}

		$script = '<?
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
	}

	} else if ($time >= $morgen && $time < $tag) {
	// Morgen
	$dayset = 2;
	} else if ($time >= $tag) {

	// Tag
	$dayset = 3;

	// Dämmerung
	if ($lux <= $luxDaemmerung && $hour > 12) {
		$dayset = 4;
		// Abend
		if ($lux <= $luxAbend) {
			$dayset = 5;
		}

	}

	// Nacht
	if($time >= $nacht) {
		$dayset = 6;
	}

	}

	SetValue(59623 /*[Zentrale\DaySet\DaySet\DaySet]*/, $dayset);
	#echo $dayset." ";
	echo $daysetNamen[$dayset];
	?>';


		// Create DaySet Script
		if(@IPS_GetObjectIDByIdent("DaySetScript", $this->InstanceID) === false){
			$sid = IPS_CreateScript(0 /* PHP Script */);
			IPS_SetParent($sid, $this->InstanceID);
			IPS_SetName($sid, "DaySet");
			IPS_SetIdent($sid, "DaySetScript");
			IPS_SetHidden($sid, true);
			IPS_SetScriptContent($sid, $script);
		}
	else {
		$svs = IPS_GetObjectIDByIdent("DaySetScript", $this->InstanceID);
		IPS_DeleteScript($svs, true);

		$sid = IPS_CreateScript(0 /* PHP Script */);
		IPS_SetParent($sid, $this->InstanceID);
		IPS_SetName($sid, "DaySet");
		IPS_SetIdent($sid, "DaySetScript");
		IPS_SetHidden($sid, true);
		IPS_SetScriptContent($sid, $script);
		}

		$svs = IPS_GetObjectIDByIdent("DaySetScript", $this->InstanceID);


		// Trigger on Change
		$FruehTrigger = @IPS_GetVariableIDByName("Tag", $parent);
		if (!IPS_VariableExists($DaySetID)){
			$vid = $this->CreateEventTrigger($FruehID, "Frueh");
			$vid = $this->CreateEventTrigger($AbendID, "Abend");
			$vid = $this->CreateEventTrigger($DaemmerungID, "Daemmerung");
			// Trigger on Time
			// Script, Name, Stunden, Minuten
			$vid = $this ->CreateTimeTrigger($svs, "Tag", 7, 50);
			$vid = $this ->CreateTimeTrigger($svs, "Morgen", 7, 0);
			$vid = $this ->CreateTimeTrigger($svs, "Nacht", 23, 0);
		}




	}
	print_r("Bitte Schließen");

}
}
?>
