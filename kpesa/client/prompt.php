<?php
/**
 * K-Pesa Payment Gateway Integration
 * 
 * Secure payment processing with comprehensive logging and validation
 */

// ===== Configuration =====
const API_URL = 'https://api.kpesa.co.ke/api/v1/deposits/make_payment_c2b';
const API_KEY = '7258c6-e88dfb-50627d-1bfccd-51918a';
const CA_CERT_PATH = '/path/to/cacert.pem'; // Path to CA certificate bundle
const LOG_FILE = 'kpesa_payments.log';
const MAX_LOG_SIZE = 1048576; // 1MB max log size
const ALLOWED_ACCOUNT_TYPES = ['7411652']; // Add your approved account type IDs
const BUSINESS_SHORTCODE = '7411652'; // Your business shortcode

// ===== Initialization =====
header('Content-Type: application/json');
ini_set('display_errors', 0); // Disable in production
error_reporting(E_ALL);

// Check for required extensions
if (!extension_loaded('curl')) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 'CURL_EXTENSION_MISSING',
        'message' => 'cURL extension is not enabled',
        'solution' => 'Please enable the cURL extension in your php.ini'
    ]);
    exit;
}

// ===== Helper Functions =====

/**
 * Rotate log file if it gets too large
 */
function rotateLogIfNeeded(): void {
    if (file_exists(LOG_FILE) && filesize(LOG_FILE) > MAX_LOG_SIZE) {
        $backup = LOG_FILE . '.' . date('YmdHis');
        rename(LOG_FILE, $backup);
    }
}

/**
 * Log transaction details with context
 */
function logTransaction(string $message, array $context = []): void {
    rotateLogIfNeeded();
    $timestamp = date('Y-m-d H:i:s.v');
    $logEntry = "[$timestamp] $message";
    
    if (!empty($context)) {
        // Mask sensitive information in logs
        if (isset($context['phone'])) {
            $context['phone'] = substr($context['phone'], 0, 3) . '****' . substr($context['phone'], -3);
        }
        $logEntry .= "\nContext: " . json_encode($context, JSON_PRETTY_PRINT);
    }
    
    file_put_contents(LOG_FILE, $logEntry . "\n", FILE_APPEND);
}

/**
 * Standardize phone number to 254 format
 */
function standardizePhoneNumber(string $phone): string {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    if (strpos($phone, '0') === 0 && strlen($phone) === 10) {
        return '254' . substr($phone, 1);
    }
    
    return $phone;
}

/**
 * Validate payment parameters
 */
function validatePaymentParameters(array $input): array {
    $errors = [];
    
    // Validate amount
    if (!isset($input['amount']) || !is_numeric($input['amount']) || $input['amount'] <= 0) {
        $errors[] = [
            'code' => 'INVALID_AMOUNT',
            'message' => 'Amount must be a positive number'
        ];
    }
    
    // Validate phone number
    if (!isset($input['phone']) || !preg_match('/^(0|254)[7-9][0-1][0-9]{7}$/', $input['phone'])) {
        $errors[] = [
            'code' => 'INVALID_PHONE',
            'message' => 'Phone must be in 07... or 254... format'
        ];
    }
    
    // Validate account type if provided
    if (isset($input['account_type_id']) && !in_array($input['account_type_id'], ALLOWED_ACCOUNT_TYPES)) {
        $errors[] = [
            'code' => 'INVALID_ACCOUNT_TYPE',
            'message' => 'Unapproved account type'
        ];
    }
    
    return $errors;
}

/**
 * Make secure API request to K-Pesa
 */
function makeKpesaRequest(array $paymentData): array {
    $requestId = 'req_' . uniqid() . '_' . time();
    
    $payload = [
        'request' => [
            'request_id' => $requestId,
            'data' => [
                'amount' => (float)$paymentData['amount'],
                'phone' => standardizePhoneNumber($paymentData['phone']),
                'short_code' => BUSINESS_SHORTCODE // Using the constant here
            ]
        ]
    ];
    
    // Log the request payload (masking sensitive data)
    $logPayload = $payload;
    $logPayload['request']['data']['phone'] = substr($logPayload['request']['data']['phone'], 0, 3) . '****' . substr($logPayload['request']['data']['phone'], -3);
    logTransaction('Initiating STK push request', $logPayload);
    
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'x-api-key: ' . API_KEY,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES),
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10
    ]);
    
    // SSL verification
    if (file_exists(CA_CERT_PATH)) {
        curl_setopt($ch, CURLOPT_CAINFO, CA_CERT_PATH);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    } else {
        // Only for development/testing
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        logTransaction('SSL verification disabled - CA cert not found', [
            'expected_path' => CA_CERT_PATH
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error,
        'request_id' => $requestId
    ];
}

// ===== Main Execution =====

try {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Only POST requests are accepted'
        ]);
        exit;
    }
    
    // Get and parse input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'code' => 'INVALID_JSON',
            'message' => 'Invalid JSON input: ' . json_last_error_msg()
        ]);
        exit;
    }
    
    // Validate required parameters
    $validationErrors = validatePaymentParameters($input);
    if (!empty($validationErrors)) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'code' => 'VALIDATION_FAILED',
            'message' => 'Parameter validation failed',
            'errors' => $validationErrors
        ]);
        exit;
    }
    
    // Make API request
    $apiResponse = makeKpesaRequest($input);
    
    // Handle API response
    if (!empty($apiResponse['error'])) {
        logTransaction('API request failed', [
            'error' => $apiResponse['error'],
            'request_id' => $apiResponse['request_id']
        ]);
        
        http_response_code(502);
        echo json_encode([
            'status' => 'error',
            'code' => 'GATEWAY_ERROR',
            'message' => 'Could not connect to payment gateway',
            'request_id' => $apiResponse['request_id']
        ]);
        exit;
    }
    
    // Parse API response
    $responseData = json_decode($apiResponse['response'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logTransaction('Invalid API response', [
            'http_code' => $apiResponse['http_code'],
            'raw_response' => $apiResponse['response'],
            'request_id' => $apiResponse['request_id']
        ]);
        
        http_response_code(502);
        echo json_encode([
            'status' => 'error',
            'code' => 'INVALID_GATEWAY_RESPONSE',
            'message' => 'Invalid response from payment gateway',
            'request_id' => $apiResponse['request_id']
        ]);
        exit;
    }
    
    // Check for API errors
    if ($apiResponse['http_code'] !== 200) {
        logTransaction('API returned error status', [
            'http_code' => $apiResponse['http_code'],
            'response' => $responseData,
            'request_id' => $apiResponse['request_id']
        ]);
        
        http_response_code(502);
        echo json_encode([
            'status' => 'error',
            'code' => 'GATEWAY_REJECTED',
            'message' => $responseData['message'] ?? 'Payment gateway returned error status',
            'gateway_status' => $apiResponse['http_code'],
            'request_id' => $apiResponse['request_id']
        ]);
        exit;
    }
    
    // Success response
    logTransaction('STK push initiated successfully', [
        'request_id' => $apiResponse['request_id'],
        'gateway_response' => $responseData
    ]);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'STK push initiated successfully',
        'data' => $responseData,
        'request_id' => $apiResponse['request_id']
    ]);
    
} catch (Throwable $e) {
    logTransaction('System error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'code' => 'INTERNAL_ERROR',
        'message' => 'An unexpected error occurred'
    ]);
}