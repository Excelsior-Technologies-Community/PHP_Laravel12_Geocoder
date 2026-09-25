<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Location Details</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >

    <style>

        body {
            background: #f4f6f9;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,.08);
        }

        #map {
            height: 450px;
            border-radius: 10px;
        }

        .coordinate {
            font-family: monospace;
            font-size: 18px;
        }

    </style>

</head>

<body>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

        <div>

            <h2>
                📍 Location Details
            </h2>

            <p class="text-muted mb-0">
                Detailed information for saved location.
            </p>

        </div>

        <div class="d-flex gap-2">

            <a
                href="{{ route('location.edit', $location) }}"
                class="btn btn-warning"
            >
                ✏️ Edit
            </a>

            <form
                method="POST"
                action="{{ route('location.destroy', $location) }}"
                onsubmit="return confirm('Delete this location?')"
            >

                @csrf

                @method('DELETE')

                <button
                    type="submit"
                    class="btn btn-danger"
                >
                    🗑️ Delete
                </button>

            </form>

            <a
                href="{{ route('location.index') }}"
                class="btn btn-primary"
            >
                ← Back
            </a>

        </div>

    </div>


    @if(session('success'))

        <div class="alert alert-success">

            {{ session('success') }}

        </div>

    @endif


    <div class="card mb-4">

        <div class="card-body">

            <h5 class="mb-3">
                Location Information
            </h5>

            <div class="row g-4">


                <div class="col-md-12">

                    <strong>
                        Address
                    </strong>

                    <div class="form-control bg-light">
                        {{ $location->address }}
                    </div>

                </div>


                <div class="col-md-4">

                    <strong>
                        Location ID
                    </strong>

                    <div class="form-control bg-light">
                        {{ $location->id }}
                    </div>

                </div>


                <div class="col-md-4">

                    <strong>
                        Latitude
                    </strong>

                    <div class="input-group">

                        <input
                            type="text"
                            class="form-control coordinate"
                            value="{{ number_format($location->latitude, 7) }}"
                            readonly
                            id="latitude"
                        >

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="copyCoordinate('latitude')"
                        >
                            📋
                        </button>

                    </div>

                </div>


                <div class="col-md-4">

                    <strong>
                        Longitude
                    </strong>

                    <div class="input-group">

                        <input
                            type="text"
                            class="form-control coordinate"
                            value="{{ number_format($location->longitude, 7) }}"
                            readonly
                            id="longitude"
                        >

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="copyCoordinate('longitude')"
                        >
                            📋
                        </button>

                    </div>

                </div>


                <div class="col-md-6">

                    <strong>
                        Created At
                    </strong>

                    <div class="form-control bg-light">

                        {{ $location->created_at
                            ? $location->created_at->format(
                                'd M Y, h:i A'
                            )
                            : '-' }}

                    </div>

                </div>


                <div class="col-md-6">

                    <strong>
                        Updated At
                    </strong>

                    <div class="form-control bg-light">

                        {{ $location->updated_at
                            ? $location->updated_at->format(
                                'd M Y, h:i A'
                            )
                            : '-' }}

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- Map links --}}

    <div class="card mb-4">

        <div class="card-body">

            <h5 class="mb-3">
                🌍 Open Location
            </h5>

            <div class="d-flex gap-2 flex-wrap">

                <a
                    href="https://www.openstreetmap.org/?mlat={{ $location->latitude }}&mlon={{ $location->longitude }}#map=16/{{ $location->latitude }}/{{ $location->longitude }}"
                    target="_blank"
                    class="btn btn-success"
                >
                    🌍 OpenStreetMap
                </a>


                <a
                    href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}"
                    target="_blank"
                    class="btn btn-primary"
                >
                    📍 Google Maps
                </a>

            </div>

        </div>

    </div>


    {{-- Map --}}

    <div class="card">

        <div class="card-body">

            <h5 class="mb-3">
                🗺️ Location Map
            </h5>

            <div id="map"></div>

        </div>

    </div>

</div>


<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>


<script>

    const latitude =
        {{ $location->latitude }};

    const longitude =
        {{ $location->longitude }};


    const map =
        L.map('map').setView(
            [latitude, longitude],
            14
        );


    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution:
                '© OpenStreetMap contributors'
        }
    ).addTo(map);


    L.marker([
        latitude,
        longitude
    ])
        .addTo(map)
        .bindPopup(
            `<strong>{{ addslashes($location->address) }}</strong>
            <br>
            Latitude: ${latitude}
            <br>
            Longitude: ${longitude}`
        )
        .openPopup();


    function copyCoordinate(id)
    {
        const input =
            document.getElementById(id);

        navigator.clipboard
            .writeText(input.value)
            .then(function() {

                alert(
                    'Copied: ' + input.value
                );

            });
    }

</script>

</body>
</html>