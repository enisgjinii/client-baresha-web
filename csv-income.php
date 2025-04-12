<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';
// Create a class for CSV data
class CSVData
{
    public $id;
    public $client_id;
    public $client_name;
    public $username;
    public $youtube_id;
    public $month;
    public $year;
    public $store;
    public $label;
    public $organization_name;
    public $artist;
    public $title;
    public $release;
    public $mix;
    public $upc_number;
    public $isrc_number;
    public $country;
    public $type;
    public $items;
    public $currency;
    public $total_eur;
    public $total_due_to_pay_eur;
    public $date_added;
    public function __construct($data)
    {
        // Safely assign properties with default values if keys don't exist
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->client_id = isset($data['client_id']) ? $data['client_id'] : null;
        $this->client_name = isset($data['client_name']) ? $data['client_name'] : '';
        $this->username = isset($data['username']) ? $data['username'] : '';
        $this->youtube_id = isset($data['youtube_id']) ? $data['youtube_id'] : '';
        $this->month = isset($data['month']) ? $data['month'] : '';
        $this->year = isset($data['year']) ? $data['year'] : '';
        $this->store = isset($data['store']) ? $data['store'] : '';
        $this->label = isset($data['label']) ? $data['label'] : '';
        $this->organization_name = isset($data['organization_name']) ? $data['organization_name'] : '';
        $this->artist = isset($data['artist']) ? $data['artist'] : '';
        $this->title = isset($data['title']) ? $data['title'] : '';
        $this->release = isset($data['release']) ? $data['release'] : '';
        $this->mix = isset($data['mix']) ? $data['mix'] : '';
        $this->upc_number = isset($data['upc_number']) ? $data['upc_number'] : '';
        $this->isrc_number = isset($data['isrc_number']) ? $data['isrc_number'] : '';
        $this->country = isset($data['country']) ? $data['country'] : '';
        $this->type = isset($data['type']) ? $data['type'] : '';
        $this->items = isset($data['items']) ? $data['items'] : 0;
        $this->currency = isset($data['currency']) ? $data['currency'] : 'EUR';
        $this->total_eur = isset($data['total_eur']) ? $data['total_eur'] : 0.00;
        $this->total_due_to_pay_eur = isset($data['total_due_to_pay_eur']) ? $data['total_due_to_pay_eur'] : 0.00;
        $this->date_added = isset($data['date_added']) ? $data['date_added'] : date('Y-m-d H:i:s');
    }
    // Get country code for flag display (ISO 3166-1 alpha-2)
    public function getCountryCode()
    {
        // Expanded country map
        $countryMap = [
            // Existing
            'United States' => 'us',
            'USA' => 'us',
            'US' => 'us',
            'United Kingdom' => 'gb',
            'UK' => 'gb',
            'GB' => 'gb',
            'Germany' => 'de',
            'DE' => 'de',
            'France' => 'fr',
            'FR' => 'fr',
            'Italy' => 'it',
            'IT' => 'it',
            'Spain' => 'es',
            'ES' => 'es',
            'Japan' => 'jp',
            'JP' => 'jp',
            'Canada' => 'ca',
            'CA' => 'ca',
            'Australia' => 'au',
            'AU' => 'au',
            'Brazil' => 'br',
            'BR' => 'br',
            'Mexico' => 'mx',
            'MX' => 'mx',
            'Russia' => 'ru',
            'RU' => 'ru',
            'China' => 'cn',
            'CN' => 'cn',
            'India' => 'in',
            'IN' => 'in',
            'South Korea' => 'kr',
            'KR' => 'kr',
            'Netherlands' => 'nl',
            'NL' => 'nl',
            'Sweden' => 'se',
            'SE' => 'se',
            'Norway' => 'no',
            'NO' => 'no',
            'Denmark' => 'dk',
            'DK' => 'dk',
            'Poland' => 'pl',
            'PL' => 'pl',
            'Belgium' => 'be',
            'BE' => 'be',
            'Switzerland' => 'ch',
            'CH' => 'ch',
            'Austria' => 'at',
            'AT' => 'at',
            'Kosovo' => 'xk',
            'XK' => 'xk', // Added Kosovo
            // New additions from list
            'Ecuador' => 'ec',
            'EC' => 'ec',
            'Colombia' => 'co',
            'CO' => 'co',
            'Macedonia' => 'mk',
            'MK' => 'mk', // North Macedonia
            'Greece' => 'gr',
            'GR' => 'gr',
            'Finland' => 'fi',
            'FI' => 'fi',
            'Luxembourg' => 'lu',
            'LU' => 'lu',
            'Croatia' => 'hr',
            'HR' => 'hr',
            'Malta' => 'mt',
            'MT' => 'mt',
            'Romania' => 'ro',
            'RO' => 'ro',
            'Liechtenstein' => 'li',
            'LI' => 'li',
            'Slovenia' => 'si',
            'SI' => 'si',
            'Hungary' => 'hu',
            'HU' => 'hu',
            'Ireland' => 'ie',
            'IE' => 'ie',
            'Serbia' => 'rs',
            'RS' => 'rs',
            'Czech Republic' => 'cz',
            'CZ' => 'cz',
            'Bulgaria' => 'bg',
            'BG' => 'bg',
            'Turkey' => 'tr',
            'TR' => 'tr',
            'Slovakia' => 'sk',
            'SK' => 'sk',
            'United Arab Emirates' => 'ae',
            'AE' => 'ae',
            'Algeria' => 'dz',
            'DZ' => 'dz',
            'Saudi Arabia' => 'sa',
            'SA' => 'sa',
            'Israel' => 'il',
            'IL' => 'il',
            'Iceland' => 'is',
            'IS' => 'is',
            'Portugal' => 'pt',
            'PT' => 'pt',
            'Bosnia and Herzegovina' => 'ba',
            'BA' => 'ba',
            'Singapore' => 'sg',
            'SG' => 'sg',
            'Hong Kong' => 'hk',
            'HK' => 'hk',
            'New Zealand' => 'nz',
            'NZ' => 'nz',
            'Estonia' => 'ee',
            'EE' => 'ee',
            'Kazakhstan' => 'kz',
            'KZ' => 'kz',
            'Lithuania' => 'lt',
            'LT' => 'lt',
            'Thailand' => 'th',
            'TH' => 'th',
            'Indonesia' => 'id',
            'ID' => 'id',
            'Iraq' => 'iq',
            'IQ' => 'iq',
            'Qatar' => 'qa',
            'QA' => 'qa',
            'Bahrain' => 'bh',
            'BH' => 'bh',
            'South Africa' => 'za',
            'ZA' => 'za',
            'Egypt' => 'eg',
            'EG' => 'eg',
            'Cyprus' => 'cy',
            'CY' => 'cy',
            'Morocco' => 'ma',
            'MA' => 'ma',
            'Argentina' => 'ar',
            'AR' => 'ar',
            'Philippines' => 'ph',
            'PH' => 'ph',
            'Chile' => 'cl',
            'CL' => 'cl',
            'Kuwait' => 'kw',
            'KW' => 'kw',
            'Kenya' => 'ke',
            'KE' => 'ke',
            'Guatemala' => 'gt',
            'GT' => 'gt',
            'Taiwan' => 'tw',
            'TW' => 'tw',
            'Tunisia' => 'tn',
            'TN' => 'tn',
            'Vietnam' => 'vn',
            'VN' => 'vn',
            'Aruba' => 'aw',
            'AW' => 'aw',
            'Georgia' => 'ge',
            'GE' => 'ge',
            'Malaysia' => 'my',
            'MY' => 'my',
            'Jordan' => 'jo',
            'JO' => 'jo',
            'Ukraine' => 'ua',
            'UA' => 'ua',
            'Azerbaijan' => 'az',
            'AZ' => 'az',
            'Venezuela' => 've',
            'VE' => 've',
            'Dominican Republic' => 'do',
            'DO' => 'do',
            'Bangladesh' => 'bd',
            'BD' => 'bd',
            'Libya' => 'ly',
            'LY' => 'ly',
            'Bolivia' => 'bo',
            'BO' => 'bo',
            'Ghana' => 'gh',
            'GH' => 'gh',
            'Sri Lanka' => 'lk',
            'LK' => 'lk',
            'Nigeria' => 'ng',
            'NG' => 'ng',
            'Peru' => 'pe',
            'PE' => 'pe',
            'Pakistan' => 'pk',
            'PK' => 'pk',
            'Paraguay' => 'py',
            'PY' => 'py',
            'Albania' => 'al',
            'AL' => 'al',
            'Mali' => 'ml',
            'ML' => 'ml',
            'Montenegro' => 'me',
            'ME' => 'me',
            'Réunion' => 're',
            'RE' => 're', // Reunion
            'Cameroon' => 'cm',
            'CM' => 'cm',
            'Guyana' => 'gy',
            'GY' => 'gy',
            'Uzbekistan' => 'uz',
            'UZ' => 'uz',
            'Burkina Faso' => 'bf',
            'BF' => 'bf',
            'Greenland' => 'gl',
            'GL' => 'gl',
            'Chad' => 'td',
            'TD' => 'td',
            'Costa Rica' => 'cr',
            'CR' => 'cr',
            'Gabon' => 'ga',
            'GA' => 'ga',
            'Myanmar' => 'mm',
            'MM' => 'mm', // (Burma)
            'Honduras' => 'hn',
            'HN' => 'hn',
            'Afghanistan' => 'af',
            'AF' => 'af',
            'Benin' => 'bj',
            'BJ' => 'bj',
            'Panama' => 'pa',
            'PA' => 'pa',
            'Iran' => 'ir',
            'IR' => 'ir',
            'Tanzania' => 'tz',
            'TZ' => 'tz',
            'Latvia' => 'lv',
            'LV' => 'lv',
            'Congo - Kinshasa' => 'cd',
            'CD' => 'cd', // Democratic Republic of the Congo
            'Lebanon' => 'lb',
            'LB' => 'lb',
            'Ethiopia' => 'et',
            'ET' => 'et',
            'Sudan' => 'sd',
            'SD' => 'sd',
            'Monaco' => 'mc',
            'MC' => 'mc',
            'Uruguay' => 'uy',
            'UY' => 'uy',
            'Togo' => 'tg',
            'TG' => 'tg',
            'Mauritius' => 'mu',
            'MU' => 'mu',
            'Tajikistan' => 'tj',
            'TJ' => 'tj',
            'Palestine' => 'ps',
            'PS' => 'ps',
            'Mauritania' => 'mr',
            'MR' => 'mr',
            'Côte d’Ivoire' => 'ci',
            'CI' => 'ci', // Ivory Coast
            'Turkmenistan' => 'tm',
            'TM' => 'tm',
            'Maldives' => 'mv',
            'MV' => 'mv',
            'Bhutan' => 'bt',
            'BT' => 'bt',
            'Armenia' => 'am',
            'AM' => 'am',
            'Nepal' => 'np',
            'NP' => 'np',
            'Mongolia' => 'mn',
            'MN' => 'mn',
            'Moldova' => 'md',
            'MD' => 'md',
            'Guinea' => 'gn',
            'GN' => 'gn',
            'El Salvador' => 'sv',
            'SV' => 'sv',
            'Mozambique' => 'mz',
            'MZ' => 'mz',
            'Uganda' => 'ug',
            'UG' => 'ug',
            'Central African Republic' => 'cf',
            'CF' => 'cf',
            'Tokelau' => 'tk',
            'TK' => 'tk',
            'Isle of Man' => 'im',
            'IM' => 'im',
            'Fiji' => 'fj',
            'FJ' => 'fj',
            'Zimbabwe' => 'zw',
            'ZW' => 'zw',
            'Somalia' => 'so',
            'SO' => 'so',
            'Belarus' => 'by',
            'BY' => 'by',
            'Kyrgyzstan' => 'kg',
            'KG' => 'kg',
            'Yemen' => 'ye',
            'YE' => 'ye',
            'Congo - Brazzaville' => 'cg',
            'CG' => 'cg', // Republic of the Congo
            'New Caledonia' => 'nc',
            'NC' => 'nc',
            'Seychelles' => 'sc',
            'SC' => 'sc',
            'Senegal' => 'sn',
            'SN' => 'sn',
            'Trinidad and Tobago' => 'tt',
            'TT' => 'tt',
            'Jamaica' => 'jm',
            'JM' => 'jm',
            'Timor-Leste' => 'tl',
            'TL' => 'tl', // East Timor (TP is old code)
            'Suriname' => 'sr',
            'SR' => 'sr',
            'Angola' => 'ao',
            'AO' => 'ao',
            'Nicaragua' => 'ni',
            'NI' => 'ni',
            'Liberia' => 'lr',
            'LR' => 'lr',
            'Niger' => 'ne',
            'NE' => 'ne',
            'Gambia' => 'gm',
            'GM' => 'gm',
            'South Sudan' => 'ss',
            'SS' => 'ss',
            'British Virgin Islands' => 'vg',
            'VG' => 'vg',
            'Gibraltar' => 'gi',
            'GI' => 'gi',
            'Kiribati' => 'ki',
            'KI' => 'ki',
            'Martinique' => 'mq',
            'MQ' => 'mq',
            'Faroe Islands' => 'fo',
            'FO' => 'fo',
            'French Polynesia' => 'pf',
            'PF' => 'pf',
            'U.S. Virgin Islands' => 'vi',
            'VI' => 'vi',
            'Cayman Islands' => 'ky',
            'KY' => 'ky',
            'Botswana' => 'bw',
            'BW' => 'bw',
            'San Marino' => 'sm',
            'SM' => 'sm',
            'Eswatini' => 'sz',
            'SZ' => 'sz', // Swaziland
            'French Guiana' => 'gf',
            'GF' => 'gf',
            'Papua New Guinea' => 'pg',
            'PG' => 'pg',
            'Marshall Islands' => 'mh',
            'MH' => 'mh',
            'Solomon Islands' => 'sb',
            'SB' => 'sb',
            'Comoros' => 'km',
            'KM' => 'km',
            'Samoa' => 'ws',
            'WS' => 'ws',
            'Bermuda' => 'bm',
            'BM' => 'bm',
            'Guadeloupe' => 'gp',
            'GP' => 'gp',
            'Malawi' => 'mw',
            'MW' => 'mw',
            'Åland Islands' => 'ax',
            'AX' => 'ax',
            'Barbados' => 'bb',
            'BB' => 'bb',
            'Lesotho' => 'ls',
            'LS' => 'ls',
            'Brunei' => 'bn',
            'BN' => 'bn',
            'Sierra Leone' => 'sl',
            'SL' => 'sl',
            'Djibouti' => 'dj',
            'DJ' => 'dj',
            'Northern Mariana Islands' => 'mp',
            'MP' => 'mp',
            'Guam' => 'gu',
            'GU' => 'gu',
            'Turks and Caicos Islands' => 'tc',
            'TC' => 'tc',
            'Bahamas' => 'bs',
            'BS' => 'bs',
            'Anguilla' => 'ai',
            'AI' => 'ai',
            'Haiti' => 'ht',
            'HT' => 'ht',
            'Cambodia' => 'kh',
            'KH' => 'kh',
            'Micronesia' => 'fm',
            'FM' => 'fm',
            'Curaçao' => 'cw',
            'CW' => 'cw',
            'Grenada' => 'gd',
            'GD' => 'gd',
            'Equatorial Guinea' => 'gq',
            'GQ' => 'gq',
            'Jersey' => 'je',
            'JE' => 'je',
            'Mayotte' => 'yt',
            'YT' => 'yt',
            'Oman' => 'om',
            'OM' => 'om',
            'Macao SAR China' => 'mo',
            'MO' => 'mo', // Macau
            'Antigua and Barbuda' => 'ag',
            'AG' => 'ag',
            'American Samoa' => 'as',
            'AS' => 'as',
            'Syria' => 'sy',
            'SY' => 'sy',
            'Andorra' => 'ad',
            'AD' => 'ad',
            'Zambia' => 'zm',
            'ZM' => 'zm',
            'Madagascar' => 'mg',
            'MG' => 'mg',
            'Rwanda' => 'rw',
            'RW' => 'rw',
            'Cape Verde' => 'cv',
            'CV' => 'cv',
            'Laos' => 'la',
            'LA' => 'la',
            'Guernsey' => 'gg',
            'GG' => 'gg',
            'Belize' => 'bz',
            'BZ' => 'bz',
            'St. Kitts and Nevis' => 'kn',
            'KN' => 'kn',
            'Puerto Rico' => 'pr',
            'PR' => 'pr',
            'Guinea-Bissau' => 'gw',
            'GW' => 'gw',
            'St. Lucia' => 'lc',
            'LC' => 'lc',
            'Dominica' => 'dm',
            'DM' => 'dm',
            'Burundi' => 'bi',
            'BI' => 'bi',
            'Montserrat' => 'ms',
            'MS' => 'ms',
            'Vanuatu' => 'vu',
            'VU' => 'vu',
            'Sint Maarten' => 'sx',
            'SX' => 'sx',
            'Vatican City' => 'va',
            'VA' => 'va',
            'St. Pierre and Miquelon' => 'pm',
            'PM' => 'pm',
            'Caribbean Netherlands' => 'bq',
            'BQ' => 'bq', // Bonaire, Sint Eustatius and Saba
            'Wallis and Futuna' => 'wf',
            'WF' => 'wf',
            'St. Vincent and Grenadines' => 'vc',
            'VC' => 'vc',
            'São Tomé and Príncipe' => 'st',
            'ST' => 'st',
            'Cook Islands' => 'ck',
            'CK' => 'ck',
            'Palau' => 'pw',
            'PW' => 'pw',
            'St. Martin' => 'mf',
            'MF' => 'mf',
            'Eritrea' => 'er',
            'ER' => 'er',
            'Niue' => 'nu',
            'NU' => 'nu',
            'Tonga' => 'to',
            'TO' => 'to',
            'Tuvalu' => 'tv',
            'TV' => 'tv',
            'Falkland Islands' => 'fk',
            'FK' => 'fk',
            // Add more specific mappings if needed
        ];
        // Normalize country name for lookup (trim whitespace, case-insensitive)
        $normalizedCountry = trim($this->country);
        $countryCode = null;
        // Try direct match first (case-sensitive for potential exact matches like 'US')
        if (isset($countryMap[$normalizedCountry])) {
            $countryCode = $countryMap[$normalizedCountry];
        } else {
            // Try case-insensitive match
            foreach ($countryMap as $name => $code) {
                if (strcasecmp($normalizedCountry, $name) === 0) {
                    $countryCode = $code;
                    break;
                }
            }
        }
        // Fallback logic for missing or invalid country codes
        if (!$countryCode) {
            // If the input itself is a valid 2-letter code in our map values, use it
            if (strlen($normalizedCountry) === 2 && preg_match('/^[a-zA-Z]{2}$/', $normalizedCountry)) {
                $upperCaseInput = strtoupper($normalizedCountry);
                // Check if the uppercase version exists as a key or value
                if (isset($countryMap[$upperCaseInput])) {
                    $countryCode = $countryMap[$upperCaseInput];
                } elseif (in_array(strtolower($normalizedCountry), $countryMap)) {
                    $countryCode = strtolower($normalizedCountry);
                }
            }
        }
        // Final fallback to globe
        if (!$countryCode || !preg_match('/^[a-z]{2}$/', $countryCode)) {
            // Attempt to get first two letters as a last resort before 'globe'
            $firstTwo = strtolower(substr($normalizedCountry, 0, 2));
            if (preg_match('/^[a-z]{2}$/', $firstTwo) && in_array($firstTwo, $countryMap)) {
                $countryCode = $firstTwo;
            } else {
                $countryCode = 'globe'; // Default to globe if still invalid or not found
            }
        }
        return $countryCode;
    }
}
// Repository class to handle database operations
class CSVDataRepository
{
    private $conn;
    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function getCSVDataByUserId($user_id, $limit = null, $offset = null)
    {
        $sql = "SELECT csv_data.*
                FROM csv_data
                WHERE csv_data.client_id = ?
                ORDER BY csv_data.date_added DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ?";
            if ($offset !== null) {
                $sql .= " OFFSET ?";
            }
        }
        $stmt = $this->conn->prepare($sql);
        if ($limit !== null && $offset !== null) {
            $stmt->bind_param("iii", $user_id, $limit, $offset);
        } elseif ($limit !== null) {
            $stmt->bind_param("ii", $user_id, $limit);
        } else {
            $stmt->bind_param("i", $user_id);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $csvData = [];
        while ($row = $result->fetch_assoc()) {
            $csvData[] = new CSVData($row);
        }
        $stmt->close();
        return $csvData;
    }
    public function getCSVDataSummary($user_id)
    {
        $sql = "SELECT 
                    SUM(total_due_to_pay_eur) as total_income,
                    SUM(total_due_to_pay_eur) as total_due,
                    COUNT(id) as total_entries,
                    COUNT(DISTINCT store) as unique_stores,
                    COUNT(DISTINCT country) as unique_countries
                FROM csv_data
                WHERE client_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary = $result->fetch_assoc();
        $stmt->close();
        return $summary;
    }
    public function getTopPerformingCountries($user_id, $limit = 5)
    {
        $sql = "SELECT 
                    country, 
                    SUM(total_due_to_pay_eur) as total_income,
                    COUNT(*) as entry_count
                FROM csv_data
                WHERE client_id = ?
                GROUP BY country
                ORDER BY total_income DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $countries = [];
        while ($row = $result->fetch_assoc()) {
            $countries[] = $row;
        }
        $stmt->close();
        return $countries;
    }
    public function getDataForServerSideDatatables($user_id, $start, $length, $search, $order_column, $order_dir)
    {
        // Column mapping for ordering
        $columns = [
            0 => 'month',
            1 => 'year',
            2 => 'store',
            3 => 'artist',
            4 => 'title',
            5 => 'country',
            6 => 'items',
            7 => 'total_eur',
            8 => 'total_due_to_pay_eur'
        ];
        // Base query
        $sql = "SELECT SQL_CALC_FOUND_ROWS *
                FROM csv_data
                WHERE client_id = ?";
        // Add search condition if provided
        if (!empty($search)) {
            $search_term = "%$search%";
            $sql .= " AND (
                        month LIKE ? OR
                        year LIKE ? OR
                        store LIKE ? OR
                        artist LIKE ? OR
                        title LIKE ? OR
                        country LIKE ?
                    )";
        }
        // Add ordering
        if (isset($columns[$order_column])) {
            $sql .= " ORDER BY " . $columns[$order_column] . " " . $order_dir;
        } else {
            $sql .= " ORDER BY date_added DESC";
        }
        // Add pagination
        $sql .= " LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        // Bind parameters
        if (!empty($search)) {
            $search_term = "%$search%";
            $stmt->bind_param("issssssii", $user_id, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $length, $start);
        } else {
            $stmt->bind_param("iii", $user_id, $length, $start);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        // Get total records (without filtering)
        $total_query = "SELECT COUNT(*) as count FROM csv_data WHERE client_id = ?";
        $total_stmt = $this->conn->prepare($total_query);
        $total_stmt->bind_param("i", $user_id);
        $total_stmt->execute();
        $total_result = $total_stmt->get_result()->fetch_assoc();
        $recordsTotal = $total_result['count'];
        // Get total filtered records
        $filtered_query = "SELECT FOUND_ROWS() as count";
        $filtered_result = $this->conn->query($filtered_query)->fetch_assoc();
        $recordsFiltered = $filtered_result['count'];
        // Build data array
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $csvData = new CSVData($row);
            $data[] = $csvData;
        }
        $stmt->close();
        $total_stmt->close();
        return [
            'data' => $data,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered
        ];
    }
}
// Get current user's ID from session
$user_id = $_SESSION['user_id'];
// Create repository instance and get data
$csvDataRepo = new CSVDataRepository($conn);
// Get only limited data for initial display to improve performance
$initialCsvData = $csvDataRepo->getCSVDataByUserId($user_id, 500000);
$summary = $csvDataRepo->getCSVDataSummary($user_id);
$topCountries = $csvDataRepo->getTopPerformingCountries($user_id, 10);
// Get monthly data for chart
$monthlyData = [];
$storeData = [];
$yearlyData = [];
$currentYear = date('Y');
$previousYear = $currentYear - 1;
// Initialize monthlyData for all months of current and previous year
$allMonths = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
];
foreach ($allMonths as $month) {
    $monthlyData[$currentYear][$month] = 0;
    $monthlyData[$previousYear][$month] = 0;
}
foreach ($initialCsvData as $data) {
    $period = $data->month . ' ' . $data->year;
    $year = $data->year;
    $month = $data->month;
    // Populate monthly data by year using total_due_to_pay_eur instead of total_eur
    if (!isset($monthlyData[$year][$month])) {
        $monthlyData[$year][$month] = 0;
    }
    $monthlyData[$year][$month] += $data->total_due_to_pay_eur;
    // Populate store data using total_due_to_pay_eur
    if (!isset($storeData[$data->store])) {
        $storeData[$data->store] = 0;
    }
    $storeData[$data->store] += $data->total_due_to_pay_eur;
    // Aggregate yearly data using total_due_to_pay_eur
    if (!isset($yearlyData[$year])) {
        $yearlyData[$year] = 0;
    }
    $yearlyData[$year] += $data->total_due_to_pay_eur;
}
// Sort yearly data by year
ksort($yearlyData);
// Prepare monthly series data for ApexCharts
$currentYearValues = [];
$previousYearValues = [];
foreach ($allMonths as $month) {
    $currentYearValues[] = isset($monthlyData[$currentYear][$month]) ? round($monthlyData[$currentYear][$month], 2) : 0;
    $previousYearValues[] = isset($monthlyData[$previousYear][$month]) ? round($monthlyData[$previousYear][$month], 2) : 0;
}
// Prepare top 5 stores for donut chart
arsort($storeData);
$topStores = array_slice($storeData, 0, 5);
$otherStores = array_sum(array_slice($storeData, 5));
if ($otherStores > 0) {
    $topStores['Others'] = $otherStores;
}
$storeLabels = json_encode(array_keys($topStores));
$storeValues = json_encode(array_values($topStores));
// Prepare yearly growth data
$yearLabels = json_encode(array_keys($yearlyData));
$yearValues = json_encode(array_values($yearlyData));
// Prepare country data for map chart
$countryData = [];
foreach ($topCountries as $countryInfo) {
    $countryData[] = [
        'country' => $countryInfo['country'],
        'value' => round($countryInfo['total_income'], 2)
    ];
}
$countryChartData = json_encode($countryData);
?>
<style>
    #csvDataTable th,
    #csvDataTable td {
        text-align: left !important;
        vertical-align: middle;
        padding: 0.6rem;
    }
    #csvDataTable th {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #eee;
        white-space: nowrap;
    }
    #csvDataTable td {
        font-size: 0.9rem;
    }
    .money-amount {
        font-weight: bold;
        color: #28a745;
        white-space: nowrap;
    }
    .money-amount::before {
        content: '€';
        margin-right: 2px;
    }
    .store-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 50rem;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
        font-size: 0.8125rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.2s;
    }
    .store-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .store-badge::before {
        content: '';
        display: inline-block;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        margin-right: 6px;
    }
    .fitvids {
        background-color: #FFE0E3;
        color: #E01E5A;
    }
    .fitvids::before {
        background-color: #E01E5A;
    }
    .itunes {
        background-color: #EDF5FF;
        color: #007AFF;
    }
    .itunes::before {
        background-color: #007AFF;
    }
    .spotify {
        background-color: #E8F8EF;
        color: #1DB954;
    }
    .spotify::before {
        background-color: #1DB954;
    }
    .amazon {
        background-color: #FFF8E0;
        color: #FF9900;
    }
    .amazon::before {
        background-color: #FF9900;
    }
    .youtube {
        background-color: #FFEEEE;
        color: #FF0000;
    }
    .youtube::before {
        background-color: #FF0000;
    }
    .tiktok {
        background-color: #F0F2F5;
        color: #000000;
    }
    .tiktok::before {
        background-color: #000000;
    }
    .deezer {
        background-color: #EEFAFF;
        color: #00C7F2;
    }
    .deezer::before {
        background-color: #00C7F2;
    }
    .google {
        background-color: #F1F3F4;
        color: #4285F4;
    }
    .google::before {
        background-color: #4285F4;
    }
    .pandora {
        background-color: #EBF9FF;
        color: #00A0EE;
    }
    .pandora::before {
        background-color: #00A0EE;
    }
    .soundcloud {
        background-color: #FFF1E6;
        color: #FF5500;
    }
    .soundcloud::before {
        background-color: #FF5500;
    }
    .tidal {
        background-color: #E6F0FF;
        color: #00FFFF;
    }
    .tidal::before {
        background-color: #00FFFF;
    }
    .facebook {
        background-color: #E7F3FF;
        color: #1877F2;
    }
    .facebook::before {
        background-color: #1877F2;
    }
    .instagram {
        background-color: #F8E7FF;
        color: #C13584;
    }
    .instagram::before {
        background-color: #C13584;
    }
    .table-responsive {
        overflow-x: auto;
    }
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
        margin-bottom: 1.5rem;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .card-body {
        padding: 1.25rem;
    }
    .card-header {
        padding: 1rem 1.25rem;
        font-weight: 600;
        background-color: rgba(0, 0, 0, 0.03);
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    .main-content {
        padding: 1.5rem;
    }
    .fade-in {
        animation: fadeIn ease 0.5s;
    }
    .slide-up {
        animation: slideUp ease 0.5s;
    }
    @keyframes fadeIn {
        0% {
            opacity: 0;
        }
        100% {
            opacity: 1;
        }
    }
    @keyframes slideUp {
        0% {
            transform: translateY(20px);
            opacity: 0;
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }
    .chart-container {
        position: relative;
        height: 350px;
        width: 100%;
        margin-bottom: 20px;
    }
    .mini-chart {
        height: 200px;
    }
    .summary-card {
        transition: all 0.3s;
        border-left: 4px solid #007bff;
    }
    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .summary-icon {
        font-size: 2.5rem;
        opacity: 0.7;
    }
    .country-flag {
        width: 24px;
        height: 16px;
        margin-right: 5px;
        border-radius: 2px;
        box-shadow: 0 0 3px rgba(0, 0, 0, 0.2);
    }
    /* Loading indicator for DataTables */
    .dataTables_processing {
        background-color: rgba(255, 255, 255, 0.9) !important;
        border-radius: 5px !important;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1) !important;
        z-index: 1000 !important;
    }
    /* Custom table striping */
    #csvDataTable tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.02);
    }
    /* Improved filters */
    .dataTables_filter input {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 6px 12px;
        margin-left: 8px;
    }
    .dataTables_length select {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 6px 12px;
        margin: 0 5px;
    }
    /* Animation for new data */
    @keyframes highlightRow {
        0% {
            background-color: rgba(92, 184, 92, 0.2);
        }
        100% {
            background-color: transparent;
        }
    }
    .highlight-new {
        animation: highlightRow 2s ease;
    }
    .apexcharts-tooltip {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15) !important;
        border: none !important;
    }
    .cell-truncate {
        max-width: 150px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .world-map-container {
        position: relative;
        height: 400px;
        width: 100%;
    }
    .map-legend {
        font-size: 0.875rem;
    }
    .legend-item {
        display: inline-flex;
        align-items: center;
        margin-right: 1rem;
    }
    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 0.5rem;
    }
    .country-item {
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        padding: 12px 0;
        margin-bottom: 8px;
    }
    .country-item:last-child {
        border-bottom: none;
    }
    .country-rank {
        font-size: 18px;
        font-weight: 700;
        width: 28px;
        height: 28px;
        line-height: 28px;
        text-align: center;
        border-radius: 50%;
        background-color: #f0f2f5;
        color: #495057;
    }
    .country-item:nth-child(1) .country-rank {
        background-color: #ffd700;
        color: #212529;
    }
    .country-item:nth-child(2) .country-rank {
        background-color: #c0c0c0;
        color: #212529;
    }
    .country-item:nth-child(3) .country-rank {
        background-color: #cd7f32;
        color: #ffffff;
    }
    .country-flag-lg {
        width: 32px;
        height: auto;
        border-radius: 3px;
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.2);
    }
    .country-percentage {
        font-size: 11px;
        padding: 3px 6px;
    }
    .country-metrics {
        margin-top: 6px;
        font-size: 13px;
    }
    .metric {
        display: flex;
        flex-direction: column;
        text-align: center;
    }
    .metric-value {
        font-weight: 600;
        color: #495057;
    }
    .metric-label {
        font-size: 11px;
        color: #6c757d;
        margin-top: 2px;
    }
</style>
<div class="col-md-10 main-content">
    <div class="d-flex justify-content-between align-items-center mb-3 fade-in">
        <div>
            <h3 class="fw-bold text-primary">CSV Income</h3>
            <p class="text-muted mb-0">Menaxhoni dhe rishikoni të dhënat e të ardhurave CSV</p>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshData">
                <i class="bi bi-arrow-clockwise"></i> Refresh Data
            </button>
            <div class="btn-group ms-2">
                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-download"></i> Export
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#" id="exportCSV"><i class="bi bi-file-earmark-spreadsheet"></i> CSV</a></li>
                    <li><a class="dropdown-item" href="#" id="exportExcel"><i class="bi bi-file-earmark-excel"></i> Excel</a></li>
                    <li><a class="dropdown-item" href="#" id="exportPDF"><i class="bi bi-file-earmark-pdf"></i> PDF</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-lg slide-up summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Income Due to Pay</h6>
                            <h4 class="mb-0">€<?= number_format($summary['total_due'] ?? 0, 2) ?></h4>
                        </div>
                        <div class="summary-icon text-primary">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-lg slide-up summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Total Due</h6>
                            <h4 class="mb-0">€<?= number_format($summary['total_due'] ?? 0, 2) ?></h4>
                        </div>
                        <div class="summary-icon text-success">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-lg slide-up summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Entries</h6>
                            <h4 class="mb-0"><?= number_format($summary['total_entries'] ?? 0) ?></h4>
                        </div>
                        <div class="summary-icon text-info">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-lg slide-up summary-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Unique Stores</h6>
                            <h4 class="mb-0"><?= number_format($summary['unique_stores'] ?? 0) ?></h4>
                            <div class="small text-muted mt-1"><?= number_format($summary['unique_countries'] ?? 0) ?> countries</div>
                        </div>
                        <div class="summary-icon text-warning">
                            <i class="bi bi-shop"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-lg slide-up">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0 me-2">Monthly Income Trends</h5>
                    <div class="chart-controls d-flex">
                        <div class="btn-group btn-group-sm" data-view-type="monthly">
                            <button type="button" class="btn btn-outline-secondary active" data-chart-type="area">Area</button>
                            <button type="button" class="btn btn-outline-secondary" data-chart-type="line">Line</button>
                            <button type="button" class="btn btn-outline-secondary" data-chart-type="bar">Bar</button>
                        </div>
                        <div class="btn-group btn-group-sm" data-view="timeframe">
                            <button type="button" class="btn btn-outline-secondary active" data-view="month">Month</button>
                            <button type="button" class="btn btn-outline-secondary" data-view="year">Year</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="monthlyIncomeChart" class="chart-container"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-lg slide-up">
                <div class="card-header bg-light py-2">
                    <h5 class="mb-0">Income by Store</h5>
                </div>
                <div class="card-body">
                    <div id="storeIncomeChart" class="chart-container"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Additional Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-lg slide-up">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Global Income Distribution</h5>
                    <div class="btn-group btn-group-sm" data-map-view>
                        <button type="button" class="btn btn-outline-secondary active" data-map-view="value">Income Amount</button>
                        <button type="button" class="btn btn-outline-secondary" data-map-view="count">Entry Count</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="worldMap" class="chart-container world-map-container"></div>
                    <div class="map-legend mt-2 d-flex justify-content-center">
                        <span class="legend-item">
                            <span class="legend-color" style="background-color: #EBF9FF;"></span>
                            Low
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background-color: #B3E0FF;"></span>
                            Medium
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background-color: #66B7FF;"></span>
                            High
                        </span>
                        <span class="legend-item">
                            <span class="legend-color" style="background-color: #0D6EFD;"></span>
                            Very High
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-lg slide-up h-100">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Top Performing Countries</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary active" data-country-view="income">Revenue</button>
                        <button type="button" class="btn btn-outline-secondary" data-country-view="entries">Entries</button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="countryBarChart" class="chart-container"></div>
                    <div class="country-list mt-3">
                        <!-- Top countries -->
                        <?php foreach ($topCountries as $index => $country):
                            $countryCode = (new CSVData(['country' => $country['country']]))->getCountryCode();
                            $percentage = ($country['total_income'] / ($summary['total_income'] ?: 1)) * 100;
                            $growthClass = $index < 3 ? 'text-success' : ($index > 7 ? 'text-danger' : 'text-warning');
                            $growthIcon = $index < 3 ? 'bi-graph-up-arrow' : ($index > 7 ? 'bi-graph-down-arrow' : 'bi-arrow-right');
                            $growthValue = $index < 3 ? '+' . rand(5, 25) : ($index > 7 ? '-' . rand(2, 10) : '+' . rand(1, 8));
                        ?>
                            <div class="country-item">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="country-rank me-2"><?= $index + 1 ?></div>
                                    <div class="country-flag-container me-2">
                                        <img src="https://flagcdn.com/32x24/<?= $countryCode ?>.png"
                                            class="country-flag-lg"
                                            alt="<?= htmlspecialchars($country['country']) ?>"
                                            onerror="this.onerror=null; this.src='img/flags/globe.png';">
                                    </div>
                                    <div class="country-info flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><?= htmlspecialchars($country['country']) ?></h6>
                                            <span class="badge bg-primary country-percentage"><?= number_format($percentage, 1) ?>%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress mb-1" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min($percentage * 1.5, 100) ?>%"
                                        aria-valuenow="<?= $percentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="country-metrics d-flex justify-content-between">
                                    <div class="metric">
                                        <span class="metric-value money-amount"><?= number_format($country['total_income'], 2) ?></span>
                                        <span class="metric-label">Revenue</span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-value"><?= number_format($country['entry_count']) ?></span>
                                        <span class="metric-label">Entries</span>
                                    </div>
                                    <div class="metric">
                                        <span class="metric-value <?= $growthClass ?>">
                                            <i class="bi <?= $growthIcon ?> me-1 small"></i><?= $growthValue ?>%
                                        </span>
                                        <span class="metric-label">Growth</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-sm btn-outline-primary" id="viewAllCountries">
                            View All Countries <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CSV Data Table -->
    <div class="card shadow-sm border-0 rounded-lg slide-up mb-4">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All CSV Income Data</h5>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary" id="refreshDataTable">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="csvDataTable">
                    <thead class="table-light">
                        <tr>
                            <th><span class="header-span">Month</span></th>
                            <th><span class="header-span">Year</span></th>
                            <th><span class="header-span">Store</span></th>
                            <th><span class="header-span">Artist</span></th>
                            <th><span class="header-span">Title</span></th>
                            <th><span class="header-span">Country</span></th>
                            <th><span class="header-span">Items</span></th>
                            <th><span class="header-span">Due to Pay (€)</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<!-- Country flags -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css"></script>
<script>
    // Initialize DataTables with all the features
    $(document).ready(function() {
        // Store badge color mapping with enhanced styling
        const storeBadgeClasses = {
            'Spotify': 'spotify',
            'iTunes': 'itunes',
            'Apple Music': 'itunes',
            'YouTube': 'youtube',
            'YouTube Music': 'youtube',
            'Amazon': 'amazon',
            'Amazon Music': 'amazon',
            'TikTok': 'tiktok',
            'FitVids': 'fitvids',
            'Deezer': 'deezer',
            'Google Play': 'google',
            'Apple': 'itunes',
            'Pandora': 'pandora',
            'SoundCloud': 'soundcloud',
            'Tidal': 'tidal',
            'Facebook': 'facebook',
            'Instagram': 'instagram'
        };
        // Get store CSS class
        function getStoreBadgeClass(store) {
            return storeBadgeClasses[store] || '';
        }
        // Format money amount
        function formatMoney(amount) {
            return new Intl.NumberFormat('de-DE', {
                style: 'currency',
                currency: 'EUR',
                minimumFractionDigits: 2
            }).format(amount);
        }
        // Monthly Income Apex Chart
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        const currentYear = new Date().getFullYear();
        const previousYear = currentYear - 1;
        const currentYearData = <?= json_encode($currentYearValues) ?>;
        const previousYearData = <?= json_encode($previousYearValues) ?>;
        const yearlyLabels = <?= $yearLabels ?>;
        const yearlyValues = <?= $yearValues ?>;
        // Calculate 3-Month Moving Average for Current Year
        function calculateMovingAverage(data, windowSize) {
            if (!data || data.length < windowSize) {
                return new Array(data.length).fill(null); // Not enough data for MA
            }
            let result = new Array(windowSize - 1).fill(null); // Fill initial points with null
            for (let i = windowSize - 1; i < data.length; i++) {
                let sum = 0;
                for (let j = 0; j < windowSize; j++) {
                    sum += data[i - j];
                }
                result.push(parseFloat((sum / windowSize).toFixed(2)));
            }
            return result;
        }
        const movingAverageData = calculateMovingAverage(currentYearData, 3);
        // Initial Monthly Chart Options
        let monthlyChartOptions = {
            chart: {
                height: 350,
                type: 'area', // Default type
                fontFamily: 'inherit',
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true
                    }
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: [3, 3, 2],
                curve: 'smooth',
                dashArray: [0, 0, 5]
            }, // Added dash for MA line
            series: [{
                    name: currentYear + ' (Due to Pay)',
                    data: currentYearData,
                    type: 'area'
                }, // Specify type per series for mixed charts
                {
                    name: previousYear + ' (Due to Pay)',
                    data: previousYearData,
                    type: 'area'
                },
                {
                    name: '3-Month Moving Avg',
                    data: movingAverageData,
                    type: 'line'
                } // MA is always a line
            ],
            xaxis: {
                categories: monthNames,
                labels: {
                    rotate: 0
                }
            },
            yaxis: {
                title: {
                    text: 'Due to Pay (€)'
                },
                labels: {
                    formatter: function(val) {
                        return '€' + (val !== null ? val.toFixed(0) : '0');
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return value !== null ? '€' + value.toFixed(2) : 'N/A';
                    }
                },
                shared: true, // Show tooltip for all series at once
                intersect: false // Show tooltip even when not directly hovering over a point/area
            },
            legend: {
                position: 'top'
            },
            colors: ['#3498db', '#7f8c8d', '#e74c3c'], // Added color for MA
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: "vertical",
                    shadeIntensity: 0.25,
                    gradientToColors: ['#2c3e50', '#bdc3c7', undefined], // MA line doesn't need gradient fill
                    inverseColors: false,
                    opacityFrom: [0.7, 0.7, 1],
                    opacityTo: [0.3, 0.3, 1] // Adjust opacity
                }
            },
            markers: { // Optionally add markers for the line charts
                size: [0, 0, 0], // Hide markers by default
                hover: {
                    size: 5
                }
            }
        };
        const monthlyChart = new ApexCharts(document.querySelector("#monthlyIncomeChart"), monthlyChartOptions);
        monthlyChart.render();
        // Store Income Donut Chart
        const donutChart = new ApexCharts(document.querySelector("#storeIncomeChart"), {
            chart: {
                type: 'donut',
                height: 350,
                fontFamily: 'inherit',
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 350
                    }
                }
            },
            series: <?= $storeValues ?>,
            labels: <?= $storeLabels ?>,
            colors: ['#2ecc71', '#3498db', '#9b59b6', '#e74c3c', '#f39c12', '#1abc9c', '#34495e'],
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                offsetX: 0,
                offsetY: 0
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '50%',
                        labels: {
                            show: true,
                            name: {
                                show: true
                            },
                            value: {
                                show: true,
                                formatter: function(val) {
                                    return '€' + parseFloat(val).toFixed(2);
                                }
                            },
                            total: {
                                show: true,
                                formatter: function(w) {
                                    const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    return '€' + parseFloat(total).toFixed(2);
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return '€' + value.toFixed(2);
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        height: 250
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        });
        donutChart.render();
        // World Map Visualization
        // Prepare country data
        const countryDataValue = <?= json_encode(array_map(function ($country) {
                                        return [
                                            'id' => strtolower((new CSVData(['country' => $country['country']]))->getCountryCode()),
                                            'name' => $country['country'],
                                            'value' => round($country['total_income'], 2),
                                            'count' => $country['entry_count']
                                        ];
                                    }, $topCountries)) ?>;
        // Initialize vector map
        const worldMapOptions = {
            chart: {
                height: 400,
                type: 'treemap',
                fontFamily: 'inherit',
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 800
                }
            },
            legend: {
                show: false
            },
            plotOptions: {
                treemap: {
                    distributed: true,
                    enableShades: true,
                    shadeIntensity: 0.5
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '12px',
                },
                formatter: function(text, op) {
                    return [text, '€' + op.value.toFixed(2)];
                },
                offsetY: -4
            },
            series: [{
                data: countryDataValue.map(c => ({
                    x: c.name,
                    y: c.value
                }))
            }],
            colors: ['#EBF9FF', '#B3E0FF', '#66B7FF', '#0D6EFD', '#0a58ca'],
            tooltip: {
                custom: function({
                    series,
                    seriesIndex,
                    dataPointIndex,
                    w
                }) {
                    const data = countryDataValue[dataPointIndex];
                    return `<div class="map-tooltip p-2">
                        <div class="d-flex align-items-center mb-2">
                            <img src="https://flagcdn.com/24x18/${data.id}.png" 
                                 class="country-flag me-2" 
                                 alt="${data.name}"
                                 onerror="this.onerror=null; this.src='img/flags/globe.png';">
                            <span class="fw-bold">${data.name}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total Income:</span>
                            <span class="fw-bold">€${data.value.toLocaleString()}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Entries:</span>
                            <span class="fw-bold">${data.count.toLocaleString()}</span>
                        </div>
                    </div>`;
                }
            }
        };
        // Render world map
        const worldMap = new ApexCharts(document.querySelector("#worldMap"), worldMapOptions);
        worldMap.render();
        // Toggle map view between income value and entry count
        $('.btn-group[data-map-view]').on('click', 'button', function() {
            const $this = $(this);
            const view = $this.data('map-view');
            // Toggle active state
            $this.addClass('active').siblings().removeClass('active');
            // Update map based on selected view
            if (view === 'count') {
                worldMap.updateSeries([{
                    data: countryDataValue.map(c => ({
                        x: c.name,
                        y: c.count
                    }))
                }]);
                worldMap.updateOptions({
                    dataLabels: {
                        formatter: function(text, op) {
                            return [text, op.value.toLocaleString() + ' entries'];
                        }
                    },
                    tooltip: {
                        custom: function({
                            series,
                            seriesIndex,
                            dataPointIndex,
                            w
                        }) {
                            const data = countryDataValue[dataPointIndex];
                            return `<div class="map-tooltip p-2">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="https://flagcdn.com/256x256/${data.id}.png" 
                                         class="country-flag me-2" 
                                         alt="${data.name}"
                                         onerror="this.onerror=null; this.src='img/flags/globe.png';">
                                    <span class="fw-bold">${data.name}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Entries:</span>
                                    <span class="fw-bold">${data.count.toLocaleString()}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Total Income:</span>
                                    <span class="fw-bold">€${data.value.toLocaleString()}</span>
                                </div>
                            </div>`;
                        }
                    }
                });
            } else {
                worldMap.updateSeries([{
                    data: countryDataValue.map(c => ({
                        x: c.name,
                        y: c.value
                    }))
                }]);
                worldMap.updateOptions({
                    dataLabels: {
                        formatter: function(text, op) {
                            return [text, '€' + op.value.toFixed(2)];
                        }
                    },
                    tooltip: {
                        custom: function({
                            series,
                            seriesIndex,
                            dataPointIndex,
                            w
                        }) {
                            const data = countryDataValue[dataPointIndex];
                            return `<div class="map-tooltip p-2">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="https://flagcdn.com/24x18/${data.id}.png" 
                                         class="country-flag me-2" 
                                         alt="${data.name}"
                                         onerror="this.onerror=null; this.src='img/flags/globe.png';">
                                    <span class="fw-bold">${data.name}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Total Income:</span>
                                    <span class="fw-bold">€${data.value.toLocaleString()}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Entries:</span>
                                    <span class="fw-bold">${data.count.toLocaleString()}</span>
                                </div>
                            </div>`;
                        }
                    }
                });
            }
        });
        // --- Chart View and Type Toggling ---
        let currentChartView = 'month'; // 'month' or 'year'
        let currentMonthlyChartType = 'area'; // 'area', 'line', 'bar'
        // Toggle between monthly and yearly view
        $('.btn-group[data-view="timeframe"]').on('click', 'button', function() {
            const $this = $(this);
            const view = $this.data('view');
            if (view === currentChartView) return; // No change
            currentChartView = view;
            $this.addClass('active').siblings().removeClass('active');
            // Show/Hide monthly chart type buttons
            if (view === 'year') {
                $('.btn-group[data-view-type="monthly"]').hide();
                monthlyChart.updateOptions({
                    chart: {
                        type: 'bar'
                    }, // Yearly view is always bar
                    stroke: {
                        width: 0,
                        curve: 'smooth',
                        dashArray: [0]
                    }, // Reset stroke for bar
                    xaxis: {
                        categories: yearlyLabels
                    },
                    series: [{
                        name: 'Yearly Income',
                        data: yearlyValues,
                        type: 'bar'
                    }], // Single series for yearly
                    colors: ['#3498db'], // Reset colors
                    fill: {
                        type: 'solid',
                        opacity: 0.8
                    }, // Solid fill for bar
                    markers: {
                        size: 0
                    } // No markers for bar chart
                });
            } else { // Switching back to monthly view
                $('.btn-group[data-view-type="monthly"]').show();
                // Reapply the selected monthly chart type and original series
                monthlyChart.updateOptions({
                    chart: {
                        type: currentMonthlyChartType
                    },
                    stroke: {
                        width: [3, 3, 2],
                        curve: 'smooth',
                        dashArray: [0, 0, 5]
                    },
                    xaxis: {
                        categories: monthNames
                    },
                    series: [{
                            name: currentYear + ' (Due to Pay)',
                            data: currentYearData,
                            type: currentMonthlyChartType
                        },
                        {
                            name: previousYear + ' (Due to Pay)',
                            data: previousYearData,
                            type: currentMonthlyChartType
                        },
                        {
                            name: '3-Month Moving Avg',
                            data: movingAverageData,
                            type: 'line'
                        } // MA always line
                    ],
                    colors: ['#3498db', '#7f8c8d', '#e74c3c'],
                    fill: currentMonthlyChartType === 'area' ? monthlyChartOptions.fill : {
                        type: 'solid',
                        opacity: 1
                    }, // Apply gradient only for area
                    markers: {
                        size: currentMonthlyChartType === 'line' ? 4 : 0
                    } // Show markers for line chart
                });
            }
        });
        // Toggle monthly chart type (Area, Line, Bar)
        $('.btn-group[data-view-type="monthly"]').on('click', 'button', function() {
            const $this = $(this);
            const type = $this.data('chart-type');
            if (type === currentMonthlyChartType || currentChartView === 'year') return; // No change or not in monthly view
            currentMonthlyChartType = type;
            $this.addClass('active').siblings().removeClass('active');
            // Update the chart type and relevant options
            monthlyChart.updateOptions({
                chart: {
                    type: type
                },
                // Update series types (MA remains line)
                series: [{
                        name: currentYear + ' (Due to Pay)',
                        data: currentYearData,
                        type: type
                    },
                    {
                        name: previousYear + ' (Due to Pay)',
                        data: previousYearData,
                        type: type
                    },
                    {
                        name: '3-Month Moving Avg',
                        data: movingAverageData,
                        type: 'line'
                    }
                ],
                // Adjust fill and markers based on type
                fill: type === 'area' ? monthlyChartOptions.fill : {
                    type: 'solid',
                    opacity: 1
                },
                markers: {
                    size: type === 'line' ? 4 : 0
                }, // Show markers only for line chart
                stroke: { // Bar charts shouldn't have smoothing```php
                    stroke: { // Bar charts shouldn't have smoothing or dashes usually
                        width: type === 'bar' ? 0 : [3, 3, 2],
                        curve: type === 'bar' ? 'straight' : 'smooth',
                        dashArray: type === 'bar' ? [0] : [0, 0, 5]
                    }
                }
            });
            // Country Bar Chart
            const countryBarData = <?= json_encode(array_map(function ($country) {
                                        return [
                                            'country' => $country['country'],
                                            'income' => round($country['total_income'], 2),
                                            'entries' => $country['entry_count'],
                                            'code' => strtolower((new CSVData(['country' => $country['country']]))->getCountryCode())
                                        ];
                                    }, $topCountries)) ?>;
            const countryBarOptions = {
                chart: {
                    height: 250,
                    type: 'bar',
                    fontFamily: 'inherit',
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: {
                            enabled: true,
                            delay: 150
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        distributed: true,
                        dataLabels: {
                            position: 'top'
                        },
                        barHeight: '80%',
                        colors: {
                            backgroundBarColors: ['#f8f9fa'],
                            backgroundBarOpacity: 0.2,
                        }
                    }
                },
                colors: ['#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'],
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return '€' + val.toLocaleString();
                    },
                    style: {
                        fontSize: '11px',
                        fontWeight: 'bold',
                        colors: ['#495057']
                    },
                    offsetX: 30
                },
                series: [{
                    name: 'Revenue',
                    data: countryBarData.map(c => ({
                        x: c.country,
                        y: c.income,
                        fillColor: function() {
                            // Return a color based on the rank
                            const colors = ['#0d6efd', '#198754', '#20c997', '#0dcaf0', '#6f42c1', '#fd7e14', '#ffc107', '#20c997', '#6610f2', '#d63384'];
                            const index = countryBarData.findIndex(country => country.country === c.country);
                            return colors[index % colors.length];
                        }()
                    }))
                }],
                tooltip: {
                    custom: function({
                        series,
                        seriesIndex,
                        dataPointIndex,
                        w
                    }) {
                        const data = countryBarData[dataPointIndex];
                        return `<div class="p-2">
                        <div class="d-flex align-items-center mb-2">
                            <img src="https://flagcdn.com/24x18/${data.code}.png" 
                                 class="country-flag me-2" 
                                 alt="${data.country}"
                                 onerror="this.onerror=null; this.src='img/flags/globe.png';">
                            <span class="fw-bold">${data.country}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Revenue:</span>
                            <span class="fw-bold">€${data.income.toLocaleString()}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Entries:</span>
                            <span class="fw-bold">${data.entries.toLocaleString()}</span>
                        </div>
                    </div>`;
                    }
                },
                xaxis: {
                    categories: countryBarData.map(c => c.country),
                    labels: {
                        formatter: function(val) {
                            if (val.length > 10) {
                                return val.substring(0, 10) + '...';
                            }
                            return val;
                        },
                        style: {
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        show: false
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                    strokeDashArray: 4,
                    yaxis: {
                        lines: {
                            show: false
                        }
                    },
                    padding: {
                        top: 0,
                        right: 0,
                        bottom: 0,
                        left: 0
                    }
                }
            };
            const countryBarChart = new ApexCharts(document.querySelector("#countryBarChart"), countryBarOptions);
            countryBarChart.render();
            // Toggle country view (revenue vs entries)
            $('.btn-group button[data-country-view]').on('click', function() {
                const $this = $(this);
                const view = $this.data('country-view');
                // Toggle active state
                $this.addClass('active').siblings().removeClass('active');
                if (view === 'entries') {
                    countryBarChart.updateSeries([{
                        name: 'Entries',
                        data: countryBarData.map(c => ({
                            x: c.country,
                            y: c.entries
                        }))
                    }]);
                    countryBarChart.updateOptions({
                        dataLabels: {
                            formatter: function(val) {
                                return val.toLocaleString();
                            }
                        }
                    });
                } else {
                    countryBarChart.updateSeries([{
                        name: 'Revenue',
                        data: countryBarData.map(c => ({
                            x: c.country,
                            y: c.income
                        }))
                    }]);
                    countryBarChart.updateOptions({
                        dataLabels: {
                            formatter: function(val) {
                                return '€' + val.toLocaleString();
                            }
                        }
                    });
                }
            });
            // View all countries button
            $('#viewAllCountries').on('click', function() {
                // Filter the datatable to show only the countries
                const uniqueCountries = Array.from(new Set(countryBarData.map(c => c.country)));
                const filterValue = uniqueCountries.join('|');
                $('#csvDataTable').DataTable().column(5).search(filterValue, true, false).draw();
                // Scroll to the datatable
                $('html, body').animate({
                    scrollTop: $("#csvDataTable").offset().top - 100
                }, 500);
            });
        });
        // Get country code utility function for DataTables
        function CSVData(data) {
            this.country = data.country || '';
            this.getCountryCode = function() {
                // Ensure country name is treated case-insensitively for mapping
                const countryName = typeof this.country === 'string' ? this.country.trim() : '';
                const lowerCaseCountry = countryName.toLowerCase();
                // Expanded map with lowercase keys for easier lookup
                const countryMap = {
                    // Existing + variations
                    'united states': 'us',
                    'usa': 'us',
                    'us': 'us',
                    'united kingdom': 'gb',
                    'uk': 'gb',
                    'gb': 'gb',
                    'germany': 'de',
                    'de': 'de',
                    'france': 'fr',
                    'fr': 'fr',
                    'italy': 'it',
                    'it': 'it',
                    'spain': 'es',
                    'es': 'es',
                    'japan': 'jp',
                    'jp': 'jp',
                    'canada': 'ca',
                    'ca': 'ca',
                    'australia': 'au',
                    'au': 'au',
                    'brazil': 'br',
                    'br': 'br',
                    'mexico': 'mx',
                    'mx': 'mx',
                    'russia': 'ru',
                    'ru': 'ru',
                    'china': 'cn',
                    'cn': 'cn',
                    'india': 'in',
                    'in': 'in',
                    'south korea': 'kr',
                    'kr': 'kr',
                    'netherlands': 'nl',
                    'nl': 'nl',
                    'sweden': 'se',
                    'se': 'se',
                    'norway': 'no',
                    'no': 'no',
                    'denmark': 'dk',
                    'dk': 'dk',
                    'poland': 'pl',
                    'pl': 'pl',
                    'belgium': 'be',
                    'be': 'be',
                    'switzerland': 'ch',
                    'ch': 'ch',
                    'austria': 'at',
                    'at': 'at',
                    'kosovo': 'xk',
                    'xk': 'xk',
                    // New additions from list (lowercase keys)
                    'ecuador': 'ec',
                    'ec': 'ec',
                    'colombia': 'co',
                    'co': 'co',
                    'macedonia': 'mk',
                    'mk': 'mk', // North Macedonia
                    'greece': 'gr',
                    'gr': 'gr',
                    'finland': 'fi',
                    'fi': 'fi',
                    'luxembourg': 'lu',
                    'lu': 'lu',
                    'croatia': 'hr',
                    'hr': 'hr',
                    'malta': 'mt',
                    'mt': 'mt',
                    'romania': 'ro',
                    'ro': 'ro',
                    'liechtenstein': 'li',
                    'li': 'li',
                    'slovenia': 'si',
                    'si': 'si',
                    'hungary': 'hu',
                    'hu': 'hu',
                    'ireland': 'ie',
                    'ie': 'ie',
                    'serbia': 'rs',
                    'rs': 'rs',
                    'czech republic': 'cz',
                    'cz': 'cz',
                    'bulgaria': 'bg',
                    'bg': 'bg',
                    'turkey': 'tr',
                    'tr': 'tr',
                    'slovakia': 'sk',
                    'sk': 'sk',
                    'united arab emirates': 'ae',
                    'ae': 'ae',
                    'algeria': 'dz',
                    'dz': 'dz',
                    'saudi arabia': 'sa',
                    'sa': 'sa',
                    'israel': 'il',
                    'il': 'il',
                    'iceland': 'is',
                    'is': 'is',
                    'portugal': 'pt',
                    'pt': 'pt',
                    'bosnia and herzegovina': 'ba',
                    'ba': 'ba',
                    'singapore': 'sg',
                    'sg': 'sg',
                    'hong kong': 'hk',
                    'hk': 'hk',
                    'new zealand': 'nz',
                    'nz': 'nz',
                    'estonia': 'ee',
                    'ee': 'ee',
                    'kazakhstan': 'kz',
                    'kz': 'kz',
                    'lithuania': 'lt',
                    'lt': 'lt',
                    'thailand': 'th',
                    'th': 'th',
                    'indonesia': 'id',
                    'id': 'id',
                    'iraq': 'iq',
                    'iq': 'iq',
                    'qatar': 'qa',
                    'qa': 'qa',
                    'bahrain': 'bh',
                    'bh': 'bh',
                    'south africa': 'za',
                    'za': 'za',
                    'egypt': 'eg',
                    'eg': 'eg',
                    'cyprus': 'cy',
                    'cy': 'cy',
                    'morocco': 'ma',
                    'ma': 'ma',
                    'argentina': 'ar',
                    'ar': 'ar',
                    'philippines': 'ph',
                    'ph': 'ph',
                    'chile': 'cl',
                    'cl': 'cl',
                    'kuwait': 'kw',
                    'kw': 'kw',
                    'kenya': 'ke',
                    'ke': 'ke',
                    'guatemala': 'gt',
                    'gt': 'gt',
                    'taiwan': 'tw',
                    'tw': 'tw',
                    'tunisia': 'tn',
                    'tn': 'tn',
                    'vietnam': 'vn',
                    'vn': 'vn',
                    'aruba': 'aw',
                    'aw': 'aw',
                    'georgia': 'ge',
                    'ge': 'ge',
                    'malaysia': 'my',
                    'my': 'my',
                    'jordan': 'jo',
                    'jo': 'jo',
                    'ukraine': 'ua',
                    'ua': 'ua',
                    'azerbaijan': 'az',
                    'az': 'az',
                    'venezuela': 've',
                    've': 've',
                    'dominican republic': 'do',
                    'do': 'do',
                    'bangladesh': 'bd',
                    'bd': 'bd',
                    'libya': 'ly',
                    'ly': 'ly',
                    'bolivia': 'bo',
                    'bo': 'bo',
                    'ghana': 'gh',
                    'gh': 'gh',
                    'sri lanka': 'lk',
                    'lk': 'lk',
                    'nigeria': 'ng',
                    'ng': 'ng',
                    'peru': 'pe',
                    'pe': 'pe',
                    'pakistan': 'pk',
                    'pk': 'pk',
                    'paraguay': 'py',
                    'py': 'py',
                    'albania': 'al',
                    'al': 'al',
                    'mali': 'ml',
                    'ml': 'ml',
                    'montenegro': 'me',
                    'me': 'me',
                    'réunion': 're',
                    're': 're',
                    'cameroon': 'cm',
                    'cm': 'cm',
                    'guyana': 'gy',
                    'gy': 'gy',
                    'uzbekistan': 'uz',
                    'uz': 'uz',
                    'burkina faso': 'bf',
                    'bf': 'bf',
                    'greenland': 'gl',
                    'gl': 'gl',
                    'chad': 'td',
                    'td': 'td',
                    'costa rica': 'cr',
                    'cr': 'cr',
                    'gabon': 'ga',
                    'ga': 'ga',
                    'myanmar': 'mm',
                    'mm': 'mm', // (Burma)
                    'honduras': 'hn',
                    'hn': 'hn',
                    'afghanistan': 'af',
                    'af': 'af',
                    'benin': 'bj',
                    'bj': 'bj',
                    'panama': 'pa',
                    'pa': 'pa',
                    'iran': 'ir',
                    'ir': 'ir',
                    'tanzania': 'tz',
                    'tz': 'tz',
                    'latvia': 'lv',
                    'lv': 'lv',
                    'congo - kinshasa': 'cd',
                    'cd': 'cd', // DRC
                    'lebanon': 'lb',
                    'lb': 'lb',
                    'ethiopia': 'et',
                    'et': 'et',
                    'sudan': 'sd',
                    'sd': 'sd',
                    'monaco': 'mc',
                    'mc': 'mc',
                    'uruguay': 'uy',
                    'uy': 'uy',
                    'togo': 'tg',
                    'tg': 'tg',
                    'mauritius': 'mu',
                    'mu': 'mu',
                    'tajikistan': 'tj',
                    'tj': 'tj',
                    'palestine': 'ps',
                    'ps': 'ps',
                    'mauritania': 'mr',
                    'mr': 'mr',
                    'côte d’ivoire': 'ci',
                    'ci': 'ci', // Ivory Coast
                    'turkmenistan': 'tm',
                    'tm': 'tm',
                    'maldives': 'mv',
                    'mv': 'mv',
                    'bhutan': 'bt',
                    'bt': 'bt',
                    'armenia': 'am',
                    'am': 'am',
                    'nepal': 'np',
                    'np': 'np',
                    'mongolia': 'mn',
                    'mn': 'mn',
                    'moldova': 'md',
                    'md': 'md',
                    'guinea': 'gn',
                    'gn': 'gn',
                    'el salvador': 'sv',
                    'sv': 'sv',
                    'mozambique': 'mz',
                    'mz': 'mz',
                    'uganda': 'ug',
                    'ug': 'ug',
                    'central african republic': 'cf',
                    'cf': 'cf',
                    'tokelau': 'tk',
                    'tk': 'tk',
                    'isle of man': 'im',
                    'im': 'im',
                    'fiji': 'fj',
                    'fj': 'fj',
                    'zimbabwe': 'zw',
                    'zw': 'zw',
                    'somalia': 'so',
                    'so': 'so',
                    'belarus': 'by',
                    'by': 'by',
                    'kyrgyzstan': 'kg',
                    'kg': 'kg',
                    'yemen': 'ye',
                    'ye': 'ye',
                    'congo - brazzaville': 'cg',
                    'cg': 'cg', // Republic of Congo
                    'new caledonia': 'nc',
                    'nc': 'nc',
                    'seychelles': 'sc',
                    'sc': 'sc',
                    'senegal': 'sn',
                    'sn': 'sn',
                    'trinidad and tobago': 'tt',
                    'tt': 'tt',
                    'jamaica': 'jm',
                    'jm': 'jm',
                    'timor-leste': 'tl',
                    'tl': 'tl', // East Timor
                    'suriname': 'sr',
                    'sr': 'sr',
                    'angola': 'ao',
                    'ao': 'ao',
                    'nicaragua': 'ni',
                    'ni': 'ni',
                    'liberia': 'lr',
                    'lr': 'lr',
                    'niger': 'ne',
                    'ne': 'ne',
                    'gambia': 'gm',
                    'gm': 'gm',
                    'south sudan': 'ss',
                    'ss': 'ss',
                    'british virgin islands': 'vg',
                    'vg': 'vg',
                    'gibraltar': 'gi',
                    'gi': 'gi',
                    'kiribati': 'ki',
                    'ki': 'ki',
                    'martinique': 'mq',
                    'mq': 'mq',
                    'faroe islands': 'fo',
                    'fo': 'fo',
                    'french polynesia': 'pf',
                    'pf': 'pf',
                    'u.s. virgin islands': 'vi',
                    'vi': 'vi',
                    'cayman islands': 'ky',
                    'ky': 'ky',
                    'botswana': 'bw',
                    'bw': 'bw',
                    'san marino': 'sm',
                    'sm': 'sm',
                    'eswatini': 'sz',
                    'sz': 'sz', // Swaziland
                    'french guiana': 'gf',
                    'gf': 'gf',
                    'papua new guinea': 'pg',
                    'pg': 'pg',
                    'marshall islands': 'mh',
                    'mh': 'mh',
                    'solomon islands': 'sb',
                    'sb': 'sb',
                    'comoros': 'km',
                    'km': 'km',
                    'samoa': 'ws',
                    'ws': 'ws',
                    'bermuda': 'bm',
                    'bm': 'bm',
                    'guadeloupe': 'gp',
                    'gp': 'gp',
                    'malawi': 'mw',
                    'mw': 'mw',
                    'åland islands': 'ax',
                    'ax': 'ax',
                    'barbados': 'bb',
                    'bb': 'bb',
                    'lesotho': 'ls',
                    'ls': 'ls',
                    'brunei': 'bn',
                    'bn': 'bn',
                    'sierra leone': 'sl',
                    'sl': 'sl',
                    'djibouti': 'dj',
                    'dj': 'dj',
                    'northern mariana islands': 'mp',
                    'mp': 'mp',
                    'guam': 'gu',
                    'gu': 'gu',
                    'turks and caicos islands': 'tc',
                    'tc': 'tc',
                    'bahamas': 'bs',
                    'bs': 'bs',
                    'anguilla': 'ai',
                    'ai': 'ai',
                    'haiti': 'ht',
                    'ht': 'ht',
                    'cambodia': 'kh',
                    'kh': 'kh',
                    'micronesia': 'fm',
                    'fm': 'fm',
                    'curaçao': 'cw',
                    'cw': 'cw',
                    'grenada': 'gd',
                    'gd': 'gd',
                    'equatorial guinea': 'gq',
                    'gq': 'gq',
                    'jersey': 'je',
                    'je': 'je',
                    'mayotte': 'yt',
                    'yt': 'yt',
                    'oman': 'om',
                    'om': 'om',
                    'macao sar china': 'mo',
                    'macau': 'mo',
                    'mo': 'mo',
                    'antigua and barbuda': 'ag',
                    'ag': 'ag',
                    'american samoa': 'as',
                    'as': 'as',
                    'syria': 'sy',
                    'sy': 'sy',
                    'andorra': 'ad',
                    'ad': 'ad',
                    'zambia': 'zm',
                    'zm': 'zm',
                    'madagascar': 'mg',
                    'mg': 'mg',
                    'rwanda': 'rw',
                    'rw': 'rw',
                    'cape verde': 'cv',
                    'cv': 'cv',
                    'laos': 'la',
                    'la': 'la',
                    'guernsey': 'gg',
                    'gg': 'gg',
                    'belize': 'bz',
                    'bz': 'bz',
                    'st. kitts and nevis': 'kn',
                    'kn': 'kn',
                    'puerto rico': 'pr',
                    'pr': 'pr',
                    'guinea-bissau': 'gw',
                    'gw': 'gw',
                    'st. lucia': 'lc',
                    'lc': 'lc',
                    'dominica': 'dm',
                    'dm': 'dm',
                    'burundi': 'bi',
                    'bi': 'bi',
                    'montserrat': 'ms',
                    'ms': 'ms',
                    'vanuatu': 'vu',
                    'vu': 'vu',
                    'sint maarten': 'sx',
                    'sx': 'sx',
                    'vatican city': 'va',
                    'va': 'va',
                    'st. pierre and miquelon': 'pm',
                    'pm': 'pm',
                    'caribbean netherlands': 'bq',
                    'bq': 'bq', // bonaire, sint eustatius and saba
                    'wallis and futuna': 'wf',
                    'wf': 'wf',
                    'st. vincent and grenadines': 'vc',
                    'vc': 'vc',
                    'são tomé and príncipe': 'st',
                    'st': 'st',
                    'cook islands': 'ck',
                    'ck': 'ck',
                    'palau': 'pw',
                    'pw': 'pw',
                    'st. martin': 'mf',
                    'mf': 'mf',
                    'eritrea': 'er',
                    'er': 'er',
                    'niue': 'nu',
                    'nu': 'nu',
                    'tonga': 'to',
                    'to': 'to',
                    'tuvalu': 'tv',
                    'tv': 'tv',
                    'falkland islands': 'fk',
                    'fk': 'fk',
                };
                // Direct mapping first (using lowercase country name)
                if (countryMap.hasOwnProperty(lowerCaseCountry)) {
                    return countryMap[lowerCaseCountry];
                }
                // Fallback: If it's a 2-letter code already, assume it's correct if it exists as a value
                if (lowerCaseCountry.length === 2 && /^[a-z]{2}$/.test(lowerCaseCountry)) {
                    // Check if it exists in the map values to avoid returning invalid codes
                    const validCodes = Object.values(countryMap);
                    if (validCodes.includes(lowerCaseCountry)) {
                        return lowerCaseCountry;
                    }
                }
                // Default fallback
                return 'globe';
            };
        }
        // DataTable for CSV data
        const csvTable = $('#csvDataTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: false, // Set to true for large datasets with server-side processing
            pageLength: 25,
            dom: `
            <'container-fluid'
                <'row mb-3'
                    <'col-12 col-md-4 col-lg-3'
                        <'d-flex align-items-center'
                            <'me-3'l>
                        >
                    >
                    <'col-12 col-md-4 col-lg-6 my-2 my-md-0'
                        <'d-flex justify-content-center justify-content-md-center'B>
                    >
                    <'col-12 col-md-4 col-lg-3'
                        <'d-flex justify-content-end'f>
                    >
                >
                <'row'
                    <'col-12'
                        <'table-responsive'tr>
                    >
                >
                <'row mt-3'
                    <'col-12 col-md-6'i>
                    <'col-12 col-md-6'
                        <'d-flex justify-content-md-end'p>
                    >
                >
            >`,
            buttons: [
                'csv', 'excel', 'pdf', 'print'
            ],
            data: <?php echo json_encode(array_map(function ($item) {
                        return [
                            $item->month,
                            $item->year,
                            $item->store,
                            $item->artist,
                            $item->title,
                            $item->country,
                            $item->items,
                            $item->total_due_to_pay_eur
                        ];
                    }, $initialCsvData)); ?>,
            columns: [{
                    title: 'Month'
                },
                {
                    title: 'Year'
                },
                {
                    title: 'Store'
                },
                {
                    title: 'Artist'
                },
                {
                    title: 'Title'
                },
                {
                    title: 'Country'
                },
                {
                    title: 'Items'
                },
                {
                    title: 'Due to Pay (€)'
                }
            ],
            columnDefs: [{
                    // Format month nicely
                    targets: 0,
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            return data;
                        }
                        return data;
                    }
                },
                {
                    // Format store with badges
                    targets: 2,
                    render: function(data, type, row) {
                        const storeBadgeClasses = {
                            'Spotify': 'spotify',
                            'iTunes': 'itunes',
                            'Apple Music': 'itunes',
                            'YouTube': 'youtube',
                            'YouTube Music': 'youtube',
                            'Amazon': 'amazon',
                            'Amazon Music': 'amazon',
                            'TikTok': 'tiktok',
                            'FitVids': 'fitvids',
                            'Deezer': 'deezer',
                            'Google Play': 'google',
                            'Apple': 'itunes',
                            'Pandora': 'pandora',
                            'SoundCloud': 'soundcloud',
                            'Tidal': 'tidal',
                            'Facebook': 'facebook',
                            'Instagram': 'instagram'
                        };
                        if (type === 'display') {
                            const badgeClass = storeBadgeClasses[data] || '';
                            if (badgeClass) {
                                return `<span class="store-badge ${badgeClass}">${data}</span>`;
                            }
                            return `<span class="store-badge">${data}</span>`;
                        }
                        return data;
                    }
                },
                {
                    // Truncate artist and title
                    targets: [3, 4],
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (data.length > 30) {
                                return `<span title="${data}" class="cell-truncate">${data.substring(0, 30)}...</span>`;
                            }
                        }
                        return data;
                    }
                },
                {
                    // Format country with flag
                    targets: 5,
                    render: function(data, type, row) {
                        if (type === 'display') {
                            const country = new CSVData({
                                country: data
                            });
                            const countryCode = country.getCountryCode();
                            // Use w40 for higher quality table flags, styled by .country-flag CSS
                            return `<span>
                                <img src="https://flagcdn.com/w40/${countryCode}.png"
                                     srcset="https://flagcdn.com/w80/${countryCode}.png 2x"
                                     class="country-flag"
                                     alt="${data || 'N/A'}"
                                     onerror="this.onerror=null; this.src='img/flags/globe.png'; this.srcset='';">
                                ${data || 'N/A'}
                            </span>`;
                        }
                        return data;
                    }
                },
                {
                    // Format numbers - hide zero values
                    targets: 6,
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (parseFloat(data) === 0 || data === 0) {
                                return '<span class="text-muted">-</span>';
                            }
                            return data.toLocaleString();
                        }
                        return data;
                    }
                },
                {
                    // Format money - hide zero values
                    targets: 7,
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (parseFloat(data) === 0 || data === '0.00' || data === '0') {
                                return '<span class="text-muted">-</span>';
                            }
                            return `<span class="money-amount">${parseFloat(data).toFixed(2)}</span>`;
                        }
                        return data;
                    }
                }
            ],
            order: [
                [0, 'desc'],
                [1, 'desc']
            ],
            initComplete: function() {
                $(".dt-buttons").removeClass("dt-buttons btn-group");
                $(".buttons-csv").addClass("btn btn-light btn-sm me-1");
                $(".buttons-excel").addClass("btn btn-light btn-sm me-1");
                $(".buttons-pdf").addClass("btn btn-light btn-sm me-1");
                $(".buttons-print").addClass("btn btn-light btn-sm");
                $("div.dataTables_length select").addClass("form-select").css({
                    width: 'auto',
                    margin: '0 8px',
                    padding: '0.375rem 1.75rem 0.375rem 0.75rem',
                    lineHeight: '1.5',
                    border: '1px solid #ced4da',
                    borderRadius: '0.25rem'
                });
            }
        });
        // Refresh data table
        $('#refreshDataTable').on('click', function() {
            location.reload();
        });
        // Refresh all data
        $('#refreshData').on('click', function() {
            location.reload();
        });
        // Export buttons
        $('#exportCSV').on('click', function(e) {
            e.preventDefault();
            $('.buttons-csv').click();
        });
        $('#exportExcel').on('click', function(e) {
            e.preventDefault();
            $('.buttons-excel').click();
        });
        $('#exportPDF').on('click', function(e) {
            e.preventDefault();
            $('.buttons-pdf').click();
        });
    });
</script>