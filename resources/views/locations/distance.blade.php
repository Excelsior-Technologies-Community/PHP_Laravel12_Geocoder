<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Location Distance Calculator</title>

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

        .distance-result {
            font-size: 36px;
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
                    📏 Location Distance Calculator
                </h2>

                <p class="mb-0">
                    Calculate the distance between two saved locations.
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


    <!-- Error -->

    @if($errors->any())

        <div class="alert alert-danger">

            @foreach($errors->all() as $error)

                <div>
                    {{ $error }}
                </div>

            @endforeach

        </div>

    @endif


    <!-- Calculator -->

    <div class="card mb-4">

        <div class="card-body">

            <h5 class="mb-4">
                Select Two Locations
            </h5>

            <form
                method="POST"
                action="{{ route('location.distance.calculate') }}"
            >

                @csrf

                <div class="row g-4">

                    <div class="col-md-5">

                        <label class="form-label">
                            Location One
                        </label>

                        <select
                            name="location_one"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select first location
                            </option>

                            @foreach($locations as $location)

                                <option
                                    value="{{ $location->id }}"
                                    @selected(
                                        isset($locationOne) &&
                                        $locationOne->id == $location->id
                                    )
                                >
                                    {{ $location->address }}
                                    ({{ number_format($location->latitude, 4) }},
                                    {{ number_format($location->longitude, 4) }})
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-md-2 d-flex align-items-end justify-content-center">

                        <div class="fs-2">
                            ↔
                        </div>

                    </div>


                    <div class="col-md-5">

                        <label class="form-label">
                            Location Two
                        </label>

                        <select
                            name="location_two"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select second location
                            </option>

                            @foreach($locations as $location)

                                <option
                                    value="{{ $location->id }}"
                                    @selected(
                                        isset($locationTwo) &&
                                        $locationTwo->id == $location->id
                                    )
                                >
                                    {{ $location->address }}
                                    ({{ number_format($location->latitude, 4) }},
                                    {{ number_format($location->longitude, 4) }})
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-12">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            📏 Calculate Distance
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- Result -->

    @isset($distance)

        <div class="card">

            <div class="card-body text-center">

                <h5>
                    Distance Result
                </h5>

                <hr>

                <div class="row align-items-center">

                    <div class="col-md-5">

                        <h6>
                            Location One
                        </h6>

                        <p class="fw-bold">
                            {{ $locationOne->address }}
                        </p>

                        <small class="text-muted">

                            {{ number_format($locationOne->latitude, 7) }},
                            {{ number_format($locationOne->longitude, 7) }}

                        </small>

                    </div>


                    <div class="col-md-2">

                        <div class="distance-result text-primary">

                            {{ number_format($distance, 2) }}

                        </div>

                        <div class="text-muted">
                            kilometers
                        </div>

                    </div>


                    <div class="col-md-5">

                        <h6>
                            Location Two
                        </h6>

                        <p class="fw-bold">
                            {{ $locationTwo->address }}
                        </p>

                        <small class="text-muted">

                            {{ number_format($locationTwo->latitude, 7) }},
                            {{ number_format($locationTwo->longitude, 7) }}

                        </small>

                    </div>

                </div>

            </div>

        </div>

    @endisset


    @if($locations->count() < 2)

        <div class="alert alert-warning mt-4">

            You need at least
            <strong>2 saved locations</strong>
            to calculate distance.

            Add more addresses from the
            <a href="{{ route('location.index') }}">
                Geocoder page
            </a>.

        </div>

    @endif

</div>

</body>
</html>