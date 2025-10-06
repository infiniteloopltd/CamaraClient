<?php

namespace InfiniteLoop\CamaraClient;

use InfiniteLoop\CamaraClient\Internal\Auth;

class CamaraClient
{
    private array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;

        // Validate required settings
        $requiredKeys = ['token_url', 'service_url', 'client_id', 'client_secret', 'opco', 'sp_name'];
        foreach ($requiredKeys as $key) {
            if (!isset($settings[$key]) || empty($settings[$key])) {
                throw new \InvalidArgumentException("Missing required setting: {$key}");
            }
        }
    }

    /**
     * Perform HLR (Home Location Register) lookup for a given MSISDN
     */
    public function HLRLookup(string $msisdn): string
    {
        try {
            // Get fresh token for this specific API call
            $accessToken = Auth::getOAuthToken($this->settings, 'HLR_Lookup', 'openid hlr_data:roi');

            if ($accessToken === null) {
                return 'Failed to obtain access token for HLR service';
            }


            $premiumInfo = [
                'msisdn' => $msisdn,
                'is_valid_number' => '',
                'get_home_network' => '',
                'get_hashed_IMSI' => '',
                'get_cf_status' => '',
                'is_roaming' => '',
                'get_country' => '',
                'is_in_country' => '272',
                'get_divert_number' => 'no answer',
                'get_location' => '',
                'get_roaming_network' => ''
            ];

            $camaraRequest = CamaraRequest::createRequest($premiumInfo);

            $response = CamaraRequest::request(
                $accessToken,
                $camaraRequest,
                'HLR_Lookup',  // siClass
                $this->settings // Settings array
            );

            return $response;

        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Perform Deactivated MSISDN lookup
     */
    public function Deactivated(string $msisdn): string
    {
        try {

            $accessToken = Auth::getOAuthToken($this->settings, 'Deactivated_MSISDN', 'openid msisdn_deact:roi');

            if ($accessToken === null) {
                return 'Failed to obtain access token';
            }


            $premiumInfo = [
                'msisdn' => $msisdn,
            ];

            $camaraRequest = CamaraRequest::createRequest($premiumInfo);

            $response = CamaraRequest::request(
                $accessToken,
                $camaraRequest,
                'Deactivated_MSISDN',  // siClass
                $this->settings // Settings array
            );

            return $response;

        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Perform Account Takeover Protection (ATP) for MSISDN
     */
    public function ATP(string $msisdn): string
    {
        try {

            $accessToken = Auth::getOAuthToken($this->settings, 'Class1', 'openid atp1:roi');

            if ($accessToken === null) {
                return 'Failed to obtain access token';
            }


            $premiumInfo = [
                'msisdn' => $msisdn,
                'account_state' => '',
                'account_tenure' => '',
                'account_tenure_is_greater_than' => '2020-01-01',
                'billing_segment' => '',
                'contract_start_date' => '',
                'customer_type' => '',
                'device_change' => '',
                'device_type' => '',
                'IMEI' => '',
                'is_lost_stolen' => '',
                'is_unconditional_call_divert_active' => '',
                'lost_stolen_date' => '',
                'ported_in' => '',
                'ported_in_date' => '',
                'sim_change' => ''
            ];

            $camaraRequest = CamaraRequest::createRequest($premiumInfo);

            $response = CamaraRequest::request(
                $accessToken,
                $camaraRequest,
                'Class1',  // siClass
                $this->settings // Settings array
            );

            return $response;

        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Perform Discovery for MSISDN
     */
    public function Discovery(string $msisdn): string
    {
        try {

            $accessToken = Auth::getOAuthToken($this->settings, 'H3G_Discovery', 'openid h3g_discovery:roi');

            if ($accessToken === null) {
                return 'Failed to obtain access token';
            }


            $premiumInfo = [
                'msisdn' => $msisdn,
            ];

            $camaraRequest = CamaraRequest::createRequest($premiumInfo);

            $response = CamaraRequest::request(
                $accessToken,
                $camaraRequest,
                'H3G_Discovery',  // siClass
                $this->settings // Settings array
            );

            return $response;

        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Perform Discovery for MSISDN
     */
    public function KYC(array $kycData): string
    {
        try {

            $accessToken = Auth::getOAuthToken($this->settings, 'Class1', 'openid kyc1_hashed:roi');

            if ($accessToken === null) {
                return 'Failed to obtain access token';
            }


            $premiumInfo = KYCRequest::createKycClaims($kycData);

            $camaraRequest = CamaraRequest::createRequest($premiumInfo);

            $response = CamaraRequest::request(
                $accessToken,
                $camaraRequest,
                'Class1',  // siClass
                $this->settings // Settings array
            );

            return $response;

        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Get package version
     */
    public function getVersion(): string
    {
        return '2.1.0';
    }

    /**
     * Get package information
     */
    public function getPackageInfo(): array
    {
        return [
            'name' => 'CamaraClient',
            'vendor' => 'InfiniteLoop',
            'version' => $this->getVersion(),
            'description' => 'CAMARA API Client with configurable authentication',
            'opco' => $this->settings['opco'],
            'service_provider' => $this->settings['sp_name']
        ];
    }
}
