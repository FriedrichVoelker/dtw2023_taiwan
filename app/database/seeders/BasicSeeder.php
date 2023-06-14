<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;



class BasicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){

        // # Remove old roles/permissions

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Schema::disableForeignKeyConstraints();
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        Role::truncate();
        Permission::truncate();
        Schema::enableForeignKeyConstraints();




        Role::create(["name" => "developer"]);
        Role::create(["name" => "admin"]);
        Role::create(["name" => "inspirer"]);



    }
}
