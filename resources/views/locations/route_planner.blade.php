<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multi-Stop Route Optimizer | Laravel Geocoder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #198754, #0d6efd);
            color: white;
            padding: 25px;
            border-radius: 15px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.08);
        }
        #routeMap {
            height: 480px;
            border-radius: 12px;
        }
        .waypoint-badge {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
    </style>
</head>
<body>

<div class="container py-4">

    {{-- Header --}}
    <div class="header mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h2 class="mb-1">🚘 Multi-Stop Route Optimizer & Navigation</h2>
                <p class="mb-0">Calculate optimal driving routes across multiple waypoints with instant exports.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('location.index') }}" class="btn btn-light">📍 Geocoder Main</a>
                <a href="{{ route('location.radius-search') }}" class="btn btn-info text-white">🎯 Radius Search</a>
                <a href="{{ route('location.dashboard') }}" class="btn btn-warning">📊 Dashboard</a>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        {{-- Waypoints Config --}}
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">🧭 Select Route Waypoints</h5>
                    <form method="POST" action="{{ route('location.calculate-route') }}" id="routeForm">
                        @csrf

                        <div id="waypointsContainer">
                            @if(isset($waypoints) && count($waypoints) >= 2)
                                @foreach($waypoints as $idx => $wp)
                                    <div class="mb-3 waypoint-row d-flex gap-2 align-items-center">
                                        <span class="waypoint-badge bg-primary">{{ chr(65 + $idx) }}</span>
                                        <select name="waypoints[]" class="form-select" required>
                                            <option value="">Select location...</option>
                                            @foreach($locations as $loc)
                                                <option value="{{ $loc->id }}" @selected($wp->id == $loc->id)>
                                                    {{ $loc->address }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($idx >= 2)
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.waypoint-row').remove(); updateLabels();">❌</button>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                {{-- Waypoint A --}}
                                <div class="mb-3 waypoint-row d-flex gap-2 align-items-center">
                                    <span class="waypoint-badge bg-primary">A</span>
                                    <select name="waypoints[]" class="form-select" required>
                                        <option value="">Start Location (Point A)...</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc->id }}">{{ $loc->address }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Waypoint B --}}
                                <div class="mb-3 waypoint-row d-flex gap-2 align-items-center">
                                    <span class="waypoint-badge bg-primary">B</span>
                                    <select name="waypoints[]" class="form-select" required>
                                        <option value="">Destination (Point B)...</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc->id }}">{{ $loc->address }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="addWaypoint()">➕ Add Intermediate Stop</button>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold">🚀 Calculate Route & Geometry</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Route Map --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">🗺️ Interactive Waypoint Polyline Map</h5>
                    <div id="routeMap"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Route Summary --}}
    @if(isset($waypoints) && isset($segments))
        <div class="card mb-4 border-success">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                    <div>
                        <h4 class="mb-1 text-success">✅ Route Calculated Successfully!</h4>
                        <p class="text-muted mb-0">Driving route covering {{ count($waypoints) }} waypoints.</p>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ $googleMapsUrl }}" target="_blank" class="btn btn-outline-primary">
                            🗺️ Open in Google Maps
                        </a>

                        <form action="{{ route('location.export-gpx') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="waypoint_ids" value="{{ implode(',', array_column($waypoints, 'id')) }}">
                            <button type="submit" class="btn btn-outline-success">📥 Export GPX</button>
                        </form>

                        <form action="{{ route('location.export-kml') }}" method="POST" class="d-inline">
                            @csrf
                            <input type="hidden" name="waypoint_ids" value="{{ implode(',', array_column($waypoints, 'id')) }}">
                            <button type="submit" class="btn btn-outline-info text-white">📄 Export KML</button>
                        </form>
                    </div>
                </div>

                <div class="row text-center g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">TOTAL DISTANCE</div>
                            <div class="fs-2 fw-bold text-primary">{{ $totalDistanceKm }} KM</div>
                            <small class="text-secondary">({{ $totalDistanceMiles }} Miles)</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">ESTIMATED DRIVING TIME</div>
                            <div class="fs-2 fw-bold text-success">{{ $estimatedTimeFormatted }}</div>
                            <small class="text-secondary">(Avg 50 km/h speed)</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">WAYPOINTS COUNT</div>
                            <div class="fs-2 fw-bold text-warning">{{ count($waypoints) }} Stops</div>
                            <small class="text-secondary">Sequentially connected</small>
                        </div>
                    </div>
                </div>

                {{-- Segment Breakdown Table --}}
                <h5 class="mb-3">📊 Route Legs Breakdown</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Leg</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Leg Distance (KM)</th>
                                <th>Leg Distance (Miles)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($segments as $idx => $seg)
                                <tr>
                                    <td><span class="badge bg-secondary">Leg {{ $idx + 1 }}</span></td>
                                    <td><strong>{{ $seg['from']->address }}</strong></td>
                                    <td><strong>{{ $seg['to']->address }}</strong></td>
                                    <td><span class="fw-bold text-primary">{{ $seg['distance_km'] }} km</span></td>
                                    <td><span class="fw-bold text-secondary">{{ $seg['distance_miles'] }} mi</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('routeMap').setView([23.0225, 72.5714], 6);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    @if(isset($waypoints) && count($waypoints) >= 2)
        const waypointsData = @json($waypoints);
        const latLngs = [];
        const bounds = L.latLngBounds();

        waypointsData.forEach((wp, idx) => {
            const lat = parseFloat(wp.latitude);
            const lng = parseFloat(wp.longitude);
            const pt = [lat, lng];
            latLngs.push(pt);
            bounds.extend(pt);

            const letter = String.fromCharCode(65 + idx);
            L.marker(pt)
                .addTo(map)
                .bindPopup(`<strong>Stop ${letter}:</strong> ${escapeHtml(wp.address)}`)
                .openPopup();
        });

        // Polyline connecting route waypoints
        const polyline = L.polyline(latLngs, {
            color: '#198754',
            weight: 5,
            opacity: 0.8,
            dashArray: '10, 10'
        }).addTo(map);

        map.fitBounds(bounds, { padding: [50, 50] });
    @endif

    function addWaypoint() {
        const container = document.getElementById('waypointsContainer');
        const count = container.children.length;
        const letter = String.fromCharCode(65 + count);

        const div = document.createElement('div');
        div.className = 'mb-3 waypoint-row d-flex gap-2 align-items-center';

        let options = '<option value="">Select location...</option>';
        @foreach($locations as $loc)
            options += `<option value="{{ $loc->id }}">{{ addslashes($loc->address) }}</option>`;
        @endforeach

        div.innerHTML = `
            <span class="waypoint-badge bg-primary">${letter}</span>
            <select name="waypoints[]" class="form-select" required>${options}</select>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.waypoint-row').remove(); updateLabels();">❌</button>
        `;

        container.appendChild(div);
    }

    function updateLabels() {
        const rows = document.querySelectorAll('#waypointsContainer .waypoint-row');
        rows.forEach((row, idx) => {
            const badge = row.querySelector('.waypoint-badge');
            if (badge) {
                badge.innerText = String.fromCharCode(65 + idx);
            }
        });
    }

    function escapeHtml(text) {
        return String(text).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
</script>
</body>
</html>
