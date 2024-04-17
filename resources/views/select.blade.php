<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Hardware</title>
    <link rel="stylesheet" href= "{{ asset('css/select.css') }}">
    <link rel="icon" href="{{ asset('images/logoNethesis.png') }}">
</head>
<body>
    <div class="container">
        <h1>Select Hardware</h1>    
        <a href= "{{ route('hardware', ['installation' => 'NethServer'] )}}"><button><strong>NethServer</strong></button></a>
        <a href= "{{ route('hardware', ['installation' => 'NethSecurity'] )}}"><button><strong>NethSecurity</strong></button></a>
    </div>
</body>
</html>
