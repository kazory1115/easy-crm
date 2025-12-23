<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerActivitySeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $users = User::all();

        if ($customers->isEmpty() || $users->isEmpty()) {
            return;
        }

        CustomerActivity::factory()
            ->count(40)
            ->make()
            ->each(function ($activity) use ($customers, $users) {
                $activity->customer_id = $customers->random()->id;
                $activity->user_id = $users->random()->id;
                $activity->save();
            });
    }
}
