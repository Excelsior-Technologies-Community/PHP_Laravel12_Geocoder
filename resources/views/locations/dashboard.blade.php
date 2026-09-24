<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Location Analytics Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f4f6f9;
        }

        .header {
            background: #0d6efd;
            color: white;
            border-radius: 12px;
            padding: 25px;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,.08);
        }

        .stat-number {
            font-size: 30px;
            font-weight: 700;
        }

    </style>

</head>

<body>

<div class="container py-4">

    <!-- Header -->

    <div class="header mb-4">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h2>
                    📊 Location Analytics Dashboard
                </h2>

                <p class="mb-0">
                    Statistics and activity overview of geocoded locations.
                </p>

            </div>

            <a
                href="{{ route('location.index') }}"
                class="btn btn-light"
            >
                ← Back to Geocoder
            </a>

        </div>

    </div>


    <!-- Statistics -->

    <div class="row g-4 mb-4">

        <div class="col-md-3">

            <div class="card stat-card">

                <div class="card-body">

                    <h6 class="text-muted">
                        Total Locations
                    </h6>

                    <div class="stat-number text-primary">
                        {{ $totalLocations }}
                    </div>

                    <small class="text-muted">
                        All saved geocoder records
                    </small>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card stat-card">

                <div class="card-body">

                    <h6 class="text-muted">
                        Added Today
                    </h6>

                    <div class="stat-number text-success">
                        {{ $locationsToday }}
                    </div>

                    <small class="text-muted">
                        Locations created today
                    </small>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card stat-card">

                <div class="card-body">

                    <h6 class="text-muted">
                        Unique Addresses
                    </h6>

                    <div class="stat-number text-warning">
                        {{ $uniqueAddresses }}
                    </div>

                    <small class="text-muted">
                        Distinct address records
                    </small>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card stat-card">

                <div class="card-body">

                    <h6 class="text-muted">
                        Map Records
                    </h6>

                    <div class="stat-number text-info">
                        {{ $totalLocations }}
                    </div>

                    <small class="text-muted">
                        Saved coordinate points
                    </small>

                </div>

            </div>

        </div>

    </div>


    <!-- Coordinate Statistics -->

    <div class="row g-4 mb-4">

        <div class="col-md-6">

            <div class="card stat-card">

                <div class="card-body">

                    <h5>
                        🌐 Average Coordinates
                    </h5>

                    <hr>

                    <p class="mb-2">

                        <strong>Average Latitude:</strong>

                        @if($averageLatitude !== null)

                            {{ number_format($averageLatitude, 7) }}

                        @else

                            N/A

                        @endif

                    </p>

                    <p class="mb-0">

                        <strong>Average Longitude:</strong>

                        @if($averageLongitude !== null)

                            {{ number_format($averageLongitude, 7) }}

                        @else

                            N/A

                        @endif

                    </p>

                </div>

            </div>

        </div>


        <div class="col-md-6">

            <div class="card stat-card">

                <div class="card-body">

                    <h5>
                        📅 Location Timeline
                    </h5>

                    <hr>

                    <p class="mb-2">

                        <strong>First Location:</strong>

                        @if($firstLocation)

                            {{ $firstLocation->created_at->format('d M Y, h:i A') }}

                        @else

                            N/A

                        @endif

                    </p>

                    <p class="mb-0">

                        <strong>Latest Location:</strong>

                        @if($latestLocation)

                            {{ $latestLocation->created_at->format('d M Y, h:i A') }}

                        @else

                            N/A

                        @endif

                    </p>

                </div>

            </div>

        </div>

    </div>


    <!-- Recent Locations -->

    <div class="card stat-card">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">
                    🕒 Recent Geocoded Locations
                </h5>

                <a
                    href="{{ route('location.export') }}"
                    class="btn btn-success btn-sm"
                >
                    📥 Export CSV
                </a>

            </div>

            <div class="table-responsive">

                <table class="table table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>

                            <th>Address</th>

                            <th>Latitude</th>

                            <th>Longitude</th>

                            <th>Created</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($latestLocations as $location)

                        <tr>

                            <td>
                                {{ $location->id }}
                            </td>

                            <td>
                                {{ $location->address }}
                            </td>

                            <td>
                                {{ number_format($location->latitude, 7) }}
                            </td>

                            <td>
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
                                class="text-center"
                            >
                                No locations available.
                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>