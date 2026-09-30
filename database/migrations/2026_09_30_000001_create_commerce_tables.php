<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dummyjson_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->index();
            $table->string('username')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->text('image')->nullable();
            $table->json('company')->nullable();
            $table->json('address')->nullable();
            $table->json('raw_payload');
            $table->timestamp('synced_at');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dummyjson_id')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->index();
            $table->string('brand')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('discount_percentage', 8, 2)->default(0);
            $table->decimal('rating', 4, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->string('availability_status')->nullable();
            $table->text('thumbnail')->nullable();
            $table->json('images')->nullable();
            $table->json('raw_payload');
            $table->timestamp('synced_at');
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dummyjson_id')->unique();
            $table->foreignId('external_user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('dummyjson_user_id')->index();
            $table->decimal('total', 14, 2);
            $table->decimal('discounted_total', 14, 2);
            $table->unsignedInteger('total_products');
            $table->unsignedInteger('total_quantity');
            $table->string('status')->default('imported');
            $table->json('raw_payload');
            $table->timestamp('synced_at');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('dummyjson_product_id')->index();
            $table->string('title');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('total', 14, 2);
            $table->decimal('discount_percentage', 8, 2)->default(0);
            $table->decimal('discounted_total', 14, 2);
            $table->text('thumbnail')->nullable();
            $table->json('raw_payload');
            $table->timestamps();
        });

        Schema::create('external_api_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('resource');
            $table->text('endpoint');
            $table->json('response');
            $table->timestamp('fetched_at');
            $table->timestamps();
            $table->index(['provider', 'resource']);
        });

        Schema::create('data_syncs', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('status')->index();
            $table->json('counts')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_syncs');
        Schema::dropIfExists('external_api_snapshots');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('external_users');
    }
};
