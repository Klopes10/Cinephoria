// public/js/account.js
document.addEventListener('DOMContentLoaded', () => {
    const $  = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  
    // ---------- Config ----------
    const cfg         = $('#rating-config');
    const ENDPOINT    = cfg?.dataset.endpoint || '/mon-compte/note';
    const STAR_FULL   = cfg?.dataset.starFull  || '';
    const STAR_EMPTY  = cfg?.dataset.starEmpty || '';
  
    // ---------- Modale ----------
    const modal     = $('#rate-modal');
    const backdrop  = modal ? modal.querySelector('.modal-backdrop') : null;
    const btnClose  = modal ? modal.querySelector('.modal-close')   : null;
    const btnOK     = $('#rate-validate');
    const commentEl = $('#rate-comment-input');
  
    let currentReservationId = null;
    let currentValue = 0; // valeur validée dans la modale
  
    const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
  
    function getModalStars() {
      return modal ? $$('.m-star-btn[data-value]', modal) : [];
    }
  
    function paintModalStars(value) {
      const v = clamp(parseInt(value || 0, 10), 0, 5);
      getModalStars().forEach((btn, idx) => {
        const img = btn.querySelector('.m-star-ico');
        if (!img) return;
        const filled = idx < v;
        img.src = filled ? STAR_FULL : STAR_EMPTY;
        img.alt = filled ? '★' : '☆';
        btn.setAttribute('aria-pressed', filled ? 'true' : 'false');
      });
    }
  
    function setModalValue(value) {
      currentValue = clamp(parseInt(value || 0, 10), 0, 5);
      paintModalStars(currentValue);
    }
  
    function openModal(reservationId, initialValue) {
      currentReservationId = reservationId;
      setModalValue(initialValue);
      if (commentEl) commentEl.value = '';
      modal.classList.remove('hidden');
      modal.setAttribute('aria-hidden', 'false');
    }
  
    function closeModal() {
      modal.classList.add('hidden');
      modal.setAttribute('aria-hidden', 'true');
    }
  
    // Init : étoiles de la modale à vide
    if (modal) paintModalStars(0);
  
    // ---------- Etoiles "inline" (dans la liste) ----------
    function fillInline(container, value) {
      const v = clamp(parseInt(value || 0, 10), 0, 5);
      const stars = $$('.star-btn', container);
      stars.forEach((btn, idx) => {
        btn.style.backgroundImage = idx < v ? `var(--star-full)` : `var(--star-empty)`;
      });
    }
  
    // Attache sur tous les groupes interactifs
    function initInlineGroups() {
      const groups = $$('.js-rate-trigger');
      groups.forEach(group => {
        // état initial : toutes vides
        fillInline(group, 0);
  
        // Survol -> remplir de gauche à droite jusqu’à la star survolée
        $$('.star-btn', group).forEach(btn => {
          btn.addEventListener('mouseenter', () => {
            const val = parseInt(btn.dataset.value || '0', 10);
            fillInline(group, val);
          });
        });
  
        // Sortie du groupe -> revenir à vide
        group.addEventListener('mouseleave', () => fillInline(group, 0));
  
        // Clic -> ouvrir modale avec la valeur cliquée
        group.addEventListener('click', (e) => {
          const star = e.target.closest('.star-btn');
          if (!star) return;
          e.preventDefault();
          const resId = group.dataset.reservation || null;
          const value = parseInt(star.dataset.value || '0', 10);
          openModal(resId, value);
        });
      });
    }
  
    initInlineGroups();
  
    // ---------- Interaction modale ----------
    // Hover dans la modale = aperçu
    modal?.addEventListener('mousemove', (e) => {
      const over = e.target.closest('.m-star-btn[data-value]');
      if (!over) return;
      paintModalStars(over.dataset.value || '0');
    });
  
    // Clique une étoile dans la modale = sélection
    modal?.addEventListener('click', (e) => {
      const star = e.target.closest('.m-star-btn[data-value]');
      if (star) {
        setModalValue(star.dataset.value || '0');
      }
    });
  
    // Sortie de la zone étoiles -> revenir sur la valeur choisie
    modal?.querySelector('.rate-stars')?.addEventListener('mouseleave', () => {
      paintModalStars(currentValue);
    });
  
    // Fermer modale
    btnClose?.addEventListener('click', closeModal);
    backdrop?.addEventListener('click', closeModal);
    window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
  
    // ---------- Valider (= POST + lock instantané dans la liste) ----------
    btnOK?.addEventListener('click', async () => {
      const note = clamp(currentValue, 1, 5);
      const comment = (commentEl?.value || '').trim();
  
      if (!currentReservationId || note < 1) {
        closeModal();
        return;
      }
  
      try {
        const resp = await fetch(ENDPOINT, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            reservationId: currentReservationId,
            note,
            comment
          })
        });
  
        const data = await resp.json();
  
        if (!resp.ok || !data.ok) {
          // Tu peux afficher un toast/flash ici si besoin
          closeModal();
          return;
        }
  
        // Remplacer le bloc interactif par un bloc "verrouillé" statique et plein à 'note'
        const selector = `.js-rate-trigger[data-reservation="${CSS.escape(String(currentReservationId))}"]`;
        const target = document.querySelector(selector);
        if (target) {
          target.classList.remove('js-rate-trigger');
          target.classList.add('is-locked');
  
          // Remplacer l'intérieur par 5 icônes statiques
          target.innerHTML = Array.from({ length: 5 })
            .map((_, i) => {
              const filled = i < note;
              const src = filled ? STAR_FULL : STAR_EMPTY;
              const alt = filled ? '★' : '☆';
              return `<span class="star-static"><img class="star-ico" src="${src}" alt="${alt}"></span>`;
            })
            .join('');
        }
  
        closeModal();
      } catch (err) {
        // Gestion erreur silencieuse
        closeModal();
      }
    });
  });
  