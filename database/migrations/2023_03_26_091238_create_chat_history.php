<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_history', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('teacher_id')->nullable(false)->default(0)->comment('教师id');
            $table->bigInteger('student_id')->nullable(false)->default(0)->comment('学生id');
            $table->string('msg', 2000)->nullable(false)->default('')->comment('消息内容');
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
        //
    }
}
