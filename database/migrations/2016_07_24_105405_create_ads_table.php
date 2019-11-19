<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();

            $table->integer('category_id')->nullable();
            $table->integer('sub_category_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->enum('type', ['personal', 'business'])->nullable();
            $table->string('ad_condition')->nullable();
            $table->string('model')->nullable();
            $table->decimal('price', 12,2)->nullable();
            $table->enum('is_negotiable', [0,1])->nullable();

            $table->string('seller_name')->nullable();
            $table->string('seller_email')->nullable();
            $table->string('seller_phone')->nullable();

            $table->integer('country_id')->nullable();
            $table->integer('state_id')->nullable();
            $table->integer('city_id')->nullable();
            $table->string('address')->nullable();
            $table->string('video_url')->nullable();
            $table->string('category_type')->nullable();

            //0 =pending for review, 1= published, 2=blocked, 3=archived
            $table->enum('status', [0,1,2,3]);
            $table->enum('price_plan', ['regular','premium'])->nullable();
            $table->enum('mark_ad_urgent', ['0','1'])->nullable();

            $table->integer('view')->nullable();
            $table->integer('max_impression')->nullable();
            $table->integer('user_id')->nullable();

            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->date('expired_at')->nullable();
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
        Schema::drop('ads');
    }
}
