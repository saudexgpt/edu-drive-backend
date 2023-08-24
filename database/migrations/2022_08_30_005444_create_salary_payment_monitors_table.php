<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalaryPaymentMonitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_payment_monitors', function (Blueprint $table) {
            $table->id();
            $table->integer('school_id');
            $table->integer('staff_id');
            $table->integer('staff_salary_payment_id');
            $table->double('amount_paid', 10, 2);
            $table->integer('recorded_by');
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
        Schema::dropIfExists('salary_payment_monitors');
    }
}
