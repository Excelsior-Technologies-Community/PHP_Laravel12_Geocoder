<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Location</title>

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
            padding: 25px;
            border-radius: 12px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,.08);
        }

        .coordinate {
            font-family: monospace;
        }

    </style>

</head>

<body>

<div class="container py-4">

    <div class="header mb-4">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h2>
                    ✏️ Edit Location
                </h2>

                <p class="mb-0">
                    Update the address and automatically re-geocode it.
                </p>

            </div>

            <a
                href="{{ route('location.index') }}"
                class="btn btn-light"
            >
                ← Back
            </a>

        </div>

    </div>


    @if(session('error'))

        <div class="alert alert-danger">

            {{ session('error') }}

        </div>

    @endif


    @if($errors->any())

        <div class="alert alert-danger">

            @foreach($errors->all() as $error)

                <div>
                    {{ $error }}
                </div>

            @endforeach

        </div>

    @endif


    <div class="card">

        <div class="card-body">

            <h5 class="mb-4">
                Location Information
            </h5>


            <form
                method="POST"
                action="{{ route('location.update', $location) }}"
            >

                @csrf

                @method('PUT')


                <div class="mb-4">

                    <label class="form-label">
                        Address
                    </label>

                    <input
                        type="text"
                        name="address"
                        class="form-control"
                        value="{{ old('address', $location->address) }}"
                        required
                    >

                    <div class="form-text">

                        Changing the address will automatically
                        generate new latitude and longitude.

                    </div>

                </div>


                <div class="row g-3 mb-4">

                    <div class="col-md-4">

                        <label class="form-label">
                            Location ID
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $location->id }}"
                            readonly
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Current Latitude
                        </label>

                        <input
                            type="text"
                            class="form-control coordinate"
                            value="{{ number_format($location->latitude, 7) }}"
                            readonly
                        >

                    </div>


                    <div class="col-md-4">

                        <label class="form-label">
                            Current Longitude
                        </label>

                        <input
                            type="text"
                            class="form-control coordinate"
                            value="{{ number_format($location->longitude, 7) }}"
                            readonly
                        >

                    </div>

                </div>


                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        🔄 Update & Re-Geocode
                    </button>

                    <a
                        href="{{ route('location.show', $location) }}"
                        class="btn btn-outline-secondary"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</body>
</html>