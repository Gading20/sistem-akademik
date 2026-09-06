<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female'])->nullable()->after('nuptk');
            $table->string('nik', 30)->nullable()->after('gender');
            $table->string('place_of_birth', 255)->nullable()->after('subject_id');
            $table->date('date_of_birth')->nullable()->after('place_of_birth');
            $table->string('qualification', 100)->nullable()->after('date_of_birth');
            $table->string('specialization', 100)->nullable()->after('qualification');
            $table->string('phone', 20)->nullable()->after('specialization');
            $table->text('address')->nullable()->after('phone');
            $table->enum('employment_status', ['active', 'inactive', 'retired'])->default('active')->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'nik',
                'place_of_birth',
                'date_of_birth',
                'qualification',
                'specialization',
                'phone',
                'address',
                'employment_status',
            ]);
        });
    }
};
