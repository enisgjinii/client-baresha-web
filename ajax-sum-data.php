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
        'success' => false,
        'error' => 'Not authenticated'
    ]);
    exit;
}

// Get user ID from POST data or session
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : $_SESSION['user_id'];
$search = isset($_POST['search']) ? $_POST['search'] : '';

try {
    // Prepare SQL to calculate sum with search filter
    $sql = "SELECT SUM(total_due_to_pay_eur) as total_sum
            FROM csv_data
            WHERE client_id = ?";
    
    // Add search condition if provided
    if (!empty($search)) {
        $sql .= " AND (
                    month LIKE ? OR
                    year LIKE ? OR
                    store LIKE ? OR
                    artist LIKE ? OR
                    title LIKE ? OR
                    country LIKE ?
                )";
    }
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters
    if (!empty($search)) {
        $search_term = "%$search%";
        $stmt->bind_param("issssss", $user_id, $search_term, $search_term, $search_term, $search_term, $search_term, $search_term);
    } else {
        $stmt->bind_param("i", $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $total_sum = $row['total_sum'] ? (float)$row['total_sum'] : 0;
    
    // Send response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'sum' => $total_sum
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 