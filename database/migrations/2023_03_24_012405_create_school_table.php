<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 *  学校表
 */
class CreateSchoolTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('school', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name")->nullable(false)->default("")->comment("名称");
            $table->string("country")->nullable(false)->default("")->comment("国家");
            $table->string("province")->nullable(false)->default("")->comment("省份");
            $table->string("city")->nullable(false)->default("")->comment("城市");
            $table->string("address")->nullable(false)->default("")->comment("地址");
            $table->tinyInteger("status")->nullable(false)->default(0)->comment("状态，0待审核，1正常，2封禁");
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
        Schema::dropIfExists('school');
    }
}
