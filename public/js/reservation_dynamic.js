document.addEventListener('DOMContentLoaded', function () {
    const cinemaField = document.querySelector('#Reservation_cinema');
    const filmField = document.querySelector('#Reservation_film');

    if (!cinemaField || !filmField) {
        console.warn('Champ cinéma ou film non trouvé dans le DOM');
        return;
    }

    cinemaField.addEventListener('change', function () {
        const cinemaId = this.value;
        filmField.innerHTML = '<option value="">Chargement...</option>';

        if (!cinemaId) {
            filmField.innerHTML = '<option value="">Sélectionnez un film</option>';
            return;
        }

        fetch(`/admin/ajax/films?cinemaId=${cinemaId}`)
            .then(response => response.json())
            .then(data => {
                filmField.innerHTML = '<option value="">Sélectionnez un film</option>';
                data.forEach(film => {
                    const option = document.createElement('option');
                    option.value = film.id;
                    option.textContent = film.titre;
                    filmField.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Erreur lors du chargement des films:', error);
                filmField.innerHTML = '<option value="">Erreur de chargement</option>';
            });
    });
});
