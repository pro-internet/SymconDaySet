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

		// Create Instance Vars (RGBW & FadeWert)
		// CreateVariable($type, $name, $ident, $parent, $position, $initVal, $profile, $action, $hide)
		$VarID = @IPS_GetVariableIDByName("DaySet", $parent);
		if (!IPS_VariableExists($VarID)){
			$vid = $this->CreateVariable(1,"DaySet", "DaySet", $parent, 1, 0, "DaySet", "", false);
		}

		// Create DaySet Script
		if(@IPS_GetObjectIDByIdent("DaySet Script", $this->InstanceID) === false){
		$sid = IPS_CreateScript(0 /* PHP Script */);
		IPS_SetParent($sid, $this->InstanceID);
		IPS_SetName($sid, "DaySet");
		IPS_SetIdent($sid, "DaySet Script");
		IPS_SetHidden($sid, true);
		IPS_SetScriptContent($sid, '<?

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

	$lux = GetValue(43183 /*[Zentrale\Wetter\Geräte\Dämmerung\Wert]*/);
$luxFrueh = GetValue(33780 /*[Zentrale\DaySet\DaySet\DaySet Früh ab]*/);
$luxDaemmerung = GetValue(53933 /*[Zentrale\DaySet\DaySet\DaySet Dämmerung ab]*/);
$luxAbend = GetValue(48283 /*[Zentrale\DaySet\DaySet\DaySet Abend ab]*/);

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
	$sid = IPS_GetObjectIDByIdent("DaySet Script", $this->InstanceID);
	IPS_SetScriptContent($sid, '<?

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

$lux = GetValue(43183 /*[Zentrale\Wetter\Geräte\Dämmerung\Wert]*/);
$luxFrueh = GetValue(33780 /*[Zentrale\DaySet\DaySet\DaySet Früh ab]*/);
$luxDaemmerung = GetValue(53933 /*[Zentrale\DaySet\DaySet\DaySet Dämmerung ab]*/);
$luxAbend = GetValue(48283 /*[Zentrale\DaySet\DaySet\DaySet Abend ab]*/);

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
}

$svs = IPS_GetObjectIDByIdent("SetValueScript", $this->InstanceID);




}

public function ApplyChanges()
{

	//Never delete this line!
	parent::ApplyChanges();

	//Create our trigger


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

}

?>
