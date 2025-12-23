<?php

namespace Database\Seeders;

use App\Models\Opportunity;
use App\Models\OpportunityLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class OpportunityLogSeeder extends Seeder
{
    public function run(): void
    {
        $opportunities = Opportunity::all();
        $users = User::all();

        if ($opportunities->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($opportunities as $opportunity) {
            OpportunityLog::factory()
                ->count(rand(1, 3))
                ->make([
                    'opportunity_id' => $opportunity->id,
                    'user_id' => $users->random()->id,
                ])
                ->each->save();
        }
    }
}
