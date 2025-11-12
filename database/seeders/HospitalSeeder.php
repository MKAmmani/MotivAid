<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hospital;

class HospitalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hospitals = [
            [
                'name' => 'General Hospital',
                'location' => 'New York, NY',
                'contact_info' => '+1-555-0101'
            ],
            [
                'name' => 'City Medical Center',
                'location' => 'Los Angeles, CA',
                'contact_info' => '+1-555-0102'
            ],
            [
                'name' => 'Regional Health Complex',
                'location' => 'Chicago, IL',
                'contact_info' => '+1-555-0103'
            ],
            [
                'name' => 'University Hospital',
                'location' => 'Houston, TX',
                'contact_info' => '+1-555-0104'
            ],
            [
                'name' => 'Community Medical Center',
                'location' => 'Phoenix, AZ',
                'contact_info' => '+1-555-0105'
            ],
            [
                'name' => 'Metropolitan Hospital',
                'location' => 'Philadelphia, PA',
                'contact_info' => '+1-555-0106'
            ],
            [
                'name' => 'St. Mary\'s Hospital',
                'location' => 'San Antonio, TX',
                'contact_info' => '+1-555-0107'
            ],
            [
                'name' => 'Central Medical Facility',
                'location' => 'San Diego, CA',
                'contact_info' => '+1-555-0108'
            ],
            [
                'name' => 'Northside Hospital',
                'location' => 'Dallas, TX',
                'contact_info' => '+1-555-0109'
            ],
            [
                'name' => 'Eastside Medical Center',
                'location' => 'San Jose, CA',
                'contact_info' => '+1-555-0110'
            ],
            [
                'name' => 'West Coast Hospital',
                'location' => 'Austin, TX',
                'contact_info' => '+1-555-0111'
            ],
            [
                'name' => 'Midwest Medical Center',
                'location' => 'Jacksonville, FL',
                'contact_info' => '+1-555-0112'
            ],
            [
                'name' => 'Sunshine General Hospital',
                'location' => 'Fort Worth, TX',
                'contact_info' => '+1-555-0113'
            ],
            [
                'name' => 'Mountain View Hospital',
                'location' => 'Columbus, OH',
                'contact_info' => '+1-555-0114'
            ],
            [
                'name' => 'Riverside Medical Center',
                'location' => 'Charlotte, NC',
                'contact_info' => '+1-555-0115'
            ],
            [
                'name' => 'Coastal Hospital',
                'location' => 'San Francisco, CA',
                'contact_info' => '+1-555-0116'
            ],
            [
                'name' => 'Valley General Hospital',
                'location' => 'Indianapolis, IN',
                'contact_info' => '+1-555-0117'
            ],
            [
                'name' => 'Pine Valley Medical Center',
                'location' => 'Seattle, WA',
                'contact_info' => '+1-555-0118'
            ],
            [
                'name' => 'Oakland Regional Hospital',
                'location' => 'Denver, CO',
                'contact_info' => '+1-555-0119'
            ],
            [
                'name' => 'Lakeside Hospital',
                'location' => 'Washington, DC',
                'contact_info' => '+1-555-0120'
            ],
            [
                'name' => 'Heritage Medical Center',
                'location' => 'Boston, MA',
                'contact_info' => '+1-555-0121'
            ],
            [
                'name' => 'Capital Hospital',
                'location' => 'El Paso, TX',
                'contact_info' => '+1-555-0122'
            ],
            [
                'name' => 'Memorial Medical Center',
                'location' => 'Nashville, TN',
                'contact_info' => '+1-555-0123'
            ],
            [
                'name' => 'Sunset General Hospital',
                'location' => 'Detroit, MI',
                'contact_info' => '+1-555-0124'
            ],
            [
                'name' => 'Sunnyvale Hospital',
                'location' => 'Portland, OR',
                'contact_info' => '+1-555-0125'
            ],
            [
                'name' => 'Heritage General Hospital',
                'location' => 'Las Vegas, NV',
                'contact_info' => '+1-555-0126'
            ],
            [
                'name' => 'Cedar Medical Center',
                'location' => 'Memphis, TN',
                'contact_info' => '+1-555-0127'
            ],
            [
                'name' => 'Mountain General Hospital',
                'location' => 'Louisville, KY',
                'contact_info' => '+1-555-0128'
            ],
            [
                'name' => 'Brookside Hospital',
                'location' => 'Baltimore, MD',
                'contact_info' => '+1-555-0129'
            ],
            [
                'name' => 'Fairview Medical Center',
                'location' => 'Milwaukee, WI',
                'contact_info' => '+1-555-0130'
            ]
        ];

        foreach ($hospitals as $hospital) {
            Hospital::create($hospital);
        }
    }
}
