<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id')->nullable();
            $table->integer('ad_id')->nullable();
            $table->string('job_nature')->nullable();
            $table->string('job_validity')->nullable();
            $table->text('apply_instruction')->nullable();
            $table->date('application_deadline')->nullable();
            $table->string('is_any_where')->nullable();
            $table->string('salary_will_be')->nullable();
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
        Schema::dropIfExists('jobs');
    }
}
