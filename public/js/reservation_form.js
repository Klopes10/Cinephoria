// public/js/admin/reservation_form.js

alert("Form chargé");
document.addEventListener('DOMContentLoaded', function () {
    const cinemaSelect = document.querySelector('select[name="cinema"]');
    const filmSelect = document.querySelector('select[name="film"]');
    const seanceSelect = document.querySelector('select[name="Reservation[seance]"]');

    if (!cinemaSelect || !filmSelect || !seanceSelect) return;

    cinemaSelect.addEventListener('change', function () {
        const cinemaId = this.value;

        filmSelect.innerHTML = '<option value="">Chargement...</option>';
        fetch(`/admin/ajax/films?cinemaId=${cinemaId}`)
            .then(res => res.json())
            .then(films => {
                filmSelect.innerHTML = '<option value="">Sélectionnez un film</option>';
                films.forEach(film => {
                    const option = document.createElement('option');
                    option.value = film.id;
                    option.textContent = film.titre;
                    filmSelect.appendChild(option);
                });
                seanceSelect.innerHTML = '<option value="">Sélectionnez un film d’abord</option>';
            });
    });

    filmSelect.addEventListener('change', function () {
        const cinemaId = cinemaSelect.value;
        const filmId = this.value;

        seanceSelect.innerHTML = '<option value="">Chargement...</option>';
        fetch(`/admin/ajax/seances?cinemaId=${cinemaId}&filmId=${filmId}`)
            .then(res => res.json())
            .then(seances => {
                seanceSelect.innerHTML = '<option value="">Sélectionnez une séance</option>';
                seances.forEach(seance => {
                    const option = document.createElement('option');
                    option.value = seance.id;
                    option.textContent = seance.label;
                    seanceSelect.appendChild(option);
                });
            });
    });
});
