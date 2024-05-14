// fichier lieu.js

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
                            document.getElementById('sortie_form_rue').value = fullStreet;
                            document.getElementById('sortie_form_codePostal').value = item.address.postcode || '';
                            document.getElementById('sortie_form_ville').value = item.address.city || item.address.town || item.address.village || '';
                            document.getElementById('sortie_form_latitude').value = item.lat || '';
                            document.getElementById('sortie_form_longitude').value = item.lon || '';
                            autocompleteResults.innerHTML = '';

                            // Update the map
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
