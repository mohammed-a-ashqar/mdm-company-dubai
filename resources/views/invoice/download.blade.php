<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice {{ $doc['number'] }}</title>
    @include('invoice.designs.classic-styles')
</head>
<body>
    @include('invoice.designs.classic', ['doc' => $doc])
</body>
</html>
