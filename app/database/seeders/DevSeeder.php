<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){

        # Remove old Data

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Schema::disableForeignKeyConstraints();
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        Role::truncate();
        Permission::truncate();
        User::truncate();
        Schema::enableForeignKeyConstraints();




        Role::create(["name" => "developer"]);
        Role::create(["name" => "admin"]);
        Role::create(["name" => "inspirer"]);

        $dev = User::create([
            "name" => "Dev",
            "email" => "dev@example.com",
            "password" => Hash::make("password"),
            "address" => "Teststreet 1337",
            "zip" => "00000",
            "city" => "/dev/null",
            "phone" => "0000000000",
        ]);

        $test = User::create([
            "name" => "Test",
            "email" => "test@example.com",
            "password" => Hash::make("password"),
            "address" => "Teststreet 1337",
            "zip" => "00000",
            "city" => "/dev/null",
            "phone" => "0000000000",
        ]);


        $dev->assignRole("developer");
        $test->assignRole("inspirer");
    }
}
