(function () {
  'use strict';

  var debounce = function (fn, ms) {
    var timer;
    return function () {
      var args = arguments;
      var ctx = this;
      clearTimeout(timer);
      timer = setTimeout(function () { fn.apply(ctx, args); }, ms);
    };
  };

  /* ---------- 1. Auto-number h2 ---------- */
  function autoNumber () {
    var headings = document.querySelectorAll('.wiki-content h2');
    headings.forEach(function (h, i) {
      var existing = h.querySelector('.num');
      if (existing) existing.remove();
      var num = document.createElement('span');
      num.className = 'num';
      num.textContent = i + 1;
      h.insertBefore(num, h.firstChild);
    });
  }

  /* ---------- 2. Build sidebar TOC from h2/h3 ---------- */
  function buildSidebarTOC () {
    var list = document.getElementById('sidebar-toc-list');
    if (!list) return;
    list.innerHTML = '';
    var headings = document.querySelectorAll('.wiki-content h2, .wiki-content h3');
    if (headings.length === 0) return;

    var idCount = {};
    headings.forEach(function (h) {
      var txt = h.textContent.trim();
      var numSpan = h.querySelector('.num');
      if (numSpan) txt = txt.replace(numSpan.textContent, '').trim();

      var base = h.getAttribute('id');
      if (!base) {
        base = txt.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        if (!base) base = 'section';
        var count = idCount[base] || 0;
        idCount[base] = count + 1;
        base = count > 0 ? base + '-' + count : base;
        h.id = base;
      }

      var li = document.createElement('li');
      li.className = h.tagName === 'H3' ? 'toc-h3' : '';
      var a = document.createElement('a');
      a.href = '#' + h.id;
      a.textContent = txt;
      li.appendChild(a);
      list.appendChild(li);
    });
  }

  /* ---------- 3. Extract key points from section cards ---------- */
  function extractKeyPoints () {
    var container = document.getElementById('keypoints-list');
    if (!container) return;
    container.innerHTML = '';
    var cards = document.querySelectorAll('.wiki-content .section-card');

    cards.forEach(function (card) {
      var labelEl = card.querySelector('.label');
      if (!labelEl) return;
      var label = labelEl.textContent.trim();
      var text = card.textContent.replace(label, '').trim().substring(0, 120);
      if (label === 'Recomendaciones' || label === 'Tips') return;
      var div = document.createElement('div');
      div.className = 'keypoint-item';
      div.innerHTML = '<span class="kp-label">' + label + '</span>' + text;
      container.appendChild(div);
    });
  }

  /* ---------- 4. Scroll-spy for sidebar TOC ---------- */
  function initScrollSpy () {
    var links = document.querySelectorAll('#sidebar-toc-list a');
    if (links.length === 0) return;
    var headings = document.querySelectorAll('.wiki-content h2, .wiki-content h3');

    var observer = new IntersectionObserver(function (entries) {
      var visible = [];
      entries.forEach(function (e) {
        if (e.isIntersecting) visible.push(e.target.id);
      });
      if (visible.length === 0) return;
      var topId = visible.reduce(function (a, b) {
        var aEl = document.getElementById(a);
        var bEl = document.getElementById(b);
        return (aEl && bEl && aEl.getBoundingClientRect().top < bEl.getBoundingClientRect().top) ? a : b;
      }, visible[0]);

      links.forEach(function (a) {
        a.classList.toggle('active-toc', a.getAttribute('href') === '#' + topId);
      });
    }, { threshold: 0, rootMargin: '-56px 0px -50% 0px' });

    headings.forEach(function (h) { observer.observe(h); });
  }

  /* ---------- 5. Text search ---------- */
  var savedHTML = null;

  function initSearch () {
    var input = document.getElementById('search-input');
    if (!input) return;
    var resultDiv = document.getElementById('search-results');
    var page = document.querySelector('.wiki-content .page');
    if (!page) return;

    if (!savedHTML) savedHTML = page.innerHTML;

    input.addEventListener('input', debounce(function () {
      var q = this.value.trim().toLowerCase();
      if (!q) {
        page.innerHTML = savedHTML;
        if (resultDiv) resultDiv.innerHTML = '';
        renderAll();
        return;
      }

      page.innerHTML = savedHTML;

      var treeWalker = document.createTreeWalker(page, NodeFilter.SHOW_TEXT, null, false);
      var nodes = [];
      while (treeWalker.nextNode()) nodes.push(treeWalker.currentNode);

      var count = 0;
      nodes.forEach(function (n) {
        var parent = n.parentNode;
        if (!parent || parent.nodeType !== 1) return;
        if (parent.closest('script, style, .search-bar, .toc-wrap, .cover, .print-footer')) return;

        var text = n.nodeValue;
        if (!text.trim()) return;

        var lower = text.toLowerCase();
        var idx = lower.indexOf(q);
        if (idx === -1) return;
        count++;

        var span = document.createElement('span');
        span.innerHTML = text.slice(0, idx) + '<mark class="search-hl">' + text.slice(idx, idx + q.length) + '</mark>' + text.slice(idx + q.length);
        parent.replaceChild(span, n);
      });

      if (resultDiv) resultDiv.innerHTML = '<span class="count">' + count + '</span> resultado(s) para <strong>' + this.value + '</strong>';
      autoNumber();
    }, 250));
  }

  /* ---------- 6. Back to top ---------- */
  function initBackTop () {
    var btn = document.getElementById('back-top');
    if (!btn) return;
    window.addEventListener('scroll', debounce(function () {
      btn.classList.toggle('visible', window.scrollY > 350);
    }, 80));
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /* ---------- 7. Sidebar toggle ---------- */
  function initSidebarToggle () {
    var toggle = document.getElementById('sidebar-toggle');
    var sidebar = document.querySelector('.wiki-sidebar');
    var overlay = document.querySelector('.sidebar-overlay');
    if (!toggle || !sidebar) return;

    function open() { sidebar.classList.add('open'); if (overlay) overlay.classList.add('open'); }
    function close() { sidebar.classList.remove('open'); if (overlay) overlay.classList.remove('open'); }

    toggle.addEventListener('click', function () {
      if (sidebar.classList.contains('open')) { close(); } else { open(); }
    });
    if (overlay) overlay.addEventListener('click', close);
  }

  /* ---------- 8. TOC toggle (mobile fallback) ---------- */
  function initTocToggle () {
    var toggle = document.getElementById('toc-toggle');
    var toc = document.getElementById('toc');
    if (!toggle || !toc) return;
    toggle.addEventListener('click', function () {
      toc.classList.toggle('open');
      toggle.classList.toggle('open');
    });
  }

  /* ---------- Render all ---------- */
  function renderAll () {
    autoNumber();
    buildSidebarTOC();
    extractKeyPoints();
    initScrollSpy();
    initSearch();
    initBackTop();
    initSidebarToggle();
    initTocToggle();
  }

  /* ---------- Bootstrap ---------- */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', renderAll);
  } else {
    renderAll();
  }

})();
