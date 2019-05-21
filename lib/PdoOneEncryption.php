<?php

namespace eftec;

use Exception;

/**
 * This class is used for encryption.  It could encrypt (two ways).
 * Class PdoOneEncryption
 * @version 1.2
 * @package eftec
 * @author Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. MIT License  https://github.com/EFTEC/PdoOne
 * @see https://github.com/EFTEC/PdoOne
 */
class PdoOneEncryption
{
	//<editor-fold desc="encryption fields">
	/** @var bool Encryption enabled */
	var $encEnabled = false;
	/** 
	 * @var string Encryption password.<br>
	 * If the method is INTEGER, then the password must be an integer 
	 */
	var $encPassword = '';
	/** @var string Encryption salt */
	var $encSalt = '';
	/**
	 * @var bool If iv is true then it is generated randomly, otherwise is it generated via md5<br>
	 * If true, then the encrypted value is always different (but the decryption yields the same value).<br>
	 * If false, then the value encrypted is the same for the same value.<br>
	 * Set to false if you want a deterministic value (it always returns the same value)
	 */
	var $iv=true;
	/**
	 * @var string<p> Encryption method, example AES-256-CTR (two ways).</p>
	 * <p>If the method is SIMPLE (two ways) then it's uses an simple conversion (short generated value)</p>
	 * <p>If the method is INTEGER (two was) then it's uses another simple conversion (returns an integer)</p>
	 * @see http://php.net/manual/en/function.openssl-get-cipher-methods.php
	 */
	var $encMethod = '';

	/**
	 * PdoOneEncryption constructor.
	 * @param string $encPassword
	 * @param string $encSalt
	 * @param bool $iv if true it uses true and the each encryption is different (even for the same value) but it is not deterministic.
	 * @param string $encMethod Example : AES-128-CTR @see http://php.net/manual/en/function.openssl-get-cipher-methods.php
	 */
	public function __construct($encPassword,$encSalt=null, $iv=true, $encMethod='AES-128-CTR')
	{
		
		$this->encPassword = $encPassword;
		$this->encSalt = ($encSalt===null)?$encPassword:$encSalt; // if null the it uses the same password
		$this->iv = $iv;
		$this->encMethod = $encMethod;
	}
	//</editor-fold>
	

	/**
	 * It is a two way decryption
	 * @param $data
	 * @return bool|string
	 */
	public function decrypt($data)
	{
		if (!$this->encEnabled) return $data; // no encryption
		switch ($this->encMethod) {
			case 'SIMPLE':
				return $this->decryptSimple($data);
			case 'INTEGER':
				return $this->decryptInteger($data);
		}
		$data=base64_decode(str_replace(array('-', '_'),array('+', '/'),$data));
		$iv_strlen = 2 * openssl_cipher_iv_length($this->encMethod);
		if (preg_match("/^(.{" . $iv_strlen . "})(.+)$/", $data, $regs)) {
			try {
				list(, $iv, $crypted_string) = $regs;
				$decrypted_string = openssl_decrypt($crypted_string, $this->encMethod, $this->encPassword, 0, hex2bin($iv));
				return substr($decrypted_string, strlen($this->encSalt));
			} catch(Exception $ex) {
				return false;
			}
		} else {
			return false;
		}
	}	
	/**
	 * It is a two way encryption. The result is htlml/link friendly.
	 * @param $data
	 * @return string
	 */
	public function encrypt($data)
	{
		if (!$this->encEnabled) return $data; // no encryption
		switch ($this->encMethod) {
			case 'SIMPLE':
				return $this->encryptSimple($data);
			case 'INTEGER':
				return $this->encryptInteger($data);
		}
		if ($this->iv) {
			$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->encMethod));
		} else {
			$iv=substr(md5($data,true),0,openssl_cipher_iv_length($this->encMethod));
		}
		$encrypted_string = bin2hex($iv) . openssl_encrypt($this->encSalt . $data, $this->encMethod, $this->encPassword, 0, $iv);
		return str_replace(array('+', '/'), array('-', '_'),base64_encode($encrypted_string));
	}
	
	//<editor-fold desc="encryption SIMPLE">
	/**
	 * It is a simple decryption. It's less safe but the result is shorter.
	 * @param $data
	 * @return string
	 */
	function decryptSimple($data) {
		$result = '';
		$data=base64_decode(str_replace(array('-', '_'),array('+', '/'),$data));
		for($i=0; $i<strlen($data); $i++) {
			$char = substr($data, $i, 1);
			$keychar = substr($this->encPassword, ($i % strlen($this->encPassword))-1, 1);
			$char = chr(ord($char)-ord($keychar));
			$result.=$char;
		}
		return $result;
	}

	/**
	 * It is a simple encryption. It's less safe but generates a short string
	 * @param $data
	 * @return mixed
	 */
	function encryptSimple($data) {
		$result = '';
		for($i=0; $i<strlen($data); $i++) {
			$char = substr($data, $i, 1);
			$keychar = substr($this->encPassword, ($i % strlen($this->encPassword))-1, 1);
			$char = chr(ord($char)+ord($keychar));
			$result.=$char;
		}
		return str_replace(array('+', '/'), array('-', '_'),base64_encode($result));
	}
	//</editor-fold>
	
	
	/**
	 * @param $password
	 * @param $salt
	 * @param $encMethod
	 * @param bool $iv
	 * @throws Exception
	 */
	public function setEncryption($password, $salt, $encMethod,$iv=true)
	{
		if (!extension_loaded('openssl')) {
			$this->encEnabled = false;
			throw new Exception("OpenSSL not loaded, encryption disabled");
		} else {
			$this->encEnabled = true;
			$this->encPassword = $password;
			$this->encSalt = $salt;
			$this->encMethod = $encMethod;
			$this->iv=$iv;
		}
	}
	//<editor-fold desc="encryption INTEGER">

	/**
	 * It encrypts an integer. 
	 * @param integer $n 
	 * @return int
	 */
	public function encryptInteger($n) {
		if (!is_numeric($n)) return null;
		return (PHP_INT_SIZE == 4 ? self::encrypt32($n) : self::encrypt64($n)) ^ $this->encPassword;
	}
	public function decryptInteger($n) {
		if (!is_numeric($n)) return null;
		$n ^= $this->encPassword;
		return PHP_INT_SIZE == 4 ? self::decrypt32($n) : self::decrypt64($n);
	}

	/** @param $n
	 * @return int
	 * @see \eftec\PdoOneEncryption::encryptInteger
	 */
	private function encrypt32($n) {
		return ((0x000000FF & $n) << 24) + (((0xFFFFFF00 & $n) >> 8) & 0x00FFFFFF);
	}
	/** @param $n
	 * @return int
	 * @see \eftec\PdoOneEncryption::decryptInteger
	 */
	private function decrypt32($n) {
		return ((0x00FFFFFF & $n) << 8) + (((0xFF000000 & $n) >> 24) & 0x000000FF);
	}
	/** @param $n
	 * @return int
	 * @see \eftec\PdoOneEncryption::encryptInteger
	 */
	private function encrypt64($n) {
		return ((0x000000000000FFFF & $n) << 48) + (((0xFFFFFFFFFFFF0000 & $n) >> 16) & 0x0000FFFFFFFFFFFF);
	}
	/** @param $n
	 * @return int
	 * @see \eftec\PdoOneEncryption::decryptInteger
	 */
	private function decrypt64($n) {
		return ((0x0000FFFFFFFFFFFF & $n) << 16) + (((0xFFFF000000000000 & $n) >> 48) & 0x000000000000FFFF);
	}
	//</editor-fold">
}