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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('age')->nullable();
            $table->string('address')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('password');
            $table->string('last_name');
            $table->string('user_role');
            $table->string('first_name');
            $table->string('contact_number');
            $table->string('email')->unique();
            $table->text('profile_image')->nullable(); // base64 string
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
