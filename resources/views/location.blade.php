<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Laravel Geocoder</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Leaflet -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <style>

        body {
            background: #f4f6f9;
        }

        .page-header {
            background: #0d6efd;
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        #map {
            height: 450px;
            border-radius: 10px;
        }

        .coordinate {
            font-family: monospace;
        }

        .table th {
            white-space: nowrap;
        }

    </style>

</head>

<body>

<div class="container py-4">

    <!-- Header -->
    <div class="page-header">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>
                <h2 class="mb-1">
                    Laravel Geocoder
                </h2>

                <p class="mb-0">
                    Convert addresses into geographic coordinates
                    using OpenStreetMap.
                </p>
            </div>

            <div class="d-flex gap-2 flex-wrap">

                <a
                    href="{{ route('location.dashboard') }}"
                    class="btn btn-light"
                >
                    📊 Dashboard
                </a>

                <a
                    href="{{ route('location.distance') }}"
                    class="btn btn-warning"
                >
                    📏 Distance
                </a>

                <a
                    href="{{ route('location.export') }}"
                    class="btn btn-success"
                >
                    📥 Export CSV
                </a>

            </div>

        </div>

    </div>


    <!-- Messages -->

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    @if($errors->any())

        <div class="alert alert-danger">

            <strong>Please fix the following:</strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    <!-- Geocoder Form -->

    <div class="card mb-4">

        <div class="card-body">

            <h5 class="card-title">
                📍 Geocode New Address
            </h5>

            <form
                method="POST"
                action="{{ route('location.store') }}"
            >

                @csrf

                <div class="input-group">

                    <input
                        type="text"
                        name="address"
                        class="form-control"
                        value="{{ old('address') }}"
                        placeholder="Enter address e.g. Ahmedabad, Gujarat"
                        required
                    >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Get Coordinates
                    </button>

                </div>

            </form>

        </div>

    </div>


    <!-- Search & Filters -->

    <div class="card mb-4">

        <div class="card-body">

            <h5 class="mb-3">
                🔎 Search & Filter Locations
            </h5>

            <form
                method="GET"
                action="{{ route('location.index') }}"
            >

                <div class="row g-3">

                    <!-- Search -->

                    <div class="col-md-4">

                        <label class="form-label">
                            Address Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Search address..."
                        >

                    </div>


                    <!-- Latitude Minimum -->

                    <div class="col-md-2">

                        <label class="form-label">
                            Min Latitude
                        </label>

                        <input
                            type="number"
                            step="any"
                            name="latitude_min"
                            class="form-control"
                            value="{{ request('latitude_min') }}"
                            placeholder="e.g. 20"
                        >

                    </div>


                    <!-- Latitude Maximum -->

                    <div class="col-md-2">

                        <label class="form-label">
                            Max Latitude
                        </label>

                        <input
                            type="number"
                            step="any"
                            name="latitude_max"
                            class="form-control"
                            value="{{ request('latitude_max') }}"
                            placeholder="e.g. 25"
                        >

                    </div>


                    <!-- Longitude Minimum -->

                    <div class="col-md-2">

                        <label class="form-label">
                            Min Longitude
                        </label>

                        <input
                            type="number"
                            step="any"
                            name="longitude_min"
                            class="form-control"
                            value="{{ request('longitude_min') }}"
                            placeholder="e.g. 70"
                        >

                    </div>


                    <!-- Longitude Maximum -->

                    <div class="col-md-2">

                        <label class="form-label">
                            Max Longitude
                        </label>

                        <input
                            type="number"
                            step="any"
                            name="longitude_max"
                            class="form-control"
                            value="{{ request('longitude_max') }}"
                            placeholder="e.g. 75"
                        >

                    </div>


                    <!-- Date From -->

                    <div class="col-md-3">

                        <label class="form-label">
                            Date From
                        </label>

                        <input
                            type="date"
                            name="date_from"
                            class="form-control"
                            value="{{ request('date_from') }}"
                        >

                    </div>


                    <!-- Date To -->

                    <div class="col-md-3">

                        <label class="form-label">
                            Date To
                        </label>

                        <input
                            type="date"
                            name="date_to"
                            class="form-control"
                            value="{{ request('date_to') }}"
                        >

                    </div>


                    <!-- Buttons -->

                    <div class="col-md-6 d-flex align-items-end gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            🔎 Apply Filters
                        </button>

                        <a
                            href="{{ route('location.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            Clear Filters
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- Locations Table -->

    <div class="card mb-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">
                    📍 Saved Locations
                </h5>

                <span class="badge bg-primary">
                    {{ $locations->total() }} Results
                </span>

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>#</th>

                            <th>Address</th>

                            <th>Latitude</th>

                            <th>Longitude</th>

                            <th>Created</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($locations as $location)

                        <tr>

                            <td>
                                {{ $location->id }}
                            </td>

                            <td>
                                {{ $location->address }}
                            </td>

                            <td class="coordinate">
                                {{ number_format($location->latitude, 7) }}
                            </td>

                            <td class="coordinate">
                                {{ number_format($location->longitude, 7) }}
                            </td>

                            <td>
                                {{ $location->created_at->format('d M Y') }}
                            </td>

                            <td>

                                <a
                                    href="{{ route('location.show', $location) }}"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    View
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="text-center py-4"
                            >
                                No locations found.
                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            <!-- Pagination -->

            <div class="mt-3">

                {{ $locations->links() }}

            </div>

        </div>

    </div>


    <!-- Map -->

    <div class="card">

        <div class="card-body">

            <h5 class="mb-3">
                🗺️ Location Map
            </h5>

            <div id="map"></div>

        </div>

    </div>

</div>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


<!-- Leaflet JS -->

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>


<script>

    const locations = @json($mapLocations);

    let map = L.map('map').setView(
        [23.0225, 72.5714],
        6
    );

    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution: '© OpenStreetMap contributors'
        }
    ).addTo(map);


    locations.forEach(function (location) {

        if (
            location.latitude !== null &&
            location.longitude !== null
        ) {

            const marker = L.marker([
                location.latitude,
                location.longitude
            ]).addTo(map);

            marker.bindPopup(`
                <strong>${escapeHtml(location.address)}</strong>
                <br>
                Latitude:
                ${Number(location.latitude).toFixed(7)}
                <br>
                Longitude:
                ${Number(location.longitude).toFixed(7)}
                <br><br>
                <a
                    href="/location/${location.id}"
                    class="btn btn-sm btn-primary"
                >
                    View Details
                </a>
            `);

        }

    });


    function escapeHtml(text) {

        const div = document.createElement('div');

        div.textContent = text;

        return div.innerHTML;
    }

</script>

</body>
</html>