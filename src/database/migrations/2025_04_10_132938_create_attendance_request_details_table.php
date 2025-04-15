<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_request_id')->constrained()->onDelete('cascade');
            $table->time('before_clock_in')->nullable();
            $table->time('after_clock_in')->nullable();
            $table->time('before_clock_out')->nullable();
            $table->time('after_clock_out')->nullable();
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
        Schema::dropIfExists('attendance_request_details');
    }
}
