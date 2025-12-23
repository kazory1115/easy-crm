<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerContactSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $users = User::all();

        if ($customers->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($customers as $customer) {
            $count = rand(1, 3);
            $contacts = CustomerContact::factory()
                ->count($count)
                ->make([
                    'customer_id' => $customer->id,
                    'created_by' => $users->random()->id,
                    'updated_by' => $users->random()->id,
                ]);

            $contacts->first()->is_primary = true;
            $contacts->each->save();
        }
    }
}
