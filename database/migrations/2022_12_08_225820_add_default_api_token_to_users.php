<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use App\Models\System\User;


class AddDefaultApiTokenToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->setTokenUser();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->setTokenUser(false);
    }

    
    /**
     *
     * @param  bool $add_token
     * @return void
     */
    private function setTokenUser($add_token = true)
    {
        $user = User::first();

        if($user)
        {
            $user->api_token = $add_token ? Str::random(60) : null;
            $user->save();
        }
    }

}
