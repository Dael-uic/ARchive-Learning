// FR6: Support PWA caching so previously-fetched 3D model assets and app
// shell pages can be inspected again without a network connection.
const CACHE_NAME = "ar3d-cache-v1";
const APP_SHELL = ["style.css", "app.js", "index.html"];

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL))
  );
  self.skipWaiting();
});

self.addEventListener("activate", (event) => {
  event.waitUntil(self.clients.claim());
});

// Cache-first for uploaded .glb/.gltf assets (they rarely change once published),
// network-first for API calls so scores/progress stay live.
self.addEventListener("fetch", (event) => {
  const url = new URL(event.request.url);

  if (url.pathname.startsWith("/storage/models/")) {
    event.respondWith(
      caches.open(CACHE_NAME).then(async (cache) => {
        const cached = await cache.match(event.request);
        if (cached) return cached;
        const response = await fetch(event.request);
        cache.put(event.request, response.clone());
        return response;
      })
    );
    return;
  }

  if (url.pathname.startsWith("/api/")) {
    return; // always go to network for live data
  }

  event.respondWith(
    caches.match(event.request).then((cached) => cached || fetch(event.request))
  );
});
