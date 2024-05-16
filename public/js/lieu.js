document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('.autocomplete');
    const autocompleteResults = document.createElement('div');
    autocompleteResults.classList.add('autocomplete-results');
    searchInput.parentNode.appendChild(autocompleteResults);

    let map;
    let marker;

    searchInput.addEventListener('input', function () {
        const query = searchInput.value;
        if (query.length > 2) {
            fetch(`/location/search?query=${query}`)
                .then(response => response.json())
                .then(data => {
                    console.log('API Results:', data);
                    autocompleteResults.innerHTML = '';
                    data.forEach(item => {
                        const resultItem = document.createElement('div');
                        resultItem.classList.add('autocomplete-item');
                        resultItem.textContent = `${item.display_name}`;
                        resultItem.addEventListener('click', function () {
                            searchInput.value = item.display_name;
                            const streetNumber = item.address.house_number || '';
                            const streetName = item.address.road || '';
                            const fullStreet = streetNumber ? `${streetNumber} ${streetName}` : streetName;

                            const rueInput = document.getElementById('sortie_modification_form_rue');
                            const codePostalInput = document.getElementById('sortie_modification_form_codePostal');
                            const villeInput = document.getElementById('sortie_modification_form_ville');
                            const latitudeInput = document.getElementById('sortie_modification_form_latitude');
                            const longitudeInput = document.getElementById('sortie_modification_form_longitude');

                            if (rueInput && codePostalInput && villeInput && latitudeInput && longitudeInput) {
                                rueInput.value = fullStreet;
                                codePostalInput.value = item.address.postcode || '';
                                villeInput.value = item.address.city || item.address.town || item.address.village || '';
                                latitudeInput.value = item.lat || '';
                                longitudeInput.value = item.lon || '';
                            } else {
                                console.error("Un ou plusieurs champs de formulaire sont introuvables.");
                            }

                            autocompleteResults.innerHTML = '';

                            const latitude = parseFloat(item.lat);
                            const longitude = parseFloat(item.lon);
                            if (!map) {
                                map = L.map('map').setView([latitude, longitude], 13);
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                }).addTo(map);
                            } else {
                                map.setView([latitude, longitude], 13);
                            }

                            if (marker) {
                                map.removeLayer(marker);
                            }
                            marker = L.marker([latitude, longitude]).addTo(map);
                        });
                        autocompleteResults.appendChild(resultItem);
                    });
                });
        } else {
            autocompleteResults.innerHTML = '';
        }
    });

    document.addEventListener('click', function (event) {
        if (!searchInput.contains(event.target) && !autocompleteResults.contains(event.target)) {
            autocompleteResults.innerHTML = '';
        }
    });
});
