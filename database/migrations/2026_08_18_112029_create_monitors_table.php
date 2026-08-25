<?php

use App\Enums\HttpMethod;
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
        Schema::create('monitors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('url_address')->nullable();
            $table->ipAddress()->nullable();
            $table->unsignedTinyInteger('request_method')->default(HttpMethod::Get);
            $table->json('request_headers')->nullable();
            $table->boolean('is_httpable')->default(true);
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index('is_httpable');
            $table->index('checked_at');
            $table->index('updated_at');
        });

        DB::statement('alter table `monitors` add constraint `monitors_url_or_ip_address` check ((`url_address` is null) <> (`ip_address` is null))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitors');
    }
};
