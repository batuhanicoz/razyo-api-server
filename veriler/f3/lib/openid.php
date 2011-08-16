<?php

/**
	OpenID plugin for single sign-on and authentication

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2011 F3::Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package OpenID
		@version 2.0.4
**/

//! OpenID plugin
class OpenID extends Base {

	//@{ Locale-specific error/exception messages
	const
		TEXT_EndPoint='Unable to find OpenID provider';
	//@}

	var
		//! HTTP request parameters
		$args=array();

	/**
		Approve OpenID login
			@public
	**/
	function approve() {
		var_dump($GLOBALS['_'.$_REQUEST['METHOD']]);
		die;
	}

	/**
		Initiate OpenID authentication sequence
			@return bool
			@public
	**/
	function auth() {
		if (isset($this->args['server']))
			$op=$this->args['server'];
		elseif (isset($this->args['provider']))
			$op=$this->args['provider'];
		else {
			trigger_error(self::TEXT_EndPoint);
			return FALSE;
		}
		$root=self::$vars['PROTOCOL'].'://'.
			$_SERVER['SERVER_NAME'].(self::$vars['BASE']?:'');
		foreach (array('return_to','trust_root') as $var)
			if (isset($this->args[$var]) &&
				!preg_match('/:\/\//',$this->args[$var]))
				$this->args[$var]=$root.$this->args[$var];
		$vars=array(
			'openid.ns'=>
				urlencode('http://specs.openid.net/auth/2.0'),
			'openid.mode'=>
				'checkid_setup',
			'openid.identity'=>
				urlencode($this->args['identity']),
			'openid.trust_root'=>
				urlencode($this->args['trust_root'])
		);
		if ($this->args['identity']!=$this->args['claimed_id'])
			$vars['openid.claimed_id']=$this->args['claimed_id'];
		if (!isset($this->args['required']))
			$this->args['required']='fullname,email,dob,gender,'.
				'postcode,country,language,timezone';
		foreach (array('required','optional') as $var)
			if (isset($this->args[$var]))
				$vars['openid.sreg.'.$var]=urlencode($this->args[$var]);
		$query=http_build_query($vars);
		foreach ($op as $server) {
			$result=Web::http('POST '.$server,$query);
			if ($result) {
				echo $result;
				break;
			}
		}
	}

	/**
		Bind value to OpenID request parameter
			@param $key string
			@param $val string
			@public
	**/
	function set($key,$val) {
		if ($key=='identity') {
			// Normalize
			if (!preg_match('/https?:\/\//i',$val))
				$val='http://'.$val;
			$url=parse_url($val);
			// Remove fragment; reconnect parts
			$val=$url['scheme'].'://'.
				(isset($url['user'])?
					($url['user'].
					(isset($url['pass'])?
						(':'.$url['pass']):'').'@'):'').
				strtolower($url['host']).
				(isset($url['path'])?$url['path']:'/').
				(isset($url['query'])?('?'.$url['query']):'');
			$this->args['claimed_id']=$val;
			$this->args['identity']=$val;
			// HTML-based discovery of OpenID provider
			$text=Web::http('GET '.$val);
			$len=strlen($text);
			$ptr=0;
			// Parse document
			while ($ptr<$len)
				if (preg_match('/^<link\b'.
					'((?:\s+\w+s*=\s*(?:"(?:.+?)"|\'(?:.+?)\'))*)\s*\/?>/is',
					substr($text,$ptr),$match)) {
					if ($match[1]) {
						// Process attributes
						preg_match_all('/\s+(rel|href)\s*=\s*'.
							'(?:"(.+?)"|\'(.+?)\')/s',$match[1],$attr,
							PREG_SET_ORDER);
						$node=array();
						foreach ($attr as $kv)
							$node[$kv[1]]=isset($kv[2])?$kv[2]:$kv[3];
						if (isset($node['rel']) &&
							preg_match_all('/openid2?\.(\w+)/',
								$node['rel'],$var,PREG_SET_ORDER) &&
							isset($node['href']))
							foreach ($var as &$tag) {
								if (!isset($this->args[$tag[1]]))
									$this->args[$tag[1]]=array();
								array_push($this->args[$tag[1]],$node['href']);
							}
					}
					$ptr+=strlen($match[0]);
				}
				else
					$ptr++;
			// Get OpenID provider's endpoint URL
			if (isset($this->args['provider'])) {
				// OpenID 2.0
				if (isset($this->args['localidentity']))
					$this->args['identity']=$this->args['localidentity'];
			}
			elseif (isset($this->args['server'])) {
				// OpenID 1.1
				if (isset($this->args['delegate']))
					$this->args['identity']=$this->args['delegate'];
			}
		}
		else
			$this->args[$key]=self::resolve($val);
	}

	/**
		Return value of OpenID request parameter
			@param $key string
			@public
	**/
	function get($key) {
		return isset($this->args[$key])?$this->args[$key]:NULL;
	}

	/**
		Remove OpenID request parameter
			@param $key
			@public
	**/
	function clear($key) {
		unset($this->args[$key]);
	}

	/**
		Override base constructor
			@public
	**/
	function __construct() {
	}

}
