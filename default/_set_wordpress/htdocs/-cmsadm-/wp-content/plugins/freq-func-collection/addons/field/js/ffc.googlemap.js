;jQuery(function ($) {
    $('.mapCanvas').each(function () {
        var latlng = $(this).data('latlng');

        if ('undefined' === typeof latlng
            || !sunat.utils.isArray(latlng)
            || 3 !== latlng.length
        ) {
            return;
        }

        var markerPosition = {
                lat: latlng[0],
                lng: latlng[1]
            },
            zoom = +latlng[2],
            gmaps = null;

        gmaps = new sunat.googleMap(this, {
            center: new google.maps.LatLng(markerPosition.lat, markerPosition.lng),
            zoom: zoom,
            streetViewControl : false
        });
        gmaps.marker(markerPosition.lat, markerPosition.lng, {
            draggable: false
        });
    });
});
