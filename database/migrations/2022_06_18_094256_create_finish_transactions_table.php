<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinishTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finish_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('patient_id')->unsigned()->nullable();
            $table->bigInteger('transaction_id')->unsigned()->nullable();
            $table->string('patient_name')->nullable();
            $table->string('patient_medrec')->nullable();
            $table->string('patient_address')->nullable();
            $table->string('patient_phone')->nullable();
            $table->string('patient_email')->nullable();
            $table->enum('patient_gender', ['M', 'F']);
            $table->date('patient_birthdate');
            $table->bigInteger('room_id')->unsigned()->nullable();
            $table->string('room_name')->nullable();
            $table->bigInteger('doctor_id')->unsigned()->nullable();
            $table->string('doctor_name')->nullable();
            $table->bigInteger('insurance_id')->unsigned()->nullable();
            $table->string('insurance_name')->nullable();
            $table->bigInteger('analyzer_id')->unsigned()->nullable();
            $table->string('analyzer_name')->nullable();
            $table->enum('type',['rawat_inap', 'rawat_jalan', 'igd', 'rujukan']);
            $table->string('no_lab')->nullable();
            $table->text('note')->nullable();
            $table->integer('status')->nullable()->default(0);
            $table->boolean('cito')->nullable();
            $table->integer('check')->nullable();
            $table->integer('draw')->nullable();
            $table->integer('result_status')->nullable();
            $table->integer('verify_status')->nullable();
            $table->integer('validate_status')->nullable();
            $table->integer('report_status')->nullable();
            $table->datetime('checkin_time')->nullable();
            $table->datetime('created_time')->nullable();
            $table->datetime('analytic_time')->nullable();
            $table->datetime('post_time')->nullable();
            $table->text('memo_result')->nullable();
            $table->integer('print')->nullable();
            $table->boolean('get_status')->nullable();
            $table->bigInteger('verficator_id')->unsigned()->nullable();
            $table->string('verficator_name')->nullable();
            $table->bigInteger('validator_id')->unsigned()->nullable();
            $table->string('validator_name')->nullable();
            $table->string('shipper')->nullable();
            $table->string('receiver')->nullable();
            $table->string('no_order')->nullable();
            $table->timestamps();
        });

        Schema::table('finish_transactions', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('insurance_id')->references('id')->on('insurances')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('analyzer_id')->references('id')->on('analyzers')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('finish_transactions');
    }
}
