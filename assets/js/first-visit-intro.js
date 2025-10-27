(function () {
  const FLAG = 'ds_seenIntro_v1';      // bump to re-show
  const INTRO_SEGMENT = 'intro/';

  function isIntroPath() {
    const p = location.pathname;
    return p.endsWith('/intro/') || p.endsWith('/intro/index.html');
  }

  function isRootPath() {
    const p = location.pathname;
    return p === '/' || p.endsWith('/index.html') || p.match(/\/$/) && !p.includes('/intro') && !p.includes('/categories') && !p.includes('/feedback') && !p.includes('/create-faq');
  }

  // Build an absolute categories URL
  function categoriesUrl() {
    // Always use absolute URL to avoid path issues
    const origin = location.origin;
    const port = location.port ? ':' + location.port : '';
    
    // For local development, force to root level
    if (origin.includes('localhost') || origin.includes('127.0.0.1')) {
      const categoriesUrl = `${location.protocol}//${location.hostname}${port}/categories/`;
      console.log('[first-visit] Local dev - Categories URL:', categoriesUrl);
      return categoriesUrl;
    }
    
    // For production, check if we have a base path like /diesel-subs/
    const currentPath = location.pathname;
    let basePath = '/';
    
    // Look for GitHub Pages pattern
    if (currentPath.includes('/diesel-subs/')) {
      basePath = '/diesel-subs/';
    }
    
    const categoriesUrl = origin + basePath + 'categories/';
    console.log('[first-visit] Production - Categories URL:', categoriesUrl);
    return categoriesUrl;
  }

  // Build an absolute intro URL using <link rel="canonical"> when available
  function introUrl() {
    const canon = document.querySelector('link[rel="canonical"]');
    if (canon && canon.href) {
      // ensure we target the site root, not the current subpath
      const u = new URL(canon.href);
      u.pathname = u.pathname.replace(/[^/]+$/, ''); // drop filename if present
      if (!u.pathname.endsWith('/')) u.pathname += '/';
      u.pathname += 'intro/';
      return u.toString();
    }
    // fallback: origin + top-level site path + intro/
    const base = location.origin + location.pathname.split('/intro/')[0].replace(/\/+$/, '') + '/';
    return base + INTRO_SEGMENT;
  }

  // Optional: allow reset via ?intro=reset
  const urlHas = (k, v) => new URLSearchParams(location.search).get(k) === v;
  if (urlHas('intro', 'reset')) localStorage.removeItem(FLAG);

  // Redirect logic for first-time vs returning visitors
  const hasSeenIntro = localStorage.getItem(FLAG);
  
  // If they haven't seen intro and not already on intro page, show intro
  if (!hasSeenIntro && !isIntroPath()) {
    // remember intended destination
    sessionStorage.setItem('ds_intended', location.pathname + location.search + location.hash);
    // ABSOLUTE redirect; prevents /intro/intro/ stacking
    location.replace(introUrl());
  }
  
  // If they HAVE seen intro and are on root page, send to categories
  // Only redirect if this is a direct load of the root page, not navigation
  if (hasSeenIntro && isRootPath() && (document.referrer === '' || document.referrer === location.href)) {
    location.replace(categoriesUrl());
  }

  // Mark as seen when user is on intro page
  if (isIntroPath()) {
    localStorage.setItem(FLAG, '1');
    // Optional: send them to the intended page when they click your “Continue” button
    // document.querySelector('#continue')?.addEventListener('click', () => {
    //   const next = sessionStorage.getItem('ds_intended') || '/';
    //   location.assign(next);
    // });
  }
})();
