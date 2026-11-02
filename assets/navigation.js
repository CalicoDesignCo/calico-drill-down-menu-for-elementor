/* Calico Drilldown Navigation. Native disclosure navigation; no application-menu roles. */
(() => {
  'use strict';
  function init(nav) {
    if (nav.dataset.cdnReady) return;
    nav.dataset.cdnReady = 'true';
    nav.classList.add('cdn-enhanced');
    nav.dir = getComputedStyle(nav).direction;
    const root = nav.querySelector('.cdn-root');
    const mobile = nav.querySelector('.cdn-mobile-toggle');
    const media = matchMedia(`(max-width: ${Number(nav.dataset.breakpoint) || 767}px)`);
    const isMobile = () => nav.dataset.collapse !== 'never' && media.matches;
    const states = new Map();
    const controller = new AbortController();
    const listen = (el, event, fn) => el.addEventListener(event, fn, {signal: controller.signal});
    const panelFocus = panel => panel.querySelector('button,a');
    function resize(state) {
      if (!state.li.classList.contains('cdn-open')) return;
      const panel = state.stack[state.stack.length - 1].panel;
      state.viewport.style.height = `${panel.getBoundingClientRect().height}px`;
      // Keep a dropdown inside the viewport even when the header sits near an edge.
      state.dropdown.style.marginInlineStart = '';
      if (!isMobile()) {
        const rect = state.dropdown.getBoundingClientRect();
        const shift = Math.max(12 - rect.left, Math.min(0, innerWidth - 12 - rect.right));
        state.dropdown.style.marginInlineStart = `${nav.dir === 'rtl' ? -shift : shift}px`;
      }
    }
    function activate(state, focus = false) {
      const active = state.stack[state.stack.length - 1].panel;
      state.panels.forEach(panel => {
        const on = panel === active;
        panel.classList.toggle('cdn-active', on);
        panel.classList.toggle('cdn-past', !on && state.stack.some(entry => entry.panel === panel));
        panel.inert = !on;
        panel.setAttribute('aria-hidden', String(!on));
      });
      resize(state);
      if (focus) panelFocus(active)?.focus({preventScroll: true});
    }
    function close(state, focus = false) {
      state.li.classList.remove('cdn-open');
      state.li.querySelectorAll('[aria-expanded]').forEach(el => el.setAttribute('aria-expanded', 'false'));
      state.panels.forEach(panel => { panel.classList.remove('cdn-active', 'cdn-past'); panel.inert = true; panel.setAttribute('aria-hidden', 'true'); });
      state.stack = [{panel: state.panels[0], trigger: state.toggle}];
      if (focus) state.toggle.focus();
    }
    function open(state, focus = false) {
      states.forEach(other => { if (other !== state) close(other); });
      state.li.classList.add('cdn-open');
      state.toggle.setAttribute('aria-expanded', 'true');
      activate(state, focus);
    }
    function back(state) {
      if (state.stack.length < 2) { close(state, true); return; }
      const previous = state.stack.pop();
      previous.trigger.setAttribute('aria-expanded', 'false');
      activate(state);
      previous.trigger.focus({preventScroll: true});
    }
    Array.from(root.children).forEach(li => {
      const dropdown = li.querySelector('.cdn-dropdown');
      if (!dropdown) return;
      const toggle = li.querySelector('[data-root-toggle]');
      const panels = Array.from(dropdown.querySelectorAll('.cdn-panel'));
      const state = {li, dropdown, toggle, panels, viewport: dropdown.querySelector('.cdn-viewport'), stack: [{panel: panels[0], trigger: toggle}]};
      states.set(li, state);
      close(state);
      listen(toggle, 'click', () => li.classList.contains('cdn-open') ? close(state) : open(state));
      let hoverTimer;
      listen(li, 'pointerenter', event => {
        clearTimeout(hoverTimer);
        if (!isMobile() && nav.dataset.trigger === 'hover' && event.pointerType === 'mouse') open(state);
      });
      listen(li, 'pointerleave', event => {
        if (!isMobile() && nav.dataset.trigger === 'hover' && event.pointerType === 'mouse') {
          hoverTimer = setTimeout(() => { if (!li.contains(document.activeElement)) close(state); }, 180);
        }
      });
      listen(dropdown, 'click', event => {
        const button = event.target.closest('button');
        if (!button) return;
        if (button.classList.contains('cdn-back')) { back(state); return; }
        const panel = panels.find(p => p.id === button.dataset.target);
        if (!panel) return;
        state.stack.push({panel, trigger: button});
        button.setAttribute('aria-expanded', 'true');
        panel.scrollTop = 0;
        activate(state, true);
      });
      listen(li, 'keydown', event => {
        const next = nav.dir === 'rtl' ? 'ArrowLeft' : 'ArrowRight';
        const prev = nav.dir === 'rtl' ? 'ArrowRight' : 'ArrowLeft';
        if (event.key === 'Escape' && li.classList.contains('cdn-open')) { event.preventDefault(); event.stopPropagation(); back(state); }
        else if (event.key === 'ArrowDown' && event.target === toggle) { event.preventDefault(); open(state, true); }
        else if (event.key === next && event.target.matches('.cdn-next:not([data-root-toggle])')) { event.preventDefault(); event.target.click(); }
        else if (event.key === prev && state.stack.length > 1) { event.preventDefault(); back(state); }
      });
      listen(li, 'focusout', () => setTimeout(() => { if (nav.isConnected && !li.contains(document.activeElement)) close(state); }, 0));
    });
    function setMobile(open) {
      nav.classList.toggle('cdn-mobile-open', open);
      mobile.setAttribute('aria-expanded', String(open));
      if (!open) states.forEach(state => close(state));
    }
    listen(mobile, 'click', () => setMobile(!nav.classList.contains('cdn-mobile-open')));
    listen(nav, 'keydown', event => {
      if (event.key === 'Escape' && isMobile() && nav.classList.contains('cdn-mobile-open')) { setMobile(false); mobile.focus(); }
    });
    listen(document, 'pointerdown', event => {
      if (!nav.contains(event.target)) { states.forEach(state => close(state)); setMobile(false); }
    });
    function responsive() {
      const wasMobile = nav.classList.contains('cdn-is-mobile');
      nav.classList.toggle('cdn-is-mobile', isMobile());
      if (wasMobile !== isMobile()) {
        if (nav.contains(document.activeElement)) {
          (isMobile() ? mobile : root.querySelector('a,button'))?.focus();
        }
        setMobile(false);
      }
      states.forEach(state => resize(state));
    }
    listen(window, 'resize', responsive);
    const observer = new ResizeObserver(() => states.forEach(state => resize(state)));
    states.forEach(state => state.panels.forEach(panel => observer.observe(panel)));
    // Elementor replaces widget DOM when settings change. Release document/window listeners.
    const cleanup = new MutationObserver(() => {
      if (!nav.isConnected) { controller.abort(); observer.disconnect(); cleanup.disconnect(); }
    });
    cleanup.observe(document.documentElement, {childList: true, subtree: true});
    responsive();
  }
  const scan = scope => scope.querySelectorAll('.cdn-nav').forEach(init);
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => scan(document));
  else scan(document);
  let registered = false;
  function registerElementor() {
    if (registered || !window.elementorFrontend?.hooks) return;
    registered = true;
    window.elementorFrontend.hooks.addAction('frontend/element_ready/calico-drilldown-navigation.default', scope => scan(scope[0]));
  }
  // Elementor emits its initialization event through jQuery, including the editor iframe.
  if (window.jQuery) window.jQuery(window).on('elementor/frontend/init', registerElementor);
  else window.addEventListener('elementor/frontend/init', registerElementor);
  registerElementor();
})();
