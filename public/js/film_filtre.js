document.addEventListener('DOMContentLoaded', () => {
    const villeSelect = document.getElementById('ville');

    if (villeSelect) {
        villeSelect.addEventListener('change', () => {
            const selectedVille = villeSelect.value;

            fetch('/films/par-ville?ville=' + encodeURIComponent(selectedVille), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors du chargement des films.');
                }
                return response.text();
            })
            .then(html => {
                const filmsContainer = document.getElementById('films-container');
                filmsContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Erreur AJAX :', error);
            });
        });
    }
});
