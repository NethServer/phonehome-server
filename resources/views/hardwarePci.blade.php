<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hardware PCI</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/hardware.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="icon" href="{{ asset('images/logoNethesis.png')}}">
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
        <li class="nav-item">
          <a class="nav-link active" href="{{route('select-hardware')}}">Select Hardware</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div class="scrollable-content">
    <div class="container">
    <h1>Find {{$installation}} Hardware PCI</h1>
    <p>Enter the Vendor ID and Device ID in the input fields below to find {{$installation}} PCI hardware matching your requirements.</p>

    <form action="{{ route('hardware-pci', ['installation' => $installation]) }}" method="GET">
        <div class="btn-group">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Search by PCI id
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item" href="{{ route('hardware', ['installation' => $installation]) }}">Search by name</a>
              <a class="dropdown-item search-by-id" href="{{ route('hardware-pci', ['installation' => $installation]) }}">Search by PCI id</a>
            </div>
        </div>
        @csrf
        <input type="text" name="pci_search" id="pci_search" placeholder="Search hardware PCI (xxxx:xxxx) ...">
        <button type="submit">Search</button>
    </form>

    @if($pciSearch === null || $pciSearch = '')
      <p></p>
    @elseif (isset($error))
    <div class="alert alert-danger" role="alert">
        {{ $error }}
    </div>
    @elseif (empty($pciHardware))
        <p>No hardware found</p>
    @else
        <p>{{$count}} hardware found </p>
        @foreach ($pciHardware as $key => $hardware)
        <div class="card">
        <ul class="list-group list-group-flush">
            <li calss="list-group-item">
            <div class="card-header">
                <strong> Hardware occurences ({{ $hardwareCounts[$key] }})</strong><br>
            </div>
                <br>
                <li class="list-group-item"><strong><label>Class ID:</label></strong> {{ $hardware->class_id }}</li><br>
                <li class="list-group-item"><strong>Vendor ID:</strong> {{ $hardware->vendor_id }}</li><br>
                <li class="list-group-item"><strong>Device ID:</strong> {{ $hardware->device_id }}</li><br>
                <li class="list-group-item"><strong>Class Name:</strong> {{ $hardware->class_name }}</li><br>
                <li class="list-group-item"><strong>Vendor Name:</strong> {{ $hardware->vendor_name }}</li><br>
                <li class="list-group-item"><strong>Device Name:</strong> {{ $hardware->device_name }}</li><br>
                <li class="list-group-item"><strong>Driver:</strong> {{ $hardware->driver ?? 'N/A' }}</li><br>
                <br>
            </li>
        </ul>
        </div>
        <br>
        @endforeach
    @endif
</div>
</body>
</html>
