const $city   = document.getElementById('citySelect');
const $film   = document.getElementById('filmSelect');
const $places = document.getElementById('placesSelect');
const $days   = document.getElementById('dayStrip');
const $area   = document.getElementById('sessionsArea');

let currentCity = null;
// On part sur le 1er jour actif pour afficher quelque chose dès l'arrivée
let currentDate = document.querySelector('.day-pill.active')?.dataset.date || new Date().toISOString().slice(0,10);

function hint(msg){
  $area.innerHTML = `<p class="hint">${msg || 'Sélectionner une ville, un film  puis un jour pour voir les séances disponibles.'}</p>`;
}

function resolvePosterPath(raw) {
    if (!raw) return window.PLACEHOLDER;
    if (raw.startsWith('http') || raw.startsWith('/')) return raw;
    if (raw.startsWith('uploads/')) return '/' + raw;        // déjà relatif public/
    return (window.AFFICHES_BASE || '/uploads/affiches/') + raw;
  }

function mergeAllCitiesForDate(date){
  const merged = {};
  Object.entries(allData || {}).forEach(([cityKey, byDate])=>{
    if (byDate && byDate[date]) {
      Object.values(byDate[date]).forEach(f=>{
        if (!merged[f.film.id]) merged[f.film.id] = { film: f.film, seances: [] };
        merged[f.film.id].seances.push(...f.seances);
      });
    }
  });
  return merged;
}

function updatePlacesMax(filmsObj){
  // Prend le max de places sur les séances actuellement considérées, borné à 10
  let max = 0;
  Object.values(filmsObj).forEach(f=>{
    f.seances.forEach(s=>{ if (s.places > max) max = s.places; });
  });
  if (max <= 0) max = 10;
  if (max > 10) max = 10;

  const prev = $places.value;
  let html = `<option value="">Sélectionner le nombre de places</option>`;
  for (let i=1; i<=max; i++) html += `<option value="${i}">${i}</option>`;
  $places.innerHTML = html;
  // Tente de conserver la valeur si elle est encore possible
  if (prev && parseInt(prev) <= max) $places.value = prev;
}

function renderFilms() {
  const cityFilter = currentCity;
  const filmsByCity = cityFilter ? (allData[cityFilter]?.[currentDate] || {}) : mergeAllCities(currentDate);

  const filmId = $film.value || '';
  const minPlaces = parseInt($places.value) || 0;

  let filmsToShow = Object.values(filmsByCity);
  if (filmId) filmsToShow = filmsToShow.filter(f => f.film.id == filmId);

  let html = '';
  filmsToShow.forEach(f => {
    const seances = f.seances.filter(s => !minPlaces || s.places >= minPlaces);
    if (seances.length === 0) return;

    const poster = resolvePosterPath(f.film.affiche);
   html += `
  <a class="film-horizontal-card">
    <img src="${poster}" alt="${f.film.titre}">
        <div class="film-info">
          <h2>${f.film.titre}</h2>
          <p class="public">${f.film.age ? 'Déconseillé aux moins de ' + f.film.age + ' ans' : 'Tout public'}</p>
          <p class="desc">${f.film.synopsis}</p>
          <div class="sessions-grid">
            ${seances.map(s => `
              <a class="session-chip" href="/reservation/seance/${s.id}">
                <div class="chip-top"><span class="chip-version">séance</span></div>
                <div class="chip-time">${s.heure}</div>
                <div class="chip-end">${s.places} places restantes</div>
                <div class="chip-bottom"><span class="chip-room">${s.salle}</span></div>
              </a>
            `).join('')}
          </div>
        </div>
      </a>`;
  });

  $area.innerHTML = html || `<p class="hint">Aucune séance disponible.</p>`;
}


function populateFilmSelectForCity(){
  // Remplit le select Film avec TOUS les films ayant ≥ 1 séance dans la ville (peu importe le jour de la semaine)
  if (!currentCity || !filmsByCity[currentCity]) {
    $film.innerHTML = `<option value="">Choisir un film</option>`;
    $film.disabled = true;
    return;
  }
  let html = `<option value="">Tous les films</option>`;
  filmsByCity[currentCity].forEach(f => {
    html += `<option value="${f.id}">${f.titre}</option>`;
  });
  $film.innerHTML = html;
  $film.disabled = false;
}

/* ----------------- Events ----------------- */

// Ville : on garde TOUTES les villes listées; au changement → on remplit le select Film (liste globale de la ville)
$city.addEventListener('change', ()=>{
  currentCity = $city.value || null;
  populateFilmSelectForCity();
  renderFilms();
});

// Jour : pilote l'affichage (ne modifie PAS la liste des films de la ville)
$days.addEventListener('click', (e)=>{
  const btn = e.target.closest('.day-pill'); if (!btn) return;
  document.querySelectorAll('.day-pill').forEach(b=>b.classList.remove('active'));
  btn.classList.add('active');
  currentDate = btn.dataset.date || currentDate;
  renderFilms();
});

// Film / Places : affinent l'affichage
$film.addEventListener('change', renderFilms);
$places.addEventListener('change', renderFilms);

// Initial render : jour actif (toutes villes confondues), filmSelect désactivé tant que pas de ville choisie
populateFilmSelectForCity();
renderFilms();


