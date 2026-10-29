<?php

/*
| Seller (MDM) details printed on every tax invoice / credit note.
| Override any line from .env without touching the templates.
*/
return [
    'seller' => [
        'name' => env('INVOICE_SELLER_NAME', 'MDM Derma'),
        'name_ar' => env('INVOICE_SELLER_NAME_AR', 'إم دي إم ديرما'),
        'address' => env('INVOICE_SELLER_ADDRESS', 'Office 153-101, King Mohammed Abdulaziz Mohammed bin Faris, Al Murar, Deira, Dubai, United Arab Emirates'),
        'trn' => env('INVOICE_SELLER_TRN', '104718581200003'),
        'phone' => env('INVOICE_SELLER_PHONE'),
        'email' => env('INVOICE_SELLER_EMAIL'),
        'website' => env('INVOICE_SELLER_WEBSITE', 'mdmderma.com'),
    ],

    'currency' => env('INVOICE_CURRENCY', 'AED'),

    'terms' => [
        'Goods remain the property of the seller until full payment is received.',
        'Customer must sign and stamp the invoice copies to acknowledge receipt of the goods in good condition and in full.',
        'Any discrepancy or damage must be reported within 48 hours of delivery.',
        'Payment must be settled within the agreed payment and credit terms.',
    ],
];
