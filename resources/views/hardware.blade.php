<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Hardware</title>
</head>
<body>
    <h1>List of Hardware</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Product Name</th>
                <th>Manufacturer</th>
                <th>Processor</th>
                <th>Vga Controller</th>
                <th>Usb Controller</th>
                <th>Pci Bridge</th>
                <th>Sata Controller</th>
                <th>Communication Controller</th>
                <th>SCSI Controller</th>
                <th>Ethernet</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hardware as $item)
            <tr>
                <td>{{ $item->id}}</td>
                <td>{{ $counter++ }}</td>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->manufacturer }}</td>
                <td>{{ $item->processor }}</td>
                <td>{{ $item->vga_controller }}</td>
                <td>{{ $item->usb_controller }}</td>
                <td>{{ $item->pci_bridge }}</td>
                <td>{{ $item->sata_controller }}</td>
                <td>{{ $item->communication_controller }}</td>
                <td>{{ $item->scsi_controller }}</td>
                <td>{{ $item->ethernet}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
