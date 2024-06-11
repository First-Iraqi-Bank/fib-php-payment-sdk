<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('fib_payments', function (Blueprint $table) {
                $table->id();
                $table->string('fib_payment_id')->unique();
                $table->string('readable_code');
                $table->string('personal_app_link');
                $table->string('status')->index();
                $table->integer('amount');
                $table->dateTime('valid_until');
                $table->timestamps();
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('fib_payments');
        }
    };
