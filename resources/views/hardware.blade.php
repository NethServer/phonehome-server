<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find Hardware</title>
    <link rel="stylesheet" href= "css/style.css">
    
</head>
<body>
    <div class="container">
    <h1>Find Hardware</h1>

    <form action="{{ route('hardware') }}" method="POST">
        @csrf
        <input type="text" name="search_term" placeholder="Search hardware...">
        <button type="submit">Search</button>
    </form>

    @if (empty($groupedInputMatch))
        <p>No hardware found</p>
    @else
        <ul>
        {{$count}} hardware found <br><br>
        @foreach ($groupedInputMatch as $key => $group)
            <li><strong>{{ $key }}:</strong></li>
            <ul>
                @foreach ($group['rows'] as $row)
                    <li>{{ $row }} ({{ $group['occurrences'][$row] }})</li> 
                @endforeach
            </ul>
        @endforeach
        </ul>
    @endif
</div>
</body>
</html>
