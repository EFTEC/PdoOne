<?php
/** @noinspection UnnecessaryCastingInspection */
/** @noinspection PhpComposerExtensionStubsInspection */
/** @noinspection EncryptionInitializationVectorRandomnessInspection */
/** @noinspection CryptographicallySecureRandomnessInspection */

namespace eftec;

use Exception;
use RuntimeException;

/**
 * This class is used for encryption.  It could encrypt (two ways).
 * Class PdoOneEncryption
 * @version       3.8
 * @package       eftec
 * @author        Jorge Castro Castillo
 * @copyright (c) Jorge Castro C. Dual Licence: MIT and Commercial License  https://github.com/EFTEC/PdoOne
 * @see           https://github.com/EFTEC/PdoOne
 */
class PdoOneEncryption
{
    //<editor-fold desc="encryption fields">
    /** @var bool Encryption enabled */
    public $encEnabled = false;
    /**
     * @var string=['sha256','sha512','md5'][$i]
     * @see https://www.php.net/manual/en/function.hash-algos.php
     */
    public $hashType = 'sha256';
    /**
     * @var string Encryption password.<br>
     * If the method is INTEGER, then the password must be an integer
     */
    public $encPassword = '';
    /** @var string Encryption salt */
    public $encSalt = '';
    /**
     * @var bool If iv is true then it is generated randomly, otherwise is it generated via md5<br>
     * If true, then the encrypted value is always different (but the decryption yields the same value).<br>
     * If false, then the value encrypted is the same for the same value.<br>
     * Set to false if you want a deterministic value (it always returns the same value)
     */
    public $iv = true;
    /**
     * @var string<p> Encryption method, example AES-256-CTR (two ways).</p>
     * <p>If the method is SIMPLE (two ways) then it's uses a simple conversion (short generated value)</p>
     * <p>If the method is INTEGER (two was) then it's uses another simple conversion (returns an integer)</p>
     * @see http://php.net/manual/en/function.openssl-get-cipher-methods.php
     */
    public $encMethod = '';

    /**
     * PdoOneEncryption constructor.
     * @param string $encPassword
     * @param string|null $encSalt
     * @param bool   $iv        If iv is true then it is generated randomly (not deterministically)
     *                          otherwise, is it generated via md5
     * @param string $encMethod Example : AES-128-CTR @see
     *                          http://php.net/manual/en/function.openssl-get-cipher-methods.php
     */
    public function __construct(string $encPassword,?string $encSalt = null,bool $iv = true,string $encMethod = 'AES-256-CTR')
    {
        $this->encPassword = $encPassword;
        $this->encSalt = $encSalt ?? $encPassword; // if null then it uses the same password
        $this->iv = $iv;
        $this->encMethod = $encMethod;
    }
    //</editor-fold>


    /**
     * It is a two-way decryption
     * @param mixed $data
     * @return bool|string
     */
    public function decrypt($data)
    {
        if (!$this->encEnabled || $data === null) {
            return $data;
        } // no encryption
        switch ($this->encMethod) {
            case 'SIMPLE':
                return $this->decryptSimple($data);
            case 'INTEGER':
                return $this->decryptInteger($data);
        }
        $data = base64_decode(str_replace(array('-', '_'), array('+', '/'), $data));
        $iv_strlen = 2 * openssl_cipher_iv_length($this->encMethod);
        if (preg_match('/^(.{' . $iv_strlen . '})(.+)$/', $data, $regs)) {
            try {
                [, $iv, $crypted_string] = $regs;
                $decrypted_string = openssl_decrypt($crypted_string, $this->encMethod, $this->encPassword, 0, hex2bin($iv));
                $result = substr($decrypted_string, strlen($this->encSalt));
                if (strlen($result) > 2 && $result[1] === ':') {
                    /** @noinspection UnserializeExploitsInspection */
                    $resultfinal = @unserialize($result); // we try to unserialize, if fails, then we keep the current value
                    $result = $resultfinal === false ? $result : $resultfinal;
                }
                return $result;
            } catch (Exception $ex) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * It is a two-way encryption. The result is htlml/link friendly.
     * @param mixed $data For the method simple, it could be a simple value (string,int,etc.)<br>
     *                    For the method integer, it must be an integer<br>
     *                    For other methods, it could be any value. If it is an object or array, then it is
     *                    serialized<br>
     * @return string|int|false     Returns a string with the value encrypted
     */
    public function encrypt($data)
    {
        if (!$this->encEnabled) {
            return $data;
        } // no encryption
        switch ($this->encMethod) {
            case 'SIMPLE':
                return $this->encryptSimple($data);
            case 'INTEGER':
                return $this->encryptInteger($data);
        }
        if (is_array($data) || is_object($data)) {
            $data = serialize($data);
        }
        if ($this->iv) {
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->encMethod));
        } else {
            $iv = substr(md5($data, true), 0, openssl_cipher_iv_length($this->encMethod));
        }
        $encrypted_string = bin2hex($iv) . openssl_encrypt($this->encSalt . $data, $this->encMethod
                , $this->encPassword, 0, $iv);
        return str_replace(array('+', '/'), array('-', '_'), base64_encode($encrypted_string));
    }

    /**
     * It generates a hash based in the hash type ($this->hashType), the data used and the SALT.
     *
     * @param mixed $data It could be any type of serializable data.
     * @return string If the serialization is not set, then it returns the same value.
     */
    public function hash($data): string
    {
        if (!is_string($data)) {
            $data = serialize($data);
        }
        if (!$this->encEnabled) {
            return $data;
        }
        return hash($this->hashType, $this->encSalt . $data);
    }

    //<editor-fold desc="encryption SIMPLE">

    /**
     * It is a simple decryption. It's less safe but the result is shorter.
     * @param $data
     * @return string
     */
    public function decryptSimple($data): string
    {
        $result = '';
        $data = base64_decode(str_replace(array('-', '_'), array('+', '/'), $data));
        $l = strlen($data);
        for ($i = 0; $i < $l; $i++) {
            $char = $data[$i];
            $keychar = $this->encPassword[($i % strlen($this->encPassword)) - 1];
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }

    /**
     * It is a simple encryption. It's less safe but generates a short string
     * @param $data
     * @return array|string|string[]
     */
    public function encryptSimple($data)
    {
        $result = '';
        $l = strlen($data);
        for ($i = 0; $i < $l; $i++) {
            $char = substr($data, $i, 1);
            $keychar = $this->encPassword[($i % strlen($this->encPassword)) - 1];
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return str_replace(array('+', '/'), array('-', '_'), base64_encode($result));
    }
    //</editor-fold>


    /**
     * @param string $password
     * @param string $salt
     * @param string $encMethod
     * @param bool $iv
     * @throws Exception
     */
    public function setEncryption(string $password,string $salt,string $encMethod,bool $iv = true): void
    {
        if (!extension_loaded('openssl')) {
            $this->encEnabled = false;
            throw new RuntimeException('OpenSSL not loaded, encryption disabled');
        }
        $this->encEnabled = true;
        $this->encPassword = $password;
        $this->encSalt = $salt;
        $this->encMethod = $encMethod;
        $this->iv = $iv;
    }

    /**
     * It changes the hash type.
     *
     * @param string $hashType =hash_algos()[$i]
     * @return void
     * @see https://www.php.net/manual/en/function.hash-algos.php
     */
    public function setHashType(string $hashType): void
    {
        $this->hashType = $hashType;
    }
    //<editor-fold desc="encryption INTEGER">

    /**
     * It encrypts an integer.
     * @param integer $n
     * @return int|false
     */
    public function encryptInteger(int $n)
    {
        if (!is_numeric($n)) {
            return false;
        }
        return (PHP_INT_SIZE === 4 ? $this->encrypt32($n) : $this->encrypt64($n)) ^ $this->encPassword;
    }

    /**
     * It decrypt an integer
     *
     * @param int $n
     * @return int|null
     */
    public function decryptInteger(int $n): ?int
    {
        if (!is_numeric($n)) {
            return null;
        }
        $n ^= $this->encPassword;
        return PHP_INT_SIZE === 4 ? $this->decrypt32($n) : $this->decrypt64($n);
    }

    /** @param int $n
     * @return int
     * @see \eftec\PdoOneEncryption::encryptInteger
     */
    private function encrypt32(int $n): int
    {
        return ((0x000000FF & $n) << 24) + (((0xFFFFFF00 & $n) >> 8) & 0x00FFFFFF);
    }

    /** @param int $n
     * @return int
     * @see \eftec\PdoOneEncryption::decryptInteger
     */
    private function decrypt32(int $n): int
    {
        return ((0x00FFFFFF & $n) << 8) + (((0xFF000000 & $n) >> 24) & 0x000000FF);
    }

    /** @param int $n
     * @return int
     * @see \eftec\PdoOneEncryption::encryptInteger
     */
    private function encrypt64(int $n): int
    {
        /** @noinspection PhpCastIsUnnecessaryInspection */
        return ((0x000000000000FFFF & $n) << 48) + ((((int)0xFFFFFFFFFFFF0000 & $n) >> 16.0) & 0x0000FFFFFFFFFFFF);
    }

    /** @param int $n
     * @return int
     * @see \eftec\PdoOneEncryption::decryptInteger
     */
    private function decrypt64(int $n): int
    {
        /** @noinspection PhpCastIsUnnecessaryInspection */
        return (((int)0x0000FFFFFFFFFFFF & $n) << 16.0) + ((((int)0xFFFF000000000000 & $n) >> 48.0) & 0x000000000000FFFF);
    }
    //</editor-fold">
}
