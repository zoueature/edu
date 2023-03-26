<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger("school_id")->nullbale(false)->default(0)->comment('学校id');
            $table->string("username")->nullbale(false)->default('')->comment('用户名');
            $table->string('password')->nullbale(false)->default('')->comment('密码');
            $table->string('name')->nullbale(false)->default(0)->comment('姓名');
            $table->string('age')->nullbale(false)->default(0)->comment('年龄');
            $table->string('grade')->nullbale(false)->default(0)->comment('年段');
            $table->string('class')->nullbale(false)->default(0)->comment('班级');
            $table->tinyInteger("status")->nullable(false)->default(0)->comment("状态：0正常, 1封禁");
            $table->index(['school_id'], 'idx_school');
            $table->unique(['username'], 'uk_username');
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
        Schema::dropIfExists('student');
    }
}
