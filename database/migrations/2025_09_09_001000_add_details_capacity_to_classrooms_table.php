<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            if (!Schema::hasColumn('classrooms', 'details')) {
                $table->text('details')->nullable()->after('name');
            }
            if (!Schema::hasColumn('classrooms', 'max_size')) {
                $table->unsignedSmallInteger('max_size')->nullable()->after('details');
            }
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            if (Schema::hasColumn('classrooms', 'max_size')) {
                $table->dropColumn('max_size');
            }
            if (Schema::hasColumn('classrooms', 'details')) {
                $table->dropColumn('details');
            }
        });
    }
};

