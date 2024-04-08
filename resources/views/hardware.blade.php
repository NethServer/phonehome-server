<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Risultati della ricerca hardware</title>
</head>
<body>
    <h1>Risultati della ricerca hardware</h1>

    <form action="{{ route('hardware') }}" method="POST">
        @csrf
        <input type="text" name="search_term" placeholder="Cerca hardware...">
        <button type="submit">Cerca</button>
    </form>

    @if ($matchingHardware->isEmpty())
        <p>Nessun hardware trovato.</p>
    @else
        <ul>
            @foreach ($matchingHardware as $hardware)
                <li>
                    <strong>Product Name:</strong> {{ $hardware->product_name }} <br>
                    <strong>Manufacturer:</strong> {{ $hardware->manufacturer }} <br>
                    <strong>Processor:</strong> {{ $hardware->processor }} <br>
                    <strong>Vga Controller: </strong> {{ $hardware->vga_controller }} <br>
                    <strong>Usb Controller: </strong> {{ $hardware->usb_controller }} <br>
                    <strong>Pci Bridge: </strong>  {{ $hardware->pci_bridge }} <br>
                    <strong>Stata Controller: </strong> {{ $hardware->stata_controller }} <br>
                    <strong>Communication Controller: </strong> {{ $hardware->communication_controller }} <br>
                    <strong>Scsi Controller: </strong> {{ $hardware->scsi_controller }} <br>
                    <strong>Ethernet: </strong> {{ $hardware->ethernet }} <br>
                </li>
            @endforeach
        </ul>
    @endif
</body>
</html>
