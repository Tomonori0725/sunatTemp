;jQuery(function ($) {
    $('.mapField').each(function () {
        var latlng = $(this).data('latlng'),
            markerPosition = {
                lat: latlng[0],
                lng: latlng[1]
            },
            zoom = +latlng[2],
            $element = {
                lat: $('.lat', this),
                lng: $('.lng', this),
                zoom: $('.zoom', this),
                visible: $('.visible', this)
            },
            gmaps = null,
            marker = null;

        if ($element.lat.val() && $element.lng.val()) {
            markerPosition.lat = $element.lat.val();
            markerPosition.lng = $element.lng.val();
            $element.visible.prop('checked', false);
        }
        if ($element.zoom.val()) {
            zoom = +$element.zoom.val();
        }

        gmaps = new sunat.googleMap($('.mapCanvas', this).get(0), {
            center: new google.maps.LatLng(markerPosition.lat, markerPosition.lng),
            zoom: zoom,
            streetViewControl : false
        });
        marker = gmaps.marker(markerPosition.lat, markerPosition.lng, {
            draggable: true
        });

        var writeLatLngZoom = function (e) {
            var position = marker.object.getPosition();

            $element.lat.val(position.lat());
            $element.lng.val(position.lng());
            $element.zoom.val(gmaps.getMap().getZoom());
            $element.visible.prop('checked', false);
        },
        ReadLatLngZoom = function (e) {
            var lat = $element.lat.val(),
                lng = $element.lng.val(),
                zoom = $element.zoom.val();

            if (lat && lng) {
                var latlng = new google.maps.LatLng(lat, lng);
                marker.object.setPosition(latlng);
                gmaps.getMap().setCenter(latlng);
                $element.visible.prop('checked', false);
                if (!zoom.length) {
                    $element.zoom.val(gmaps.getMap().getZoom());
                }
                if (zoom && gmaps.getMap().getZoom() !== +zoom) {
                    gmaps.getMap().setZoom(+zoom);
                }
            } else {
                $element.zoom.val('');
            }
        };

        google.maps.event.addListener(marker.object, 'dragend', writeLatLngZoom);
        google.maps.event.addListener(gmaps.getMap(), 'zoom_changed', writeLatLngZoom);

        $element.visible.on('change', function (e) {
            if ($(this).prop('checked')) {
                $element.lat.val('');
                $element.lng.val('');
                $element.zoom.val('');
            } else {
                writeLatLngZoom(e);
            }
        });
        $element.lat.on('keyup', ReadLatLngZoom);
        $element.lng.on('keyup', ReadLatLngZoom);
        $element.zoom.on('keyup', ReadLatLngZoom);
    });
});
