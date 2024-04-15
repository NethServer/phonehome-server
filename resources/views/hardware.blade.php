<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hardware</title>
    <link rel="stylesheet" href= "css/hardware.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

</head>
<body>
<a href="/select" class="dropdown-item">Search Hardware</a>
<div class="scrollable-content" style="overflow-y: scroll; max-height: 700px;">
    <div class="container">
    <h1 style="font-size: 24px; margin-bottom: 5px;">Find {{$hardware_type}} Hardware</h1>
    <p style="margin-bottom: 40px; opacity: 0.6;">Enter a search term in the input box below to find {{$hardware_type}} hardware matching your requirements.</p>

    <form action="{{ route('hardware') }}" method="GET">
        @csrf
        <input type="text" name="search_term" id="search_term" placeholder="Search hardware...">
        <button type="submit">Search</button>
    </form>

    @if (empty($groupedInputMatch))
        <p>No hardware found</p>
    @else
        <ul>
        {{$count}} hardware found <br><br>
        @foreach ($groupedInputMatch as $key => $group)
            <button class="accordion" style="background-color: #f9f9f9; color: #333; cursor: pointer; padding: 18px; width: 100%; text-align: left; border: none; outline: none; transition: 0.4s; margin-bottom: 10px;"
            onmouseover="this.style.backgroundColor='#ddd'" onmouseout="this.style.backgroundColor='#f9f9f9'"><h2 style="text-align: left; left; margin-left: 5px; font-size: 18px; margin-bottom: 10px;"><strong>{{ $key }}:</strong></h2>
            <span><i class="bi bi-caret-down-fill" style="font-size: 12px; float: right; margin-top: -25px;"></i></span></button>
            <div class="panel" style="padding: 0 18px; background-color: white; display: none; overflow: hidden;">
            <ul>
                @foreach ($group['rows'] as $row)
                    <li>{{ $row }} <span style="float: right;">({{ $group['occurrences'][$row] }})</span></li> 
                @endforeach
            </ul>
            </div>
        @endforeach
        </ul>
    @endif
    </div>
</div>
<script>
    var accordions = document.getElementsByClassName("accordion");
    var i;

    for (i = 0; i < accordions.length; i++) {
        accordions[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel.style.display === "block") {
                panel.style.display = "none";
            } else {
                panel.style.display = "block";
            }
        });
    }
</script>
</body>
</html>
