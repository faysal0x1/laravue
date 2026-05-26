<?php

$migrationsPath = __DIR__.'/database/migrations/';

// Remove old users table to replace it
@unlink($migrationsPath.'0001_01_01_000000_create_users_table.php');

$migrations = [
    '2026_05_08_300001_create_branches_table.php' => <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('branches', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->string('code')->unique();
            \$table->string('phone')->nullable();
            \$table->string('email')->nullable();
            \$table->text('address');
            \$table->decimal('latitude', 10, 7)->nullable();
            \$table->decimal('longitude', 10, 7)->nullable();
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('branches'); }
};
PHP,

    '2026_05_08_300002_create_users_table.php' => <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            \$table->string('name');
            \$table->string('email')->unique();
            \$table->string('phone')->unique()->nullable();
            \$table->timestamp('email_verified_at')->nullable();
            \$table->string('password');
            \$table->enum('type', ['admin', 'manager', 'staff', 'customer', 'merchant', 'rider'])->default('customer');
            \$table->string('avatar')->nullable();
            \$table->boolean('is_active')->default(true);
            \$table->rememberToken();
            \$table->timestamps();
        });
        Schema::create('password_reset_tokens', function (Blueprint \$table) {
            \$table->string('email')->primary();
            \$table->string('token');
            \$table->timestamp('created_at')->nullable();
        });
        Schema::create('sessions', function (Blueprint \$table) {
            \$table->string('id')->primary();
            \$table->foreignId('user_id')->nullable()->index();
            \$table->string('ip_address', 45)->nullable();
            \$table->text('user_agent')->nullable();
            \$table->longText('payload');
            \$table->integer('last_activity')->index();
        });
    }
    public function down(): void {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
PHP,

    '2026_05_08_300003_create_addresses_table.php' => <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('addresses', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete();
            \$table->string('label')->nullable();
            \$table->string('contact_name');
            \$table->string('contact_phone');
            \$table->text('address');
            \$table->string('city');
            \$table->string('state')->nullable();
            \$table->string('postal_code')->nullable();
            \$table->decimal('latitude', 10, 7)->nullable();
            \$table->decimal('longitude', 10, 7)->nullable();
            \$table->boolean('is_default')->default(false);
            \$table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('addresses'); }
};
PHP,

    '2026_05_08_300004_create_merchants_table.php' => <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('merchants', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            \$table->string('business_name');
            \$table->string('trade_license')->nullable();
            \$table->string('website')->nullable();
            \$table->text('business_address')->nullable();
            \$table->decimal('current_balance', 12, 2)->default(0);
            \$table->decimal('pending_balance', 12, 2)->default(0);
            \$table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('merchants'); }
};
PHP,

    '2026_05_08_300005_create_riders_table.php' => <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('riders', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            \$table->string('vehicle_type')->nullable();
            \$table->string('vehicle_number')->nullable();
            \$table->string('driving_license')->nullable();
            \$table->enum('availability_status', ['online', 'offline', 'on_delivery'])->default('offline');
            \$table->decimal('current_latitude', 10, 7)->nullable();
            \$table->decimal('current_longitude', 10, 7)->nullable();
            \$table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('riders'); }
};
PHP,

    '2026_05_08_300006_create_warehouses_and_products_tables.php' => <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('warehouses', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            \$table->string('name');
            \$table->text('address');
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
        });
        Schema::create('products', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            \$table->string('name');
            \$table->string('sku')->unique();
            \$table->text('description')->nullable();
            \$table->decimal('weight', 10, 2)->nullable();
            \$table->decimal('price', 12, 2)->default(0);
            \$table->timestamps();
        });
        Schema::create('product_stocks', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('product_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            \$table->integer('quantity')->default(0);
            \$table->integer('reserved_quantity')->default(0);
            \$table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('products');
        Schema::dropIfExists('warehouses');
    }
};
PHP,

    '2026_05_08_300007_create_shipments_tables.php' => <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('shipments', function (Blueprint \$table) {
            \$table->id();
            \$table->string('tracking_number')->unique();
            \$table->string('invoice_number')->nullable()->unique();
            \$table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            \$table->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();
            \$table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            \$table->foreignId('assigned_rider_id')->nullable()->constrained('riders')->nullOnDelete();
            \$table->enum('delivery_type', ['standard', 'express'])->default('standard');
            \$table->enum('status', ['created', 'pending', 'picked_up', 'dispatched', 'in_transit', 'out_for_delivery', 'delivered', 'cancelled', 'returned'])->default('created');
            \$table->string('sender_name');
            \$table->string('sender_phone');
            \$table->text('pickup_address');
            \$table->string('receiver_name');
            \$table->string('receiver_phone');
            \$table->text('delivery_address');
            \$table->decimal('pickup_latitude', 10, 7)->nullable();
            \$table->decimal('pickup_longitude', 10, 7)->nullable();
            \$table->decimal('delivery_latitude', 10, 7)->nullable();
            \$table->decimal('delivery_longitude', 10, 7)->nullable();
            \$table->decimal('weight', 10, 2)->nullable();
            \$table->decimal('distance_km', 10, 2)->nullable();
            \$table->decimal('delivery_fee', 12, 2)->default(0);
            \$table->decimal('cod_amount', 12, 2)->default(0);
            \$table->timestamp('scheduled_at')->nullable();
            \$table->timestamp('picked_up_at')->nullable();
            \$table->timestamp('delivered_at')->nullable();
            \$table->text('special_instruction')->nullable();
            \$table->timestamps();
        });
        Schema::create('shipment_items', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            \$table->string('item_name');
            \$table->integer('quantity')->default(1);
            \$table->decimal('weight', 10, 2)->nullable();
            \$table->decimal('price', 12, 2)->default(0);
            \$table->timestamps();
        });
        Schema::create('shipment_tracking_histories', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            \$table->string('status');
            \$table->text('description')->nullable();
            \$table->decimal('latitude', 10, 7)->nullable();
            \$table->decimal('longitude', 10, 7)->nullable();
            \$table->timestamps();
        });
        Schema::create('rider_assignments', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('rider_id')->constrained()->cascadeOnDelete();
            \$table->enum('status', ['assigned', 'accepted', 'rejected', 'completed'])->default('assigned');
            \$table->timestamp('accepted_at')->nullable();
            \$table->timestamp('completed_at')->nullable();
            \$table->timestamps();
        });
        Schema::create('delivery_proofs', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            \$table->string('signature_image')->nullable();
            \$table->string('delivery_photo')->nullable();
            \$table->text('note')->nullable();
            \$table->timestamp('delivered_at')->nullable();
            \$table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('delivery_proofs');
        Schema::dropIfExists('rider_assignments');
        Schema::dropIfExists('shipment_tracking_histories');
        Schema::dropIfExists('shipment_items');
        Schema::dropIfExists('shipments');
    }
};
PHP,

    '2026_05_08_300008_create_financial_tables.php' => <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('financial_transactions', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
            \$table->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();
            \$table->foreignId('rider_id')->nullable()->constrained()->nullOnDelete();
            \$table->enum('type', ['income', 'expense', 'commission', 'refund', 'withdraw']);
            \$table->decimal('amount', 12, 2);
            \$table->string('reference')->nullable();
            \$table->text('note')->nullable();
            \$table->timestamps();
        });
        Schema::create('commissions', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('rider_id')->constrained()->cascadeOnDelete();
            \$table->decimal('commission_amount', 12, 2);
            \$table->boolean('is_paid')->default(false);
            \$table->timestamp('paid_at')->nullable();
            \$table->timestamps();
        });
        Schema::create('payment_histories', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
            \$table->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();
            \$table->string('payment_method');
            \$table->string('transaction_id')->nullable();
            \$table->decimal('amount', 12, 2);
            \$table->enum('status', ['pending', 'paid', 'failed'])->default('pending');
            \$table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('payment_histories');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('financial_transactions');
    }
};
PHP,

    '2026_05_08_300009_create_support_and_content_tables.php' => <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('support_tickets', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete();
            \$table->string('subject');
            \$table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            \$table->enum('status', ['open', 'pending', 'resolved', 'closed'])->default('open');
            \$table->text('message');
            \$table->timestamps();
        });
        Schema::create('support_ticket_messages', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            \$table->foreignId('user_id')->constrained()->cascadeOnDelete();
            \$table->text('message');
            \$table->timestamps();
        });
        Schema::create('notifications', function (Blueprint \$table) {
            \$table->uuid('id')->primary();
            \$table->string('type');
            \$table->morphs('notifiable');
            \$table->text('data');
            \$table->timestamp('read_at')->nullable();
            \$table->timestamps();
        });
        Schema::create('notification_templates', function (Blueprint \$table) {
            \$table->id();
            \$table->string('event_key')->unique();
            \$table->string('title');
            \$table->text('sms_template')->nullable();
            \$table->longText('email_template')->nullable();
            \$table->longText('push_template')->nullable();
            \$table->boolean('sms_enabled')->default(true);
            \$table->boolean('email_enabled')->default(true);
            \$table->boolean('push_enabled')->default(true);
            \$table->timestamps();
        });
        Schema::create('blogs', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            \$table->string('title');
            \$table->string('slug')->unique();
            \$table->longText('content');
            \$table->string('thumbnail')->nullable();
            \$table->boolean('is_published')->default(false);
            \$table->timestamp('published_at')->nullable();
            \$table->timestamps();
        });
        Schema::create('faqs', function (Blueprint \$table) {
            \$table->id();
            \$table->string('question');
            \$table->text('answer');
            \$table->integer('sort_order')->default(0);
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
        });
        Schema::create('pages', function (Blueprint \$table) {
            \$table->id();
            \$table->string('title');
            \$table->string('slug')->unique();
            \$table->longText('content');
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
        });
        Schema::create('banners', function (Blueprint \$table) {
            \$table->id();
            \$table->string('title');
            \$table->string('image');
            \$table->string('button_text')->nullable();
            \$table->string('button_link')->nullable();
            \$table->boolean('is_active')->default(true);
            \$table->timestamps();
        });
        Schema::create('api_clients', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            \$table->string('name');
            \$table->string('api_key')->unique();
            \$table->string('api_secret');
            \$table->boolean('is_active')->default(true);
            \$table->timestamp('last_used_at')->nullable();
            \$table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint \$table) {
            \$table->id();
            \$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            \$table->string('action');
            \$table->string('module');
            \$table->text('description')->nullable();
            \$table->ipAddress('ip_address')->nullable();
            \$table->text('user_agent')->nullable();
            \$table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('api_clients');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('support_ticket_messages');
        Schema::dropIfExists('support_tickets');
    }
};
PHP,
];

foreach ($migrations as $filename => $content) {
    file_put_contents($migrationsPath.$filename, $content);
}

echo "Created migrations successfully.\n";
