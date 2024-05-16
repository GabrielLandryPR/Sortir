document.addEventListener('DOMContentLoaded', function() {
    const siteSelect = document.getElementById('siteSelect');
    const organizerCheckbox = document.getElementById('organizer');
    const registeredCheckbox = document.getElementById('registered');
    const notRegisteredCheckbox = document.getElementById('notRegistered');
    const pastCheckbox = document.getElementById('past');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const searchNameInput = document.getElementById('searchName');
    const resetButton = document.getElementById('resetButton');
    const resetDateButton = document.getElementById('resetDateButton');
    const clearSearch = document.getElementById('clearSearch');
    const sortiesBody = document.getElementById('sortiesBody');

    let validationInProgress = false;

    const today = new Date().toISOString().split('T')[0];
    startDateInput.value = today;

    function updateSorties() {
        if (validationInProgress) {
            validationInProgress = false;
            return;
        }

        if (endDateInput.value && endDateInput.value < startDateInput.value) {
            validationInProgress = true;
            alert("La date 'Et' ne peut pas être antérieure à la date 'Comprise entre'.");
            endDateInput.value = '';
            return;
        }

        const startDateTime = startDateInput.value ? `${startDateInput.value}T00:00:00` : '';
        const endDateTime = endDateInput.value ? `${endDateInput.value}T23:59:59` : '';

        const params = new URLSearchParams({
            site: siteSelect.value,
            organizer: organizerCheckbox.checked ? '1' : '',
            registered: registeredCheckbox.checked ? '1' : '',
            notRegistered: notRegisteredCheckbox.checked ? '1' : '',
            past: pastCheckbox.checked ? '1' : '',
            startDate: startDateTime,
            endDate: endDateTime,
            searchName: searchNameInput.value
        });

        fetch(`/sortir/filter_sorties?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                updateSortiesList(data);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors du chargement des données : \n'
                    + error);
            });
    }

    function updateSortiesList(data) {
        sortiesBody.innerHTML = '';

        if (data.sorties && data.sorties.length > 0) {
            data.sorties.forEach(sortie => {
                const row = `<tr>
                    <td>${sortie.nomSortie}</td>
                    <td>${sortie.dateDebut}</td>
                    <td>${sortie.etatSortie}</td>
                    <td>${sortie.dateFin}</td>
                    <td>${sortie.description}</td>
                    <td>${sortie.organisateur}</td>
                </tr>`;
                sortiesBody.innerHTML += row;
            });
        } else {
            sortiesBody.innerHTML = '<tr><td colspan="6">Aucune sortie trouvée correspondant aux critères de recherche.</td></tr>';
        }
    }

    function resetFilters() {
        startDateInput.value = today;
        endDateInput.value = '';
        searchNameInput.value = '';
        updateSorties();
    }

    function resetDates() {
        startDateInput.value = today;
        endDateInput.value = '';
        updateSorties();
    }

    function clearSearchField() {
        searchNameInput.value = '';
        updateSorties();
    }

    siteSelect.addEventListener('change', updateSorties);
    organizerCheckbox.addEventListener('change', updateSorties);
    registeredCheckbox.addEventListener('change', updateSorties);
    notRegisteredCheckbox.addEventListener('change', updateSorties);
    pastCheckbox.addEventListener('change', updateSorties);
    startDateInput.addEventListener('change', updateSorties);
    endDateInput.addEventListener('change', updateSorties);
    searchNameInput.addEventListener('input', updateSorties);
    resetButton.addEventListener('click', resetFilters);
    resetDateButton.addEventListener('click', resetDates);
    clearSearch.addEventListener('click', clearSearchField);

    updateSorties();
});
