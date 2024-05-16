document.addEventListener('DOMContentLoaded', function() {
    const toggleInscriptionLinks = document.querySelectorAll('.toggle-inscription-link');

    toggleInscriptionLinks.forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            const sortieId = this.dataset.sortieId;
            if (this.textContent === 'S\'inscrire') {
                inscrire(sortieId);
            } else {
                desinscrire(sortieId);
            }
        });
    });

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
});
