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

    /*
    | Footer of the statement of accounts: legal entity and bank details.
    */
    'statement' => [
        'legal_name' => env('STATEMENT_LEGAL_NAME', 'M D M PARA PHARMACEUTICAL PRODUCTS TRADING L L C'),
        'address' => env('STATEMENT_ADDRESS', 'Office 153-101 King Mohammed Abdulaziz Mohammed bin Faris - Deira - Al Murar'),
        'trn' => env('STATEMENT_TRN', '104718581200003'),
        'account_no' => env('STATEMENT_ACCOUNT_NO', '097524142748501'),
        'iban' => env('STATEMENT_IBAN', 'AE52 0240 0975 2414 2748 501'),
        'account_name' => env('STATEMENT_ACCOUNT_NAME', 'M D M PARA PHARMACEUTICAL PRODUCTS TRADING L L C'),
        'bank' => env('STATEMENT_BANK', 'Dubai Islamic Bank'),
    ],

    'terms' => [
        'Goods remain the property of the seller until full payment is received.',
        'Customer must sign and stamp the invoice copies to acknowledge receipt of the goods in good condition and in full.',
        'Any discrepancy or damage must be reported within 48 hours of delivery.',
        'Payment must be settled within the agreed payment and credit terms.',
    ],
];
