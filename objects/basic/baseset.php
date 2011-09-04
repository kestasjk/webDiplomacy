<?php

class baseSet {
	protected $maxBits=64;
	static function base($elementCount) {
		return floor(exp((log(2)*$this->maxBits)/$elementCount));
	}

	protected $value;
	protected $base;
	protected $elementCount;
	public function __construct($base, $elementCount, $value=false) {
		$this->base=$base;
		$this->elementCount=$elementCount;

		$this->value=array();
		if( !$value )
		{
			for($i=0;$i<$elementCount;$i++)
				$this->value[$i]=0;
		}
		else
		{
			for($i=0;$i<$elementCount;$i++)
				$this->value[$i]=(int)substr($value,$i,1);
		}
	}
	public function add($elementIndex) {
		if( $this->value[$elementIndex]+1 >= $this->base )
			for($i=0;$i<$this->elementCount;$i++)
				if( $this->value[$i]>0 )
					$this->value[$i]--;

		$this->value[$elementIndex]++;
	}
	public function update() {
		return "CONV(".implode('',$this->value).",".$this->base.",10)";
	}
	static function select($colName, $base, $elementCount) {
		return "LPAD(CAST(CONV(".$colName.",10,".$base.") AS CHAR),".$elementCount.",'0')";
	}
	public function compare(baseSet $cmp) {

		$mySum=0;
		$hisSum=0;
		$maxSum=$this->elementCount*($this->base-1);

		for($i=0;$i<$this->elementCount;$i++)
		{
			$mySum+=$this->values[$i];
			$hisSum+=$cmp->values[$i];
		}
		if($mySum==0||$hisSum==0) return 0;
		$mySum/=$maxSum;
		$hisSum/=$maxSum;

		;

		for($i=0;$i<$this->elementCount;$i++)
		{
			($this->values[$i]/($this->base-1))*(($cmp->values[$i]/($this->base-1))/($mySum/$hisSum));
		}
	}
}
/*
 * name,
 * [ userID, countryID, orderStatus, newMessagesFrom [ 92341,192391,1239023,1293912,94395,658969 ]
 *
function encryptBit($privateKey, $bit) {
	// The base; a whole multiple of privateKey
	$base = (floor(rand(pow(2,20),pow(2,30))/$privateKey)*$privateKey);

	// Bit-flag, indicating the value of the bit; larger than (privateKey/2-1) if true, smaller if false
	$bitFlag = ($bit ? rand(floor($privateKey/2),$privateKey-1) : rand(0,floor($privateKey/2)-1));

	return $base + $bitFlag;

	/*
	 * function decryptBit(privateKey, encryptedBit) {
	 * 		// Remove $base using privateKey, as it is a multiple of privateKey, giving $bitFlag
	 * 		var bitFlag = (encryptedBit%privateKey);
	 * 		// bitFlag's size in relation to the privateKey gives us the value of the bit
	 * 		return ( bitFlag >= Math.floor(privateKey/2.0) );
	 * }
	 *
}
*/