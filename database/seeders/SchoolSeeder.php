<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        School::updateOrCreate(
            ['schoolname' => 'Yellow Field Fountain Schools'], // use your default school name
            [
                'email' => 'account@schooldrive.com.ng',
                'phone' => '+2347063415220',
                'address' => 'Plot 7, Lekan Oyekunle Str, Meiran Bustop, Agbado-Ijaye, Lagos.',
                'logo_url' => '/images/logo.png',

                'bank1' => 'Zenith Bank',
                'accountname1' => 'Yellow Field Fountain Schools',
                'accountno1' => '0123456789',

                'bank2' => 'Access Bank',
                'accountname2' => 'Yellow Field Fountain Schools',
                'accountno2' => '1234567890',

                'bank3' => 'UBA',
                'accountname3' => 'Yellow Field Fountain Schools',
                'accountno3' => '9876543210',

                'term' => 'First Term',
                'session' => '2025/2026',
            ]
        );
    }
}
