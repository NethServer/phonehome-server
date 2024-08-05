<!doctype html>
<html lang="{{ str_replace('-', '_', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="h-screen flex justify-center items-center">
    <h1 class="text-4xl">Hello World!</h1>
</div>
</body>
</html>
