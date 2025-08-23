document.addEventListener('DOMContentLoaded', () => {
    // Choices.js optionnel
    const safeInit = (selector) => {
      try { if (window.Choices) return new Choices(selector, { searchEnabled: false, itemSelectText: '' }); } catch {}
      return null;
    };
    safeInit('#ville'); safeInit('#genre'); safeInit('#jour');
  
    const villeSelect = document.getElementById('ville');
    const genreSelect = document.getElementById('genre');
    const jourSelect  = document.getElementById('jour');
  
    const container   = document.getElementById('films-container');
    const pager       = document.querySelector('.pagination');
  
    // Map FR -> ISO-8601 (1..7)
    const MAP_JOUR = {
      'Lundi':1,'Mardi':2,'Mercredi':3,'Jeudi':4,'Vendredi':5,'Samedi':6,'Dimanche':7
    };
  
    // Charge la disponibilité (film -> {villes[], jours[]})
    let AVAIL = {};
    try {
      const json = document.getElementById('film-availability')?.textContent || '{}';
      AVAIL = JSON.parse(json);
    } catch { AVAIL = {}; }
  
    // Filtrage instantané
    function applyFilters() {
      const ville = (villeSelect.value || '').trim();
      const genre = (genreSelect.value || '').trim().toLowerCase();
      const jour  = (jourSelect.value  || '').trim();
      const wantedDow = MAP_JOUR[jour] || null;
  
      const cards = container.querySelectorAll('.film-horizontal-card');
  
      let anyFilter = !!(ville || genre || jour);
      let visibles = 0;
  
      cards.forEach(card => {
        const fid    = parseInt(card.dataset.filmId || '0', 10);
        const fgenre = (card.dataset.genre || '').toLowerCase();
  
        const okGenre = !genre || fgenre === genre;
  
        const a = AVAIL[fid] || { villes: [], jours: [] };
        const okVille = !ville || (a.villes && a.villes.includes(ville));
        const okJour  = !wantedDow || (a.jours && a.jours.includes(wantedDow));
  
        const show = okGenre && okVille && okJour;
  
        card.style.display = show ? '' : 'none';
        if (show) visibles++;
      });
  
      // Si on filtre côté client, on masque la pagination serveur (sinon elle ne correspond plus)
      if (pager) pager.style.display = anyFilter ? 'none' : '';
    }
  
    villeSelect.addEventListener('change', applyFilters);
    genreSelect.addEventListener('change', applyFilters);
    jourSelect.addEventListener('change', applyFilters);
  });
  