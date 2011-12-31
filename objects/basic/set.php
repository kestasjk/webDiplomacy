<?php

abstract class set
{
	protected $set=array();
	public $updated=false;

	public function __construct($set)
	{
		foreach($this->allowed as $name)
			$this->set[$name]=false;

		$setNames=explode(',',$set);
		foreach($this->set as $name=>$value)
			if( in_array($name, $setNames) )
				$this->set[$name] = true;
	}

	public function __set($name, $value)
	{
		$value=($value ? true : false);

		if( $this->{$name}!=$value)
		{
			$this->set[$name] = $value;
			$this->updated=true;
		}
	}

	public function __get($name)
	{
		if( !isset($this->set[$name]) )
			throw new Exception("Unknown set entry ".$name." for set (only ".implode(', ',array_keys($this->set))." allowed).");
		else
			return $this->set[$name];
	}

	public function __toString()
	{
		$a=array();
		foreach($this->set as $n=>$v)
			if( $v ) $a[]=$n;

		return implode(",", $a );
	}
}

class setMemberOrderStatus extends set {
	protected $allowed=array('None','Saved','Completed','Ready');

	/**
	 * None,Saved,Completed,Ready
	 * @return unknown_type
	 */
	function icon() {
		if( $this->None )
			return '- ';
		elseif( $this->Ready )
			return '<img src="images/icons/tick.png" alt="Ready" title="Ready to move to the next turn" /> ';
		elseif( $this->Completed )
			return '<img src="images/icons/tick_faded.png" alt="Completed" title="Orders completed, but not ready for next turn" /> ';
		elseif( $this->Saved )
			return '<img src="images/icons/alert_minor.png" alt="Saved" title="Orders saved, but not completed!" /> ';
		else
			return '<img src="images/icons/alert.png" alt="Not received" title="No orders submitted!" /> ';
	}
	function iconText() {
		if( $this->None )
			return 'No orders to submit';
		elseif( $this->Ready )
			return 'Ready to move to the next turn';
		elseif( $this->Completed )
			return 'Orders completed, but not ready for next turn';
		elseif( $this->Saved )
			return 'Orders saved, but not completed!';
		else
			return 'No orders submitted!';
	}
}
class setMemberVotes extends set {
	protected $allowed=array('Pause','Draw','Cancel');
}
class setMemberNewMessagesFrom extends set {
	protected $allowed=array('Global','England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');
}
class setVariantStatus extends set {
	protected $allowed=array('Installed','Allowed','Enabled');
}
class setUserNotifications extends set {
	protected $allowed=array('PrivateMessage', 'GameMessage', 'Unfinalized', 'GameUpdate');
}

?>