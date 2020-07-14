@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-body" id="mapid"></div>
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.1/dist/leaflet.css"
    integrity="sha512-Rksm5RenBEKSKFjgI3a41vrjkw4EVPlJ3+OiI65vTjIdo9brlAacEuKOiQ5OFh7cOI1bkDwLqdLw3Zg0cRJAAQ=="
    crossorigin=""/>

<style>
    #mapid { min-height: 500px; }
</style>


@endsection
@push('scripts')
<!-- Make sure you put this AFTER Leaflet's CSS -->
<script src="https://unpkg.com/leaflet@1.3.1/dist/leaflet.js"
    integrity="sha512-/Nsx9X4HebavoBvEBuyp3I7od5tA0UzAxs+j83KgC8PU0kgB4XiK4Lfe4y4cgBtaRJQEIFCW+oC506aPT2L1zw=="
    crossorigin=""></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.3.0/leaflet.markercluster.js" integrity="sha256-3ojygIn9E2azRCTFNSS+qcVr8FT9yfpol9iG2ZaRz4w=" crossorigin="anonymous"></script>
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fuse.js/3.0.4/fuse.min.js"></script>


<script>
    var map = L.map('mapid').setView([{{ config('leaflet.map_center_latitude') }}, {{ config('leaflet.map_center_longitude') }}], {{ config('leaflet.zoom_level') }});
    var baseUrl = "{{ url('/') }}";

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);


    var greenIcon = new L.Icon({
        iconUrl: 'https://img.icons8.com/emoji/96/000000/red-circle-emoji.png',
        iconSize: [50, 50],
        iconAnchor: [25, 50]
      });

    var markers = L.markerClusterGroup();

    axios.get('{{ route('api.outlets.index') }}')
    .then(function (response) {
        console.log(response.data);
        L.geoJSON(response.data, {
            pointToLayer: function(geoJsonPoint, latlng) {
                return markers.addLayer(L.marker(latlng,{icon:greenIcon}));
            }
        })
        .bindPopup(function (layer) {
            return layer.feature.properties.map_popup_content;
        }).addTo(map.addLayer(markers));

    })
    .catch(function (error) {
        console.log(error);
    });

    var markers = L.markerClusterGroup();

    @can('create', new App\Outlet)
    var theMarker;

    map.on('click', function(e) {
        let latitude = e.latlng.lat.toString().substring(0, 15);
        let longitude = e.latlng.lng.toString().substring(0, 15);

        if (theMarker != undefined) {
            map.removeLayer(theMarker);
        };

        var popupContent = "Ubicación : " + latitude + ", " + longitude + ".";
        popupContent += '<br><a href="{{ route('outlets.create') }}?latitude=' + latitude + '&longitude=' + longitude + '">Add new outlet here</a>';

        theMarker = L.marker([latitude, longitude]).addTo(map);
        theMarker.bindPopup(popupContent)
        .openPopup();
    });
    @endcan
</script>

<script type="text/javascript">
    window.onload = function() {
        var popup = L.popup();
        var geolocationMap = L.map('map', {
            layers: MQ.mapLayer(),
            center: [40.731701, -73.993411],
            zoom: 12
        });

        function geolocationErrorOccurred(geolocationSupported, popup, latLng) {
            popup.setLatLng(latLng);
            popup.setContent(geolocationSupported ?
                    '<b>Error:</b> The Geolocation service failed.' :
                    '<b>Error:</b> This browser doesn\'t support geolocation.');
            popup.openOn(geolocationMap);
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                var latLng = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };

                popup.setLatLng(latLng);
                popup.setContent('This is your current location');
                popup.openOn(geolocationMap);

                geolocationMap.setView(latLng);
            }, function() {
                geolocationErrorOccurred(true, popup, geolocationMap.getCenter());
            });
        } else {
            //No browser support geolocation service
            geolocationErrorOccurred(false, popup, geolocationMap.getCenter());
        }
    }
</script>


@endpush
