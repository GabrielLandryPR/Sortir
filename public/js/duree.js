document.addEventListener('DOMContentLoaded', () => {
    const dureeInput = document.getElementById('SortieFormType_duree');

    document.addEventListener('DOMContentLoaded', () => {
    const dureeInput = document.getElementById('sortie_form_type_duree');
    const dureeDisplay = document.getElementById('dureeDisplay');

    dureeInput.addEventListener('input', () => {
        const duree = parseInt(dureeInput.value, 10);
        if (!isNaN(duree) && duree >= 60) {
            const days = Math.floor(duree / 1440);
            const hours = Math.floor((duree % 1440) / 60);
            const minutes = duree % 60;

            let displayText = '';
            if (days > 0) {
                displayText += `${days} jour(s) `;
            }
            if (hours > 0) {
                displayText += `${hours} heure(s) `;
            }
            if (minutes > 0) {
                displayText += `${minutes} minute(s)`;
            }

            dureeDisplay.textContent = displayText;
        } else {
            dureeDisplay.textContent = '';
        }
    });
});
});
