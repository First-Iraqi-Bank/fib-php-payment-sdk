<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('fib_refunds', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payment_id');
                $table->string('fib_trace_id')->nullable();
                $table->string('status')->default('PENDING')->index();
                $table->string('refund_details')->nullable();
                $table->string('refund_failure_reason')->nullable();
                $table->timestamps();

                $table->foreign('payment_id')
                    ->references('id')->on('fib_payments')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

        public function down(): void
        {
            Schema::dropIfExists('fib_refunds');
        }
    };
