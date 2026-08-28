<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->unsignedBigInteger('oauth_user_id')->nullable()->unique();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->boolean('auto_assigned')->default(false)->after('model_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
