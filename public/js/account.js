// public/js/account.js
document.addEventListener('DOMContentLoaded', () => {
  const $  = (s, r=document)=>r.querySelector(s);
  const $$ = (s, r=document)=>Array.from(r.querySelectorAll(s));

  const cfg        = $('#rating-config');
  const ENDPOINT   = cfg?.dataset.endpoint || '';
  const STAR_FULL  = cfg?.dataset.starFull  || '';
  const STAR_EMPTY = cfg?.dataset.starEmpty || '';

  const modal     = $('#rate-modal');
  const backdrop  = modal?.querySelector('.modal-backdrop');
  const btnClose  = modal?.querySelector('.modal-close');
  const btnOK     = $('#rate-validate');
  const commentEl = $('#rate-comment-input');

  let currentReservationId = null;
  let currentValue = 0;

  const clamp = (v,min,max)=>Math.max(min,Math.min(max,v));

  // ---- Init : peindre les étoiles inline d'après data-initial
  $$('.js-rate-trigger').forEach(c => {
    const initial = clamp(parseInt(c.dataset.initial||'0',10)||0,0,5);
    c.querySelectorAll('.star-btn').forEach((btn, idx) => {
      const i = idx + 1;
      btn.style.setProperty('--star', i <= initial ? `url(${STAR_FULL})` : `url(${STAR_EMPTY})`);
    });
  });

  // ---- Modal helpers
  function paintModalStars(v) {
    const val = clamp(parseInt(v,10)||0,0,5);
    $$('.m-star-btn', modal).forEach((btn, idx) => {
      const img = btn.querySelector('.m-star-ico');
      const filled = (idx+1) <= val;
      img.src = filled ? STAR_FULL : STAR_EMPTY;
      img.alt = filled ? '★' : '☆';
    });
  }
  function openModal(resId, initialValue) {
    currentReservationId = resId;
    currentValue = clamp(parseInt(initialValue,10)||0,0,5);
    paintModalStars(currentValue);
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden','false');
  }
  function closeModal() {
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden','true');
  }
  if (modal) paintModalStars(0);

  // ---- Ouvrir la modale au clic (ignore si verrouillé)
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-rate-trigger .star-btn');
    if (btn) {
      const container = btn.closest('.js-rate-trigger');
      if (container.classList.contains('is-locked')) return; // déjà noté
      e.preventDefault();
      openModal(container.dataset.reservation, btn.dataset.value || '0');
      return;
    }
    const mBtn = e.target.closest('#rate-modal .m-star-btn[data-value]');
    if (mBtn) {
      currentValue = clamp(parseInt(mBtn.dataset.value||'0',10)||0,0,5);
      paintModalStars(currentValue);
      return;
    }
    if (e.target === backdrop || e.target.closest('.modal-close')) {
      closeModal();
    }
  });

  // Aperçu provisoire
  modal?.querySelector('.rate-stars')?.addEventListener('mouseleave', ()=>paintModalStars(currentValue));
  modal?.querySelector('.rate-stars')?.addEventListener('mousemove', (e)=>{
    const over = e.target.closest('.m-star-btn[data-value]');
    if (over) paintModalStars(over.dataset.value||'0');
  });

  function showFeedback(message, { autoClose=false } = {}) {
    const fb  = document.getElementById('rate-feedback');
    const box = modal?.querySelector('.modal-panel');
    if (!fb || !box) { closeModal(); return; }
  
    // masquer les contrôles
    modal.querySelector('.rate-stars')?.setAttribute('hidden', 'true');
    modal.querySelector('.rate-comment')?.setAttribute('hidden', 'true');
    btnOK?.setAttribute('hidden', 'true');
  
    // afficher le message
    fb.textContent = message;
    fb.style.display = 'block';
  
    if (autoClose) {
      setTimeout(() => {
        closeModal();
        // remettre la modale en état initial pour la prochaine ouverture
        fb.style.display = 'none';
        modal.querySelector('.rate-stars')?.removeAttribute('hidden');
        modal.querySelector('.rate-comment')?.removeAttribute('hidden');
        btnOK?.removeAttribute('hidden');
        commentEl && (commentEl.value = '');
        paintModalStars(0);
      }, 3000);
    }
  }
  
  // ---- Valider
  btnOK?.addEventListener('click', async () => {
    if (!ENDPOINT || !currentReservationId || currentValue < 1) return;
    const payload = {
      reservationId: currentReservationId,
      note: currentValue,
      comment: commentEl?.value || ""
    };
    try {
      const r = await fetch(ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify(payload)
      });
      const json = await r.json();
      if (!json.ok) { alert(json.error || 'Erreur'); return; }
  
      // MAJ immédiate de la ligne (comme avant)
      const rowStars = document.querySelector(`.js-rate-trigger[data-reservation="${json.resId}"]`);
      if (rowStars) {
        const N = Math.max(1, Math.min(5, parseInt(json.note || '0', 10) || 0));
        rowStars.querySelectorAll('.star-btn').forEach((btn, idx) => {
          const i = idx + 1;
          btn.style.setProperty('--star', i <= N ? `url(${STAR_FULL})` : `url(${STAR_EMPTY})`);
          btn.disabled = true;
        });
        rowStars.classList.add('is-locked');
        rowStars.dataset.initial = String(N);
  
        if (json.validated) {
          // Scénario 1 : pas de commentaire => validé directement
          rowStars.classList.remove('is-pending');
        } else {
          // Scénario 2 : commentaire => en attente
          rowStars.classList.add('is-pending');
          const cardRight = rowStars.parentElement; // .order-right
          if (cardRight && !cardRight.querySelector('.tag-pending')) {
            const tag = document.createElement('span');
            tag.className = 'tag tag-pending';
            tag.textContent = 'En attente de validation';
            cardRight.appendChild(tag);
          }
        }
      }
  
      // Message dans la pop-up selon le cas
      if (json.validated) {
        showFeedback("Merci d'avoir noté ce film.", { autoClose: true });
      } else {
        showFeedback("Votre avis a été déposé et est soumis à validation");
      }
    } catch (e) {
      console.error(e);
      alert('Erreur réseau');
    }
  });
  

  // ESC
  window.addEventListener('keydown', (e)=>{ if (e.key === 'Escape') closeModal(); });
});
