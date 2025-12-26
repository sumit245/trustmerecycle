<?php

namespace App\Observers;

use App\Models\ScrapEntry;
use App\Models\User;
use App\Notifications\ScrapLimitReachedNotification;

class ScrapEntryObserver
{
    /**
     * Handle the ScrapEntry "created" event.
     */
    public function created(ScrapEntry $scrapEntry): void
    {
        $godown = $scrapEntry->godown;
        
        // Update the godown stock
        $godown->updateStock($scrapEntry->amount_mt);
        
        // Check if threshold is reached
        if ($godown->checkThreshold()) {
            // Send notification to all admin users
            $admins = User::where('role', 'admin')->get();
            
            foreach ($admins as $admin) {
                $admin->notify(new ScrapLimitReachedNotification($godown));
            }
        }
    }
}

