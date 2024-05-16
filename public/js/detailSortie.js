document.addEventListener('DOMContentLoaded', function() {
    const sortieId = document.querySelector('.card-header').dataset.sortieId;
    const cardFooter = document.querySelector('.card-footer');
    const userIsOrganizer = cardFooter.dataset.userIsOrganizer === 'true';
    const etatSortie = cardFooter.dataset.etatSortie;
    const dateSortie = new Date(cardFooter.dataset.dateSortie);

    function addButton(label, className, action) {
        const button = document.createElement('button');
        button.textContent = label;
        button.classList.add('btn', className);
        button.dataset.sortieId = sortieId;
        button.addEventListener('click', action);
        cardFooter.appendChild(button);
    }

    function showModal() {
        $('#annulModal').modal('show');
    }

    function annulerSortie() {
        const motif = document.getElementById('annulReason').value;
        fetch(`/sortir/ajax_annulerSortie/${sortieId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ motif: motif })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(`Erreur : ${data.error}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de l\'annulation de la sortie.');
            });
    }

    function publierSortie() {
        if (dateSortie < new Date()) {
            alert('La date de la sortie est antérieure à la date actuelle, vous ne pouvez pas la publier.');
            return;
        }
        fetch(`/sortir/ajax_publierSortie/${sortieId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(`Erreur : ${data.error}`);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de la publication de la sortie.');
            });
    }

    function inscrire(sortieId) {
        fetch(`/sortir/ajax_inscriptionSortie/${sortieId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    console.error(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function desinscrire(sortieId) {
        fetch(`/sortir/ajax_desinscriptionSortie/${sortieId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    console.error(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    document.querySelectorAll('.inscription-link').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const sortieId = this.dataset.sortieId;
            inscrire(sortieId);
        });
    });

    document.querySelectorAll('.desinscription-link').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const sortieId = this.dataset.sortieId;
            desinscrire(sortieId);
        });
    });

    if (userIsOrganizer) {
        if (etatSortie === 'Créée') {
            addButton('Modifier', 'btn-primary', () => {
                window.location.href = `/sortir/modifierSortie/${sortieId}`;
            });
            addButton('Publier', 'btn-success', publierSortie);
            addButton('Annuler', 'btn-warning', showModal);
        } else if (etatSortie === 'Ouverte') {
            addButton('Annuler', 'btn-warning', showModal);
        }
    }

    document.getElementById('confirmAnnulButton').addEventListener('click', annulerSortie);

    const mapElement = document.getElementById('map');
    const latitude = parseFloat(mapElement.dataset.latitude);
    const longitude = parseFloat(mapElement.dataset.longitude);

    if (latitude && longitude) {
        const map = L.map('map').setView([latitude, longitude], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        const marker = L.marker([latitude, longitude]).addTo(map);
    } else {
        console.error('Latitude and/or Longitude data not found');
    }
});
