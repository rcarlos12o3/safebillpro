<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CashDocumentPayment extends ModelTenant
{
    
    protected $fillable = [
        'cash_id',
        'document_payment_id',
        'sale_note_payment_id',
        'cash_document_id',
        'cash_document_credit_id'
    ];

    public function cash()
    {
        return $this->belongsTo(Cash::class);
    }



}
