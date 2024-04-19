<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Hardware</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href= "{{ asset('css/select.css') }}">
    <link rel="icon" href="{{ asset('images/logoNethesis.png') }}">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand"><img src="{{ asset('images/logoNethesis.png') }}" alt="Logo Nethesis"></a>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link active" href="{{route('map')}}">Map</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
    <div class="container">
        <h1><strong>Select Hardware<strong></h1>
        <a class="btn btn-primary" href= "{{ route('hardware', ['installation' => 'NethServer'] )}}" role="button"><strong>NethServer</strong></a>
        <a class="btn btn-primary" href= "{{ route('hardware', ['installation' => 'NethSecurity'] )}}" role="button"><strong>NethSecurity</strong></a>
    </div>
</body>
</html>
