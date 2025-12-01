<?php

namespace Database\Seeders;

use App\Models\PermissionGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Contracts\Permission;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // User::factory(10)->create();

    $this->call([
      CompanySeeder::class,
      BranchSeeder::class,
      RolePermissionSeeder::class,
      UserSeeder::class,
      PermissionGroupsSeeder::class,
      MenuSeeder::class,
      CashFlowCategorySeeder::class,
      AccountClassSeeder::class,
      AccountGroupSeeder::class,
      ChartAccountSeeder::class,
      CashCollateralTypeSeeder::class,
      EquityCategorySeeder::class,
      RegionsTableSeeder::class,
      DistrictsTableSeeder::class,
      SupplierSeeder::class,
      FeeSeeder::class,
      FiletypeSeeder::class,
      PermissionGroupSeeder::class,
      BranchUserSeeder::class,
      GroupSeeder::class,
    ]);
  }
}
