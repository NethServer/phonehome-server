//Init Overlays
var overlays = {};
//Hide the button that will be showed after the page is charged
$("#button").hide();
$("#totalUnity").hide();
//Init BaseMaps with all the tile that we need
var basemaps = {
  Default: L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 5,
    minZoom: 1,
    id: "OpenStreet",
  }),
  Dark: L.tileLayer(
    "http://services.arcgisonline.com/arcgis/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}",
    {
      maxZoom: 5,
      minZoom: 1,
      id: "MapID",
    }
  ),
  Light: L.tileLayer(
    "http://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png",
    {
      maxZoom: 5,
      minZoom: 1,
      id: "MapID",
    }
  ),
  Detailed: L.tileLayer("https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}", {
    maxZoom: 5,
    minZoom: 1,
    id: "MapID",
  }),
};
//Set all the options of the map
var mapOptions = {
  zoomControl: false,
  attributionControl: false,
  center: [27.9027835, 17.4963655],
  zoom: 1.5,
  layers: [basemaps.Default],
};
//Render Main Map
var map = L.map("map", mapOptions);
map.on("baselayerchange", onOverlayAdd);
function onOverlayAdd(e) {
  //Find wich map the user has selected
  for (var i in geoJsons) {
    var themeSelected = e.name;
    switch (themeSelected) {
      case "Default":
        var geojson = geoJsons[i];
        geojson.setStyle({
          weight: 2,
          opacity: 1,
          dashArray: "2",
          color: "#4E6E7D",
          fillOpacity: 0.7,
        });
        break;
      case "Dark":
        var geojson = geoJsons[i];
        geojson.setStyle({
          weight: 2,
          opacity: 1,
          dashArray: "2",
          color: "#E66952  ",
          fillOpacity: 0.7,
        });
        break;
      case "Light":
        var geojson = geoJsons[i];
        geojson.setStyle({
          weight: 2,
          opacity: 1,
          dashArray: "2",
          color: "#591COB",
          fillOpacity: 0.7,
        });
        break;
      case "Detailed":
        var geojson = geoJsons[i];
        geojson.setStyle({
          weight: 2,
          opacity: 1,
          dashArray: "2",
          color: "#56B7B4",
          fillOpacity: 0.7,
        });
        break;
      default:
        var geojson = geoJsons[i];
        geojson.setStyle({
          weight: 2,
          opacity: 1,
          dashArray: "2",
          color: "pink",
          fillOpacity: 0.7,
        });
        break;
    }
  }
}
//Render Zoom Control
L.control
  .zoom({
    position: "bottomright",
  })
  .addTo(map);
//Render Layer Control & Move to Sidebar
var themeControl = L.control
  .layers(basemaps, overlays, {
    position: "topright",
    collapsed: true,
  })
  .addTo(map);
//Lock size of the map
var southWest = L.latLng(-82.98155760646617, -99999),
  northEast = L.latLng(90.99346179538875, 99999);
var bounds = L.latLngBounds(southWest, northEast);
map.setMaxBounds(bounds);
map.on("drag", function () {
  map.panInsideBounds(bounds, { animate: false });
});
var geoJsons = [];
var globalInstallations = 0;
//call phoneHome Api to retrieve country installation
$.ajax({
  url: "/api/installation",
  type: "GET",
  data: "interval=" + interval,
  success: function (resp) {
    //Get response from Api
    var installations = resp;
    //Get geoJson
      var geoJson = LAYER;
      //get countryCodes
        var countryCodes = COORDINATES;
        //loop installations
        for (var i in installations) {
          var installation = installations[i];
          //get geojson coordinates
          var coordinates = getCoordinates(geoJson, installation.country_code);
          //get centroide of marker
          var centroide = getCentroide(countryCodes, installation.country_code);
          // //get installations of country
          var nethserverInstallations = getNethserverInstallation(
            installation.installations,
            installation.country_name
          );
          //draw elements: draw layer
          if (coordinates) {
            var geo = L.geoJson(coordinates, {
              style: style,
            }).addTo(map);
            geoJsons.push(geo);
          }
          //draw elements: draw marker
          if (centroide) {
            var marker = L.marker(centroide, {
              icon: L.BeautifyIcon.icon(icon),
            });
          }
          //draw elemensts: draw popup
          if (nethserverInstallations.length > 0) {
            if (marker) {
              var content = "";
              var textMarker = "";
              var totalInstallations = 0;

              for (var i in nethserverInstallations) {
                //Create all the variable that will be showed in the marker
                installationsNumber = nethserverInstallations[i].number;
                versionsInstallation = nethserverInstallations[i].version;
                countryName = nethserverInstallations[i].countryN;
                textMarker +=
                  "<tr><td><b>" +
                  versionsInstallation +
                  "</b></td><td><b>" +
                  installationsNumber;
                ("</b></td></tr>");
                //Check the total number of the installations
                totalInstallations += parseInt(installationsNumber);
              }
              globalInstallations += totalInstallations;
              if (totalInstallations >= 1000) {
                totalInstallations =
                  Math.floor(totalInstallations / 1000).toString() + "k";
              }
              marker.options.icon.options.text = totalInstallations;
              //Show the unity total
              $("#totalUnity").text(globalInstallations.toString());
              //Create the marker body
              content +=
                '<table class="table  is-hoverable">' +
                '<thead><tr><th style="background-color: white; color: black; font-size: 12; border-radius:4px; border-color: transparent; " colspan="2";>' +
                countryName +
                "</th></tr></thead>" +
                "<tbody><tr><th>Release</th><th>Installations</th></tr></tbody>" +
                textMarker +
                "</table>" +
                "</div>" +
                "</div>";
              var txt = `${content}`;
              marker.addTo(map).bindPopup(txt);
              //when the map is completely load hide the blur effect
              $("#loader").hide();
              $("#map").css("filter", "blur(0px)");
              $("#button").show();
              $("#totalUnity").show();
              $(".leaflet-control-zoom").css("visibility", "visible");
              $(".leaflet-control-layers-toggle").css("visibility", "visible");
              $(".button.is-info").css("visibility", "visible");
              $("#totalUnity").css("visibility", "visible");
              //Check wich currency of time is selected
              var selectedTime = $("#current_interval").text();
              //Change color of text selected
              switch (selectedTime) {
                case "Last week":
                  $("#interval_week").css("background-color", "#1F3549") &&
                    $("#interval_week").css("color", "white") &&
                    $("#interval_week").css("margin-top", "-5px");
                  break;
                case "Last month":
                  $("#interval_month").css("background-color", "#1F3549") &&
                    $("#interval_month").css("color", "white");
                  break;
                case "Last 6 months":
                  $("#interval_6months").css("background-color", "#1F3549") &&
                    $("#interval_6months").css("color", "white");
                  break;
                case "Last year":
                  $("#interval_year").css("background-color", "#1F3549") &&
                    $("#interval_year").css("color", "white") &&
                    $("#interval_year").css("margin-bottom", "-8px");
                  break;
                case "All":
                  $("#interval_all").css("background-color", "#1F3549") &&
                    $("#interval_all").css("color", "white") &&
                    $("#interval_all").css("margin-top", "-8px") &&
                    $("#interval_all").css("margin-bottom", "-8px") &&
                    $("#interval_all").css(
                      "border-bottom-left-radius",
                      "4px"
                    ) &&
                    $("#interval_all").css("border-bottom-right-radius", "4px");
                  break;
                default:
                  break;
              }
            }
          }
        }
  },
  error: function (errResp) {
    console.error(errResp);
  },
});
