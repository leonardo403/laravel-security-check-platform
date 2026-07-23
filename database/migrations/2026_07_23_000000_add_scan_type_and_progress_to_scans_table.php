<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->string('scan_type')->default('repository')->after('branch');
            $table->string('env_file_path')->nullable()->after('scan_type');
            $table->string('project_upload_path')->nullable()->after('env_file_path');
            $table->integer('progress')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn(['scan_type', 'env_file_path', 'project_upload_path', 'progress']);
        });
    }
};
