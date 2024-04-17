<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Hardware</title>
    <link rel="stylesheet" href= "css/select.css">
</head>
<body>
    <div class="container">
        <h1>Select Hardware</h1>    
        <button> <a href= "{{ route('hardware', ['installation' => 'NethServer'] )}}"><strong>NethServer</strong></a></button>
        <button> <a href= "{{ route('hardware', ['installation' => 'NethSecurity'] )}}"><strong>NethSecurity</strong></a></button>
    </div>
</body>
</html>
