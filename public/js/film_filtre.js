document.addEventListener('DOMContentLoaded', () => {
    // Initialisation Choices.js
    const villeChoices = new Choices('#ville', { searchEnabled: false, itemSelectText: '' });
    const genreChoices = new Choices('#genre', { searchEnabled: false, itemSelectText: '' });
    const jourChoices = new Choices('#jour', { searchEnabled: false, itemSelectText: '' });

    // Récupère les vrais <select> après transformation
    const villeSelect = document.getElementById('ville');
    const genreSelect = document.getElementById('genre');
    const jourSelect = document.getElementById('jour');

    function updateFilms() {
        const params = new URLSearchParams();

        const ville = villeSelect.value;
        const genre = genreSelect.value;
        const jour = jourSelect.value;

        console.log('Filtres appliqués :', { ville, genre, jour });

        if (ville) params.append('ville', ville);
        if (genre) params.append('genre', genre);
        if (jour) params.append('jour', jour);

        fetch('/films/filter?' + params.toString(), {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur serveur lors du filtrage');
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('films-container').innerHTML = html;
        })
        .catch(error => {
            console.error('Erreur AJAX :', error);
        });
    }

    // Écoute les changements via Choices
    villeSelect.addEventListener('change', updateFilms);
    genreSelect.addEventListener('change', updateFilms);
    jourSelect.addEventListener('change', updateFilms);
});
