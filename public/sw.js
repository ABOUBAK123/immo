const CACHE = 'immogest-v1';
const OFFLINE_URL = '/immo/public/m';

const PRECACHE = [
    '/immo/public/m',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
];

self.addEventListener('install', e => {
    e.waitUntil(
        caches.open(CACHE).then(c => c.addAll(PRECACHE).catch(() => {}))
    );
    self.skipWaiting();
});

self.addEventListener('activate', e => {
    e.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', e => {
    if (e.request.method !== 'GET') return;

    const url = new URL(e.request.url);

    // Ne pas mettre en cache les API / webhook calls
    if (url.pathname.includes('/dispo') || url.pathname.includes('/webhook') ||
        url.pathname.includes('/initier') || url.pathname.includes('/reserver')) return;

    e.respondWith(
        fetch(e.request)
            .then(res => {
                if (res.ok) {
                    const clone = res.clone();
                    caches.open(CACHE).then(c => c.put(e.request, clone));
                }
                return res;
            })
            .catch(() => caches.match(e.request).then(cached => cached || caches.match(OFFLINE_URL)))
    );
});
