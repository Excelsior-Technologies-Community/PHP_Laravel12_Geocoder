<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radius Search Studio | Laravel Geocoder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #0d6efd, #055160);
            color: white;
            padding: 25px;
            border-radius: 15px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.08);
        }
        #radiusMap {
            height: 450px;
            border-radius: 12px;
        }
        .distance-badge {
            font-size: 14px;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container py-4">

    {{-- Header --}}
    <div class="header mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="mb-1">📍 Nearby Places & Radius Search Studio</h2>
                <p class="mb-0">Filter saved locations within a geographic radius overlay.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('location.index') }}" class="btn btn-light">📍 Geocoder Main</a>
                <a href="{{ route('location.route-planner') }}" class="btn btn-warning">🚘 Route Optimizer</a>
                <a href="{{ route('location.dashboard') }}" class="btn btn-info text-white">📊 Dashboard</a>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Search Form --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">🎯 Configure Radius</h5>
                    <form method="GET" action="{{ route('location.radius-search') }}">

                        <div class="mb-3">
                            <label class="form-label">Select Base Location</label>
                            <select name="location_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Custom Coordinates / Address</option>
                                @foreach($locationsList as $loc)
                                    <option value="{{ $loc->id }}" @selected(request('location_id') == $loc->id)>
                                        {{ $loc->address }} ({{ number_format($loc->latitude, 3) }}, {{ number_format($loc->longitude, 3) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Center Latitude</label>
                            <input type="number" step="any" name="center_lat" id="centerLatInput" class="form-control" value="{{ $centerLat }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Center Longitude</label>
                            <input type="number" step="any" name="center_lng" id="centerLngInput" class="form-control" value="{{ $centerLng }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Search Radius (Kilometers)</label>
                            <select name="radius" class="form-select">
                                <option value="1" @selected($radiusKm == 1)>1 KM</option>
                                <option value="5" @selected($radiusKm == 5)>5 KM</option>
                                <option value="10" @selected($radiusKm == 10)>10 KM</option>
                                <option value="25" @selected($radiusKm == 25)>25 KM</option>
                                <option value="50" @selected($radiusKm == 50)>50 KM</option>
                                <option value="100" @selected($radiusKm == 100)>100 KM</option>
                                <option value="250" @selected($radiusKm == 250)>250 KM</option>
                                <option value="500" @selected($radiusKm == 500)>500 KM</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">🔍 Search Radius</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="useCurrentLocation()">🌐 Use My Device Location</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Map View --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">🗺️ Visual Radius Circle Overlay</h5>
                        <span class="badge bg-success fs-6">{{ count($nearbyLocations) }} Places within {{ $radiusKm }} KM</span>
                    </div>
                    <div id="radiusMap"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="card">
        <div class="card-body p-4">
            <h5 class="card-title mb-3">📍 Matching Locations inside {{ $radiusKm }} KM Radius</h5>
            @if(count($nearbyLocations) > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Address</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Distance (KM)</th>
                                <th>Distance (Miles)</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nearbyLocations as $index => $loc)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $loc->address }}</strong></td>
                                    <td class="font-monospace">{{ number_format($loc->latitude, 6) }}</td>
                                    <td class="font-monospace">{{ number_format($loc->longitude, 6) }}</td>
                                    <td><span class="badge bg-primary distance-badge">{{ $loc->distance_km }} km</span></td>
                                    <td><span class="badge bg-secondary distance-badge">{{ $loc->distance_miles }} mi</span></td>
                                    <td>
                                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $loc->latitude }},{{ $loc->longitude }}" target="_blank" class="btn btn-sm btn-outline-success">🗺️ Directions</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center py-4 mb-0">
                    ℹ️ No saved locations found within <strong>{{ $radiusKm }} KM</strong> of selected point. Try increasing radius.
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const centerLat = {{ $centerLat }};
    const centerLng = {{ $centerLng }};
    const radiusMeters = {{ $radiusKm * 1000 }};

    const map = L.map('radiusMap').setView([centerLat, centerLng], {{ $radiusKm <= 10 ? 12 : ($radiusKm <= 50 ? 10 : 7) }});

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Center marker
    const centerIcon = L.icon({
        iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });

    L.marker([centerLat, centerLng], { icon: centerIcon })
        .addTo(map)
        .bindPopup("<strong>🎯 Center Point</strong><br>" + @json($centerAddress))
        .openPopup();

    // Radius Circle
    L.circle([centerLat, centerLng], {
        color: '#0d6efd',
        fillColor: '#0d6efd',
        fillOpacity: 0.15,
        radius: radiusMeters
    }).addTo(map);

    // Nearby markers
    const nearbyLocations = @json($nearbyLocations);
    const bounds = L.latLngBounds([[centerLat, centerLng]]);

    nearbyLocations.forEach(loc => {
        const lat = parseFloat(loc.latitude);
        const lng = parseFloat(loc.longitude);
        bounds.extend([lat, lng]);

        L.marker([lat, lng])
            .addTo(map)
            .bindPopup(`<strong>📍 ${escapeHtml(loc.address)}</strong><br>Distance: <b>${loc.distance_km} km</b> (${loc.distance_miles} mi)`);
    });

    if (nearbyLocations.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }

    function escapeHtml(text) {
        return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function useCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(position => {
                document.getElementById('centerLatInput').value = position.coords.latitude;
                document.getElementById('centerLngInput').value = position.coords.longitude;
                document.querySelector('form').submit();
            }, () => {
                alert('Unable to retrieve your current location.');
            });
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    }
</script>
</body>
</html>
