<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Hardware</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            display: flex;
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
        }

        .container {
            max-width: 400px;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        input[type="radio"] {
            display: none;
        }

        label {
            display: block;
            margin-bottom: 10px;
            cursor: pointer;
            padding: 10px 20px;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            transition: background-color 0.3s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Select Hardware</h1>
        
        <label for="nethserver_radio" onclick="redirectTo('/hardware-nethserver')">Nethserver</label>
        <input type="radio" name="hardware_type" id="nethserver_radio" value="nethserver">

        <label for="nethsecurity_radio" onclick="redirectTo('/hardware-nethsecurity')">Nethsecurity</label>
        <input type="radio" name="hardware_type" id="nethsecurity_radio" value="nethsecurity">
    </div>

    <script>
        function redirectTo(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
