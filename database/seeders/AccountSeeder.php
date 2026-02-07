<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    protected $seeds = [
        [
            'name' => 'Tooths Arrr Us',
            'website' => 'toothsarrrus.com',
        ],
        [
            'name' => 'Island City Inc.',
            'website' => 'welcometotheisland.com',
        ],
        [
            'name' => 'Super Smiles',
            'website' => 'supersmiles.com',
        ],
        [
            'name' => 'Acme Ortho',
            'website' => 'acmeortho.com',
        ],
        [
            'name' => 'Applefield Staffing',
            'website' => 'applefieldstaffing.com',
        ],
        [
            'name' => 'Orange City Home Care',
            'website' => 'orangecityhomecare.com',
        ],
        [
            'name' => 'Sunnyside Education Center',
            'website' => 'sunnysidecenters.edu',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run()
    {
        foreach ($this->seeds as $data) {
            Account::create($data);
        }
    }
}
