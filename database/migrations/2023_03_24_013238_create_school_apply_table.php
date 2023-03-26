<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * 学校申请表
 */
class CreateSchoolApplyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school_apply', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger("apply_teacher_id")->nullable(false)->default(0)->comment('申请人id');
            $table->bigInteger("school_id")->nullable(false)->default(0)->comment('学校id');
            $table->tinyInteger("status")->nullable(false)->default(0)->comment("状态：0待审核,1通过,2拒绝");
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
        Schema::dropIfExists('school_apply');
    }
}
