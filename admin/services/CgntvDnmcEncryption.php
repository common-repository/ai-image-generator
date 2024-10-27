<?php

/**
 * Class CloudCredentialsEncryption
 */
class CgntvDnmcEncryption
{

    /**
     * set cipher algo
     */
    const CIPHER_ALGO = 'AES-256-CBC';

    /**
     * Set encryption algorithm
     */
    const ALGO = 'sha256';

    /**
     * SECRET KEY
     */
    const UPCASTED_SECRET_KEY = 'cgntvdnmc_off_load_secret_key_2020';
    /**
     * SECRET KEY IV
     */
    const UPCASTED_SECRET_KEY_IV = 'cgntvdnmc_off_load_secret_key_iv_2020';

    /**
     * @var false|string
     */
    private static $iv;

    /**
     * @var string
     */
    private static $key;

    /**
     * @var null|CgntvDnmcEncryption
     */
    private static $instance = null;

    /**
     * CloudCredentialsEncryption constructor.
     */
    public function __construct()
    {
        self::$key = hash('sha256', self::UPCASTED_SECRET_KEY);
        self::$iv = substr(hash('sha256', self::UPCASTED_SECRET_KEY_IV), 0, 16);
    }

    /**
     * @return CgntvDnmcEncryption|null
     */
    public static function getInstance(): CgntvDnmcEncryption
    {
        if (self::$instance == null) {
            self::$instance = new CgntvDnmcEncryption();
        }

        return self::$instance;
    }

    /**
     * @param $data
     *
     * @return string
     */
    public static function encrypt(string $data): string
    {
        return base64_encode(openssl_encrypt($data, self::CIPHER_ALGO, self::$key, 0, self::$iv));
    }

    /**
     * @param $data
     *
     * @return false|string
     */
    public static function decrypt(string $data): string
    {
        return openssl_decrypt(base64_decode($data), self::CIPHER_ALGO, self::$key, 0, self::$iv);
    }
}