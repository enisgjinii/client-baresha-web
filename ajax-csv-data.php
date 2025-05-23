<?php
// Include database connection
include 'connection.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Not authenticated',
        'data' => [],
        'recordsTotal' => 0,
        'recordsFiltered' => 0
    ]);
    exit;
}

// Define the CSVData class here instead of including csv-income.php to avoid circular references
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
        // Basic country mapping for flags
        $countryMap = [
            'United States' => 'us', 'USA' => 'us', 'US' => 'us',
            'United Kingdom' => 'gb', 'UK' => 'gb', 'GB' => 'gb',
            'Germany' => 'de', 'DE' => 'de',
            'France' => 'fr', 'FR' => 'fr',
            'Italy' => 'it', 'IT' => 'it',
            'Spain' => 'es', 'ES' => 'es',
            'Japan' => 'jp', 'JP' => 'jp',
            'Canada' => 'ca', 'CA' => 'ca',
            'Australia' => 'au', 'AU' => 'au',
            'Kosovo' => 'xk', 'XK' => 'xk'
        ];
        
        // Normalize country name for lookup
        $normalizedCountry = trim($this->country);
        
        // Try direct match
        if (isset($countryMap[$normalizedCountry])) {
            return $countryMap[$normalizedCountry];
        }
        
        // Try case-insensitive match
        foreach ($countryMap as $name => $code) {
            if (strcasecmp($normalizedCountry, $name) === 0) {
                return $code;
            }
        }
        
        // If it's a 2-letter code already
        if (strlen($normalizedCountry) === 2 && preg_match('/^[a-zA-Z]{2}$/', $normalizedCountry)) {
            return strtolower($normalizedCountry);
        }
        
        // Default fallback
        return 'globe';
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

// Get user ID from POST data or session
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : $_SESSION['user_id'];

// Get DataTables parameters
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';

// Get column for ordering
$orderColumn = 0; // Default to first column
if (isset($_POST['order'][0]['column'])) {
    $orderColumn = intval($_POST['order'][0]['column']);
}
$orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';

// Create repository instance
$csvDataRepo = new CSVDataRepository($conn);

// Get data for DataTables
$result = $csvDataRepo->getDataForServerSideDatatables(
    $user_id,
    $start,
    $length,
    $search,
    $orderColumn,
    $orderDir
);

// Map English month names to Albanian for display
$monthMap = [
    'January' => 'Janar',
    'February' => 'Shkurt',
    'March' => 'Mars',
    'April' => 'Prill',
    'May' => 'Maj',
    'June' => 'Qershor',
    'July' => 'Korrik',
    'August' => 'Gusht',
    'September' => 'Shtator',
    'October' => 'Tetor',
    'November' => 'Nëntor',
    'December' => 'Dhjetor'
];

// Format data for DataTables
$formattedData = [];
foreach ($result['data'] as $item) {
    $displayMonth = isset($monthMap[$item->month]) ? $monthMap[$item->month] : $item->month;
    $countryCode = $item->getCountryCode();
    
    $formattedData[] = [
        $displayMonth, // Muaji (Month)
        $item->year, // Viti (Year)
        '<span class="store-badge ' . strtolower(str_replace(' ', '-', $item->store)) . '">' . $item->store . '</span>', // Dyqani (Store)
        $item->artist && strlen($item->artist) > 30 ? '<span title="' . htmlspecialchars($item->artist) . '" class="cell-truncate">' . htmlspecialchars(substr($item->artist, 0, 30)) . '...</span>' : htmlspecialchars($item->artist), // Artisti (Artist)
        $item->title && strlen($item->title) > 30 ? '<span title="' . htmlspecialchars($item->title) . '" class="cell-truncate">' . htmlspecialchars(substr($item->title, 0, 30)) . '...</span>' : htmlspecialchars($item->title), // Titulli (Title)
        '<span><img src="https://flagcdn.com/w40/' . $countryCode . '.png" srcset="https://flagcdn.com/w80/' . $countryCode . '.png 2x" class="country-flag" alt="' . htmlspecialchars($item->country) . '" onerror="this.onerror=null; this.src=\'img/flags/globe.png\'; this.srcset=\'\';">' . htmlspecialchars($item->country) . '</span>', // Shteti (Country)
        number_format($item->items, 0, '.', ','), // Artikuj (Items)
        '<span class="money-amount">' . number_format($item->total_due_to_pay_eur, 2, '.', ',') . '</span>' // Për t'u Paguar (€) (Amount to Pay)
    ];
}

// Prepare response
$response = [
    'draw' => $draw,
    'recordsTotal' => $result['recordsTotal'],
    'recordsFiltered' => $result['recordsFiltered'],
    'data' => $formattedData
];

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response); 