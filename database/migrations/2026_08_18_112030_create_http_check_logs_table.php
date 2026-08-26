<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('http_check_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('monitor_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at');
            $table->smallInteger('status_code');
            $table->boolean('is_successful')->storedAs('status_code between 200 and 299');
            $table->integer('response_time_ms');
            $table->smallInteger('dns_time_ms')->nullable();
            $table->smallInteger('tcp_time_ms')->nullable();
            $table->smallInteger('tls_time_ms')->nullable();
            $table->string('error_message')->nullable();
            $table->json('response_headers')->nullable();
            $table->json('request_headers')->nullable();

            $table->index(['is_successful', 'created_at']);
            $table->index(['monitor_id', 'created_at']);
            $table->index(['monitor_id', 'status_code']);
            $table->index(['monitor_id', 'is_successful']);
        });

        DB::statement('alter table `http_check_logs` add constraint `http_check_logs_status_code_range` check (`status_code` between 100 and 999)');
        DB::statement('alter table `http_check_logs` add constraint `http_check_logs_error_message_not_successful` check (`error_message` is null or `status_code` not between 200 and 299)');
        DB::statement('alter table `http_check_logs` add constraint `http_check_logs_response_headers_max_length` check (`response_headers` is null or char_length(cast(`response_headers` as char)) <= 5000)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('http_check_logs');
    }
};
