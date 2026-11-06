<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement of Accounts - {{ $doc['customer']['name'] }}</title>
    @include('statement.styles')
</head>
<body>
    @include('statement.document', ['doc' => $doc])
</body>
</html>
