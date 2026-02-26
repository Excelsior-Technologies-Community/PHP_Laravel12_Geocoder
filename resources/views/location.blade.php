<!DOCTYPE html>
<html>
<head>
    <title>Laravel Geocoder</title>

    <!-- Leaflet Map -->
    <link rel="stylesheet"
     href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>

body{
    font-family:Arial;
    background:#f4f6f9;
    padding:40px;
}

.container{
    max-width:900px;
    margin:auto;
    background:white;
    padding:30px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,.1);
}

h2{text-align:center;}

form{
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

input{
    flex:1;
    padding:12px;
    border:1px solid #ccc;
    border-radius:6px;
}

button{
    background:#1677ff;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:6px;
    cursor:pointer;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

th{
    background:#1677ff;
    color:white;
    padding:12px;
}

td{
    padding:10px;
    text-align:center;
    border-bottom:1px solid #ddd;
}

#map{
    height:400px;
    margin-top:25px;
    border-radius:8px;
}

.success{background:#d4edda;padding:10px;margin-bottom:10px;}
.error{background:#f8d7da;padding:10px;margin-bottom:10px;}

</style>
</head>

<body>

<div class="container">

<h2>Laravel Geocoder</h2>

@if(session('success'))
<div class="success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="error">{{ session('error') }}</div>
@endif

<form method="POST" action="{{ route('location.store') }}">
    @csrf
    <input type="text" name="address"
           placeholder="Enter Address (Example: Ahmedabad)" required>
    <button>Get Coordinates</button>
</form>

<table>
<tr>
    <th>Address</th>
    <th>Latitude</th>
    <th>Longitude</th>
</tr>

@foreach($locations as $loc)
<tr>
    <td>{{ $loc->address }}</td>
    <td>{{ $loc->latitude }}</td>
    <td>{{ $loc->longitude }}</td>
</tr>
@endforeach

</table>

<h3>Location Map</h3>
<div id="map"></div>

</div>

<script>

const locations = @json($locations);

let map = L.map('map').setView([23.0225,72.5714],6);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© OpenStreetMap'
}).addTo(map);

locations.forEach(loc => {

    if(loc.latitude && loc.longitude){
        L.marker([loc.latitude, loc.longitude])
            .addTo(map)
            .bindPopup(
                `<b>${loc.address}</b><br>
                 Lat: ${loc.latitude}<br>
                 Lng: ${loc.longitude}`
            );
    }

});

</script>

</body>
</html>