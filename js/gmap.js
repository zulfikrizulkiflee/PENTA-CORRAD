var map;

function initMap() {
    var mapDiv = document.getElementById('map');
    map = new google.maps.Map(mapDiv, {
        center: {
            lat: 1.4760883200603618
            , lng: 103.7373015123726
        }
        , zoom: 15
        , mapTypeId: google.maps.MapTypeId.ROADMAP
        , disableDefaultUI: true
    });
    var infoWindow = new google.maps.InfoWindow();
    var jb = [
        new google.maps.LatLng(1.4582775898253464, 103.74509811401367)
        , new google.maps.LatLng(1.4915688629698645, 103.79247665405273)
        , new google.maps.LatLng(1.5341260441526365, 103.7578010559082)
        , new google.maps.LatLng(1.5035810549524486, 103.69634628295898)];
    var bounds = new google.maps.LatLngBounds();
    for (var i = 0, LtLgLen = jb.length; i < LtLgLen; i++) {
        bounds.extend(jb[i]);
    }
    map.fitBounds(bounds);
    var shapes = [];
    var path = [
    new google.maps.LatLng(1.4803288239309305, 103.74705612659454)
    , new google.maps.LatLng(1.4805647795359766, 103.74737799167633)
    , new google.maps.LatLng(1.4820770398628114, 103.74749600887299)
    , new google.maps.LatLng(1.482495324451795, 103.74822556972504)
    , new google.maps.LatLng(1.48275273031342, 103.74875128269196)
    , new google.maps.LatLng(1.4836536505933515, 103.74914824962616)
    , new google.maps.LatLng(1.484919228462049, 103.74715268611908)
    , new google.maps.LatLng(1.4853696882065826, 103.74664843082428)
    , new google.maps.LatLng(1.4836000243964782, 103.74582231044769)
    , new google.maps.LatLng(1.483331893392633, 103.74640166759491)
    , new google.maps.LatLng(1.4828278070174237, 103.74653041362762)
    , new google.maps.LatLng(1.482495324451795, 103.74647676944733)
    , new google.maps.LatLng(1.4822486438063194, 103.74630510807037)
    , new google.maps.LatLng(1.48210921560324, 103.74617636203766)
    , new google.maps.LatLng(1.481840127717802, 103.74631407226752)
    , new google.maps.LatLng(1.4816158541987936, 103.74626219272614)
    , new google.maps.LatLng(1.4814871512056422, 103.74623000621796)
    , new google.maps.LatLng(1.4808114603689653, 103.74579012393951)];
    var polyline1 = new google.maps.Polygon({
        path: path
        , title: "Taman Hutan Bandar"
        , strokeColor: "#fff"
        , strokeOpacity: 1
        , strokeWeight: 1
        , fillColor: '#fcf2a6'
        , fillOpacity: 0.6
    });
    var p1 = new google.maps.Marker({
        position: new google.maps.LatLng(1.4808114603689653, 103.74579012393951)
        , map: map
        , icon: 'images/marker/yellow_MarkerC.png'
    });
    p1.setMap(map);
    polyline1.setMap(map);
    shapes.push(polyline1);
    var polytitle1 = polyline1.title;
    google.maps.event.addListener(polyline1, 'click', function (e) {
        infoWindow.setPosition(e.latLng);
        infoWindow.setContent("<b>" + polytitle1 + "</b><br><a href='#' onclick='popUp()'>Lihat kemudahan</a><br><a href='#' onclick='profil()'>Profil taman</a>");
        infoWindow.open(map);
    });
    google.maps.event.addListener(p1, 'click', function (e) {
        infoWindow.setPosition(e.latLng);
        infoWindow.setContent("<b>" + polytitle1 + "</b><br><a href='#' onclick='popUp()'>Lihat kemudahan</a><br><a href='#' onclick='profil()'>Profil taman</a>");
        infoWindow.open(map);
    });
    var path = [
    new google.maps.LatLng(1.505007498369711, 103.70896875858307)
    , new google.maps.LatLng(1.505683181768063, 103.71020257472992)
    , new google.maps.LatLng(1.5051254748511715, 103.71190845966339)
    , new google.maps.LatLng(1.5034845286724923, 103.71130764484406)
    , new google.maps.LatLng(1.5029268211935713, 103.71022403240204)
    , new google.maps.LatLng(1.5040529611486, 103.70944082736969)];
    var polyline2 = new google.maps.Polygon({
        path: path
        , title: "Taman Orkid"
        , strokeColor: "#fff"
        , strokeOpacity: 1
        , strokeWeight: 1
        , fillColor: '#a6c1fc'
        , fillOpacity: 0.6
    });
    var p2 = new google.maps.Marker({
        position: new google.maps.LatLng(1.5040529611486, 103.70944082736969)
        , map: map
        , icon: 'images/marker/blue_MarkerB.png'
    });
    p2.setMap(map);
    polyline2.setMap(map);
    shapes.push(polyline2);
    var polytitle2 = polyline2.title;
    google.maps.event.addListener(polyline2, 'click', function (e) {
        infoWindow.setPosition(e.latLng);
        infoWindow.setContent("<b>" + polytitle2 + "</b><br><a href='#' onclick='popUp()'>Lihat kemudahan</a><br><a href='#' onclick='profil()'>Profil taman</a>");
        infoWindow.open(map);
    });
    google.maps.event.addListener(p2, 'click', function (e) {
        infoWindow.setPosition(e.latLng);
        infoWindow.setContent("<b>" + polytitle2 + "</b><br><a href='#' onclick='popUp()'>Lihat kemudahan</a><br><a href='#' onclick='profil()'>Profil taman</a>");
        infoWindow.open(map);
    });
    var path = [
    new google.maps.LatLng(1.4819161611537217, 103.73594641685486)
    , new google.maps.LatLng(1.481723106687359, 103.73693346977234)
    , new google.maps.LatLng(1.478076519164826, 103.7364399433136)
    , new google.maps.LatLng(1.4782695739486333, 103.73485207557678)];
    var polyline3 = new google.maps.Polygon({
        path: path
        , title: "Taman Merdeka"
        , strokeColor: "#fff"
        , strokeOpacity: 1
        , strokeWeight: 1
        , fillColor: '#c0edb1'
        , fillOpacity: 0.6
    });
    var p3 = new google.maps.Marker({
        position: new google.maps.LatLng(1.4782695739486333, 103.73485207557678)
        , map: map
        , icon: 'images/marker/green_MarkerA.png'
    });
    p2.setMap(map);
    polyline3.setMap(map);
    shapes.push(polyline3);
    var polytitle3 = polyline3.title;
    google.maps.event.addListener(polyline3, 'click', function (e) {
        infoWindow.setPosition(e.latLng);
        infoWindow.setContent("<b>" + polytitle3 + "</b><br><a href='#' onclick='popUp()'>Lihat kemudahan</a><br><a href='#' onclick='profil()'>Profil taman</a>");
        infoWindow.open(map);
    });
    google.maps.event.addListener(p3, 'click', function (e) {
        infoWindow.setPosition(e.latLng);
        infoWindow.setContent("<b>" + polytitle3 + "</b><br><a href='#' onclick='popUp()'>Lihat kemudahan</a><br><a href='#' onclick='profil()'>Profil taman</a>");
        infoWindow.open(map);
    });
    google.maps.event.addListener(map, 'click', function () {
        infoWindow.close();
    });
    $('#recenter').click(function () {
        map.fitBounds(bounds);
        //map.panTo(center);
        //map.setZoom(13);
    });
    // Create the search box and link it to the UI element.
    var input = document.getElementById('search');
    var searchBox = new google.maps.places.SearchBox(input);
    //map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function () {
        searchBox.setBounds(map.getBounds());
    });
    var markers = [];
    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener('places_changed', function () {
        var places = searchBox.getPlaces();
        if (places.length == 0) {
            return;
        }
        // Clear out the old markers.
        markers.forEach(function (marker) {
            marker.setMap(null);
        });
        markers = [];
        // For each place, get the icon, name and location.
        var bounds = new google.maps.LatLngBounds();
        places.forEach(function (place) {
            if (!place.geometry) {
                console.log("Returned place contains no geometry");
                return;
            }
            var icon = {
                url: place.icon
                , size: new google.maps.Size(71, 71)
                , origin: new google.maps.Point(0, 0)
                , anchor: new google.maps.Point(17, 34)
                , scaledSize: new google.maps.Size(25, 25)
            };
            // Create a marker for each place.
            markers.push(new google.maps.Marker({
                map: map
                , icon: icon
                , title: place.name
                , position: place.geometry.location
            }));
            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            }
            else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);
    });
}

function popUp() {
    $('#modal1').openModal();
}

function profil() {
    window.open("profil-taman.html");
}