<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->text('request_reason');
            $table->foreignId('status_id')->constrained('attendance_request_statuses')->cascadeOnDelete();
            $table->boolean('is_admin_created')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_requests');
    }
}

