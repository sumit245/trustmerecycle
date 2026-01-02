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

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // Debug: Log available column names from first row
        if ($collection->isNotEmpty()) {
            $firstRow = $collection->first();
            \Log::info('Excel columns available: ' . implode(', ', array_keys($firstRow->toArray())));
        }

        foreach ($collection as $rowIndex => $row) {
            try {
                // Skip empty rows
                $rowData = $row->toArray();
                if (empty(array_filter($rowData, function($value) {
                    return $value !== null && $value !== '' && (is_string($value) ? trim($value) !== '' : true);
                }))) {
                    continue; // Skip completely empty rows
                }
                
                // Convert row to array and normalize keys (lowercase, trim spaces)
                $rowArray = [];
                foreach ($rowData as $key => $value) {
                    if ($value !== null && $value !== '') {
                        $normalizedKey = strtolower(trim(str_replace([' ', '-'], '_', $key)));
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
                    ?? null;
                    
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

                // Try original keys too (case-sensitive)
                if (!$name && isset($row['Name'])) $name = $row['Name'];
                if (!$name && isset($row['Vendor Name'])) $name = $row['Vendor Name'];
                if (!$name && isset($row['Store Name'])) $name = $row['Store Name'];
                
                if (!$email && isset($row['Email'])) $email = $row['Email'];
                if (!$email && isset($row['Email Address'])) $email = $row['Email Address'];
                
                if (!$phone && isset($row['Phone'])) $phone = $row['Phone'];
                if (!$phone && isset($row['Contact'])) $phone = $row['Contact'];
                if (!$phone && isset($row['Mobile'])) $phone = $row['Mobile'];
                
                if (!$godownName && isset($row['Godown Name'])) $godownName = $row['Godown Name'];
                if (!$godownName && isset($row['Godown'])) $godownName = $row['Godown'];
                if (!$godownName && isset($row['Store Name'])) $godownName = $row['Store Name'];
                
                if (!$location && isset($row['Location'])) $location = $row['Location'];
                if (!$location && isset($row['City'])) $location = $row['City'];
                
                if (!$address && isset($row['Address'])) $address = $row['Address'];
                if (!$address && isset($row['Full Address'])) $address = $row['Full Address'];
                
                if (!$capacity && isset($row['Capacity'])) $capacity = $row['Capacity'];
                if (!$capacity && isset($row['Capacity Limit'])) $capacity = $row['Capacity Limit'];

                if (!$name || !$email) {
                    $this->errors[] = "Row " . ($rowIndex + 2) . " missing name or email. Available columns: " . implode(', ', array_keys($row->toArray()));
                    continue;
                }

                // Check if user already exists
                $existingUser = User::where('email', $email)->first();
                if ($existingUser) {
                    $this->errors[] = "Email already exists: {$email}";
                    continue;
                }

                // Create user
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'password' => Hash::make('password'), // Default password
                    'role' => 'vendor',
                ]);

                // Create godown
                if ($godownName && $location && $address) {
                    Godown::create([
                        'vendor_id' => $user->id,
                        'name' => $godownName,
                        'location' => $location,
                        'address' => $address,
                        'capacity_limit_mt' => is_numeric($capacity) ? (float)$capacity : 100,
                        'current_stock_mt' => 0,
                    ]);
                }

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($rowIndex + 2) . " error: " . $e->getMessage() . " | Available columns: " . implode(', ', array_keys($row->toArray()));
                \Log::error('Vendor import error on row ' . ($rowIndex + 2) . ': ' . $e->getMessage(), [
                    'row_data' => $row->toArray(),
                    'trace' => $e->getTraceAsString()
                ]);
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
}


