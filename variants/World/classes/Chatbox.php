<?php

class WorldVariant_Chatbox extends Chatbox {

	public function renderMessages($msgCountryID, $messages) {
		global $Member;

		for($i=0; $i<count($messages); $i++)
			if( $messages[$i]['fromCountryID']!=0)
				if (!isset($Member) || $Member->countryID != $messages[$i]['fromCountryID'])
					$messages[$i]['message'] = '[<strong>'.$this->countryName($messages[$i]['fromCountryID']).'</strong>]<span style="color: rgba(255, 255, 255, 0.8);">:'.$messages[$i]['message'];
				else
					$messages[$i]['message'] = '[<strong>You</strong>]<span style="color: rgba(255, 255, 255, 0.8);">:'.$messages[$i]['message'];				

		return parent::renderMessages($msgCountryID, $messages);

	}
}
