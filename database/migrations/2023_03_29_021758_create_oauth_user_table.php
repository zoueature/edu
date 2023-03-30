<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_user', function (Blueprint $table) {
            $table->increments('id');
            $table->string('login_type')->nullable(false)->default('')->comment('第三方登录平台, line');
            $table->string('oauth_user_id')->nullable(false)->default('')->comment('第三方用户标识');
            $table->unique(['oauth_user_id', 'login_type'], 'uk_oauth_user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oauth_user');
    }
}
