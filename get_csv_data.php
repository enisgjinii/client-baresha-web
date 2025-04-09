<?php
error_reporting(0);
header('Content-Type: application/json');

// Include database connection
include 'connection.php';

// Include necessary classes
require_once 'csv-income.php';

// Check if session is not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug logging
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    // Log incoming request parameters
    error_log("DataTables Request: " . print_r($_POST, true));
    
    // Add SQL query logging
    class DebugCSVDataRepository extends CSVDataRepository {
        public function getDataForServerSideDatatables($user_id, $start, $length, $search, $order_column, $order_dir) {
            error_log("Executing query with params: user_id=$user_id, start=$start, length=$length, search=$search, order_column=$order_column, order_dir=$order_dir");
            $result = parent::getDataForServerSideDatatables($user_id, $start, $length, $search, $order_column, $order_dir);
            error_log("Query result: " . print_r($result, true));
            return $result;
        }
    }
    $csvDataRepo = new DebugCSVDataRepository($conn);
} else {
    $csvDataRepo = new CSVDataRepository($conn);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Handle AJAX request for DataTables
if (isset($_POST['action']) && $_POST['action'] === 'get_csv_data') {
    // DataTables server-side parameters
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $search = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    
    // Get column for ordering
    $order_column = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $order_dir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc';
      try {
        // Get data from repository
        $result = $csvDataRepo->getDataForServerSideDatatables($user_id, $start, $length, $search, $order_column, $order_dir);
        
        if (!is_array($result) || !isset($result['data'])) {
            throw new Exception('Invalid data structure returned from repository');
        }

        // Format data for DataTables
        $data = [];
        foreach ($result['data'] as $row) {
            // Ensure all required fields exist with proper defaults
            $data[] = [
                'month' => $row->month ?? '',
                'year' => $row->year ?? '',
                'store' => $row->store ?? '',
                'artist' => $row->artist ?? '',
                'title' => $row->title ?? '',
                'release' => $row->release ?? '',
                'country' => $row->country ?? '',
                'items' => intval($row->items ?? 0),
                'total_eur' => floatval($row->total_eur ?? 0),
                'total_due_to_pay_eur' => floatval($row->total_due_to_pay_eur ?? 0),
                'date_added' => $row->date_added ?? date('Y-m-d H:i:s')
            ];
        }
        
        $response = [
            'draw' => $draw,
            'recordsTotal' => intval($result['recordsTotal'] ?? 0),
            'recordsFiltered' => intval($result['recordsFiltered'] ?? 0),
            'data' => $data
        ];

        // Ensure we can encode the response
        $json = json_encode($response, JSON_PARTIAL_OUTPUT_ON_ERROR);
        if ($json === false) {
            throw new Exception('Failed to encode JSON response: ' . json_last_error_msg());
        }

        echo $json;
        exit;

    } catch (Exception $e) {
        // Log the error but don't expose it to the client
        error_log('DataTables ERROR: ' . $e->getMessage());
        
        // Return a valid JSON response even in case of error
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'An error occurred while processing your request'
        ]);
        exit;
    }
}

// Handle other AJAX requests
if (isset($_POST['action']) && $_POST['action'] === 'refresh_summary') {
    $csvDataRepo = new CSVDataRepository($conn);
    $summary = $csvDataRepo->getCSVDataSummary($user_id);
    
    echo json_encode([
        'success' => true,
        'summary' => $summary
    ]);
    exit;
}

// If no valid action parameter, return error
echo json_encode([
    'success' => false,
    'error' => 'Invalid action or missing parameters'
]);
exit;
