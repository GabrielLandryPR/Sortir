document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('.autocomplete');
    const autocompleteResults = document.createElement('div');
    autocompleteResults.classList.add('autocomplete-results');
    searchInput.parentNode.appendChild(autocompleteResults);

    function debounce(func, delay) {
        let debounceTimer;
        return function () {
            const context = this;
            const args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(context, args), delay);
        };
    }

    const handleInput = function () {
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
                            const placeName = item.address.road || item.display_name || '';
                            searchInput.value = placeName;

                            const streetNumber = item.address.house_number || '';
                            const streetName = item.address.road || '';
                            const fullStreet = streetNumber ? `${streetNumber} ${streetName}` : streetName;
                            document.getElementById('sortie_form_rue').value = fullStreet;
                            document.getElementById('sortie_form_codePostal').value = item.address.postcode || '';
                            document.getElementById('sortie_form_ville').value = item.address.city || item.address.town || item.address.village || '';
                            document.getElementById('sortie_form_latitude').value = item.lat || '';
                            document.getElementById('sortie_form_longitude').value = item.lon || '';
                            autocompleteResults.innerHTML = '';

                            const latitude = parseFloat(item.lat);
                            const longitude = parseFloat(item.lon);
                            window.updateMap(latitude, longitude);
                        });
                        autocompleteResults.appendChild(resultItem);
                    });
                });
        } else {
            autocompleteResults.innerHTML = '';
        }
    };

    searchInput.addEventListener('input', debounce(handleInput, 300));

    document.addEventListener('click', function (event) {
        if (!searchInput.contains(event.target) && !autocompleteResults.contains(event.target)) {
            autocompleteResults.innerHTML = '';
        }
    });
});
