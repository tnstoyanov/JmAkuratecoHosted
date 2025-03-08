<?php

class PaymentGateway {
    private $merchantKey;
    private $password;
    private $apiEndpoint;
    
    public function __construct(string $merchantKey, string $password) {
        $this->merchantKey = $merchantKey;
        $this->password = $password;
        $this->apiEndpoint = 'https://checkout.jmfinancialkw.com/api/v1/session';
    }
    
    private function generateOrderNumber(): string {
        return strval(rand(3000000, 3999999));
    }
    
    private function calculateHash(array $order): string {
        // Concatenate values and convert to uppercase
        $toHash = $order['number'] . 
                 $order['amount'] . 
                 $order['currency'] . 
                 $order['description'] . 
                 $this->password;
        
        // Log debug information instead of echo
        error_log("Debug Hash Calculation:");
        error_log("1. Original string: " . $toHash);
        error_log("2. Uppercase string: " . strtoupper($toHash));
        
        // Calculate MD5 hash (keep lowercase)
        $md5Hash = md5(strtoupper($toHash));
        error_log("3. MD5 hash (lowercase): " . $md5Hash);
        
        // Calculate SHA1 of the MD5 hash and convert to lowercase
        $finalHash = strtolower(sha1($md5Hash));
        error_log("4. Final SHA1 hash: " . $finalHash);
        
        return $finalHash;
    }
        
    public function createPaymentSession(array $customerData): array {
        $order = [
            'number' => $this->generateOrderNumber(),
            'amount' => number_format((float)$customerData['amount'], 2, '.', ''),  // Use provided amount
            'currency' => 'USD',
            'description' => 'Topup'
        ];
        
        $payload = [
            'merchant_key' => $this->merchantKey,
            'operation' => 'purchase',
            'order' => $order,
            'cancel_url' => 'https://tnstoyanov.wixsite.com/payment-response/cancel',
            'success_url' => 'https://tnstoyanov.wixsite.com/payment-response/success',
            'customer' => [
                'name' => $customerData['name'],
                'email' => $customerData['email']
            ],
            'billing_address' => [
                'country' => $customerData['country'],
                'city' => $customerData['city'],
                'address' => $customerData['address'],
                'zip' => $customerData['zip'],
                'phone' => $customerData['phone']
            ],
            'hash' => ''
        ];
        
        // Calculate and add hash
        $payload['hash'] = $this->calculateHash($order);
        
        // Prepare cURL request
        $ch = curl_init($this->apiEndpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'response' => json_decode($response, true),
            'request' => $payload
        ];
    }
}