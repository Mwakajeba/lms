<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Filetype;

class FiletypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filetypes = [
            'Passport',
            'National ID',
            'Driver License',
            'Proof of Residence',
            'Proof of Income',
            'Birth Certificate',
            'Company Registration',
        ];

        foreach ($filetypes as $type) {
            Filetype::create(['name' => $type]);
        }
    }
}
