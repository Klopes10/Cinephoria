(() => {
  const $city   = document.getElementById('citySelect');
  const $film   = document.getElementById('filmSelect');
  const $places = document.getElementById('placesSelect');
  const $days   = document.getElementById('dayStrip');
  const $area   = document.getElementById('sessionsArea');

  // Jour actif (bouton sélectionné) ou aujourd’hui
  let currentDate = document.querySelector('.day-pill.active')?.dataset.date
                 || new Date().toISOString().slice(0,10);

  // Ville courante (null au chargement => empêche l’affichage des films)
  let currentCity = null;

  const HINT_NEED_CITY   = 'Sélectionnez un cinéma pour choisir votre séance.';
  const HINT_NO_SESSIONS = 'Aucune séance disponible.';

  function hint(msg){
    $area.innerHTML = `<p class="hint">${msg || HINT_NEED_CITY}</p>`;
  }

  // Normalise l’URL d’affiche
  function resolvePosterPath(raw) {
    if (!raw) return window.PLACEHOLDER || '/images/placeholder.jpg';
    if (raw.startsWith('http') || raw.startsWith('/')) return raw;
    if (raw.startsWith('uploads/')) return '/' + raw;
    return (window.AFFICHES_BASE || '/uploads/affiches/') + raw;
  }

  // Met à jour le sélecteur "places" selon les séances visibles (borne max 10)
  function updatePlacesMax(filmsObj){
    let max = 0;
    Object.values(filmsObj).forEach(f=>{
      (f.seances || []).forEach(s=>{ if (s.places > max) max = s.places; });
    });
    if (max < 1) max = 10;
    if (max > 10) max = 10;

    const prev = $places.value;
    let html = `<option value="">Sélectionner le nombre de places</option>`;
    for (let i=1; i<=max; i++) {
      html += `<option value="${i}">${i} place${i>1?'s':''}</option>`;
    }
    $places.innerHTML = html;
    if (prev && parseInt(prev) <= max) $places.value = prev;
  }

  // Remplit la liste des films pour la ville courante
  function populateFilmSelectForCity(){
    if (!currentCity || !filmsByCity[currentCity]) {
      $film.innerHTML  = `<option value="">Choisir un film</option>`;
      $film.disabled   = true;
      $places.disabled = true;
      return;
    }
    let html = `<option value="">Tous les films</option>`;
    filmsByCity[currentCity].forEach(f => {
      html += `<option value="${f.id}">${f.titre}</option>`;
    });
    $film.innerHTML  = html;
    $film.disabled   = false;
    $places.disabled = false;
  }

  // Rendu des films & séances — n’affiche rien tant que la ville n’est pas choisie
  function renderFilms() {
    if (!currentCity) {               // 🔒 ville obligatoire
      hint(HINT_NEED_CITY);
      return;
    }

    const filmsForDay = allData[currentCity]?.[currentDate] || {}; // uniquement la ville choisie
    const filmId      = $film.value || '';
    const minPlaces   = parseInt($places.value) || 0;

    let entries = Object.values(filmsForDay);
    if (filmId) entries = entries.filter(f => String(f.film.id) === String(filmId));

    updatePlacesMax(filmsForDay);

    let html = '';
    entries.forEach(f => {
      const seances = (f.seances || []).filter(s => !minPlaces || s.places >= minPlaces);
      if (seances.length === 0) return;

      const poster   = resolvePosterPath(f.film.affiche || '');
      const synopsis = f.film.synopsis || '';
      const age      = (f.film.age ?? f.film.ageMinimum);
      const genre    = (f.film.genre && (f.film.genre.nom || f.film.genre)) || f.film.genreName || '';

      const metaParts = [];
      if (age != null && age !== '') metaParts.push(`-${age} ans`);
      else metaParts.push('Tout public');
      if (genre) metaParts.push(genre);
      const metaLine = metaParts.join('  |  ');

      // Badge coup de cœur si film.coupDeCoeur est vrai
      const badge = f.film.coupDeCoeur
        ? `
          <div class="badge">
            Coup de cœur
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </div>
        ` : '';

      html += `
        <div class="film-horizontal-card">
          <div class="film-poster" style="position:relative">
            ${badge}
            <a href="/films/${f.film.id}">
              <img src="${poster}" alt="${f.film.titre}">
            </a>
          </div>
          <div class="film-info">
            <h3><a href="/films/${f.film.id}" style="color:inherit;text-decoration:none">${f.film.titre}</a></h3>
            <p class="meta">${metaLine}</p>
            ${synopsis ? `<p class="desc">${synopsis}</p>` : ''}
            <div class="sessions-grid">
              ${seances.map(s => `
                <a class="session-chip" href="/reservation/seance/${s.id}">
                  <div class="chip-top">
                    ${s.format ? `<span class="chip-version">${String(s.format).toLowerCase()}</span>` : ''}
                  </div>
                  <div class="chip-time">${s.heure}</div>
                  <div class="chip-end">${
                    s.fin ? `fin à ${s.fin}` : `${s.places} place${s.places > 1 ? 's' : ''} restantes`
                  }</div>
                  <div class="chip-bottom">
                    <div class="chip-icons">
                      <span class="icon" aria-label="Accès fauteuil">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                             viewBox="0 0 24 24" aria-hidden="true">
                          <circle cx="9" cy="4" r="2" fill="currentColor"/>
                          <path fill="currentColor" d="M16.98 14.804A1 1 0 0 0 16 14h-4.133l-.429-3H16V9h-4.847l-.163-1.142A1 1 0 0 0 10 7H9a1.003 1.003 0 0 0-.99 1.142l.877 6.142A2.01 2.01 0 0 0 10.867 16h4.313l.839 4.196c.094.467.504.804.981.804h3v-2h-2.181z"/>
                          <path fill="currentColor" d="M12.51 17.5c-.739 1.476-2.25 2.5-4.01 2.5A4.505 4.505 0 0 1 4 15.5a4.5 4.5 0 0 1 2.817-4.167l-.289-2.025C3.905 10.145 2 12.604 2 15.5C2 19.084 4.916 22 8.5 22a6.5 6.5 0 0 0 5.545-3.126l-.274-1.374z"/>
                        </svg>
                      </span>
                    </div>
                    <span class="chip-room">${s.salle || ''}</span>
                  </div>
                </a>
              `).join('')}
            </div>
          </div>
        </div>
      `;
    });

    $area.innerHTML = html || `<p class="hint">${HINT_NO_SESSIONS}</p>`;
  }

  /* ----------------- Events ----------------- */

  $city.addEventListener('change', () => {
    currentCity = $city.value || null;
    if (!currentCity) {
      $film.value = '';
      $places.value = '';
      $film.disabled = true;
      $places.disabled = true;
      hint(HINT_NEED_CITY);
      return;
    }
    populateFilmSelectForCity();
    renderFilms();
  });

  $days.addEventListener('click', (e) => {
    const btn = e.target.closest('.day-pill'); if (!btn) return;
    document.querySelectorAll('.day-pill').forEach(b=>b.classList.remove('active'));
    btn.classList.add('active');
    currentDate = btn.dataset.date || currentDate;
    if (!currentCity) {
      hint(HINT_NEED_CITY);
      return;
    }
    renderFilms();
  });

  $film.addEventListener('change', renderFilms);
  $places.addEventListener('change', renderFilms);

  $film.disabled   = true;
  $places.disabled = true;
  hint(HINT_NEED_CITY);
})();
