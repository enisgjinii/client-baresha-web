<?php
include 'connection.php';
include 'header.php';
include 'sidebar.php';

// Create a class for CSV data
class CSVData {
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
      public function __construct($data) {
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
    public function getCountryCode() {
        $countryMap = [
            'United States' => 'us',
            'United Kingdom' => 'gb',
            'Germany' => 'de',
            'France' => 'fr',
            'Italy' => 'it',
            'Spain' => 'es',
            'Japan' => 'jp',
            'Canada' => 'ca',
            'Australia' => 'au',
            'Brazil' => 'br',
            'Mexico' => 'mx',
            'Russia' => 'ru',
            'China' => 'cn',
            'India' => 'in',
            'South Korea' => 'kr',
            'Netherlands' => 'nl',
            'Sweden' => 'se',
            'Norway' => 'no',
            'Denmark' => 'dk',
            'Poland' => 'pl',
            'Belgium' => 'be',
            'Switzerland' => 'ch',
            'Austria' => 'at',
            // Add more mappings as needed
        ];

        $countryCode = isset($countryMap[$this->country]) ? $countryMap[$this->country] : null;

        // Fallback logic for missing or invalid country codes
        if (!$countryCode) {
            $countryCode = strtolower(substr($this->country, 0, 2)); // Try using the first two letters
            if (!preg_match('/^[a-z]{2}$/', $countryCode)) {
                $countryCode = 'globe'; // Default to globe if invalid
            }
        }

        return $countryCode;
    }
}

// Repository class to handle database operations
class CSVDataRepository {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getCSVDataByUserId($user_id, $limit = null, $offset = null) {
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
    
    public function getCSVDataSummary($user_id) {
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
    
    public function getTopPerformingCountries($user_id, $limit = 5) {
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
    
    public function getDataForServerSideDatatables($user_id, $start, $length, $search, $order_column, $order_dir) {
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
    'January', 'February', 'March', 'April', 'May', 'June', 
    'July', 'August', 'September', 'October', 'November', 'December'
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
    }    .store-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 50rem;
        font-weight: 600;
        text-align: center;
        white-space: nowrap;
        font-size: 0.8125rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.2s;
    }
    
    .store-badge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
        box-shadow: 0 0 3px rgba(0,0,0,0.2);
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
        border-bottom: 1px solid rgba(0,0,0,0.08);
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
        box-shadow: 0 0 4px rgba(0,0,0,0.2);
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
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Monthly Income Trends</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary active" data-view="month">Month</button>
                        <button type="button" class="btn btn-outline-secondary" data-view="year">Year</button>
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
    $(document).ready(function() {        // Store badge color mapping with enhanced styling
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
        
        const monthlyChart = new ApexCharts(document.querySelector("#monthlyIncomeChart"), {
            chart: {
                height: 350,
                type: 'area',
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
                width: [3, 3],
                curve: 'smooth'
            },
            series: [{
                name: currentYear + ' (Due to Pay)',
                data: <?= json_encode($currentYearValues) ?>
            }, {
                name: previousYear + ' (Due to Pay)',
                data: <?= json_encode($previousYearValues) ?>
            }],
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
                        return '€' + val.toFixed(0);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return '€' + value.toFixed(2);
                    }
                }
            },
            legend: {
                position: 'top'
            },
            colors: ['#3498db', '#7f8c8d'],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: "vertical",
                    shadeIntensity: 0.25,
                    gradientToColors: ['#2c3e50', '#bdc3c7'],
                    inverseColors: false,
                    opacityFrom: 0.7,
                    opacityTo: 0.3
                }
            }
        });
        
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
        const countryDataValue = <?= json_encode(array_map(function($country) {
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
            series: [
                {
                    data: countryDataValue.map(c => ({
                        x: c.name,
                        y: c.value
                    }))
                }
            ],
            colors: ['#EBF9FF', '#B3E0FF', '#66B7FF', '#0D6EFD', '#0a58ca'],
            tooltip: {
                custom: function({ series, seriesIndex, dataPointIndex, w }) {
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
                        custom: function({ series, seriesIndex, dataPointIndex, w }) {
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
                        custom: function({ series, seriesIndex, dataPointIndex, w }) {
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
        
        // Toggle between monthly and yearly view
        $('.btn-group[data-view]').on('click', 'button', function() {
            const $this = $(this);
            const view = $this.data('view');
            
            // Toggle active state
            $this.addClass('active').siblings().removeClass('active');
            
            // Update chart based on selected view
            if (view === 'year') {
                monthlyChart.updateOptions({
                    chart: {
                        type: 'bar'
                    },
                    stroke: {
                        width: [0, 0],
                        curve: 'smooth'
                    },
                    xaxis: {
                        categories: <?= $yearLabels ?>,
                    },
                    series: [{
                        name: 'Yearly Income',
                        data: <?= $yearValues ?>
                    }]
                });
            } else {
                monthlyChart.updateOptions({
                    chart: {
                        type: 'area'
                    },
                    stroke: {
                        width: [3, 3],
                        curve: 'smooth'
                    },
                    xaxis: {
                        categories: monthNames,
                    },
                    series: [{
                        name: currentYear + ' (Due to Pay)',
                        data: <?= json_encode($currentYearValues) ?>
                    }, {
                        name: previousYear + ' (Due to Pay)',
                        data: <?= json_encode($previousYearValues) ?>
                    }]
                });
            }
        });
        
        // Country Bar Chart
        const countryBarData = <?= json_encode(array_map(function($country) {
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
                custom: function({ series, seriesIndex, dataPointIndex, w }) {
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
            const countryMap = {
                'United States': 'us',
                'United Kingdom': 'gb',
                'Germany': 'de',
                'France': 'fr',
                'Italy': 'it',
                'Spain': 'es',
                'Japan': 'jp',
                'Canada': 'ca',
                'Australia': 'au',
                'Brazil': 'br',
                'Mexico': 'mx',
                'Russia': 'ru',
                'China': 'cn',
                'India': 'in',
                'South Korea': 'kr',
                'Netherlands': 'nl',
                'Sweden': 'se',
                'Norway': 'no',
                'Denmark': 'dk',
                'Poland': 'pl',
                'Belgium': 'be',
                'Switzerland': 'ch',
                'Austria': 'at',
                // Add 2-letter country codes
                'US': 'us',
                'GB': 'gb',
                'DE': 'de',
                'FR': 'fr',
                'IT': 'it',
                'ES': 'es',
                'JP': 'jp',
                'CA': 'ca',
                'AU': 'au',
                'BR': 'br',
                'MX': 'mx',
                'RU': 'ru',
                'CN': 'cn',
                'IN': 'in',
                'KR': 'kr',
                'NL': 'nl',
                'SE': 'se',
                'NO': 'no',
                'DK': 'dk',
                'PL': 'pl',
                'BE': 'be',
                'CH': 'ch',
                'AT': 'at',
                'XK': 'xk'
            };
            
            return countryMap[this.country] || 'globe';
        };
    }
    
    // Initialize DataTables with all the features
    $(document).ready(function() {
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
            data: <?php echo json_encode(array_map(function($item) {
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
            columns: [
                { title: 'Month' },
                { title: 'Year' },
                { title: 'Store' },
                { title: 'Artist' },
                { title: 'Title' },
                { title: 'Country' },
                { title: 'Items' },
                { title: 'Due to Pay (€)' }
            ],
            columnDefs: [
                {
                    // Format month nicely
                    targets: 0,
                    render: function (data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            return data;
                        }
                        return data;
                    }
                },
                {
                    // Format store with badges
                    targets: 2,
                    render: function (data, type, row) {
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
                    render: function (data, type, row) {
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
                    render: function (data, type, row) {
                        if (type === 'display') {
                            const country = new CSVData({ country: data });
                            const countryCode = country.getCountryCode();
                            return `<span>
                                <img src="https://flagcdn.com/24x18/${countryCode}.png" 
                                     class="country-flag" 
                                     alt="${data}"
                                     onerror="this.onerror=null; this.src='img/flags/globe.png';">
                                ${data}
                            </span>`;
                        }
                        return data;
                    }
                },
                {
                    // Format numbers
                    targets: 6,
                    render: function (data, type, row) {
                        if (type === 'display') {
                            return data.toLocaleString();
                        }
                        return data;
                    }
                },
                {
                    // Format money
                    targets: 7,
                    render: function (data, type, row) {
                        if (type === 'display') {
                            return `<span class="money-amount">${parseFloat(data).toFixed(2)}</span>`;
                        }
                        return data;
                    }
                }
            ],
            order: [[0, 'desc'], [1, 'desc']],
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
