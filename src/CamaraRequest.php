<?php

namespace InfiniteLoop\CamaraClient;

use InfiniteLoop\CamaraClient\Internal\Security;

class CamaraRequest
{
    /**
     * Make a CAMARA API request with encryption
     *
     * @param string $accessToken OAuth access token
     * @param array $camaraRequest Associative array with correlation_id and claims
     * @param string $siClass Service class identifier
     * @param array $settings Settings array containing SP_NAME, OPCO, etc.
     * @return string|null Decrypted response or null on failure
     */
    public static function request(
        string $accessToken,
        array $camaraRequest,
        string $siClass,
        array $settings
    ): ?string {
        try {
            // Initialize cURL
            $ch = curl_init($settings['service_url']);

            // Serialize request payload to JSON
            $jsonPayload = json_encode($camaraRequest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            echo "🔐 Original payload:\n{$jsonPayload}\n\n";

            // Encrypt the payload
            $encryptedPayload = Security::encrypt($jsonPayload, $settings['encryption_key']);
            echo "🔐 Encrypted payload: " . substr($encryptedPayload, 0, 50) . "...\n\n";

            // Build headers array
            $headers = [
                "Authorization: Bearer {$accessToken}",
                "X-SI-SP: {$settings['sp_name']}",
                "X-SI-OPCO: {$settings['opco']}",
                "X-SI-CLASS: {$siClass}",
                "X-CORRELATION-ID: {$camaraRequest['correlation_id']}",
                "X-SI-ESP: {$settings['esp_id']}",
                "X-KEY-VERSION: 1.0.0",
                "Content-Type: text/plain"
            ];

            if (!empty($settings['use_sandbox'])) {
                $headers[] = "sa_service_id: STUB";
            }

            // Configure cURL options
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $encryptedPayload,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_TIMEOUT => 30
            ]);

            echo "🔍 Request to: {$settings['service_url']}\n";
            echo "📋 Correlation ID: {$camaraRequest['correlation_id']}\n\n";

            // Execute request
            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            curl_close($ch);

            // Check for cURL errors
            if ($responseBody === false) {
                echo "💥 cURL error: {$curlError}\n";
                return null;
            }

            // Check HTTP status
            if ($httpCode >= 200 && $httpCode < 300) {
                // Try to decrypt response if it's encrypted
                try {
                    $decryptedResponse = Security::decrypt($responseBody, $settings['encryption_key']);
                    echo "🔓 Decrypted response:\n{$decryptedResponse}\n";
                    return $decryptedResponse;
                } catch (\Exception $e) {
                    // Response wasn't encrypted or decryption failed
                    echo "📄 Raw response: {$responseBody}\n";
                    return $responseBody;
                }
            }

            echo "❌ Request failed: HTTP {$httpCode}\n";
            echo "📄 Response: {$responseBody}\n";
            return null;

        } catch (\Exception $ex) {
            echo "💥 Request exception: {$ex->getMessage()}\n";
            return null;
        }
    }

    /**
     * Create a CamaraRequest array structure
     *
     * @param array $premiumInfo Associative array for premiuminfo data
     * @param string|null $correlationId Optional correlation ID (generates one if not provided)
     * @return array CamaraRequest structure
     */
    public static function createRequest(array $premiumInfo, ?string $correlationId = null): array
    {
        if ($correlationId === null) {
            // Generate a GUID-like correlation ID
            $correlationId = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }

        return [
            'correlation_id' => $correlationId,
            'claims' => [
                'premiuminfo' => $premiumInfo
            ]
        ];
    }
}