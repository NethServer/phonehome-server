//Get coordinates of country
function getCoordinates(obj, countryCode) {
  if (countryCode == "") {
    return null;
  }
  for (var i in obj.features) {
    var geo = obj.features[i];
    if (geo.properties.ISO_A3 == countryCode) {
      return geo;
    }
  }
}
//Get the center of every Country 
function getCentroide(obj, countryCode) {
  if (countryCode == "") {
    return null;
  }
  for (var i in obj) {
    var geo = obj[i];
    if (geo.country_code == countryCode) {
      return geo.latlng;
    }
  }
}
//Get the number and the versions of installation
function getNethserverInstallation(installations, countryName) {
  var versions = [];
  var splittedI = installations.split(",");
  splittedI.sort();
  for (i in splittedI) {
    var splittedV = splittedI[i].split("#");
    var version = {
      version: splittedV[0],
      number: splittedV[1],
      countryN: countryName,
    };
    versions.push(version);
  }
  return versions;
}
//color of the map border
function style(feature) {
  return {
    weight: 2,
    opacity: 1,
    dashArray: "2",
    color: "#4E6E7D",
    fillOpacity: 0.7,
  };
}
//import a specif icon for the marker with a determinate size
icon = {
  iconShape: "marker",
  isAlphaNumericIcon: true,
  text: "",
  iconSize: [22, 26],
};
//Define determinate period of time
var interval;
var interval_menu = document.getElementById("current_interval") ;
var interval = sessionStorage.getItem("interval_value") || "7";
if (interval == "7") interval_menu.innerHTML = "Last week";
else if (interval == "30") interval_menu.innerHTML = "Last month";
else if (interval == "180") interval_menu.innerHTML = "Last 6 months";
else if (interval == "365") interval_menu.innerHTML = "Last year";
else if (interval == "1") interval_menu.innerHTML = "All";
function refresh_interval() {
  var interval_menu = document.getElementById("current_interval");
  var toStore = 0;
  if (interval_menu.innerHTML == "Last week") toStore = 7;
  else if (interval_menu.innerHTML == "Last month") toStore = 30;
  else if (interval_menu.innerHTML == "Last 6 months") toStore = 180;
  else if (interval_menu.innerHTML == "Last year") toStore = 365;
  else if (interval_menu.innerHTML == "All") toStore = 1;
  sessionStorage.setItem("interval_value", toStore);
  location.reload();
}
//Refresh the page in order to the specific choose
document.getElementById("interval_week").addEventListener("click", function () {
  var interval_menu = document.getElementById("current_interval");
  interval_menu.innerHTML = "Last week";
  refresh_interval();
});
document
  .getElementById("interval_month")
  .addEventListener("click", function () {
    var interval_menu = document.getElementById("current_interval");
    interval_menu.innerHTML = "Last month";
    refresh_interval();
  });
document
  .getElementById("interval_6months")
  .addEventListener("click", function () {
    var interval_menu = document.getElementById("current_interval");
    interval_menu.innerHTML = "Last 6 months";
    refresh_interval();
  });
document.getElementById("interval_year").addEventListener("click", function () {
  var interval_menu = document.getElementById("current_interval");
  interval_menu.innerHTML = "Last year";
  refresh_interval();
});
document.getElementById("interval_all").addEventListener("click", function () {
  var interval_menu = document.getElementById("current_interval");
  interval_menu.innerHTML = "All";
  refresh_interval();
});
//Event for dropdown button
const menu = document.getElementById('dropdown')
menu.addEventListener('click', e=>{
  e.stopPropagation()
  menu.classList.toggle('is-active')
})
document.addEventListener('click', ()=>{
    menu.classList.remove('is-active')
})
