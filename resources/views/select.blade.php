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
        
        <label for="nethserver_radio" onclick="selectHardware('Nethserver')">Nethserver</label>
        <input type="radio" name="hardware_type" id="nethserver_radio" value="Nethserver">

        <label for="nethsecurity_radio" onclick="selectHardware('Nethsecurity')">Nethsecurity</label>
        <input type="radio" name="hardware_type" id="nethsecurity_radio" value="Nethsecurity">
    </div>

    <form id="hardware_form" action="{{ route('hardware')}}" method="POST">
        @csrf
        <input type="hidden" id="hidden_hardware_type" name="hardware_type" value="">
    </form>

    <script>
    function redirectTo(url, hardwareType) {
        window.location.href = url;
    }

    function selectHardware(hardwareType) {
            document.getElementById('hidden_hardware_type').value = hardwareType;
            document.getElementById('hardware_form').submit();
        }
    </script>

</body>
</html>
