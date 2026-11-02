/* Calico Drilldown Navigation. Native disclosure navigation; no application-menu roles. */
(() => {
  'use strict';
  // DOM attributes survive Elementor's popup clones; event listeners do not.
  const instances = new WeakMap();
  function init(nav) {
    if (!nav.isConnected || instances.has(nav)) return;
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
    function animateDropdown(state, opening, finish = () => {}) {
      state.animation?.cancel();
      state.animation = null;
      const inline = isMobile() || nav.classList.contains('cdn-is-vertical');
      const duration = parseFloat(getComputedStyle(nav).getPropertyValue('--cdn-duration')) || 260;
      if (!inline || matchMedia('(prefers-reduced-motion: reduce)').matches || !state.dropdown.animate) { finish(); return; }
      const height = state.dropdown.getBoundingClientRect().height;
      const margin = getComputedStyle(state.dropdown).marginTop;
      const collapsed = {height: '0px', opacity: 0, marginTop: '0px', overflow: 'hidden'};
      const expanded = {height: `${height}px`, opacity: 1, marginTop: margin, overflow: 'hidden'};
      const animation = state.dropdown.animate(opening ? [collapsed, expanded] : [expanded, collapsed], {duration, easing: 'ease'});
      state.animation = animation;
      animation.onfinish = () => { if (state.animation === animation) { state.animation = null; finish(); } };
    }
    const panelFocus = panel => panel.querySelector('button,a');
    function focusPanel(state, panel) {
      state.movingFocus = true;
      panelFocus(panel)?.focus({preventScroll: true});
      // In a sticky header the incoming panel may still be visually hidden until
      // the browser applies its new styles. Wait one frame before moving focus.
      requestAnimationFrame(() => {
        if (nav.isConnected && state.li.classList.contains('cdn-open') && state.stack[state.stack.length - 1].panel === panel) {
          if (document.activeElement === document.body) panelFocus(panel)?.focus({preventScroll: true});
        }
        state.movingFocus = false;
      });
      // Some host styles defer visibility for the full slide. Recover focus at
      // its end only if it is still on body; never steal a visitor's new focus.
      const duration = parseFloat(getComputedStyle(nav).getPropertyValue('--cdn-duration')) || 260;
      setTimeout(() => {
        if (nav.isConnected && state.li.classList.contains('cdn-open') && state.stack[state.stack.length - 1].panel === panel && document.activeElement === document.body) {
          panelFocus(panel)?.focus({preventScroll: true});
        }
      }, duration + 32);
    }
    function resize(state) {
      if (!state.li.classList.contains('cdn-open')) return;
      const panel = state.stack[state.stack.length - 1].panel;
      state.viewport.style.height = `${panel.getBoundingClientRect().height}px`;
      // Keep a dropdown inside the viewport even when the header sits near an edge.
      state.dropdown.style.marginInlineStart = '';
      if (!isMobile() && !nav.classList.contains('cdn-is-vertical')) {
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
      if (focus) focusPanel(state, active);
    }
    function close(state, focus = false) {
      const wasOpen = state.li.classList.contains('cdn-open');
      if (wasOpen) state.li.classList.add('cdn-closing');
      state.li.classList.remove('cdn-open');
      state.li.querySelectorAll('[aria-expanded]').forEach(el => el.setAttribute('aria-expanded', 'false'));
      state.panels.forEach(panel => { panel.inert = true; panel.setAttribute('aria-hidden', 'true'); });
      state.stack = [{panel: state.panels[0], trigger: state.toggle}];
      const finish = () => {
        state.li.classList.remove('cdn-closing');
        state.panels.forEach(panel => panel.classList.remove('cdn-active', 'cdn-past'));
      };
      if (wasOpen) animateDropdown(state, false, finish);
      else if (!state.li.classList.contains('cdn-closing')) finish();
      if (focus) state.toggle.focus();
    }
    function open(state, focus = false) {
      const wasOpen = state.li.classList.contains('cdn-open');
      state.animation?.cancel();
      state.animation = null;
      state.li.classList.remove('cdn-closing');
      states.forEach(other => { if (other !== state) close(other); });
      state.li.classList.add('cdn-open');
      state.toggle.setAttribute('aria-expanded', 'true');
      activate(state, focus);
      if (!wasOpen) animateDropdown(state, true);
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
      listen(toggle, 'click', event => {
        // A mouse hover already opens the dropdown. Its following click must
        // keep it open instead of immediately toggling it closed.
        if (!isMobile() && nav.dataset.trigger === 'hover' && event.detail > 0 && event.pointerType !== 'touch') open(state);
        else li.classList.contains('cdn-open') ? close(state) : open(state);
      });
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
      listen(li, 'focusout', event => {
        // Making the outgoing panel inert generates a blur with no destination.
        // That is an internal transition, not keyboard navigation out of the menu.
        // A sibling trigger receives focus on pointerdown, before click. Closing
        // here collapses the stack under the pointer and cancels that click.
        // Let the sibling's click open it and close this menu together.
        if (!event.relatedTarget || state.movingFocus || nav.contains(event.relatedTarget)) return;
        setTimeout(() => { if (nav.isConnected && !state.movingFocus && !li.contains(document.activeElement)) close(state); }, 0);
      });
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
      nav.classList.toggle('cdn-is-vertical', getComputedStyle(root).flexDirection === 'column');
      if (wasMobile !== isMobile()) {
        if (nav.contains(document.activeElement)) {
          (isMobile() ? mobile : root.querySelector('a,button'))?.focus();
        }
        setMobile(false);
      }
      states.forEach(state => resize(state));
    }
    listen(window, 'resize', responsive);
    const observer = new ResizeObserver(responsive);
    observer.observe(root);
    states.forEach(state => state.panels.forEach(panel => observer.observe(panel)));
    instances.set(nav, {refresh: responsive, destroy() {
      controller.abort(); observer.disconnect();
      states.forEach(state => { state.animation?.cancel(); close(state); state.animation?.cancel(); state.li.classList.remove('cdn-closing'); });
      instances.delete(nav); delete nav.dataset.cdnReady;
    }});
    responsive();
  }
  const eachNav = (scope, callback) => {
    if (scope.nodeType !== 1 && scope.nodeType !== 9) return;
    if (scope.matches?.('.cdn-nav')) callback(scope);
    scope.querySelectorAll('.cdn-nav').forEach(callback);
  };
  const scan = scope => eachNav(scope, nav => { init(nav); instances.get(nav)?.refresh(); });
  // Popups may initialize while detached, reuse a removed node, or clone a ready
  // widget. Observe attachment rather than trusting copied ready attributes.
  const lifecycle = new MutationObserver(records => {
    records.forEach(record => record.removedNodes.forEach(node => eachNav(node, nav => {
      if (!nav.isConnected) instances.get(nav)?.destroy();
    })));
    records.forEach(record => record.addedNodes.forEach(node => scan(node)));
  });
  lifecycle.observe(document.documentElement, {childList: true, subtree: true});
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => scan(document));
  else scan(document);
  let registered = false;
  function registerElementor() {
    if (registered || !window.elementorFrontend?.hooks) return;
    registered = true;
    window.elementorFrontend.hooks.addAction('frontend/element_ready/calico-drilldown-navigation.default', scope => scan(scope[0]));
  }
  // Elementor emits its initialization event through jQuery, including the editor iframe.
  if (window.jQuery) {
    window.jQuery(window).on('elementor/frontend/init', registerElementor);
    window.jQuery(document).on('elementor/popup/show', () => scan(document));
  }
  else window.addEventListener('elementor/frontend/init', registerElementor);
  registerElementor();
})();
