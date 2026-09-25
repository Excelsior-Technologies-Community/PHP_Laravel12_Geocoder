<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Location Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f4f7fb;
            font-family: Arial, sans-serif;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #0d6efd, #084298);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 25px;
        }

        .stat-card {
            border: none;
            border-radius: 15px;
            padding: 22px;
            background: white;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.08);
            height: 100%;
        }

        .stat-title {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 30px;
            font-weight: bold;
            color: #212529;
        }

        .section-card {
            border: none;
            border-radius: 15px;
            background: white;
            box-shadow: 0 5px 18px rgba(0, 0, 0, 0.07);
        }

        .table th {
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
        }

        .coordinate {
            font-family: monospace;
            font-size: 13px;
        }

        .action-btn {
            margin: 2px;
        }

        .copy-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: none;
        }
    </style>
</head>

<body>

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="dashboard-header">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>
                <h1 class="mb-2">
                    📍 Location Dashboard
                </h1>

                <p class="mb-0">
                    Monitor geocoded locations, coordinates and location activity.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">

                <a
                    href="{{ route('location.index') }}"
                    class="btn btn-light"
                >
                    📍 Manage Locations
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
                    📥 CSV
                </a>

                <a
                    href="{{ route('location.export.json') }}"
                    class="btn btn-info text-white"
                >
                    📄 JSON
                </a>

            </div>

        </div>

    </div>


    {{-- Success Message --}}
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


    {{-- Error Message --}}
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


    {{-- Statistics --}}
    <div class="row g-4 mb-4">

        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-title">
                    📍 Total Locations
                </div>

                <div class="stat-value">
                    {{ $totalLocations }}
                </div>

                <small class="text-muted">
                    All saved locations
                </small>
            </div>
        </div>


        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-title">
                    📅 Added Today
                </div>

                <div class="stat-value text-success">
                    {{ $todayLocations }}
                </div>

                <small class="text-muted">
                    Locations created today
                </small>
            </div>
        </div>


        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-title">
                    🔤 Unique Addresses
                </div>

                <div class="stat-value text-primary">
                    {{ $uniqueAddresses }}
                </div>

                <small class="text-muted">
                    Unique saved addresses
                </small>
            </div>
        </div>


        <div class="col-md-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-title">
                    🗺️ Map Records
                </div>

                <div class="stat-value text-warning">
                    {{ $totalLocations }}
                </div>

                <small class="text-muted">
                    Available on map
                </small>
            </div>
        </div>

    </div>


    {{-- Coordinate Statistics --}}
    <div class="row g-4 mb-4">

        <div class="col-md-6">

            <div class="stat-card">

                <div class="stat-title">
                    🌐 Average Latitude
                </div>

                <div class="stat-value">
                    {{ $averageLatitude !== null ? number_format($averageLatitude, 6) : 'N/A' }}
                </div>

                <small class="text-muted">
                    Average latitude of saved locations
                </small>

            </div>

        </div>


        <div class="col-md-6">

            <div class="stat-card">

                <div class="stat-title">
                    🌐 Average Longitude
                </div>

                <div class="stat-value">
                    {{ $averageLongitude !== null ? number_format($averageLongitude, 6) : 'N/A' }}
                </div>

                <small class="text-muted">
                    Average longitude of saved locations
                </small>

            </div>

        </div>

    </div>


    {{-- Timeline --}}
    <div class="section-card mb-4">

        <div class="card-body p-4">

            <h4 class="mb-4">
                🕒 Location Timeline
            </h4>

            <div class="row g-4">

                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <div class="text-muted small">
                            First Location
                        </div>

                        @if($firstLocation)

                            <h5 class="mt-2 mb-1">
                                {{ $firstLocation->address }}
                            </h5>

                            <div class="small text-muted">
                                {{ $firstLocation->created_at?->format('d M Y, h:i A') }}
                            </div>

                        @else

                            <span class="text-muted">
                                No locations available.
                            </span>

                        @endif

                    </div>

                </div>


                <div class="col-md-6">

                    <div class="border rounded p-3">

                        <div class="text-muted small">
                            Latest Location
                        </div>

                        @if($latestLocation)

                            <h5 class="mt-2 mb-1">
                                {{ $latestLocation->address }}
                            </h5>

                            <div class="small text-muted">
                                {{ $latestLocation->created_at?->format('d M Y, h:i A') }}
                            </div>

                        @else

                            <span class="text-muted">
                                No locations available.
                            </span>

                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Latest Locations --}}
    <div class="section-card">

        <div class="card-body p-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">

                <div>
                    <h4 class="mb-1">
                        📍 Latest Locations
                    </h4>

                    <p class="text-muted mb-0">
                        Recently added geocoded locations.
                    </p>
                </div>

                <div class="d-flex gap-2">

                    <a
                        href="{{ route('location.index') }}"
                        class="btn btn-primary"
                    >
                        View All
                    </a>

                    <a
                        href="{{ route('location.export') }}"
                        class="btn btn-success"
                    >
                        📥 Export CSV
                    </a>

                    <a
                        href="{{ route('location.export.json') }}"
                        class="btn btn-info text-white"
                    >
                        📄 Export JSON
                    </a>

                </div>

            </div>


            @if($latestLocations->count() > 0)

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-dark">

                            <tr>
                                <th>ID</th>
                                <th>Address</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Created</th>
                                <th class="text-center">Actions</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach($latestLocations as $location)

                                <tr>

                                    {{-- ID --}}
                                    <td>
                                        <strong>
                                            #{{ $location->id }}
                                        </strong>
                                    </td>


                                    {{-- Address --}}
                                    <td>

                                        <div class="fw-semibold">
                                            {{ $location->address }}
                                        </div>

                                    </td>


                                    {{-- Latitude --}}
                                    <td>

                                        <div class="coordinate">
                                            {{ number_format((float) $location->latitude, 6) }}
                                        </div>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary mt-1"
                                            onclick="copyCoordinate(
                                                '{{ $location->latitude }}',
                                                this
                                            )"
                                        >
                                            📋 Copy
                                        </button>

                                    </td>


                                    {{-- Longitude --}}
                                    <td>

                                        <div class="coordinate">
                                            {{ number_format((float) $location->longitude, 6) }}
                                        </div>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary mt-1"
                                            onclick="copyCoordinate(
                                                '{{ $location->longitude }}',
                                                this
                                            )"
                                        >
                                            📋 Copy
                                        </button>

                                    </td>


                                    {{-- Created --}}
                                    <td>

                                        <div>
                                            {{ $location->created_at?->format('d M Y') }}
                                        </div>

                                        <small class="text-muted">
                                            {{ $location->created_at?->format('h:i A') }}
                                        </small>

                                    </td>


                                    {{-- Actions --}}
                                    <td class="text-center">

                                        <div class="d-flex flex-wrap justify-content-center">

                                            {{-- View --}}
                                            <a
                                                href="{{ route('location.show', $location) }}"
                                                class="btn btn-sm btn-primary action-btn"
                                                title="View Location"
                                            >
                                                👁️
                                            </a>


                                            {{-- Edit --}}
                                            <a
                                                href="{{ route('location.edit', $location) }}"
                                                class="btn btn-sm btn-warning action-btn"
                                                title="Edit Location"
                                            >
                                                ✏️
                                            </a>


                                            {{-- OpenStreetMap --}}
                                            <a
                                                href="https://www.openstreetmap.org/?mlat={{ $location->latitude }}&mlon={{ $location->longitude }}#map=16/{{ $location->latitude }}/{{ $location->longitude }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="btn btn-sm btn-success action-btn"
                                                title="Open in OpenStreetMap"
                                            >
                                                🌍
                                            </a>


                                            {{-- Google Maps --}}
                                            <a
                                                href="https://www.google.com/maps/search/?api=1&query={{ $location->latitude }},{{ $location->longitude }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="btn btn-sm btn-info text-white action-btn"
                                                title="Open in Google Maps"
                                            >
                                                🗺️
                                            </a>


                                            {{-- Delete --}}
                                            <form
                                                action="{{ route('location.destroy', $location) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this location?');"
                                            >

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-danger action-btn"
                                                    title="Delete Location"
                                                >
                                                    🗑️
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="text-center py-5">

                    <div style="font-size: 50px;">
                        📍
                    </div>

                    <h5 class="mt-3">
                        No locations found
                    </h5>

                    <p class="text-muted">
                        Start by adding your first location.
                    </p>

                    <a
                        href="{{ route('location.index') }}"
                        class="btn btn-primary"
                    >
                        Add Location
                    </a>

                </div>

            @endif

        </div>

    </div>

</div>


{{-- Copy Success Message --}}
<div
    id="copyMessage"
    class="alert alert-success copy-message shadow"
>
    ✅ Copied to clipboard!
</div>


<script>

    function copyCoordinate(value, button) {

        const originalText = button.innerHTML;

        if (navigator.clipboard && window.isSecureContext) {

            navigator.clipboard.writeText(value)
                .then(function () {

                    showCopyMessage();

                    button.innerHTML = '✅ Copied';

                    setTimeout(function () {
                        button.innerHTML = originalText;
                    }, 1500);

                })
                .catch(function () {

                    fallbackCopy(value, button, originalText);

                });

        } else {

            fallbackCopy(value, button, originalText);

        }
    }


    function fallbackCopy(value, button, originalText) {

        const textarea = document.createElement('textarea');

        textarea.value = value;

        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';

        document.body.appendChild(textarea);

        textarea.select();

        try {

            document.execCommand('copy');

            showCopyMessage();

            button.innerHTML = '✅ Copied';

            setTimeout(function () {
                button.innerHTML = originalText;
            }, 1500);

        } catch (error) {

            alert('Unable to copy coordinate.');

        }

        document.body.removeChild(textarea);
    }


    function showCopyMessage() {

        const message = document.getElementById('copyMessage');

        message.style.display = 'block';

        setTimeout(function () {

            message.style.display = 'none';

        }, 1800);

    }

</script>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>
</html>