<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,    // admins, loan officers, loan products
            TestDataSeeder::class, // borrowers, applications, loans, payments
        ]);
    }
}
