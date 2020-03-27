<?php
/* Icinga Web 2 | (c) 2020 Icinga Development Team | GPLv2+ */

namespace Icinga\Crypt;

use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Class RSA
 *
 * This class allows to encrypt and decrypt data.
 *
 * Usage
 * -----
 * <code>
 * ** Encryption **
 *
 * $rsa = new RSA();
 * $rsa->loadKey(...RSA::keygen());
 *
 * just Encrypt data
 * $data = $rsa->encrypt($myVar1, myVar2); // multiple parameters allowed
 *
 * Encrypt and encodes the given data with MIME base64
 * $data = $rsa->encryptToBase64($myVar1, myVar2); // multiple parameters allowed
 *
 *
 * ** Decryption **
 *
 * $rsa = new RSA();
 * $rsa->loadKey($publicKey, load_private_key_from_database($publicKey));
 *
 * Decrypt and decodes the given data which is encoded in MIME base64
 * list($myVar1, $myVar2) = $rsa->decryptFromBase64(...$data);
 * </code>
 *
 * @package Icinga\Crypt
 */
class RSA
{
    /** @var string */
    private $pubKey;

    /** @var string */
    private $privKey;

    /**
     * Generates a new private and public key pair
     *
     * @return array
     */
    public static function keygen()
    {
        $res = openssl_pkey_new(
            [
                'digest_alg' => 'sha512',
                'private_key_bits' => 4096,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]
        );
        // Extract the private key from $res to $privKey
        openssl_pkey_export($res, $privKey);
        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        return [$privKey, $pubKey];
    }

    /**
     * Get the public key
     *
     * @return string
     */
    public function getPublicKey()
    {
        if (empty($this->pubKey)) {
            throw new UnexpectedValueException('No public key set');
        }
        return $this->pubKey;
    }

    /**
     * Get the private key
     *
     * @return string
     */
    public function getPrivateKey()
    {
        if (empty($this->privKey)) {
            throw new UnexpectedValueException('No private key set');
        }
        return $this->privKey;
    }

    /**
     * Decrypt the given data
     *
     * @param mixed ...$data
     *
     * @return array
     */
    public function decrypt(...$data)
    {
        if (empty(array_filter($data))) {
            throw new UnexpectedValueException('decrypt() do not expects empty value');
        }
        $decryptedValues = array();
        foreach ($data as $valueToDecrypt) {
            openssl_private_decrypt($valueToDecrypt, $decryptedValues[], $this->getPrivateKey());
        }

        return $decryptedValues;
    }

    /**
     * Decrypt and decodes the given data which is encoded in MIME base64
     *
     * @param mixed ...$data
     *
     * @return array
     */
    public function decryptFromBase64(...$data)
    {
        if (empty(array_filter($data))) {
            throw new UnexpectedValueException('decryptFromBase64() do not expects empty value');
        }
        $decodedValues = array();
        foreach ($data as $decodeValue) {
            $decodedValues[] = base64_decode($decodeValue);
        }
        $decryptedData = $this->decrypt(...$decodedValues);

        return $decryptedData;
    }

    /**
     * Encrypt the given data
     *
     * @param mixed ...$data
     *
     * @return array
     */
    public function encrypt(...$data)
    {
        if (empty(array_filter($data))) {
            throw new UnexpectedValueException('encrypt() do not expects empty value');
        }
        $encryptedValues = array();
        foreach ($data as $valueTOEncrypt) {
            openssl_public_encrypt($valueTOEncrypt, $encryptedValues[], $this->getPublicKey());
        }

        return $encryptedValues;
    }

    /**
     * Encrypt and encodes the given data with MIME base64
     *
     * @param mixed ...$data
     *
     * @return array
     */
    public function encryptToBase64(...$data)
    {
        if (empty(array_filter($data))) {
            throw new UnexpectedValueException('encryptToBase64() do not expects empty value');
        }
        $data = $this->encrypt(...$data);
        $encodedValues = array();
        foreach ($data as $valueToEncode) {
            $encodedValues[] = base64_encode($valueToEncode);
        }

        return $encodedValues;
    }

    /**
     * Save the given values in the Variables (private and public key)
     *
     * @param mixed ...$key
     *
     * @return $this
     */
    public function loadKey(...$key)
    {
        if (count($key) > 2 ) {
            throw new InvalidArgumentException('loadKey() do not expects more then 2 parameters, '.count($key).' given');
        }
        foreach ($key as $k) {
            if (strpos($k, "PUBLIC")) {
                $this->pubKey = $k;
            } else {
                $this->privKey = $k;
            }
        }

        return  $this;
    }
}