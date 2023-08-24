<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryScalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_scales', function (Blueprint $table) {
            $table->id();
            $table->integer('school_id');
            $table->string('name');
            $table->double('salary', 10, 2);
            $table->timestamps();
        });
        Schema::create('salary_scale_staff', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('salary_scale_id');

            $table->foreign('salary_scale_id')->references('id')->on('salary_scales')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->primary(['salary_scale_id', 'staff_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_scales');
        Schema::dropIfExists('salary_scale_staff');
    }
}
