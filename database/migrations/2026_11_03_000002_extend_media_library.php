<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->char('source_sha256', 64)->nullable()->unique();
            $table->string('source_mime', 50)->nullable();
            $table->string('processing_status', 20)->default('ready')->index();
            $table->string('error_code', 50)->nullable();
            $table->string('folder', 80)->nullable()->index();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropConstrainedForeignId('uploaded_by');
            $table->dropColumn(['source_sha256', 'source_mime', 'processing_status', 'error_code', 'folder', 'deleted_at']);
        });
    }
};
