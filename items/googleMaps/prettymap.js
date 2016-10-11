//Modified from code by Mohamed Elgharabawy http://itscoding.com/ admin@itscoding.com
function flc_googleMap(lat, long, height, width, target)
{
    jQuery(document).ready(function() {

        var initialLocation;
        //var browserSupportFlag = new Boolean();

        var opt =
                {
                    zoom: 15,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    navigationControl: true,
                    streetViewControl: false,
                    navigationControlOptions: {style: google.maps.NavigationControlStyle.DEFAULT},
                    mapTypeControl: true,
                    mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DEFAULT}
                };

        var map = new google.maps.Map(document.getElementById(target), opt);
        jQuery('#' + target).css('width', width + 'px').css('height', height + 'px');		//set width and height

        if (lat == 'geo' && long == 'geo')
        {
            // Try W3C Geolocation (Preferred)
            if (navigator.geolocation)
            {
                browserSupportFlag = true;
                navigator.geolocation.getCurrentPosition(function(position)
                {
                    initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                    map.setCenter(initialLocation);

                    jQuery('#' + target.replace('gmap_wrapper_', '')).val(position.coords.latitude + ', ' + position.coords.longitude);
                    latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

                    var marker = new google.maps.Marker({
                        position: latlng,
                        clickable: true,
                        animation: google.maps.Animation.BOUNCE,
                        map: map,
                        icon: 'items/googleMaps/images/marker.png'
                    });

                },
                        function()
                        {
                            //handleNoGeolocation(browserSupportFlag);
                        });
            }
            else
            {
                browserSupportFlag = false;
                showNotificationError('Browser does not support geolocation!', 5);
            }
        }
        else
        {
            var latlng = new google.maps.LatLng(lat, long);
            map.setCenter(latlng);

            var marker = new google.maps.Marker({
                position: latlng,
                clickable: true,
                animation: google.maps.Animation.BOUNCE,
                map: map,
                icon: 'items/googleMaps/images/marker.png'
            });
        }
    });
}
