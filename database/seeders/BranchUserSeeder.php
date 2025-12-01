<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BranchUser;
use App\Models\User;
use App\Models\Branch;

class BranchUserSeeder extends Seeder
{
    public function run()
    {
        // Get first user and first branch
        $user = User::first();
        $branch = Branch::first();
        if ($user && $branch) {
            BranchUser::create([
                'user_id' => $user->id,
                'branch_id' => $branch->id,
            ]);
        }
    }
}
