
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.toast').forEach((t) => {
        const close = t.querySelector('.toast-close');
        const hide = () => {
          t.classList.add('hide');
          setTimeout(() => t.remove(), 250); // durée = animation CSS
        };
        const timer = setTimeout(hide, 5000); // 5 secondes

        if (close) {
          close.addEventListener('click', () => {
            clearTimeout(timer);
            hide();
          });
        }
      });
    });
  
