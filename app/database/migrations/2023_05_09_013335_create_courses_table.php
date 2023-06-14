<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string("name")->nullable();
            $table->integer("needed_inspirers")->nullable()->default(6);
            $table->string("location")->nullable()->default("@home");
            $table->mediumText("description");
            $table->unsignedBigInteger('course_type_id');
            $table->integer("duration")->nullable();
            $table->string("image")->nullable();
            $table->timestamp("start_date")->nullable();
            $table->timestamps();

            // $table->index("course_type_id");
            // $table->foreign("course_type_id")->references("id")->on("course_types")->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
};
