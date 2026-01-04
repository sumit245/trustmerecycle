<?php

namespace App\Imports;

use App\Models\Godown;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GodownsImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $successCount = 0;
    protected $skippedCount = 0;
    protected $defaultVendorId = null;
    protected $defaultLocation = 'Noida';

    public function __construct($defaultVendorId = null, $defaultLocation = 'Noida')
    {
        $this->defaultVendorId = $defaultVendorId;
        $this->defaultLocation = $defaultLocation;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        // Increase execution time limit for large imports
        set_time_limit(300); // 5 minutes

        // Pre-load all vendors into memory for efficient lookup
        $vendors = User::where('role', 'vendor')->get()->keyBy(function ($vendor) {
            return strtolower($vendor->email);
        });
        
        // Also create lookup by name
        $vendorsByName = User::where('role', 'vendor')->get()->keyBy(function ($vendor) {
            return strtolower(trim($vendor->name));
        });

        // Debug: Log available column names from first row
        if ($collection->isNotEmpty()) {
            $firstRow = $collection->first();
            \Log::info('Excel columns available: ' . implode(', ', array_keys($firstRow->toArray())));
        }

        // Prepare batch data
        $godownsToInsert = [];

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
                    ?? $rowArray['godown_name'] 
                    ?? $rowArray['store_name']
                    ?? $rowArray['warehouse_name']
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
                    ?? null;

                // Try to get vendor_id or vendor lookup
                $vendorId = null;
                $vendorName = $rowArray['vendor_name'] 
                    ?? $rowArray['vendor'] 
                    ?? null;
                $vendorEmail = $rowArray['vendor_email'] 
                    ?? $rowArray['email']
                    ?? null;

                // Try original keys too (case-sensitive and case-insensitive)
                $rowOriginal = $row->toArray();
                $rowOriginalLower = array_change_key_case($rowOriginal, CASE_LOWER);

                if (!$name) {
                    foreach (['Name', 'Godown Name', 'Store Name', 'Warehouse Name', 'name', 'godown_name', 'store_name', 'warehouse_name'] as $key) {
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

                if (!$location) {
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
                }

                // Try to get vendor_id directly or lookup vendor
                if (isset($rowArray['vendor_id']) && is_numeric($rowArray['vendor_id'])) {
                    $vendorId = (int)$rowArray['vendor_id'];
                } elseif (isset($rowOriginal['vendor_id']) && is_numeric($rowOriginal['vendor_id'])) {
                    $vendorId = (int)$rowOriginal['vendor_id'];
                } elseif (isset($rowOriginalLower['vendor_id']) && is_numeric($rowOriginalLower['vendor_id'])) {
                    $vendorId = (int)$rowOriginalLower['vendor_id'];
                }

                // If vendor_id not found, try to lookup by email or name
                if (!$vendorId) {
                    if ($vendorEmail) {
                        $vendorEmailLower = strtolower(trim($vendorEmail));
                        if (isset($vendors[$vendorEmailLower])) {
                            $vendorId = $vendors[$vendorEmailLower]->id;
                        }
                    }
                    
                    if (!$vendorId && $vendorName) {
                        $vendorNameLower = strtolower(trim($vendorName));
                        if (isset($vendorsByName[$vendorNameLower])) {
                            $vendorId = $vendorsByName[$vendorNameLower]->id;
                        }
                    }

                    // Try original keys for vendor lookup
                    if (!$vendorId) {
                        foreach (['Vendor Name', 'Vendor', 'vendor_name', 'vendor'] as $key) {
                            if (isset($rowOriginal[$key]) && trim($rowOriginal[$key]) !== '') {
                                $vendorNameLower = strtolower(trim($rowOriginal[$key]));
                                if (isset($vendorsByName[$vendorNameLower])) {
                                    $vendorId = $vendorsByName[$vendorNameLower]->id;
                                    break;
                                }
                            }
                            if (isset($rowOriginalLower[strtolower($key)]) && trim($rowOriginalLower[strtolower($key)]) !== '') {
                                $vendorNameLower = strtolower(trim($rowOriginalLower[strtolower($key)]));
                                if (isset($vendorsByName[$vendorNameLower])) {
                                    $vendorId = $vendorsByName[$vendorNameLower]->id;
                                    break;
                                }
                            }
                        }
                    }

                    if (!$vendorId) {
                        foreach (['Vendor Email', 'Email', 'vendor_email', 'email'] as $key) {
                            if (isset($rowOriginal[$key]) && trim($rowOriginal[$key]) !== '') {
                                $vendorEmailLower = strtolower(trim($rowOriginal[$key]));
                                if (isset($vendors[$vendorEmailLower])) {
                                    $vendorId = $vendors[$vendorEmailLower]->id;
                                    break;
                                }
                            }
                            if (isset($rowOriginalLower[strtolower($key)]) && trim($rowOriginalLower[strtolower($key)]) !== '') {
                                $vendorEmailLower = strtolower(trim($rowOriginalLower[strtolower($key)]));
                                if (isset($vendors[$vendorEmailLower])) {
                                    $vendorId = $vendors[$vendorEmailLower]->id;
                                    break;
                                }
                            }
                        }
                    }
                }

                // Validate required fields
                if (!$name) {
                    $this->errors[] = "Row " . ($rowIndex + 2) . ": Missing godown name. Available columns: " . implode(', ', array_keys($row->toArray()));
                    $this->skippedCount++;
                    continue;
                }

                // Use default location if not provided
                if (!$location) {
                    $location = $this->defaultLocation;
                }

                if (!$address) {
                    $this->errors[] = "Row " . ($rowIndex + 2) . ": Missing address. Available columns: " . implode(', ', array_keys($row->toArray()));
                    $this->skippedCount++;
                    continue;
                }

                if (!$capacity || !is_numeric($capacity)) {
                    $capacity = 100; // Default capacity
                } else {
                    $capacity = (float)$capacity;
                }

                // Use default vendor if not provided
                if (!$vendorId) {
                    if ($this->defaultVendorId) {
                        $vendorId = $this->defaultVendorId;
                    } else {
                        // Try to use the first vendor as default
                        $firstVendor = User::where('role', 'vendor')->first();
                        if ($firstVendor) {
                            $vendorId = $firstVendor->id;
                        } else {
                            $this->errors[] = "Row " . ($rowIndex + 2) . ": No vendor specified and no default vendor available. Please provide vendor_id, vendor_name, vendor_email, or select a default vendor. Available columns: " . implode(', ', array_keys($row->toArray()));
                            $this->skippedCount++;
                            continue;
                        }
                    }
                }

                // Verify vendor exists and is a vendor
                $vendor = User::find($vendorId);
                if (!$vendor || $vendor->role !== 'vendor') {
                    $this->errors[] = "Row " . ($rowIndex + 2) . ": Invalid vendor ID {$vendorId}. Vendor not found or is not a vendor.";
                    $this->skippedCount++;
                    continue;
                }

                // Prepare godown data for batch insert
                $godownsToInsert[] = [
                    'vendor_id' => $vendorId,
                    'name' => $name,
                    'location' => $location,
                    'address' => $address,
                    'capacity_limit_mt' => $capacity,
                    'current_stock_mt' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } catch (\Exception $e) {
                $this->errors[] = "Row " . ($rowIndex + 2) . " error: " . $e->getMessage() . " | Available columns: " . implode(', ', array_keys($row->toArray()));
                $this->skippedCount++;
                \Log::error('Godown import error on row ' . ($rowIndex + 2) . ': ' . $e->getMessage(), [
                    'row_data' => $row->toArray(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        // Batch insert godowns
        if (!empty($godownsToInsert)) {
            try {
                // Insert godowns in chunks to avoid memory issues
                $chunks = array_chunk($godownsToInsert, 100);
                foreach ($chunks as $chunk) {
                    Godown::insert($chunk);
                }
                $this->successCount = count($godownsToInsert);
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

