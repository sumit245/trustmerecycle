<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Godown;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class VendorsImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $successCount = 0;
    protected $skippedCount = 0;
    protected $existingEmails = [];
    protected $generatedEmails = [];

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // Increase execution time limit for large imports
        set_time_limit(300); // 5 minutes
        
        // Pre-load all existing emails into memory to avoid repeated queries
        $this->existingEmails = User::pluck('email')->map(function($email) {
            return strtolower($email);
        })->toArray();
        
        // Debug: Log available column names from first row
        if ($collection->isNotEmpty()) {
            $firstRow = $collection->first();
            \Log::info('Excel columns available: ' . implode(', ', array_keys($firstRow->toArray())));
        }

        // Prepare batch data
        $usersToInsert = [];
        $godownsToInsert = [];
        $emailsInBatch = []; // Track emails in current batch to avoid duplicates
        
        foreach ($collection as $rowIndex => $row) {
            try {
                // Skip empty rows
                $rowData = $row->toArray();
                if (empty(array_filter($rowData, function($value) {
                    return $value !== null && $value !== '' && (is_string($value) ? trim($value) !== '' : true);
                }))) {
                    continue; // Skip completely empty rows
                }
                
                // Convert row to array and normalize keys (lowercase, trim spaces, handle various formats)
                $rowArray = [];
                foreach ($rowData as $key => $value) {
                    if ($value !== null && $value !== '') {
                        // Normalize key: lowercase, replace spaces/hyphens with underscores, trim
                        $normalizedKey = strtolower(trim(str_replace([' ', '-', '.'], '_', $key)));
                        $rowArray[$normalizedKey] = is_string($value) ? trim($value) : $value;
                    }
                }
                
                // Also try original keys (case-insensitive matching)
                $originalKeys = array_change_key_case($rowData, CASE_LOWER);
                foreach ($originalKeys as $key => $value) {
                    $normalizedKey = strtolower(trim(str_replace([' ', '-', '.'], '_', $key)));
                    if (!isset($rowArray[$normalizedKey]) && $value !== null && $value !== '') {
                        $rowArray[$normalizedKey] = is_string($value) ? trim($value) : $value;
                    }
                }
                
                // Map Excel columns to database fields - try multiple variations
                $name = $rowArray['name'] 
                    ?? $rowArray['vendor_name'] 
                    ?? $rowArray['store_name']
                    ?? $rowArray['vendor'] 
                    ?? $rowArray['store']
                    ?? null;
                    
                $email = $rowArray['email'] 
                    ?? $rowArray['email_address'] 
                    ?? $rowArray['email_id']
                    ?? null;
                    
                $phone = $rowArray['phone'] 
                    ?? $rowArray['phone_number'] 
                    ?? $rowArray['contact'] 
                    ?? $rowArray['mobile']
                    ?? $rowArray['telephone']
                    ?? null;
                    
                $godownName = $rowArray['godown_name'] 
                    ?? $rowArray['godown'] 
                    ?? $rowArray['store_name']
                    ?? $rowArray['warehouse_name']
                    ?? $rowArray['name'] 
                    ?? null;
                    
                $location = $rowArray['location'] 
                    ?? $rowArray['city'] 
                    ?? $rowArray['area']
                    ?? $rowArray['zone']
                    ?? 'Noida'; // Default location if not provided
                    
                $address = $rowArray['address'] 
                    ?? $rowArray['full_address'] 
                    ?? $rowArray['complete_address']
                    ?? $rowArray['street_address']
                    ?? null;
                    
                $capacity = $rowArray['capacity_limit_mt'] 
                    ?? $rowArray['capacity'] 
                    ?? $rowArray['capacity_limit'] 
                    ?? $rowArray['capacity_mt']
                    ?? 100;

                // Try original keys too (case-sensitive and case-insensitive)
                $rowOriginal = $row->toArray();
                $rowOriginalLower = array_change_key_case($rowOriginal, CASE_LOWER);
                
                if (!$name) {
                    foreach (['Name', 'Vendor Name', 'Store Name', 'name', 'vendor_name', 'store_name'] as $key) {
                        if (isset($rowOriginal[$key])) {
                            $name = trim($rowOriginal[$key]);
                            break;
                        }
                        if (isset($rowOriginalLower[strtolower($key)])) {
                            $name = trim($rowOriginalLower[strtolower($key)]);
                            break;
                        }
                    }
                }
                
                if (!$email) {
                    foreach (['Email', 'Email Address', 'Email ID', 'email', 'email_address', 'email_id'] as $key) {
                        if (isset($rowOriginal[$key])) {
                            $email = trim($rowOriginal[$key]);
                            break;
                        }
                        if (isset($rowOriginalLower[strtolower($key)])) {
                            $email = trim($rowOriginalLower[strtolower($key)]);
                            break;
                        }
                    }
                }
                
                // Generate email from name if still missing
                if (!$email && $name) {
                    $emailBase = strtolower(preg_replace('/[^a-z0-9]+/', '.', $name));
                    $emailBase = trim($emailBase, '.');
                    $email = $emailBase . '@trustmerecycle.com';
                    $counter = 1;
                    // Check against cached emails, generated emails, and emails in current batch
                    $emailLower = strtolower($email);
                    while (in_array($emailLower, $this->existingEmails) || 
                           isset($this->generatedEmails[$emailLower]) || 
                           isset($emailsInBatch[$emailLower])) {
                        $email = $emailBase . $counter . '@trustmerecycle.com';
                        $emailLower = strtolower($email);
                        $counter++;
                        // Prevent infinite loop
                        if ($counter > 10000) {
                            break;
                        }
                    }
                    // Track generated email to avoid duplicates in same import
                    $this->generatedEmails[$emailLower] = true;
                    $emailsInBatch[$emailLower] = true;
                }
                
                if (!$phone) {
                    foreach (['Phone', 'Contact', 'Mobile', 'Telephone', 'phone', 'phone_number', 'contact', 'mobile'] as $key) {
                        if (isset($rowOriginal[$key])) {
                            $phone = trim($rowOriginal[$key]);
                            break;
                        }
                        if (isset($rowOriginalLower[strtolower($key)])) {
                            $phone = trim($rowOriginalLower[strtolower($key)]);
                            break;
                        }
                    }
                }
                
                if (!$godownName) {
                    foreach (['Godown Name', 'Godown', 'Store Name', 'Warehouse Name', 'godown_name', 'godown', 'store_name', 'warehouse_name'] as $key) {
                        if (isset($rowOriginal[$key])) {
                            $godownName = trim($rowOriginal[$key]);
                            break;
                        }
                        if (isset($rowOriginalLower[strtolower($key)])) {
                            $godownName = trim($rowOriginalLower[strtolower($key)]);
                            break;
                        }
                    }
                    // Use store_name as godown name if still not found
                    if (!$godownName && $name) {
                        $godownName = $name;
                    }
                }
                
                // Check original keys for location if not found yet
                if (!$location || $location === 'Noida') {
                    foreach (['Location', 'City', 'Area', 'Zone', 'location', 'city', 'area', 'zone'] as $key) {
                        if (isset($rowOriginal[$key]) && trim($rowOriginal[$key]) !== '') {
                            $location = trim($rowOriginal[$key]);
                            break;
                        }
                        if (isset($rowOriginalLower[strtolower($key)]) && trim($rowOriginalLower[strtolower($key)]) !== '') {
                            $location = trim($rowOriginalLower[strtolower($key)]);
                            break;
                        }
                    }
                    // Only use default if location is still empty or was default
                    if (!$location || ($location === 'Noida' && !isset($rowOriginal['Location']) && !isset($rowOriginal['location']) && !isset($rowOriginalLower['location']))) {
                        $location = 'Noida'; // Default location
                    }
                }
                
                if (!$address) {
                    foreach (['Address', 'Full Address', 'Complete Address', 'Street Address', 'address', 'full_address', 'complete_address', 'street_address'] as $key) {
                        if (isset($rowOriginal[$key])) {
                            $address = trim($rowOriginal[$key]);
                            break;
                        }
                        if (isset($rowOriginalLower[strtolower($key)])) {
                            $address = trim($rowOriginalLower[strtolower($key)]);
                            break;
                        }
                    }
                }
                
                if (!$capacity || !is_numeric($capacity)) {
                    foreach (['Capacity', 'Capacity Limit', 'Capacity (MT)', 'capacity', 'capacity_limit_mt', 'capacity_limit', 'capacity_mt'] as $key) {
                        if (isset($rowOriginal[$key]) && is_numeric($rowOriginal[$key])) {
                            $capacity = (float)$rowOriginal[$key];
                            break;
                        }
                        if (isset($rowOriginalLower[strtolower($key)]) && is_numeric($rowOriginalLower[strtolower($key)])) {
                            $capacity = (float)$rowOriginalLower[strtolower($key)];
                            break;
                        }
                    }
                    if (!is_numeric($capacity)) {
                        $capacity = 100; // Default capacity
                    }
                }

                // Validate required fields
                if (!$name) {
                    $this->errors[] = "Row " . ($rowIndex + 2) . " missing name. Available columns: " . implode(', ', array_keys($row->toArray()));
                    continue;
                }
                
                if (!$email) {
                    $this->errors[] = "Row " . ($rowIndex + 2) . " missing email and could not generate one. Available columns: " . implode(', ', array_keys($row->toArray()));
                    continue;
                }

                // Check if user already exists (using cached emails and current batch)
                $emailLower = strtolower($email);
                if (in_array($emailLower, $this->existingEmails) || 
                    isset($this->generatedEmails[$emailLower]) || 
                    isset($emailsInBatch[$emailLower])) {
                    // Skip duplicate emails silently (don't treat as error, just skip)
                    $this->skippedCount++;
                    continue;
                }
                
                // Track email in current batch
                $emailsInBatch[$emailLower] = true;

                // Prepare user data for batch insert
                $godownName = $godownName ?: $name; // Use name as godown name if not provided
                $location = $location ?: 'Noida'; // Default location
                $address = $address ?: ($name . ', Noida'); // Use name + location as address if not provided
                
                $usersToInsert[] = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make('password'), // Default password
                    'role' => 'vendor',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Track email to avoid duplicates in same batch (already tracked above)
                
                // Store godown data with reference to user index
                $godownsToInsert[] = [
                    'user_index' => count($usersToInsert) - 1,
                    'name' => $godownName,
                    'location' => $location,
                    'address' => $address,
                    'capacity_limit_mt' => is_numeric($capacity) ? (float)$capacity : 100,
                    'current_stock_mt' => 0,
                ];
            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($rowIndex + 2) . " error: " . $e->getMessage() . " | Available columns: " . implode(', ', array_keys($row->toArray()));
                \Log::error('Vendor import error on row ' . ($rowIndex + 2) . ': ' . $e->getMessage(), [
                    'row_data' => $row->toArray(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        // Batch insert users
        if (!empty($usersToInsert)) {
            try {
                // Insert users in chunks to avoid memory issues
                $chunks = array_chunk($usersToInsert, 100);
                $insertedUsers = [];
                
                foreach ($chunks as $chunk) {
                    User::insert($chunk);
                    // Get the inserted user IDs (we need to query them back in the same order)
                    $emails = array_column($chunk, 'email');
                    $insertedChunk = User::whereIn('email', $emails)->get()->keyBy('email');
                    // Preserve order by matching emails
                    foreach ($chunk as $userData) {
                        if (isset($insertedChunk[$userData['email']])) {
                            $insertedUsers[] = $insertedChunk[$userData['email']];
                        }
                    }
                    // Update existing emails cache with newly inserted emails
                    foreach ($emails as $email) {
                        $this->existingEmails[] = strtolower($email);
                    }
                }
                
                // Now create godowns with proper vendor_id references (batch insert)
                $godownsBatch = [];
                foreach ($godownsToInsert as $godownData) {
                    $userIndex = $godownData['user_index'];
                    if (isset($insertedUsers[$userIndex])) {
                        $godownsBatch[] = [
                            'vendor_id' => $insertedUsers[$userIndex]->id,
                            'name' => $godownData['name'],
                            'location' => $godownData['location'],
                            'address' => $godownData['address'],
                            'capacity_limit_mt' => $godownData['capacity_limit_mt'],
                            'current_stock_mt' => $godownData['current_stock_mt'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
                
                // Batch insert godowns in chunks
                if (!empty($godownsBatch)) {
                    $godownChunks = array_chunk($godownsBatch, 100);
                    foreach ($godownChunks as $godownChunk) {
                        Godown::insert($godownChunk);
                    }
                    $this->successCount = count($godownsBatch);
                }
            } catch (\Exception $e) {
                \Log::error('Batch insert error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                $this->errors[] = "Batch insert failed: " . $e->getMessage();
            }
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }
}


