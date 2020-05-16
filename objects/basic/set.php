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
			throw new Exception(l_t("Unknown set entry %s for set (only %s allowed).",$name,implode(', ',array_keys($this->set))));
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
	 * @return string
	 */
	function icon() {
		if( $this->None )
			return '- ';
		elseif( $this->Ready )
			return '<img src="'.l_s('images/icons/tick.png').'" alt="'.l_t('Ready').'" title="'.l_t('Ready to move to the next turn').'" /> ';
		elseif( $this->Completed )
		{
			return '<img src="'.l_s('images/icons/tick_faded.png').'" alt="'.l_t('Completed').'" title="'.l_t('Orders completed, but not ready for next turn').'" /> ';
		}
		elseif( $this->Saved )
			return '<img src="'.l_s('images/icons/alert_minor.png').'" alt="'.l_t('Saved').'" title="'.l_t('Orders saved, but not completed!').'" /> ';
		else
			return '<img src="'.l_s('images/icons/alert.png').'" alt="'.l_t('Not received').'" title="'.l_t('No orders submitted!').'" /> ';
	}
	function iconText() {
		if( $this->None )
			return l_t('No orders to submit');
		elseif( $this->Ready )
			return l_t('Ready to move to the next turn');
		elseif( $this->Completed )
			return l_t('Orders completed, but not ready for next turn');
		elseif( $this->Saved )
			return l_t('Orders saved, but not completed!');
		else
			return l_t('No orders submitted!');
	}
	/*
		Leaving all possible phases in place for clarity on the possible options to make future changes easier. 
	*/
	function iconAnon() {
		if( $this->None )
			return '- ';
		elseif( $this->Ready )
			return '<img src="'.l_s('images/icons/lock.png').'" alt="'.l_t('Anon').'" title="'.l_t('This country has options this turn').'" /> ';
		elseif( $this->Completed )
			return '<img src="'.l_s('images/icons/lock.png').'" alt="'.l_t('Anon').'" title="'.l_t('This country has options this turn').'" /> ';
		elseif( $this->Saved )
			return '<img src="'.l_s('images/icons/lock.png').'" alt="'.l_t('Anon').'" title="'.l_t('This country has options this turn').'" /> ';
		else
			return '<img src="'.l_s('images/icons/lock.png').'" alt="'.l_t('Anon').'" title="'.l_t('This country has options this turn').'" /> ';
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