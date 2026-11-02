<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Credit Note {{ $creditNote->credit_note_number }}</title>
    @include('invoice.designs.classic-styles')
    <style>
        @page { margin: 12mm 11mm 14mm; }
        .inv-doc, .inv-doc div, .inv-doc td, .inv-doc th { font-family: dejavusanscondensed, sans-serif; }
        .inv-doc .items td { white-space: nowrap; font-size: 8pt; }
        .inv-doc .items td.l { white-space: normal; }
    </style>
</head>
<body>
    @include('credit-note.designs.classic', ['creditNote' => $creditNote])
</body>
</html>
