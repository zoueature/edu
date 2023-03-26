<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentFollowTeacherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_follow_teacher', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger("student_id")->nullable(false)->default(0)->comment("学生用户id");
            $table->bigInteger("teacher_id")->nullable(false)->default(0)->comment("教师用户id");
            $table->softDeletes();
            $table->unique(["student_id", "teacher_id"], "uk_student_teacher");
            $table->index(["teacher_id"], "idx_teacher");
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
        Schema::dropIfExists('student_follow_teacher');
    }
}
