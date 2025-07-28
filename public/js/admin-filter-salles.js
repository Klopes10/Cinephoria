document.addEventListener('DOMContentLoaded', function () {
    const cinemaSelect = document.querySelector('[name="cinemaSelectionne"]');
    const salleSelect = document.querySelector('[name$="[salle]"]');

    if (!cinemaSelect || !salleSelect) return;

    cinemaSelect.addEventListener('change', function () {
        const cinemaId = this.value;

        fetch(`/admin/salles-by-cinema/${cinemaId}`)
            .then(response => response.json())
            .then(salles => {
                salleSelect.innerHTML = '';

                salles.forEach(salle => {
                    const option = document.createElement('option');
                    option.value = salle.id;
                    option.textContent = salle.nom;
                    salleSelect.appendChild(option);
                });
            });
    });
});
