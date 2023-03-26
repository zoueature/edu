<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeacherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teacher', function (Blueprint $table) {
            $table->increments('id');
            $table->string("email")->nullbale(false)->default('')->comment('邮箱');
            $table->string('password')->nullbale(false)->default('')->comment('密码');
            $table->string('name')->nullbale(false)->default('')->comment('姓名');
            $table->tinyInteger("status")->nullable(false)->default(0)->comment("状态：0正常, 1封禁");
            $table->unique(['email'], 'uk_email');
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
        Schema::dropIfExists('teacher');
    }
}
