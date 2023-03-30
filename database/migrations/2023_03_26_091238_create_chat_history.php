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
            $table->string('sender_role', 8)->nullable(false)->default('')->comment('消息发送者角色');
            $table->bigInteger('sender_id')->nullable(false)->default(0)->comment('发送者id');
            $table->string('receiver_role', 8)->nullable(false)->default('')->comment('消息接收者角色');
            $table->bigInteger('receiver_id')->nullable(false)->default(0)->comment('消息接收者id');
            $table->tinyInteger('is_read')->nullable(false)->default(0)->comment('0未读 1已读');
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
