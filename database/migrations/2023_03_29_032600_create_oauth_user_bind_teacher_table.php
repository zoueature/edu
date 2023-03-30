<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthUserBindTeacherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_user_bind_teacher', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('oauth_id')->nullable(false)->default(0)->comment('oauth_user.id');
            $table->bigInteger('teacher_id')->nullable(false)->default(0)->comment('teacher.id');
            $table->unique(['oauth_id', 'teacher_id'], 'idx_oauth_teacher');
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
        Schema::dropIfExists('oauth_user_bind_teacher');
    }
}
