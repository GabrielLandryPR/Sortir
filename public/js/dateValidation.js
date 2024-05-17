document.addEventListener('DOMContentLoaded', function() {
    const dateDebutInput = document.getElementById('sortie_form_dateDebut');
    const dateFinInput = document.getElementById('sortie_form_dateFin');

    const today = new Date().toISOString().split('T')[0];
    dateDebutInput.setAttribute('min', today);

    dateDebutInput.addEventListener('change', function() {
        if (dateDebutInput.value < today) {
            alert("La date de début ne peut pas être antérieure à la date actuelle.");
            dateDebutInput.value = '';
        } else {
            dateFinInput.setAttribute('min', dateDebutInput.value);
        }
    });

    dateFinInput.addEventListener('change', function() {
        if (dateFinInput.value && dateFinInput.value > dateDebutInput.value) {
            alert("La date limite d'inscription ne peut pas être supérieure à la date de début.");
            dateFinInput.value = '';
        }
    });
});
