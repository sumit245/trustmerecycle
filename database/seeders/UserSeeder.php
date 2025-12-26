<?php

namespace Database\Seeders;

use App\Models\CollectionJob;
use App\Models\Godown;
use App\Models\ScrapEntry;
use App\Models\ScrapType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create scrap types
        $scrapTypes = [
            ['name' => 'Plastic', 'unit_price_per_ton' => 5000, 'description' => 'Various plastic materials'],
            ['name' => 'Metal', 'unit_price_per_ton' => 15000, 'description' => 'Iron, steel, aluminum scrap'],
            ['name' => 'Paper', 'unit_price_per_ton' => 3000, 'description' => 'Cardboard and paper waste'],
            ['name' => 'Glass', 'unit_price_per_ton' => 2000, 'description' => 'Glass bottles and containers'],
            ['name' => 'Electronics', 'unit_price_per_ton' => 25000, 'description' => 'E-waste and electronic components'],
        ];

        foreach ($scrapTypes as $type) {
            ScrapType::create($type);
        }

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@trustmerecycle.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+91-9876543210',
        ]);

        // Create vendor 1
        $vendor1 = User::create([
            'name' => 'Vendor One',
            'email' => 'vendor1@trustmerecycle.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'phone' => '+91-9876543211',
        ]);

        // Create godown for vendor 1 (near capacity)
        $godown1 = Godown::create([
            'vendor_id' => $vendor1->id,
            'name' => 'North Godown',
            'location' => 'North Zone',
            'address' => '123 North Street, Industrial Area',
            'capacity_limit_mt' => 100.00,
            'current_stock_mt' => 85.50, // Near capacity
        ]);

        // Create some scrap entries for vendor 1
        $entries1 = [
            ['scrap_type_id' => 1, 'date' => now()->subDays(5), 'amount_mt' => 10.5, 'estimated_value' => 52500],
            ['scrap_type_id' => 2, 'date' => now()->subDays(3), 'amount_mt' => 15.0, 'estimated_value' => 225000],
            ['scrap_type_id' => 1, 'date' => now()->subDays(1), 'amount_mt' => 8.0, 'estimated_value' => 40000],
        ];

        foreach ($entries1 as $entry) {
            ScrapEntry::create(array_merge($entry, ['godown_id' => $godown1->id]));
        }

        // Create vendor 2
        $vendor2 = User::create([
            'name' => 'Vendor Two',
            'email' => 'vendor2@trustmerecycle.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'phone' => '+91-9876543212',
        ]);

        // Create godown for vendor 2 (normal stock)
        $godown2 = Godown::create([
            'vendor_id' => $vendor2->id,
            'name' => 'South Godown',
            'location' => 'South Zone',
            'address' => '456 South Avenue, Commercial Area',
            'capacity_limit_mt' => 150.00,
            'current_stock_mt' => 45.25, // Normal stock
        ]);

        // Create some scrap entries for vendor 2
        $entries2 = [
            ['scrap_type_id' => 3, 'date' => now()->subDays(7), 'amount_mt' => 12.0, 'estimated_value' => 36000],
            ['scrap_type_id' => 4, 'date' => now()->subDays(4), 'amount_mt' => 8.5, 'estimated_value' => 17000],
            ['scrap_type_id' => 2, 'date' => now()->subDays(2), 'amount_mt' => 10.0, 'estimated_value' => 150000],
        ];

        foreach ($entries2 as $entry) {
            ScrapEntry::create(array_merge($entry, ['godown_id' => $godown2->id]));
        }

        // Create a pending collection job for vendor 1 (to test the workflow)
        CollectionJob::create([
            'godown_id' => $godown1->id,
            'status' => 'pending',
        ]);
    }
}

