<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Laravel Geocoder</title>

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
            background: #f4f7fb;
            font-family: Arial, sans-serif;
        }

        .main-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .page-header {
            background: linear-gradient(
                135deg,
                #0d6efd,
                #084298
            );

            color: white;
            border-radius: 15px;
            padding: 25px;
        }

        .stat-box {
            background: white;
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        #map {
            height: 500px;
            border-radius: 12px;
        }

        .coordinate {
            font-family: monospace;
            font-size: 13px;
        }

        .action-btn {
            margin: 2px;
        }

        /*
        |--------------------------------------------------------------------------
        | Toast
        |--------------------------------------------------------------------------
        */

        .toast-container-custom {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
        }

        .custom-toast {
            min-width: 320px;
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.18);
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        .pagination .page-link {
            min-width: 40px;
            text-align: center;
        }

    </style>

</head>

<body>

<div class="container-fluid py-4">

    {{-- ================================================================
         HEADER
    ================================================================= --}}

    <div class="page-header mb-4">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <div>

                <h1 class="mb-2">
                    🌍 Laravel Geocoder
                </h1>

                <p class="mb-0">
                    Convert addresses into geographic coordinates.
                </p>

            </div>

            <div class="d-flex flex-wrap gap-2">

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
                    href="{{ route('location.export', request()->query()) }}"
                    class="btn btn-success"
                >
                    📥 CSV
                </a>

                <a
                    href="{{ route('location.export.json', request()->query()) }}"
                    class="btn btn-info text-white"
                >
                    📄 JSON
                </a>

            </div>

        </div>

    </div>


    {{-- ================================================================
         SUCCESS / ERROR TOAST
    ================================================================= --}}

    @if(session('success'))

        <div class="toast-container-custom">

            <div
                class="toast show custom-toast border-0"
                role="alert"
            >

                <div class="toast-header bg-success text-white">

                    <strong class="me-auto">
                        ✅ Success
                    </strong>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        onclick="closeToast(this)"
                    ></button>

                </div>

                <div class="toast-body bg-white">
                    {{ session('success') }}
                </div>

            </div>

        </div>

    @endif


    @if(session('error'))

        <div class="toast-container-custom">

            <div
                class="toast show custom-toast border-0"
                role="alert"
            >

                <div class="toast-header bg-danger text-white">

                    <strong class="me-auto">
                        ❌ Error
                    </strong>

                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        onclick="closeToast(this)"
                    ></button>

                </div>

                <div class="toast-body bg-white">
                    {{ session('error') }}
                </div>

            </div>

        </div>

    @endif


    {{-- ================================================================
         VALIDATION ERRORS
    ================================================================= --}}

    @if($errors->any())

        <div class="alert alert-danger">

            <strong>
                Please fix the following:
            </strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- ================================================================
         GEOCODE NEW ADDRESS
    ================================================================= --}}

    <div class="card main-card mb-4">

        <div class="card-body p-4">

            <h4 class="mb-3">
                📍 Geocode New Address
            </h4>

            <form
                action="{{ route('location.store') }}"
                method="POST"
            >

                @csrf

                <div class="row g-3 align-items-end">

                    <div class="col-md-10">

                        <label class="form-label">
                            Address
                        </label>

                        <input
                            type="text"
                            name="address"
                            class="form-control"
                            placeholder="Enter address..."
                            value="{{ old('address') }}"
                            required
                        >

                    </div>

                    <div class="col-md-2">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >
                            🌍 Geocode
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ================================================================
         SEARCH & FILTER
    ================================================================= --}}

    <div class="card main-card mb-4">

        <div class="card-body p-4">

            <h4 class="mb-4">
                🔎 Search & Filter
            </h4>

            <form
                method="GET"
                action="{{ route('location.index') }}"
            >

                <div class="row g-3">

                    <div class="col-md-4">

                        <label class="form-label">
                            Address
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Search address..."
                        >

                    </div>


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
                        >

                    </div>


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
                        >

                    </div>


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
                        >

                    </div>


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
                        >

                    </div>


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


                    <div class="col-md-3">

                        <label class="form-label">
                            Records Per Page
                        </label>

                        <select
                            name="per_page"
                            class="form-select"
                        >

                            @foreach([5, 10, 25, 50] as $number)

                                <option
                                    value="{{ $number }}"
                                    {{ $perPage == $number ? 'selected' : '' }}
                                >
                                    {{ $number }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="col-md-3 d-flex align-items-end gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary flex-grow-1"
                        >
                            🔎 Filter
                        </button>

                        <a
                            href="{{ route('location.index') }}"
                            class="btn btn-secondary"
                        >
                            Reset
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ================================================================
         SAVED LOCATIONS
    ================================================================= --}}

    <div class="card main-card mb-4">

        <div class="card-body p-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">

                <div>

                    <h4 class="mb-1">
                        📍 Saved Locations
                    </h4>

                    <span class="text-muted">
                        <strong>{{ $locations->total() }}</strong>
                        Results
                    </span>

                </div>

                <div class="d-flex gap-2">

                    <button
                        type="button"
                        class="btn btn-outline-primary btn-sm"
                        onclick="selectAllLocations()"
                    >
                        ☑️ Select All
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-secondary btn-sm"
                        onclick="clearSelections()"
                    >
                        Clear
                    </button>

                </div>

            </div>


            {{-- ============================================================
                 BULK DELETE FORM
            ============================================================= --}}

            <form
                id="bulkDeleteForm"
                action="{{ route('location.bulk-delete') }}"
                method="POST"
                onsubmit="return confirmBulkDelete()"
            >

                @csrf


                <div class="d-flex justify-content-between align-items-center mb-3">

                    <div>

                        <span
                            id="selectedCount"
                            class="badge bg-secondary"
                        >
                            0 selected
                        </span>

                    </div>


                    <button
                        type="submit"
                        id="bulkDeleteButton"
                        class="btn btn-danger btn-sm"
                        disabled
                    >
                        🗑️ Delete Selected
                    </button>

                </div>


                @if($locations->count() > 0)

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-dark">

                                <tr>

                                    <th width="40">

                                        <input
                                            type="checkbox"
                                            id="selectAllCheckbox"
                                            class="form-check-input"
                                            onclick="toggleAll(this)"
                                        >

                                    </th>


                                    {{-- ID --}}
                                    <th>

                                        @php
                                            $idDirection =
                                                ($sort === 'id' && $direction === 'asc')
                                                    ? 'desc'
                                                    : 'asc';
                                        @endphp

                                        <a
                                            href="{{ route('location.index', array_merge(request()->except('page'), [
                                                'sort' => 'id',
                                                'direction' => $idDirection
                                            ])) }}"
                                            class="text-white text-decoration-none"
                                        >
                                            ID
                                            {{ $sort === 'id' ? ($direction === 'asc' ? '↑' : '↓') : '↕' }}
                                        </a>

                                    </th>


                                    {{-- Address --}}
                                    <th>

                                        @php
                                            $addressDirection =
                                                ($sort === 'address' && $direction === 'asc')
                                                    ? 'desc'
                                                    : 'asc';
                                        @endphp

                                        <a
                                            href="{{ route('location.index', array_merge(request()->except('page'), [
                                                'sort' => 'address',
                                                'direction' => $addressDirection
                                            ])) }}"
                                            class="text-white text-decoration-none"
                                        >
                                            Address
                                            {{ $sort === 'address' ? ($direction === 'asc' ? '↑' : '↓') : '↕' }}
                                        </a>

                                    </th>


                                    {{-- Latitude --}}
                                    <th>

                                        @php
                                            $latitudeDirection =
                                                ($sort === 'latitude' && $direction === 'asc')
                                                    ? 'desc'
                                                    : 'asc';
                                        @endphp

                                        <a
                                            href="{{ route('location.index', array_merge(request()->except('page'), [
                                                'sort' => 'latitude',
                                                'direction' => $latitudeDirection
                                            ])) }}"
                                            class="text-white text-decoration-none"
                                        >
                                            Latitude
                                            {{ $sort === 'latitude' ? ($direction === 'asc' ? '↑' : '↓') : '↕' }}
                                        </a>

                                    </th>


                                    {{-- Longitude --}}
                                    <th>

                                        @php
                                            $longitudeDirection =
                                                ($sort === 'longitude' && $direction === 'asc')
                                                    ? 'desc'
                                                    : 'asc';
                                        @endphp

                                        <a
                                            href="{{ route('location.index', array_merge(request()->except('page'), [
                                                'sort' => 'longitude',
                                                'direction' => $longitudeDirection
                                            ])) }}"
                                            class="text-white text-decoration-none"
                                        >
                                            Longitude
                                            {{ $sort === 'longitude' ? ($direction === 'asc' ? '↑' : '↓') : '↕' }}
                                        </a>

                                    </th>


                                    {{-- Created --}}
                                    <th>

                                        @php
                                            $createdDirection =
                                                ($sort === 'created_at' && $direction === 'asc')
                                                    ? 'desc'
                                                    : 'asc';
                                        @endphp

                                        <a
                                            href="{{ route('location.index', array_merge(request()->except('page'), [
                                                'sort' => 'created_at',
                                                'direction' => $createdDirection
                                            ])) }}"
                                            class="text-white text-decoration-none"
                                        >
                                            Created
                                            {{ $sort === 'created_at' ? ($direction === 'asc' ? '↑' : '↓') : '↕' }}
                                        </a>

                                    </th>


                                    <th>
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach($locations as $location)

                                    <tr>

                                        {{-- Checkbox --}}
                                        <td>

                                            <input
                                                type="checkbox"
                                                name="location_ids[]"
                                                value="{{ $location->id }}"
                                                class="form-check-input location-checkbox"
                                                onclick="updateSelectedCount()"
                                            >

                                        </td>


                                        {{-- ID --}}
                                        <td>

                                            <strong>
                                                {{ $location->id }}
                                            </strong>

                                        </td>


                                        {{-- Address --}}
                                        <td>

                                            <strong>
                                                {{ $location->address }}
                                            </strong>

                                        </td>


                                        {{-- Latitude --}}
                                        <td>

                                            <div class="coordinate">
                                                {{ number_format((float) $location->latitude, 6) }}
                                            </div>

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary mt-1"
                                                onclick="copyValue(
                                                    '{{ $location->latitude }}',
                                                    'Latitude',
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
                                                onclick="copyValue(
                                                    '{{ $location->longitude }}',
                                                    'Longitude',
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
                                        <td>

                                            <div class="d-flex flex-wrap">

                                                {{-- View --}}
                                                <a
                                                    href="{{ route('location.show', $location) }}"
                                                    class="btn btn-sm btn-primary action-btn"
                                                    title="View"
                                                >
                                                    👁️
                                                </a>


                                                {{-- Edit --}}
                                                <a
                                                    href="{{ route('location.edit', $location) }}"
                                                    class="btn btn-sm btn-warning action-btn"
                                                    title="Edit"
                                                >
                                                    ✏️
                                                </a>


                                                {{-- OpenStreetMap --}}
                                                <a
                                                    href="https://www.openstreetmap.org/?mlat={{ $location->latitude }}&mlon={{ $location->longitude }}#map=16/{{ $location->latitude }}/{{ $location->longitude }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="btn btn-sm btn-success action-btn"
                                                    title="OpenStreetMap"
                                                    onclick="showSuccessToast('Opening OpenStreetMap...')"
                                                >
                                                    🌍
                                                </a>


                                                {{-- Google Maps --}}
                                                <a
                                                    href="https://www.google.com/maps/search/?api=1&query={{ $location->latitude }},{{ $location->longitude }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="btn btn-sm btn-info text-white action-btn"
                                                    title="Google Maps"
                                                    onclick="showSuccessToast('Opening Google Maps...')"
                                                >
                                                    🗺️
                                                </a>


                                                {{-- Delete --}}
                                                <form
                                                    action="{{ route('location.destroy', $location) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirmDelete()"
                                                >

                                                    @csrf

                                                    @method('DELETE')

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-danger action-btn"
                                                        title="Delete"
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


                    {{-- ====================================================
                         NUMERIC PAGINATION ONLY
                    ===================================================== --}}

                    @if($locations->hasPages())

                        <nav class="mt-4">

                            <ul class="pagination justify-content-center">

                                @for(
                                    $page = 1;
                                    $page <= $locations->lastPage();
                                    $page++
                                )

                                    <li
                                        class="page-item
                                        {{ $page == $locations->currentPage() ? 'active' : '' }}"
                                    >

                                        <a
                                            class="page-link"
                                            href="{{ $locations->url($page) }}"
                                        >
                                            {{ $page }}
                                        </a>

                                    </li>

                                @endfor

                            </ul>

                        </nav>

                    @endif

                @else

                    <div class="text-center py-5">

                        <div style="font-size: 55px;">
                            📍
                        </div>

                        <h5 class="mt-3">
                            No locations found.
                        </h5>

                        <p class="text-muted">
                            Add a new address above to start geocoding.
                        </p>

                    </div>

                @endif

            </form>

        </div>

    </div>


    {{-- ================================================================
         MAP
    ================================================================= --}}

    <div class="card main-card">

        <div class="card-body p-4">

            <h4 class="mb-3">
                🗺️ Location Map
            </h4>

            <div id="map"></div>

        </div>

    </div>

</div>


{{-- ================================================================
     LEAFLET
================================================================= --}}

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
></script>


<script>

    /*
    |--------------------------------------------------------------------------
    | Map
    |--------------------------------------------------------------------------
    */

    const defaultLatitude = 23.0225;
    const defaultLongitude = 72.5714;

    const map = L.map('map').setView(
        [
            defaultLatitude,
            defaultLongitude
        ],
        6
    );


    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,
            attribution:
                '&copy; OpenStreetMap contributors'
        }
    ).addTo(map);


    const locations = @json($mapLocations);

    const markers = [];


    /*
    |--------------------------------------------------------------------------
    | Escape HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }


    /*
    |--------------------------------------------------------------------------
    | Add markers
    |--------------------------------------------------------------------------
    */

    locations.forEach(function(location) {

        const latitude =
            parseFloat(location.latitude);

        const longitude =
            parseFloat(location.longitude);


        if (
            Number.isNaN(latitude) ||
            Number.isNaN(longitude)
        ) {
            return;
        }


        const marker = L.marker([
            latitude,
            longitude
        ]).addTo(map);


        const address =
            escapeHtml(location.address);


        marker.bindPopup(`
            <div style="min-width:220px">

                <strong>
                    ${address}
                </strong>

                <hr>

                <div>
                    <strong>Latitude:</strong>
                    ${latitude.toFixed(6)}
                </div>

                <div>
                    <strong>Longitude:</strong>
                    ${longitude.toFixed(6)}
                </div>

                <br>

                <a
                    href="/location/${location.id}"
                    class="btn btn-sm btn-primary"
                >
                    View Details
                </a>

                <a
                    href="https://www.openstreetmap.org/?mlat=${latitude}&mlon=${longitude}#map=16/${latitude}/${longitude}"
                    target="_blank"
                    class="btn btn-sm btn-success"
                >
                    OSM
                </a>

            </div>
        `);


        markers.push(marker);

    });


    /*
    |--------------------------------------------------------------------------
    | Auto-fit map
    |--------------------------------------------------------------------------
    */

    if (markers.length > 0) {

        const group =
            L.featureGroup(markers);

        map.fitBounds(
            group.getBounds().pad(0.15),
            {
                maxZoom: 15
            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Select all
    |--------------------------------------------------------------------------
    */

    function toggleAll(masterCheckbox) {

        const checkboxes =
            document.querySelectorAll(
                '.location-checkbox'
            );

        checkboxes.forEach(function(checkbox) {

            checkbox.checked =
                masterCheckbox.checked;

        });

        updateSelectedCount();

    }


    /*
    |--------------------------------------------------------------------------
    | Select all button
    |--------------------------------------------------------------------------
    */

    function selectAllLocations() {

        const master =
            document.getElementById(
                'selectAllCheckbox'
            );

        master.checked = true;

        toggleAll(master);

        showSuccessToast(
            'All visible locations selected.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Clear selection
    |--------------------------------------------------------------------------
    */

    function clearSelections() {

        const checkboxes =
            document.querySelectorAll(
                '.location-checkbox'
            );

        checkboxes.forEach(function(checkbox) {

            checkbox.checked = false;

        });


        document.getElementById(
            'selectAllCheckbox'
        ).checked = false;


        updateSelectedCount();

        showSuccessToast(
            'Selections cleared.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Selected count
    |--------------------------------------------------------------------------
    */

    function updateSelectedCount() {

        const selected =
            document.querySelectorAll(
                '.location-checkbox:checked'
            );


        const count =
            selected.length;


        document.getElementById(
            'selectedCount'
        ).textContent =
            count + ' selected';


        const button =
            document.getElementById(
                'bulkDeleteButton'
            );


        button.disabled =
            count === 0;


        const allCheckboxes =
            document.querySelectorAll(
                '.location-checkbox'
            );


        const master =
            document.getElementById(
                'selectAllCheckbox'
            );


        master.checked =
            allCheckboxes.length > 0 &&
            count === allCheckboxes.length;

    }


    /*
    |--------------------------------------------------------------------------
    | Bulk delete confirmation
    |--------------------------------------------------------------------------
    */

    function confirmBulkDelete() {

        const selected =
            document.querySelectorAll(
                '.location-checkbox:checked'
            );


        if (selected.length === 0) {

            showErrorToast(
                'Please select at least one location.'
            );

            return false;

        }


        return confirm(
            'Are you sure you want to delete ' +
            selected.length +
            ' selected location(s)?'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Single delete
    |--------------------------------------------------------------------------
    */

    function confirmDelete() {

        return confirm(
            'Are you sure you want to delete this location?'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Copy coordinate
    |--------------------------------------------------------------------------
    */

    function copyValue(
        value,
        fieldName,
        button
    ) {

        const originalText =
            button.innerHTML;


        /*
        | Modern clipboard
        */

        if (
            navigator.clipboard &&
            window.isSecureContext
        ) {

            navigator.clipboard
                .writeText(value)
                .then(function() {

                    button.innerHTML =
                        '✅ Copied';

                    showSuccessToast(
                        fieldName +
                        ' copied successfully!'
                    );


                    setTimeout(function() {

                        button.innerHTML =
                            originalText;

                    }, 1500);

                })
                .catch(function() {

                    fallbackCopy(
                        value,
                        fieldName,
                        button,
                        originalText
                    );

                });

        } else {

            fallbackCopy(
                value,
                fieldName,
                button,
                originalText
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Clipboard fallback
    |--------------------------------------------------------------------------
    */

    function fallbackCopy(
        value,
        fieldName,
        button,
        originalText
    ) {

        const textarea =
            document.createElement(
                'textarea'
            );


        textarea.value = value;

        textarea.style.position =
            'fixed';

        textarea.style.left =
            '-9999px';


        document.body.appendChild(
            textarea
        );


        textarea.select();


        try {

            document.execCommand(
                'copy'
            );


            button.innerHTML =
                '✅ Copied';


            showSuccessToast(
                fieldName +
                ' copied successfully!'
            );


            setTimeout(function() {

                button.innerHTML =
                    originalText;

            }, 1500);


        } catch (error) {

            showErrorToast(
                'Unable to copy ' +
                fieldName +
                '.'
            );

        }


        document.body.removeChild(
            textarea
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Success Toast
    |--------------------------------------------------------------------------
    */

    function showSuccessToast(message) {

        showToast(
            message,
            'success'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Error Toast
    |--------------------------------------------------------------------------
    */

    function showErrorToast(message) {

        showToast(
            message,
            'danger'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Generic Toast
    |--------------------------------------------------------------------------
    */

    function showToast(
        message,
        type = 'success'
    ) {

        /*
        | Remove existing dynamic toast
        */

        const oldToast =
            document.getElementById(
                'dynamicToast'
            );

        if (oldToast) {
            oldToast.remove();
        }


        const container =
            document.createElement(
                'div'
            );

        container.className =
            'toast-container-custom';


        const toast =
            document.createElement(
                'div'
            );

        toast.id =
            'dynamicToast';

        toast.className =
            'toast show custom-toast border-0';


        const title =
            type === 'success'
                ? '✅ Success'
                : '❌ Error';


        const headerClass =
            type === 'success'
                ? 'bg-success'
                : 'bg-danger';


        toast.innerHTML = `

            <div class="toast-header ${headerClass} text-white">

                <strong class="me-auto">
                    ${title}
                </strong>

                <button
                    type="button"
                    class="btn-close btn-close-white"
                    onclick="this.closest('.toast-container-custom').remove()"
                ></button>

            </div>

            <div class="toast-body bg-white">

                ${escapeHtml(message)}

            </div>

        `;


        container.appendChild(
            toast
        );


        document.body.appendChild(
            container
        );


        setTimeout(function() {

            container.remove();

        }, 3000);

    }


    /*
    |--------------------------------------------------------------------------
    | Close server-side toast
    |--------------------------------------------------------------------------
    */

    function closeToast(button) {

        const container =
            button.closest(
                '.toast-container-custom'
            );

        if (container) {
            container.remove();
        }

    }


    /*
    |--------------------------------------------------------------------------
    | Automatically hide Laravel session toast
    |--------------------------------------------------------------------------
    */

    setTimeout(function() {

        document
            .querySelectorAll(
                '.toast-container-custom'
            )
            .forEach(function(container) {

                container.remove();

            });

    }, 4000);


    /*
    |--------------------------------------------------------------------------
    | Initial selection count
    |--------------------------------------------------------------------------
    */

    updateSelectedCount();

</script>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>