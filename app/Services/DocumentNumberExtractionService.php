<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DocumentNumberExtractionService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.ocr_space.api_key');
        $this->baseUrl = config('services.ocr_space.base_url', 'https://api.ocr.space/parse/image');
    }

    public function extractDocumentNumber(UploadedFile $file, string $documentType)
    {
        // 1. MIME Type Validation
        $allowedMimes = ['image/jpeg', 'image/png', 'application/pdf', 'image/jpg'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return ['success' => false, 'message' => 'Automatic extraction is available for JPG, PNG and PDF files only.'];
        }

        // 2. Cache Check
        $hash = sha1_file($file->getPathname());
        $cacheKey = "ocr_extract_{$hash}_{$documentType}";

        if (Cache::has($cacheKey)) {
            Log::info("DocumentNumberExtractionService: Cache hit for $cacheKey");
            return Cache::get($cacheKey);
        }

        // 3. OCR.space API Request
        Log::info("DocumentNumberExtractionService: Sending file to OCR.space for $documentType");
        
        try {
            $response = Http::asMultipart()
                ->timeout(60)
                ->attach('file', file_get_contents($file->getPathname()), $file->getClientOriginalName())
                ->post($this->baseUrl, [
                    'apikey' => $this->apiKey,
                    'language' => 'eng',
                    'isOverlayRequired' => 'false',
                    'detectOrientation' => 'true',
                    'scale' => 'true',
                    'OCREngine' => '2', // Engine 2 is better for numbers/special characters
                ]);

            if (!$response->successful()) {
                Log::error("DocumentNumberExtractionService: OCR.space API HTTP error", ['status' => $response->status()]);
                return ['success' => false, 'message' => 'Failed to communicate with OCR service.'];
            }

            $result = $response->json();
            
            if (isset($result['IsErroredOnProcessing']) && $result['IsErroredOnProcessing']) {
                Log::error("DocumentNumberExtractionService: OCR processing error", ['response' => $result]);
                return ['success' => false, 'message' => 'OCR processing failed on the document.'];
            }

            $parsedText = '';
            if (isset($result['ParsedResults']) && is_array($result['ParsedResults'])) {
                foreach ($result['ParsedResults'] as $parsedResult) {
                    $parsedText .= ' ' . ($parsedResult['ParsedText'] ?? '');
                }
            }

            if (empty(trim($parsedText))) {
                return ['success' => false, 'message' => 'No readable text found in document.'];
            }

            // 4. Normalization and Regex Extraction
            $extractedNumber = $this->extractAndValidateNumber($parsedText, $documentType);

            if ($extractedNumber) {
                $successResult = [
                    'success' => true,
                    'document_type' => $documentType,
                    'document_number' => $extractedNumber
                ];
                
                Cache::put($cacheKey, $successResult, now()->addDays(7));
                return $successResult;
            }

            Log::warning("DocumentNumberExtractionService: Match failed for $documentType in text: " . substr(str_replace(["\r", "\n"], " ", $parsedText), 0, 200));
            return ['success' => false, 'message' => 'Document number could not be clearly identified.'];

        } catch (\Exception $e) {
            Log::error("DocumentNumberExtractionService: Exception: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during extraction.'];
        }
    }

    private function extractAndValidateNumber(string $text, string $type)
    {
        // Normalize text: uppercase, remove newlines
        $normalized = strtoupper(str_replace(["\r", "\n"], " ", $text));
        
        switch ($type) {
            case 'PAN':
                // Format: 5 Letters, 4 Digits, 1 Letter. Strip spaces.
                $clean = str_replace(' ', '', $normalized);
                if (preg_match('/[A-Z]{5}[0-9]{4}[A-Z]/', $clean, $matches)) {
                    return $matches[0];
                }
                break;
                
            case 'Aadhaar':
                // Format: 12 digits, often with spaces. 
                // Negative lookbehind/lookahead ensures we don't accidentally match 12 digits inside a larger string (like Pincode + Phone number)
                if (preg_match('/(?<!\d)[2-9][0-9]{3}\s?[0-9]{4}\s?[0-9]{4}(?!\d)/', $normalized, $matches)) {
                    return trim(preg_replace('/\s+/', ' ', $matches[0]));
                }
                break;
                
            case 'GST':
                // OCR tolerant GST: 2 Digits, 5 Letters, 4 Digits, 1 Letter, 1 Alphanum, 1 Alphanum, 1 Alphanum. Remove spaces.
                $clean = str_replace(' ', '', $normalized);
                if (preg_match('/[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{3}/', $clean, $matches)) {
                    return $matches[0];
                }
                break;
                
            case 'Udyam':
                // 1. Standard Udyam: UDYAM-XX-00-0000000
                if (preg_match('/UDYAM\s*-\s*[A-Z]{2}\s*-\s*[0-9]{2}\s*-\s*[0-9]{7}/i', $normalized, $matches)) {
                    return preg_replace('/\s*/', '', strtoupper($matches[0]));
                }
                
                // 2. Corporate Identification Number (CIN) format (e.g. U12345MH2020PTC123456)
                if (preg_match('/\b[LU][0-9]{5}[A-Z]{2}[0-9]{4}[A-Z]{3}[0-9]{6}\b/i', $normalized, $matches)) {
                    return strtoupper($matches[0]);
                }

                // 3. Shop & Establishment / Form C Registration (e.g. 14/153/S/0010/2023)
                if (preg_match('/\b\d{2,3}\/\d{2,4}\/[A-Z]{1,2}\/\d{2,5}\/\d{4}\b/i', $normalized, $matches)) {
                    return strtoupper($matches[0]);
                }
                break;
        }
        
        return null;
    }
}
