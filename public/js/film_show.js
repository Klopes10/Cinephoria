document.addEventListener('DOMContentLoaded', () => {
  const root = document.getElementById('film-show');
  if (!root) return;

  const filmId         = root.dataset.filmId;
  let   activeVille    = root.dataset.activeVille;
  let   activeDate     = root.dataset.activeDate;
  const reserveBase    = root.dataset.reserveBase || '/reservation/seance/';
  const reviewsPattern = root.dataset.reviewsPattern || '/films/ID/avis';

  // JSON préchargé (ville -> jour -> [séances])
  let cache = {};
  try {
    const jsonEl = document.getElementById('sessions-data');
    cache = JSON.parse((jsonEl && jsonEl.textContent) || '{}');
  } catch {
    cache = {};
  }

  const cities    = Array.from(document.querySelectorAll('.tabs.cities .tab'));
  const days      = Array.from(document.querySelectorAll('.tabs.days .tab'));
  const container = document.getElementById('sessions-container');
  const priceLegendEl = document.getElementById('price-legend');

  function setActive(list, value, attr) {
    list.forEach(a => {
      const v = a.getAttribute(attr);
      a.classList.toggle('active', v === value);
      // retirer le dot sur le jour actif
      if (attr === 'data-date' && v === value) {
        const dot = a.querySelector('.dot');
        if (dot) dot.remove();
      }
    });
  }

  function updateUrl() {
    const url = new URL(window.location.href);
    url.searchParams.set('ville', activeVille);
    url.searchParams.set('date', activeDate);
    window.history.replaceState({ ville: activeVille, date: activeDate }, '', url);
  }

  function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

  function tmplCard(s) {
    const href  = reserveBase + String(s.id);
    const salle = s.salle?.nom ?? s.salle ?? '';
    const fmtLabel = (typeof s.format === 'object' && s.format?.label) ? s.format.label : s.format;
    const fmt   = fmtLabel ? `<span class="chip-version">${esc(String(fmtLabel).toLowerCase())}</span>` : '';

    // heure: s.time ou s.date (string), sinon rien
    const time = s.time || (s.date && (typeof s.date === 'string' ? s.date : '')) || '';
    const end  = s.end || s.fin || null;
    const endHtml = end ? `<div class="chip-end">fin à ${esc(end)}</div>` : '';

    return `
      <a class="session-chip" href="${esc(href)}">
        <div class="chip-top">${fmt}</div>
        <div class="chip-time">${esc(time)}</div>
        ${endHtml}
        <div class="chip-bottom">
          <div class="chip-icons">
            <span class="icon" aria-label="Accès fauteuil">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="9" cy="4" r="2" fill="currentColor"/>
                <path fill="currentColor" d="M16.98 14.804A1 1 0 0 0 16 14h-4.133l-.429-3H16V9h-4.847l-.163-1.142A1 1 0 0 0 10 7H9a1.003 1.003 0 0 0-.99 1.142l.877 6.142A2.01 2.01 0 0 0 10.867 16h4.313l.839 4.196c.094.467.504.804.981.804h3v-2h-2.181z"/>
                <path fill="currentColor" d="M12.51 17.5c-.739 1.476-2.25 2.5-4.01 2.5A4.505 4.505 0 0 1 4 15.5a4.5 4.5 0 0 1 2.817-4.167l-.289-2.025C3.905 10.145 2 12.604 2 15.5C2 19.084 4.916 22 8.5 22a6.5 6.5 0 0 0 5.545-3.126l-.274-1.374z"/>
              </svg>
            </span>
            <span class="icon" aria-label="Accessibilité visuelle">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" aria-hidden="true">
                <path fill="currentColor" d="m5.24 22.51l1.43-1.42A14.06 14.06 0 0 1 3.07 16C5.1 10.93 10.7 7 16 7a12.4 12.4 0 0 1 4 .72l1.55-1.56A14.7 14.7 0 0 0 16 5A16.69 16.69 0 0 0 1.06 15.66a1 1 0 0 0 0 .68a16 16 0 0 0 4.18 6.17"/>
                <path fill="currentColor" d="M12 15.73a4 4 0 0 1 3.7-3.7l1.81-1.82a6 6 0 0 0-7.33 7.33zm18.94-.07a16.4 16.4 0 0 0-5.74-7.44L30 3.41L28.59 2L2 28.59L3.41 30l5.1-5.1A15.3 15.3 0 0 0 16 27a16.69 16.69 0 0 0 14.94-10.66a1 1 0 0 0 0-.68M20 16a4 4 0 0 1-6 3.44L19.44 14a4 4 0 0 1 .56 2m-4 9a13.05 13.05 0 0 1-6-1.58l2.54-2.54a6 6 0 0 0 8.35-8.35l2.87-2.87A14.54 14.54 0 0 1 28.93 16C26.9 21.07 21.3 25 16 25"/>
              </svg>
            </span>
          </div>
          <div class="chip-room">${esc(salle)}</div>
        </div>
      </a>
    `;
  }

  function renderSessions(city, date) {
    const list = (cache[city] && cache[city][date]) ? cache[city][date] : [];
    if (!list.length) {
      container.innerHTML = `<p class="no-session">Aucune séance pour ce jour${city && city !== '|' ? ' à ' + esc(city) : ''}.</p>`;
      if (priceLegendEl) { priceLegendEl.hidden = true; priceLegendEl.innerHTML = ''; }
      return;
    }
    container.innerHTML = `<div class="sessions-grid">${list.map(tmplCard).join('')}</div>`;
    updatePriceLegend(list);
  }

  function refreshDayDots() {
    const map = cache[activeVille] || {};
    days.forEach(a => {
      const ymd = a.getAttribute('data-date');
      const old = a.querySelector('.dot'); if (old) old.remove();
      const nb = (map[ymd] ? map[ymd].length : 0);
      if (ymd !== activeDate && nb > 0) {
        const dot = document.createElement('span');
        dot.className = 'dot';
        dot.setAttribute('aria-hidden', 'true');
        a.appendChild(dot);
      }
    });
  }

  // === Légende des prix (Qualité : Prix €)
  function updatePriceLegend(sessions) {
    if (!priceLegendEl) return;
    const map = new Map();
    (sessions || []).forEach(s => {
      const label = (typeof s.format === 'object' && s.format?.label) ? s.format.label : s.format;
      const price = s.prix ?? s.price ?? s.tarif ?? null;
      if (!label || price == null) return;
      if (!map.has(label)) map.set(label, price);
    });

    if (map.size === 0) {
      priceLegendEl.hidden = true;
      priceLegendEl.innerHTML = '';
      return;
    }

    const parts = [];
    for (const [label, price] of map.entries()) {
      const val = typeof price === 'number' ? Math.round(price) : String(price);
      parts.push(`<span class="item">${esc(label)} : ${esc(val)}&nbsp;€</span>`);
    }
    priceLegendEl.innerHTML = parts.join('<span class="sep" aria-hidden="true">|</span>');
    priceLegendEl.hidden = false;
  }

  // Interactions (sans rechargement)
  cities.forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      const v = a.getAttribute('data-ville');
      if (!v || v === '|' || v === activeVille) return;
      activeVille = v;
      setActive(cities, activeVille, 'data-ville');
      if (!cache[activeVille] || !cache[activeVille][activeDate]) {
        const firstDay = Object.keys(cache[activeVille] || {})[0];
        if (firstDay) {
          activeDate = firstDay;
          setActive(days, activeDate, 'data-date');
        }
      }
      renderSessions(activeVille, activeDate);
      refreshDayDots();
      updateUrl();
    });
  });

  days.forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      const d = a.getAttribute('data-date');
      if (!d || d === activeDate) return;
      activeDate = d;
      setActive(days, activeDate, 'data-date');
      renderSessions(activeVille, activeDate);
      refreshDayDots();
      updateUrl();
    });
  });

  // Init affichage
  renderSessions(activeVille, activeDate);
  refreshDayDots();

  // ===== Pop-up des avis (pré-rendue) =====
  const modal   = document.getElementById('reviews-modal');
  function openModal() {
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
  }
  function closeModal() {
    if (!modal) return;
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
  }
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.reviews-link');
    if (btn) {
      e.preventDefault();
      openModal();
    }
    if (e.target.matches('.modal-backdrop, .modal-close')) {
      closeModal();
    }
  });
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });
});
