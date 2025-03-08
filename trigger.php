<?php
// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    // Include the PaymentGateway class
    require_once 'backend.php';

    // Create gateway instance
    $gateway = new PaymentGateway(
        // Merchant key
        '4b2a0fbc-87a1-11ee-b9a3-76a2abd30e3c',
        // API password
        '03e40b1eacb293b83b3b89f0c413cd46'
    );

    // Process payment
    $result = $gateway->createPaymentSession($input);

    // Return result as JSON
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}