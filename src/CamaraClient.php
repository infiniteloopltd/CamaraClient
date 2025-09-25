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
            $accessToken = Auth::getOAuthToken($this->settings, 'HLR', 'hlr:lookup');

            if ($accessToken === null) {
                return 'Failed to obtain access token for HLR service';
            }

            return 'HLR Lookup result goes here';

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
