<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('event'); // parcel.created, parcel.delivered, etc.
            $table->string('channel'); // email, sms, push
            $table->string('subject')->nullable(); // for email
            $table->text('body');                  // supports variables like {{recipient_name}}
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['event', 'channel', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
