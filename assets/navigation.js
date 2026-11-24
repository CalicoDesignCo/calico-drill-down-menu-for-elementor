/* Calico Drilldown Navigation. Native disclosure navigation; no application-menu roles. */
(() => {
  'use strict';
  // DOM attributes survive Elementor's popup clones; event listeners do not.
  const instances = new WeakMap();
  const headerOwners = new WeakMap();
  const logoOwners = new WeakMap();
  const overlayOwners = new WeakMap();
  function createOverlay(shell, target) {
    if (shell.dataset.overlay !== 'yes' || window.elementorFrontend?.isEditMode?.() || document.body.classList.contains('elementor-editor-active')) return null;
    let root = shell.closest('.elementor-location-header'), generated = false;
    if (!root) {
      generated = true;
      root = target.parentElement.matches('.cdn-overlay-host') ? target.parentElement : document.createElement('div');
      if (!root.classList.contains('cdn-overlay-host')) { root.className = 'cdn-overlay-host'; target.before(root); root.append(target); }
    }
    let owner = overlayOwners.get(root);
    if (!owner) {
      const properties = ['position','top','left','right','bottom','width','margin','z-index','box-sizing'];
      if (root.dataset.cdnOverlayStyle) {
        try { const copied = JSON.parse(root.dataset.cdnOverlayStyle); properties.forEach(p => { const v = copied[p]; if (v?.[0]) root.style.setProperty(p,v[0],v[1]); else root.style.removeProperty(p); }); } catch (_) {}
      }
      const saved = Object.fromEntries(properties.map(p => [p,[root.style.getPropertyValue(p),root.style.getPropertyPriority(p)]]));
      const restore = () => properties.forEach(p => { const v = saved[p]; if (v[0]) root.style.setProperty(p,v[0],v[1]); else root.style.removeProperty(p); });
      owner = {count:0,refresh() {
        restore();
        const rect = root.getBoundingClientRect();
        root.style.setProperty('position','absolute','important');
        const parent = root.offsetParent, box = parent?.getBoundingClientRect();
        const top = parent && parent !== document.body && parent !== document.documentElement ? rect.top - box.top - parent.clientTop + parent.scrollTop : rect.top + window.scrollY;
        const left = parent && parent !== document.body && parent !== document.documentElement ? rect.left - box.left - parent.clientLeft + parent.scrollLeft : rect.left + window.scrollX;
        const values = {top:top+'px',left:left+'px',right:'auto',bottom:'auto',width:rect.width+'px',margin:'0px','z-index':String(Number(shell.dataset.headerZ)||1000),'box-sizing':'border-box'};
        Object.entries(values).forEach(([p,v]) => root.style.setProperty(p,v,'important'));
        root.dataset.cdnOverlayStyle = JSON.stringify(saved);
      },release() {
        if (--owner.count) return;
        restore(); delete root.dataset.cdnOverlayStyle; overlayOwners.delete(root);
        if (generated && root.parentNode) { while (root.firstChild) root.before(root.firstChild); root.remove(); }
      }};
      overlayOwners.set(root,owner); owner.refresh();
    }
    owner.count++;
    return {refresh:owner.refresh,destroy:owner.release};
  }
  function externalLogo(shell, target) {
    if (shell.dataset.logoLocation !== 'external' || !shell.dataset.logoId) return null;
    const scope = shell.closest('.elementor-location-header') || (target !== shell.querySelector('.cdn-header-bar') ? target : shell.closest('.e-con,.elementor-section')) || shell.parentElement;
    const attrs = ['src','srcset','sizes','width','height','style'];
    const capture = e => Object.fromEntries(attrs.map(a => [a,e.getAttribute(a)]));
    const apply = (e, values) => Object.entries(values).forEach(([a,v]) => { if (v === null) e.removeAttribute(a); else if (e.getAttribute(a) !== v) e.setAttribute(a,v); });
    // Restore cloned image attributes even if the new widget no longer targets that ID.
    scope.querySelectorAll('img[data-cdn-logo-original]').forEach(img => {
      if (logoOwners.has(img)) return;
      try { const original = JSON.parse(img.dataset.cdnLogoOriginal); apply(img,original.image); img.closest('picture')?.querySelectorAll('source').forEach((s,i) => { if (original.sources[i]) apply(s,original.sources[i]); }); } catch (_) {}
      delete img.dataset.cdnLogoOriginal;
    });
    const tagged = [scope,...scope.querySelectorAll('[id]')].find(e => e.id === shell.dataset.logoId && e.getClientRects().length);
    const image = tagged?.matches('img') ? tagged : tagged?.querySelector('img');
    if (!image || shell.contains(image) || logoOwners.has(image)) return null;
    const sources = Array.from(image.closest('picture')?.querySelectorAll('source') || []);
    logoOwners.set(image,shell);
    const original = {image:capture(image),sources:sources.map(capture)};
    image.dataset.cdnLogoOriginal = JSON.stringify(original);
    return {image, update(scrolled) {
      const css = getComputedStyle(shell), phase = scrolled && shell.dataset.shrink === 'yes' ? 'after' : 'before';
      const url = scrolled ? shell.dataset.logoAfter || shell.dataset.logoBefore : shell.dataset.logoBefore;
      if (url) {
        apply(image,{src:url,srcset:null,sizes:null});
        sources.forEach(s => { s.removeAttribute('srcset'); s.removeAttribute('sizes'); });
      } else { apply(image,Object.fromEntries(['src','srcset','sizes'].map(a => [a,original.image[a]]))); sources.forEach((s,i) => apply(s,original.sources[i])); }
      image.style.width = css.getPropertyValue('--cdn-logo-width-'+phase).trim();
      image.style.maxHeight = css.getPropertyValue('--cdn-logo-height-'+phase).trim();
      image.style.height = 'auto'; image.style.maxWidth = '100%'; image.style.objectFit = 'contain';
    }, destroy() { apply(image,original.image); sources.forEach((s,i) => apply(s,original.sources[i])); delete image.dataset.cdnLogoOriginal; logoOwners.delete(image); }};
  }
  function createHeader(shell) {
    if (!shell || !shell.getClientRects().length || shell.closest('.elementor-sticky__spacer')) return null;
    const bar = shell.querySelector('.cdn-header-bar');
    let target = bar;
    if (shell.dataset.headerScope === 'selector' && shell.dataset.headerSelector) {
      try { target = shell.closest(shell.dataset.headerSelector) || bar; } catch (_) { target = bar; }
      if (target === document.body || target === document.documentElement) target = bar;
    } else if (shell.dataset.headerScope === 'container') {
      const location = shell.closest('.elementor-location-header');
      let ancestor = shell.parentElement;
      while (ancestor && ancestor !== location && ancestor !== document.body) {
        if (ancestor.matches('.e-con,.elementor-section')) { target = ancestor; if (!location) break; }
        ancestor = ancestor.parentElement;
      }
    }
    const owner = headerOwners.get(target);
    if (owner) {
      if (owner.shell.getClientRects().length) return null;
      owner.destroy();
    }
    const properties = ['background-color','background-image','min-height','padding','position','top','left','width','margin','z-index'];
    // Restore a copied header's original inline styles before binding its clone.
    if (target.dataset.cdnHeaderStyle) {
      try {
        const copied = JSON.parse(target.dataset.cdnHeaderStyle);
        properties.forEach(p => { if (copied[p]) target.style.setProperty(p, copied[p][0], copied[p][1]); });
      } catch (_) { /* A stale marker must not stop navigation. */ }
      target.classList.remove('cdn-controlled-header','cdn-header-fixed');
      delete target.dataset.cdnHeaderStyle;
      if (target.previousElementSibling?.matches('.cdn-header-spacer')) target.previousElementSibling.remove();
    }
    const saved = new Map();
    function set(p, value, priority = '') {
      if (!saved.has(p)) saved.set(p, [target.style.getPropertyValue(p), target.style.getPropertyPriority(p)]);
      if (target.style.getPropertyValue(p) !== value || target.style.getPropertyPriority(p) !== priority) target.style.setProperty(p, value, priority);
      target.dataset.cdnHeaderStyle = JSON.stringify(Object.fromEntries(saved));
    }
    function restore(p) {
      if (!saved.has(p)) return;
      const [value, priority] = saved.get(p);
      if (value) target.style.setProperty(p, value, priority); else target.style.removeProperty(p);
      saved.delete(p);
      target.dataset.cdnHeaderStyle = JSON.stringify(Object.fromEntries(saved));
    }
    const spacer = document.createElement('div');
    spacer.className = 'cdn-header-spacer'; spacer.setAttribute('aria-hidden','true'); spacer.style.display = 'none';
    target.before(spacer);
    target.classList.add('cdn-controlled-header');
    const overlay = createOverlay(shell,target);
    if (target.parentElement.matches('.cdn-overlay-host')) target.before(spacer);
    const external = externalLogo(shell,target);
    const logo = external?.image || shell.querySelector('.cdn-header-logo');
    let fixed = false, lastState = null, normalHeight = 0, headerAnimation, logoAnimation;
    const fixedProperties = ['position','top','left','width','margin','z-index'];
    function release() {
      if (!fixed) return;
      fixedProperties.forEach(restore); spacer.style.display = 'none';
      target.classList.remove('cdn-header-fixed'); fixed = false;
    }
    function skin(scrolled) {
      const css = getComputedStyle(shell);
      const phase = scrolled && shell.dataset.shrink === 'yes' ? 'after' : 'before';
      shell.classList.toggle('cdn-scrolled', scrolled);
      external?.update(scrolled);
      const background = !scrolled && shell.dataset.transparent === 'yes' ? 'transparent' : css.getPropertyValue('--cdn-header-bg-' + (scrolled ? 'after' : 'before')).trim() || '#FFFFFF';
      set('background-color', background); set('background-image','none');
      set('min-height', css.getPropertyValue('--cdn-header-height-' + phase).trim() || (phase === 'after' ? '64px' : '96px'));
      set('padding', css.getPropertyValue('--cdn-header-padding-' + phase).trim() || (phase === 'after' ? '8px 24px' : '16px 24px'));
    }
    function snapshot() {
      const css = getComputedStyle(target);
      return {height: target.getBoundingClientRect().height + 'px', minHeight: css.minHeight, padding: css.padding, backgroundColor: css.backgroundColor};
    }
    function applySkin(scrolled) {
      // Read the destination without Elementor's CSS transition interpolating it.
      // Otherwise the WAAPI endpoint can equal the starting transparent color.
      const names = ['transition-property','transition-duration','transition-timing-function','transition-delay','transition-behavior'];
      const transitions = names.map(p => [p,target.style.getPropertyValue(p),target.style.getPropertyPriority(p)]);
      target.style.setProperty('transition','none','important');
      try { skin(scrolled); return snapshot(); }
      finally { transitions.forEach(([p,v,priority]) => { if (v) target.style.setProperty(p,v,priority); else target.style.removeProperty(p); }); }
    }
    function logoSize() { const r = logo?.getBoundingClientRect(); return r ? {width:r.width+'px',height:r.height+'px'} : null; }
    function sticky(scrolled) {
      let nativeSticky = target.classList.contains('elementor-sticky') || !!target.closest('.elementor-sticky');
      try { nativeSticky ||= !!JSON.parse(target.dataset.settings || '{}').sticky; } catch (_) {}
      const editor = window.elementorFrontend?.isEditMode?.() || document.body.classList.contains('elementor-editor-active');
      const enabled = shell.dataset.headerSticky === 'yes' && !editor && !nativeSticky;
      const admin = document.getElementById('wpadminbar')?.getBoundingClientRect();
      const offset = Math.max(0,Number(shell.dataset.headerOffset)||0) + (admin && admin.top <= 0 ? Math.max(0,admin.bottom) : 0);
      const anchor = (fixed ? spacer : target).getBoundingClientRect();
      const shouldFix = enabled && window.scrollY > 0 && (shell.dataset.stickyStart === 'threshold' ? scrolled : anchor.top <= offset);
      if (!shouldFix) { release(); return; }
      if (!fixed) {
        const css = getComputedStyle(target);
        spacer.style.cssText = `display:block;box-sizing:border-box;flex:0 0 auto;width:${anchor.width}px;height:${normalHeight}px;margin:${css.margin}`;
        fixed = true; target.classList.add('cdn-header-fixed');
        set('position','fixed','important'); set('margin','0px','important'); set('z-index',String(Number(shell.dataset.headerZ)||1000),'important');
      }
      const box = spacer.getBoundingClientRect();
      set('top',offset+'px','important'); set('left',box.left+'px','important'); set('width',box.width+'px','important');
    }
    const controller = {shell, active:true, update(scrolled, refresh = false) {
      if (!controller.active) return;
      if (!shell.getClientRects().length) { controller.destroy(); return; }
      if (refresh || lastState === null) {
        release(); overlay?.refresh(); headerAnimation?.cancel(); logoAnimation?.cancel();
        applySkin(false); normalHeight = target.getBoundingClientRect().height;
        applySkin(scrolled); lastState = scrolled;
      } else if (scrolled !== lastState) {
        const before = snapshot(), oldLogo = logoSize();
        headerAnimation?.cancel(); logoAnimation?.cancel();
        const after = applySkin(scrolled), newLogo = logoSize();
        const parsed = parseFloat(getComputedStyle(shell).getPropertyValue('--cdn-header-duration'));
        const duration = Number.isFinite(parsed) ? Math.max(0,Math.min(1500,parsed)) : 250;
        if (duration && !matchMedia('(prefers-reduced-motion: reduce)').matches && target.animate) {
          headerAnimation = target.animate([before,after],{duration,easing:'ease'});
          if (logo && oldLogo && newLogo) logoAnimation = logo.animate([oldLogo,newLogo],{duration,easing:'ease'});
        }
        lastState = scrolled;
      }
      sticky(scrolled);
    }, destroy() {
      if (!controller.active) return;
      controller.active = false; headerAnimation?.cancel(); logoAnimation?.cancel(); release();
      Array.from(saved.keys()).forEach(restore);
      target.classList.remove('cdn-controlled-header'); delete target.dataset.cdnHeaderStyle;
      if (external) { external.image.removeEventListener('load',refreshLogo); external.destroy(); }
      spacer.remove(); overlay?.destroy(); headerOwners.delete(target);
    }};
    function refreshLogo() { if (controller.active && !lastState && !fixed) normalHeight = target.getBoundingClientRect().height; }
    external?.image.addEventListener('load',refreshLogo);
    headerOwners.set(target,controller);
    return controller;
  }
  function init(nav) {
    if (!nav.isConnected || instances.has(nav) || nav.closest('.elementor-sticky__spacer')) return;
    nav.dataset.cdnReady = 'true';
    nav.classList.add('cdn-enhanced');
    nav.dir = getComputedStyle(nav).direction;
    const root = nav.querySelector('.cdn-root');
    const mobile = nav.querySelector('.cdn-mobile-toggle');
    const media = matchMedia(`(max-width: ${Number(nav.dataset.breakpoint) || 767}px)`);
    const isMobile = () => nav.dataset.collapse !== 'never' && media.matches;
    const states = new Map();
    const controller = new AbortController();
    const listen = (el, event, fn, options = {}) => el.addEventListener(event, fn, {...options, signal: controller.signal});
    let scrollFrame = 0;
    const headerShell = nav.closest('.cdn-header-shell');
    let headerController;
    function updateScrollColors() {
      const threshold = Math.max(0, Math.min(100000, Number(nav.dataset.scrollThreshold) || 0));
      const position = Math.max(0, window.scrollY || document.documentElement.scrollTop || 0);
      const scrolled = position > 0 && position >= threshold;
      nav.classList.toggle('cdn-scrolled', (nav.dataset.scrollColors === 'yes' || !!headerShell) && scrolled);
      if (headerShell) {
        if (!headerController?.active) headerController = createHeader(headerShell);
        if (headerController) headerController.update(scrolled);
        else headerShell.classList.toggle('cdn-scrolled',scrolled);
      }
    }
    if (nav.dataset.scrollColors === 'yes' || headerShell) {
      listen(window, 'scroll', () => {
        if (!scrollFrame) scrollFrame = requestAnimationFrame(() => { scrollFrame = 0; updateScrollColors(); });
      }, {passive: true});
      listen(window, 'pageshow', updateScrollColors);
      headerShell?.querySelectorAll('.cdn-header-logo img').forEach(img => {
        listen(img, 'load', () => { updateScrollColors(); headerController?.update(nav.classList.contains('cdn-scrolled'),true); });
      });
    }
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
      updateScrollColors();
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
    listen(window, 'resize', () => { responsive(); headerController?.update(nav.classList.contains('cdn-scrolled'),true); });
    const observer = new ResizeObserver(responsive);
    observer.observe(root);
    states.forEach(state => state.panels.forEach(panel => observer.observe(panel)));
    instances.set(nav, {refresh: responsive, destroy() {
      controller.abort(); observer.disconnect();
      cancelAnimationFrame(scrollFrame);
      headerController?.destroy();
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
