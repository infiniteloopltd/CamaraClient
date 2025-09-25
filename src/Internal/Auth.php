<?php

namespace InfiniteLoop\CamaraClient\Internal;

/**
 * Internal authentication class - not exposed to clients
 * @internal
 */
class Auth
{
    /**
     * Get OAuth token for specified service class and scope
     *
     * @param array $settings Client settings containing credentials and URLs
     * @param string $siClass Service interface class (e.g., "HLR_Lookup")
     * @param string $scope OAuth scope (e.g., "openid hlr_data:roi")
     * @return string|null Access token or null on failure
     */
    public static function getOAuthToken(array $settings, string $siClass, string $scope): ?string
    {
        try {
            // Validate required settings
            $requiredKeys = ['token_url', 'client_id', 'client_secret', 'sp_name', 'esp_id', 'opco'];
            foreach ($requiredKeys as $key) {
                if (!isset($settings[$key]) || empty($settings[$key])) {
                    error_log("Auth::getOAuthToken - Missing required setting: {$key}");
                    return null;
                }
            }

            // Generate correlation ID (equivalent to Guid.NewGuid():N)
            $correlationId = sprintf(
                '%08x%04x%04x%04x%012x',
                mt_rand(0, 0xffffffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0xffff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffffffffffff)
            );

            // Prepare headers
            $headers = [
                'Content-Type: application/x-www-form-urlencoded',
                'X-SI-SP: ' . $settings['sp_name'],
                'X-SI-OPCO: ' . $settings['opco'],
                'X-SI-CLASS: ' . $siClass,
                'X-CORRELATION-ID: ' . $correlationId,
                'X-SI-ESP: ' . $settings['esp_id']
            ];

            // Prepare form data
            $formData = [
                'grant_type' => 'client_credentials',
                'scope' => $scope,
                'client_id' => $settings['client_id'],
                'client_secret' => $settings['client_secret']
            ];

            // Initialize cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $settings['token_url']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($formData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            // Execute request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Check for cURL errors
            if ($curlError) {
                error_log("Auth::getOAuthToken - cURL error: {$curlError}");
                return null;
            }

            // Check HTTP response
            if ($httpCode >= 200 && $httpCode < 300) {
                $tokenResponse = json_decode($response, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("Auth::getOAuthToken - JSON decode error: " . json_last_error_msg());
                    return null;
                }

                if (!isset($tokenResponse['access_token'])) {
                    error_log("Auth::getOAuthToken - No access_token in response");
                    return null;
                }

                return $tokenResponse['access_token'];
            } else {
                error_log("Auth::getOAuthToken - HTTP error {$httpCode}: {$response}");
                return null;
            }

        } catch (\Exception $e) {
            error_log('Auth::getOAuthToken - Exception: ' . $e->getMessage());
            return null;
        }
    }
}