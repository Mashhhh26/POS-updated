/* POS THEME BOOTSTRAP — runs before the main initializer */
(function(){try{var t=localStorage.getItem('pos_theme');var p=localStorage.getItem('pos_palette');if(t==='dark'||t==='light')document.documentElement.setAttribute('data-pos-theme',t);if(['indigo','blue','emerald','violet','rose','amber'].includes(p))document.documentElement.setAttribute('data-pos-palette',p);}catch(e){}})();

(function () {
  'use strict';

  const root = document.documentElement;

  function saved(key, fallback) {
    try { return localStorage.getItem(key) || fallback; } catch (e) { return fallback; }
  }

  function store(key, value) {
    try { localStorage.setItem(key, value); } catch (e) {}
  }

  function updateThemeIcon() {
    const icon = document.getElementById('themeIcon');
    if (!icon) return;
    const dark = root.getAttribute('data-pos-theme') === 'dark';
    icon.className = dark ? 'fas fa-sun' : 'fas fa-moon';
    icon.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
  }

  function animateTheme() {
    const overlay = document.getElementById('themeTransition');
    if (!overlay) return;
    overlay.classList.remove('run');
    void overlay.offsetWidth;
    overlay.style.setProperty('--theme-x', '92%');
    overlay.style.setProperty('--theme-y', '8%');
    overlay.classList.add('run');
  }

  window.setThemeMode = function (mode) {
    if (mode !== 'light' && mode !== 'dark') return;
    animateTheme();
    root.setAttribute('data-pos-theme', mode);
    root.setAttribute('data-bs-theme', mode);
    store('pos_theme', mode);
    updateThemeIcon();
  };

  window.setThemePalette = function (palette) {
    const allowed = ['indigo', 'blue', 'emerald', 'violet', 'rose', 'amber'];
    if (!allowed.includes(palette)) return;
    animateTheme();
    root.setAttribute('data-pos-palette', palette);
    store('pos_palette', palette);
  };

  window.toggleTheme = function () {
    const next = root.getAttribute('data-pos-theme') === 'dark' ? 'light' : 'dark';
    window.setThemeMode(next);
  };

  function swalBase(opts){
    const primary=getComputedStyle(root).getPropertyValue('--pos-primary').trim() || '#4f46e5';
    const dark=root.getAttribute('data-pos-theme')==='dark';
    return Object.assign({
      buttonsStyling:true,
      customClass:{popup:'pos-swal',confirmButton:'pos-swal-confirm',cancelButton:'pos-swal-cancel'},
      confirmButtonColor:primary,
      background:dark?'#172033':'#ffffff',
      color:dark?'#f8fafc':'#172033'
    },opts||{});
  }

  window.showSuccess = function (message, title) {
    if (window.Swal) return Swal.fire(swalBase({icon:'success',title:title || 'Success',html:String(message)}));
    window.alert(message);
  };

  window.showError = function (message, title) {
    if (window.Swal) return Swal.fire(swalBase({icon:'error',title:title || 'Something went wrong',html:String(message)}));
    window.alert(message);
  };

  window.confirmAction = function (message, confirmText, callback, title) {
    if (!window.Swal) return window.confirm(message) && typeof callback === 'function' && callback();
    Swal.fire(swalBase({
      icon:'warning', title:title || 'Are you sure?', html:String(message),
      showCancelButton:true, confirmButtonText:confirmText || 'Continue', cancelButtonText:'Cancel',
      reverseButtons:true
    })).then(result => { if (result.isConfirmed && typeof callback === 'function') callback(); });
  };

  window.confirmDelete = function (message, callback) {
    return window.confirmAction(message || 'This action cannot be undone.', 'Delete', callback, 'Delete permanently?');
  };

  document.addEventListener('DOMContentLoaded', function () {
    const theme = saved('pos_theme', 'light');
    const palette = saved('pos_palette', 'indigo');
    root.setAttribute('data-pos-theme', theme === 'dark' ? 'dark' : 'light');
    root.setAttribute('data-bs-theme', theme === 'dark' ? 'dark' : 'light');
    root.setAttribute('data-pos-palette', ['indigo','blue','emerald','violet','rose','amber'].includes(palette) ? palette : 'indigo');
    updateThemeIcon();

    const flash = document.getElementById('flash-message');
    if (flash) {
      const message = flash.dataset.message || '';
      const type = flash.dataset.type === 'success' ? 'success' : 'error';
      if (window.Swal) {
        Swal.fire(swalBase({ toast:true, position:'top-end', icon:type, title:type === 'success' ? 'Success' : 'Error', text:message, timer:2600, timerProgressBar:true, showConfirmButton:false }));
        flash.remove();
      } else {
        const alert = document.createElement('div');
        alert.className = 'alert alert-' + (type === 'success' ? 'success' : 'danger') + ' alert-dismissible fade show';
        alert.textContent = message;
        const btn = document.createElement('button');
        btn.type='button'; btn.className='btn-close'; btn.dataset.bsDismiss='alert';
        alert.appendChild(btn); flash.replaceWith(alert);
      }
    }

    document.querySelectorAll('.theme-choice[data-palette]').forEach(btn => {
      btn.addEventListener('click', function () {
        window.setThemePalette(this.dataset.palette);
      });
    });
  });
})();
