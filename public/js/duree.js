cument.addEventListener('DOMContentLoaded', (event) => {
    const dureeInput = document.querySelector('#sortie_duree');
    const dureeDisplay = document.createElement('p');
    dureeInput.parentNode.appendChild(dureeDisplay);

    dureeInput.addEventListener('input', () => {
        const duree = parseInt(dureeInput.value, 10);
        if (duree >= 60) {
            const days = Math.floor(duree / 1440);
            const hours = Math.floor((duree % 1440) / 60);
            const minutes = duree % 60;

            let displayText = '';
            if (days > 0) {
                displayText += `${days} jours `;
            }
            if (hours > 0 || days > 0) {
                displayText += `${hours} heures `;
            }
            if (minutes > 0) {
                displayText += `${minutes} minutes`;
            }

            dureeDisplay.textContent = displayText;
        } else {
            dureeDisplay.textContent = '';
        }
    });
});
