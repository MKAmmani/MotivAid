<?php

namespace Database\Seeders;

use App\Models\patient_data;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users to assign to patients
        $users = User::all();
        
        if ($users->isEmpty()) {
            // If no users exist, create one for the seeding
            $user = User::factory()->create([
                'name' => 'Default User',
                'email' => 'default@example.com',
            ]);
            $users = collect([$user]);
        }

        // Create 10 patient records
        for ($i = 1; $i <= 10; $i++) {
            patient_data::create([
                'name' => fake()->name(),
                'age' => fake()->numberBetween(18, 45),
                'history_of_antenatal_visit' => fake()->randomElement(['yes', 'no']),
                'gravida' => fake()->randomElement(['1', '2', '3', '4+']),
                'history_of_previous_pph' => fake()->randomElement(['yes', 'no']),
                'history_Of_ceaserian_section' => fake()->randomElement(['yes', 'no', 'unknown']),
                'type_of_pregenency' => fake()->randomElement(['single', 'twin', 'triplet', 'higher']),
                'gestational_age' => fake()->randomElement(['35-37 weeks', '38-40 weeks', '41-42 weeks', 'Post-term']),
                'hospital' => fake()->company(),
                'user_id' => $users->random()->id, // Assign randomly to existing users
                'is_complete' => fake()->boolean(),
            ]);
        }
    }
}
