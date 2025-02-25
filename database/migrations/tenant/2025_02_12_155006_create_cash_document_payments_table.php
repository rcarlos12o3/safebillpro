<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashDocumentPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_document_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cash_id')->nullable();
            $table->foreign('cash_id')->references('id')->on('cash');
            $table->unsignedInteger('document_payment_id')->nullable();
            $table->foreign('document_payment_id')->references('id')->on('document_payments');
            $table->unsignedInteger('sale_note_payment_id')->nullable();
            $table->foreign('sale_note_payment_id')->references('id')->on('sale_note_payments');
            $table->unsignedInteger('cash_document_id')->nullable();
            $table->foreign('cash_document_id')->references('id')->on('cash_documents');
            $table->unsignedInteger('cash_document_credit_id')->nullable();
            $table->foreign('cash_document_credit_id')->references('id')->on('cash_document_credits');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_document_payments');
    }
}
