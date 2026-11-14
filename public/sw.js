// Service Worker for LOGISTIK.TOC PWA
const CACHE_NAME = 'logistik-toc-v1';
const STATIC_ASSETS = [
    '/manifest.json',
    '/images/logo-toc.jpg'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        })
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    // Only cache GET requests and skip non-http(s) schemes
    if (event.request.method !== 'GET' || !event.request.url.startsWith('http')) {
        return;
    }

    // Network-first strategy for dynamic pages to ensure latest inventory data
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // If static asset, update cache
                if (response.status === 200 && (event.request.url.includes('/images/') || event.request.url.includes('/manifest.json'))) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseClone);
                    });
                }
                return response;
            })
            .catch(() => {
                return caches.match(event.request);
            })
    );
});
