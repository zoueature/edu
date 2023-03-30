<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOauthUserBindStudentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oauth_user_bind_student', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('oauth_id')->nullable(false)->default(0)->comment('oauth_user.id');
            $table->bigInteger('student_id')->nullable(false)->default(0)->comment('student.id');
            $table->index(['oauth_id', 'student_id'], 'idx_oauth_student');
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
        Schema::dropIfExists('oauth_user_bind_student');
    }
}
