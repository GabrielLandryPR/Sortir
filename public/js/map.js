document.addEventListener('DOMContentLoaded', function () {
    const latitudeElement = document.querySelector('.card-body .latitude');
    const longitudeElement = document.querySelector('.card-body .longitude');

    if (latitudeElement && longitudeElement) {
        const latitude = parseFloat(latitudeElement.textContent);
        const longitude = parseFloat(longitudeElement.textContent);

        var map = L.map('map').setView([latitude, longitude], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        L.marker([latitude, longitude]).addTo(map)
            .bindPopup('Lieu de la sortie')
            .openPopup();
    }
});
