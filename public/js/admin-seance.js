// public/js/admin-seance.js
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form.ea-new-form, form.ea-edit-form');
    if (!form) return;
  
    // On vise les vrais champs de données (les <select> générés par EA)
    const cinemaSelect = form.querySelector("select[name$='[cinema]']");
    const salleSelect  = form.querySelector("select[name$='[salle]']");
  
    if (!cinemaSelect || !salleSelect) return;
  
    const endpointBase = salleSelect.getAttribute('data-endpoint') || '/admin/ajax/salles-by-cinema';
  
    function disableSalle(disabled, placeholder) {
      salleSelect.innerHTML = '';
      const opt = document.createElement('option');
      opt.value = '';
      opt.textContent = placeholder || (disabled ? '— D’abord choisir un cinéma —' : '— Sélectionner une salle —');
      salleSelect.appendChild(opt);
      salleSelect.toggleAttribute('disabled', disabled);
    }
  
    async function loadSallesForCinema(cinemaId, keepCurrent = true) {
      if (!cinemaId) {
        disableSalle(true, '— D’abord choisir un cinéma —');
        return;
      }
  
      const previousValue = keepCurrent ? salleSelect.value : '';
  
      disableSalle(false, '— Sélectionner une salle —');
  
      try {
        const res  = await fetch(`${endpointBase}/${encodeURIComponent(cinemaId)}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
  
        // remplir
        data.forEach(({ id, label }) => {
          const opt = document.createElement('option');
          opt.value = id;
          opt.textContent = label;
          salleSelect.appendChild(opt);
        });
  
        // tenter de restaurer la valeur si toujours présente
        if (previousValue && Array.from(salleSelect.options).some(o => o.value === previousValue)) {
          salleSelect.value = previousValue;
        } else {
          // sinon reset
          salleSelect.value = '';
        }
      } catch (e) {
        console.error('Erreur lors du chargement des salles:', e);
      }
    }
  
    // Init (nouveau / édition)
    if (cinemaSelect.value) {
      loadSallesForCinema(cinemaSelect.value, true);
    } else {
      disableSalle(true, '— D’abord choisir un cinéma —');
    }
  
    // Lorsque le cinéma change
    cinemaSelect.addEventListener('change', () => {
      const id = cinemaSelect.value || '';
      loadSallesForCinema(id, false);
    });
  });
  