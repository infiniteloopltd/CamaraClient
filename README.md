# CamaraClient

[![Latest Stable Version](https://poser.pugx.org/infiniteloop/camara-client/v)](https://packagist.org/packages/infiniteloop/camara-client)
[![License](https://poser.pugx.org/infiniteloop/camara-client/license)](https://packagist.org/packages/infiniteloop/camara-client)
[![PHP Version Require](https://poser.pugx.org/infiniteloop/camara-client/require/php)](https://packagist.org/packages/infiniteloop/camara-client)

A PHP client library for the CAMARA API with built-in OAuth2 authentication and configurable settings.

## Features

- 🔐 **OAuth2 Authentication** - Automatic token management with configurable credentials
- 📱 **HLR Lookup** - Mobile number validation and operator information
- 👤 **Customer Premium Info** - Retrieve detailed customer information
- 🛠️ **Configurable** - Flexible settings injection for multi-environment support
- 🎯 **Clean API** - Simple, intuitive method calls with automatic authentication
- 📦 **PSR-4 Compliant** - Modern PHP package structure

## Installation

Install via Composer:

```bash
composer require infiniteloop/camara-client
```

## Requirements

- PHP >= 7.4
- ext-curl (for HTTP requests)
- ext-json (for JSON handling)

## Quick Start

```php
<?php

use InfiniteLoop\CamaraClient\CamaraClient;

// Configure your settings
$settings = [
    'token_url' => 'https://your-auth-server.com/oauth/token',
    'service_url' => 'https://api.camara.example.com',
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'opco' => 'IE',
    'sp_name' => 'YourServiceProvider'
];

// Create client instance
$client = new CamaraClient($settings);

// Perform HLR lookup
$result = $client->HLRLookup('353871234567');
print_r($result);

// Get customer premium information
$customerInfo = $client->getCustomerPremiumInfo('353871234567');
print_r($customerInfo);
```

## Configuration

### Required Settings

The `CamaraClient` constructor requires an associative array with the following keys:

| Key | Type | Description |
|-----|------|-------------|
| `token_url` | string | OAuth2 token endpoint URL |
| `service_url` | string | CAMARA API base URL |
| `client_id` | string | OAuth2 client ID |
| `client_secret` | string | OAuth2 client secret |
| `opco` | string | Operating company code (e.g., 'IE', 'UK') |
| `sp_name` | string | Service provider name |

### Example Configuration

```php
$settings = [
    'token_url' => 'https://auth.example.com/oauth/token',
    'service_url' => 'https://api.camara.example.com/v1',
    'client_id' => 'abc123xyz',
    'client_secret' => 'secret-key-here',
    'opco' => 'IE',
    'sp_name' => 'Infinite Loop Development'
];
```

## Available Methods

### HLRLookup

Performs a Home Location Register lookup for a mobile number.

```php
$result = $client->HLRLookup(string $msisdn): array
```

**Parameters:**
- `$msisdn` (string): Mobile number in international format (e.g., '353871234567')

**Returns:**
- Array containing operator information, validity status, and lookup metadata

**Example:**
```php
$result = $client->HLRLookup('353871234567');
/*
[
    'msisdn' => '353871234567',
    'operator' => 'Vodafone IE',
    'valid' => true,
    'ported' => false,
    'roaming' => false,
    'lookup_time' => '2025-01-15 10:30:45',
    'timestamp' => '2025-01-15 10:30:45',
    'request_id' => 'hlr_abc123xyz'
]
*/
```

### getCustomerPremiumInfo

Retrieves detailed customer information for a mobile number.

```php
$result = $client->getCustomerPremiumInfo(string $msisdn): array
```

**Parameters:**
- `$msisdn` (string): Mobile number in international format

**Returns:**
- Array containing customer details, credit information, and account status

**Example:**
```php
$customerInfo = $client->getCustomerPremiumInfo('353871234567');
/*
[
    'msisdn' => '353871234567',
    'operator' => 'Three Ireland',
    'account_type' => 'prepaid',
    'credit_balance' => 15.50,
    'active' => true,
    'last_topup' => '2025-01-10 14:22:33',
    'timestamp' => '2025-01-15 10:30:45',
    'request_id' => 'cust_xyz789abc'
]
*/
```

### getPackageInfo

Returns information about the CamaraClient package.

```php
$info = $client->getPackageInfo(): array
```

**Returns:**
- Array with package name, vendor, version, description, and configured settings

### getVersion

Returns the current package version.

```php
$version = $client->getVersion(): string
```

## Authentication

The package handles OAuth2 authentication automatically. When you call any API method:

1. The client checks if authentication is required
2. Requests an access token using client credentials flow
3. Includes the token in API requests
4. Handles token expiration and errors gracefully

You don't need to manage tokens manually - the package handles everything internally.

## Error Handling

The package returns structured arrays with error information when operations fail:

```php
$result = $client->HLRLookup('invalid-number');

if (!$result['valid']) {
    echo "Lookup failed: " . $result['error'];
}
```

## Advanced Usage

### Multi-Environment Configuration

```php
// Load environment-specific settings
$env = getenv('APP_ENV') ?: 'production';
$configFile = __DIR__ . "/config/{$env}.php";
$settings = require $configFile;

$client = new CamaraClient($settings);
```

### Dependency Injection

```php
class MobileValidationService
{
    private $camaraClient;
    
    public function __construct(array $camaraSettings)
    {
        $this->camaraClient = new CamaraClient($camaraSettings);
    }
    
    public function validateNumber(string $msisdn): bool
    {
        $result = $this->camaraClient->HLRLookup($msisdn);
        return $result['valid'] ?? false;
    }
}
```

### Debug Information

Get comprehensive debug information about authentication and configuration:

```php
$debugInfo = $client->getDebugInfo();
print_r($debugInfo);
```

## Package Structure

```
infiniteloop/camara-client/
├── src/
│   ├── Internal/
│   │   └── Auth.php          # OAuth2 authentication handler
│   └── CamaraClient.php      # Main client class (public API)
├── composer.json
└── README.md
```

## Security Considerations

- ⚠️ **Never commit credentials** - Store `client_id` and `client_secret` in environment variables
- 🔒 **Use HTTPS** - Always use secure URLs for token and service endpoints
- 🔑 **Rotate secrets** - Regularly rotate your OAuth2 credentials
- 📝 **Audit logs** - Monitor API usage and authentication attempts

## Development

### Running Tests

```bash
composer test
```

### Code Style

This package follows PSR-4 autoloading and PSR-12 coding standards.

## Support

For issues, questions, or contributions, please visit:
- **Packagist**: https://packagist.org/packages/infiniteloop/camara-client
- **GitHub**: [Your repository URL]

## License

This package is open-source software licensed under the MIT license.

## Credits

Developed by [Infinite Loop Development Ltd](https://www.infiniteloop.ie)

## Changelog

### Version 2.0.0
- Added configurable settings injection
- Implemented OAuth2 authentication
- Added HLR lookup functionality
- Added customer premium info retrieval
- Improved error handling
- Enhanced documentation

### Version 1.0.0
- Initial release
- Basic package structure
- Hello World example

---

**Made with ❤️ by Infinite Loop Development**
