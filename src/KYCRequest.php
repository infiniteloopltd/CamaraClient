<?php

namespace InfiniteLoop\CamaraClient;

class KYCRequest
{
    public string $msisdn = '';
    public string $firstName = '';
    public string $lastName = '';
    public string $dateOfBirth = '';  // YYYY-MM-DD format
    public string $email = '';
    public string $postalCode = '';
    public string $town = '';
    public string $gender = '';  // M, F, or U
    public bool $account_state = false;
    public string $address_line_1 = '';
    public string $address_line_2 = '';
    public string $age = '';
    public string $age_is_greater_than = '';
    public bool $billing_segment = false;
    public string $city_or_province = '';
    public string $country = '';
    public string $flat_number = '';
    public string $house_name = '';
    public string $house_number = '';
    public bool $is_adult = false;
    public bool $is_age_verified = false;
    public bool $is_email_verified = false;
    public bool $is_lost_stolen = false;
    public string $middle_name = '';
    public string $po_box_number = '';
    public string $title = '';

    /**
     * Get house number or house name combined
     */
    private function getHouseNumberOrHouseName(): string
    {
        return $this->house_name . $this->house_number;
    }

    /**
     * Build KYC claims array from the request
     *
     * @return array Associative array of KYC claims
     */
    public function buildKycClaims(): array
    {
        $claims = ['msisdn' => $this->msisdn];

        // String fields that get hashed with normalization
        $stringFields = [
            'given_name_hash' => $this->firstName,
            'family_name_hash' => $this->lastName,
            'postal_code_hash' => $this->postalCode,
            'town_hash' => $this->town,
            'gender_hash' => $this->gender,
            'address_line1_hash' => $this->address_line_1,
            'address_line2_hash' => $this->address_line_2,
            'age_hash' => $this->age,
            'city_or_province_hash' => $this->city_or_province,
            'country_hash' => $this->country,
            'flat_number_hash' => $this->flat_number,
            'house_name_hash' => $this->house_name,
            'house_number_hash' => $this->house_number,
            'houseno_or_housename_hash' => $this->getHouseNumberOrHouseName(),
            'middle_name_hash' => $this->middle_name,
            'po_box_number_hash' => $this->po_box_number,
            'title_hash' => $this->title
        ];

        // Add non-empty string fields
        foreach ($stringFields as $key => $value) {
            if (!empty($value)) {
                $claims[$key] = self::hashAttribute(self::normalizeAttribute($value));
            }
        }

        // Special cases
        if (!empty($this->dateOfBirth)) {
            $claims['birthdate_hash'] = self::hashAttribute($this->dateOfBirth);
        }

        if (!empty($this->email)) {
            $claims['email_hash'] = self::hashAttribute(self::normalizeEmailAttribute($this->email));
        }

        if (!empty($this->age_is_greater_than)) {
            $claims['age_is_greater_than'] = $this->age_is_greater_than;
        }

        // Boolean flags - add as empty string if true
        $booleanFlags = [
            'account_state' => $this->account_state,
            'billing_segment' => $this->billing_segment,
            'is_adult' => $this->is_adult,
            'is_age_verified' => $this->is_age_verified,
            'is_email_verified' => $this->is_email_verified,
            'is_lost_stolen' => $this->is_lost_stolen
        ];

        foreach ($booleanFlags as $key => $value) {
            if ($value === true) {
                $claims[$key] = '';
            }
        }

        return $claims;
    }

    /**
     * Normalize attribute value
     *
     * @param string $value The value to normalize
     * @return string Normalized value
     */
    private static function normalizeAttribute(string $value): string
    {
        if (empty($value)) {
            return '';
        }

        // Step 1: Truncate if needed
        $truncated = mb_substr($value, 0, 20);

        // Step 2: Convert to lowercase
        $normalized = mb_strtolower($truncated);

        // Step 3: Remove punctuation and spaces
        $normalized = preg_replace('/[\p{P}\p{Z}]/u', '', $normalized);

        // Step 4: Handle special characters
        $specialChars = [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ç' => 'c', 'ñ' => 'n'
        ];

        $normalized = str_replace(array_keys($specialChars), array_values($specialChars), $normalized);

        return $normalized;
    }

    /**
     * Normalize email attribute
     *
     * @param string $email The email to normalize
     * @return string Normalized email
     */
    private static function normalizeEmailAttribute(string $email): string
    {
        if (empty($email)) {
            return '';
        }

        // Truncate to 40 characters, convert to lowercase, remove @ and spaces
        $truncated = mb_substr($email, 0, 40);
        $normalized = mb_strtolower($truncated);
        $normalized = str_replace(['@', ' '], '', $normalized);

        return $normalized;
    }

    /**
     * Hash attribute using SHA256
     *
     * @param string $value The value to hash
     * @return string Lowercase hex hash
     */
    private static function hashAttribute(string $value): string
    {
        if (empty($value)) {
            return '';
        }

        return hash('sha256', $value);
    }

    /**
     * Static factory method to create KYC claims from an array of data
     *
     * @param array $data Associative array of KYC data
     * @return array KYC claims array
     */
    public static function createKycClaims(array $data): array
    {
        $request = new self();

        // Map data to properties
        $request->msisdn = $data['msisdn'] ?? '';
        $request->firstName = $data['first_name'] ?? $data['firstName'] ?? '';
        $request->lastName = $data['last_name'] ?? $data['lastName'] ?? '';
        $request->dateOfBirth = $data['date_of_birth'] ?? $data['dateOfBirth'] ?? '';
        $request->email = $data['email'] ?? '';
        $request->postalCode = $data['postal_code'] ?? $data['postalCode'] ?? '';
        $request->town = $data['town'] ?? '';
        $request->gender = $data['gender'] ?? '';
        $request->account_state = $data['account_state'] ?? false;
        $request->address_line_1 = $data['address_line_1'] ?? '';
        $request->address_line_2 = $data['address_line_2'] ?? '';
        $request->age = $data['age'] ?? '';
        $request->age_is_greater_than = $data['age_is_greater_than'] ?? '';
        $request->billing_segment = $data['billing_segment'] ?? false;
        $request->city_or_province = $data['city_or_province'] ?? '';
        $request->country = $data['country'] ?? '';
        $request->flat_number = $data['flat_number'] ?? '';
        $request->house_name = $data['house_name'] ?? '';
        $request->house_number = $data['house_number'] ?? '';
        $request->is_adult = $data['is_adult'] ?? false;
        $request->is_age_verified = $data['is_age_verified'] ?? false;
        $request->is_email_verified = $data['is_email_verified'] ?? false;
        $request->is_lost_stolen = $data['is_lost_stolen'] ?? false;
        $request->middle_name = $data['middle_name'] ?? '';
        $request->po_box_number = $data['po_box_number'] ?? '';
        $request->title = $data['title'] ?? '';

        return $request->buildKycClaims();
    }
}