document.addEventListener('DOMContentLoaded', function () {
    var map;
    var marker;

    function initializeMap(latitude, longitude) {
        if (!map) {
            map = L.map('map').setView([latitude, longitude], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
        } else {
            map.setView([latitude, longitude], 13);
        }
    }

    function updateMarker(latitude, longitude) {
        if (marker) {
            map.removeLayer(marker);
        }
        marker = L.marker([latitude, longitude]).addTo(map);
    }

    window.updateMap = function (latitude, longitude) {
        initializeMap(latitude, longitude);
        updateMarker(latitude, longitude);
    }
});
