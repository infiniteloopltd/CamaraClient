<?php

namespace InfiniteLoop\CamaraClient\Internal;

use Exception;

/**
 * Security class for AES-GCM encryption and decryption
 * Ported from C# implementation
 */
class Security
{
    /**
     * Encrypt plaintext using AES-GCM with a given key
     *
     * @param string $plainText The text to encrypt
     * @param string $key The encryption key (will be padded/trimmed to 16 bytes)
     * @return string Base64 encoded encrypted data (IV + ciphertext + tag)
     * @throws Exception If encryption fails
     */
    public static function encrypt(string $plainText, string $key): string
    {
        try {
            // Pad or trim key to exactly 16 bytes (128 bits)
            $keyBytes = substr(str_pad($key, 16, "\0"), 0, 16);

            // Generate random 12-byte IV (nonce) for GCM mode
            $iv = random_bytes(12);

            // Encrypt using AES-128-GCM
            $ciphertext = openssl_encrypt(
                $plainText,
                'aes-128-gcm',
                $keyBytes,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
                '',  // Additional authenticated data (empty)
                16   // Tag length in bytes
            );

            if ($ciphertext === false) {
                throw new Exception('Encryption failed: ' . openssl_error_string());
            }

            // Combine IV + ciphertext + tag (matches C# structure)
            $result = $iv . $ciphertext . $tag;

            // Return base64 encoded result
            return base64_encode($result);

        } catch (Exception $ex) {
            throw new Exception('Encryption failed: ' . $ex->getMessage());
        }
    }

    /**
     * Decrypt encrypted text using AES-GCM with a given key
     *
     * @param string $encryptedText Base64 encoded encrypted data
     * @param string $key The decryption key (will be padded/trimmed to 16 bytes)
     * @return string Decrypted plaintext
     * @throws Exception If decryption fails
     */
    public static function decrypt(string $encryptedText, string $key): string
    {
        try {
            // Pad or trim key to exactly 16 bytes (128 bits)
            $keyBytes = substr(str_pad($key, 16, "\0"), 0, 16);

            // Decode base64 encoded data
            $encryptedBytes = base64_decode($encryptedText, true);

            if ($encryptedBytes === false) {
                throw new Exception('Invalid base64 encoded data');
            }

            $encryptedLength = strlen($encryptedBytes);

            if ($encryptedLength < 28) {
                throw new Exception('Encrypted data is too short (minimum 28 bytes required)');
            }

            // Extract components: IV (12 bytes) + ciphertext (variable) + tag (16 bytes)
            $iv = substr($encryptedBytes, 0, 12);
            $tag = substr($encryptedBytes, -16);
            $ciphertext = substr($encryptedBytes, 12, $encryptedLength - 28);

            // Decrypt using AES-128-GCM
            $plaintext = openssl_decrypt(
                $ciphertext,
                'aes-128-gcm',
                $keyBytes,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            if ($plaintext === false) {
                throw new Exception('Decryption failed: ' . openssl_error_string());
            }

            return $plaintext;

        } catch (Exception $ex) {
            throw new Exception('Decryption failed: ' . $ex->getMessage());
        }
    }
}