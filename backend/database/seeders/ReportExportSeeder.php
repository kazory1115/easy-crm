<?php

namespace Database\Seeders;

use App\Models\ReportExport;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportExportSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        ReportExport::factory()
            ->count(8)
            ->make()
            ->each(function ($export) use ($users) {
                $export->user_id = $users->random()->id;
                $export->save();
            });
    }
}
