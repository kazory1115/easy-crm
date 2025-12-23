<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Seeder;

class OpportunitySeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $users = User::all();

        if ($customers->isEmpty() || $users->isEmpty()) {
            return;
        }

        Opportunity::factory()
            ->count(20)
            ->make()
            ->each(function ($opportunity) use ($customers, $users) {
                $opportunity->customer_id = $customers->random()->id;
                $opportunity->created_by = $users->random()->id;
                $opportunity->updated_by = $users->random()->id;
                $opportunity->save();
            });
    }
}
