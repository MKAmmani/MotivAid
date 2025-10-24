<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Emotiv;

class EmotivSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Emotiv::create([
            'name' => 'Early identification of bleeding',
            'description' => 'Promptly identify and monitor blood loss. Initiate E-MOTIVE protocol if blood loss is 500ml or less.',
        ]);

        Emotiv::create([
            'name' => 'Massaging of uterus',
            'description' => '',
        ]);

        Emotiv::create([
            'name' => 'Administer Oxytocin',
            'description' => 'Administer 10IU of oxytocin (I.M)',
        ]);

        Emotiv::create([
            'name' => 'Administer Tranexamic Acid',
            'description' => '1g of traxenamic acid (I.V)',
        ]);

        Emotiv::create([
            'name' => 'IV fluids',
            'description' => 'Administer N/S or R/L with 10IU of oxytonic.',
        ]);

        Emotiv::create([
            'name' => 'Vital signs monitoring',
            'description' => '',
        ]);

        Emotiv::create([
            'name' => 'Examination / Escalation',
            'description' => '',
        ]);
    }
}