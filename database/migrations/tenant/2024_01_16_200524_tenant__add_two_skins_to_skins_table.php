<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantAddTwoSkinsToSkinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('skins')->insert([
            ['name' => 'Black', 'filename' => 'black.css'],
            ['name' => 'Modern', 'filename' => 'modern.css'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('skins')->where('name', 'Black')->where('filename', 'black.css')->delete();
        DB::table('skins')->where('name', 'Modern')->where('filename', 'modern.css')->delete();
    }
}
