import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const PIN_SVG = `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="26" height="38" style="filter:drop-shadow(0 1px 2px rgba(0,0,0,0.4));">
  <path d="M12 1.5c-4.14 0-7.5 3.36-7.5 7.5 0 5.25 7.5 13.5 7.5 13.5s7.5-8.25 7.5-13.5C19.5 4.86 16.14 1.5 12 1.5z" fill="#2563eb" stroke="#ffffff" stroke-width="1.2"/>
  <circle cx="12" cy="9" r="2.6" fill="#ffffff"/>
</svg>`;

function makePin() {
    return L.divIcon({
        className: 'location-pin-icon',
        html: PIN_SVG,
        iconSize: [26, 38],
        iconAnchor: [13, 38],
        popupAnchor: [0, -34],
    });
}

function numeric(el) {
    return el ? parseFloat(el.value) : null;
}

function setAccuracy(el, message, kind = 'info') {
    if (!el) return;
    el.textContent = message || '';
    el.className = 'mt-2 text-xs font-medium ' +
        (kind === 'warn' ? 'text-amber-600' : kind === 'error' ? 'text-red-600' : 'text-green-700');
}

export function initLocationMap(cfg = {}) {
    const mapEl = typeof cfg.mapEl === 'string' ? document.querySelector(cfg.mapEl) : cfg.mapEl;
    if (!mapEl || typeof L === 'undefined') return null;

    const isReadOnly = cfg.readOnly === true || mapEl.dataset.readonly === 'true';

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const radiusInput = document.getElementById('radius');
    const addressEl = document.getElementById('address');

    const savedLat = parseFloat(mapEl.dataset.lat) || parseFloat(cfg.lat) || -2.118077;
    const savedLng = parseFloat(mapEl.dataset.lng) || parseFloat(cfg.lng) || 106.156777;
    const defaultZoom = parseInt(mapEl.dataset.zoom || cfg.zoom || '16', 10);

    const map = L.map(mapEl, { zoomControl: true, attributionControl: true }).setView([savedLat, savedLng], defaultZoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    if (!isReadOnly) {
        map.attributionControl.setPrefix('Leaflet');
    }

    let marker = null;
    let circle = null;
    let geocodeTimer = null;
    let geocodeController = null;

    function currentRadius() {
        const input = radiusInput ? parseInt(radiusInput.value, 10) : parseInt(mapEl.dataset.radius || '100', 10);
        return Number.isFinite(input) && input > 0 ? input : 50;
    }

    function render(mLat, mLng) {
        const ll = L.latLng(mLat, mLng);

        if (marker) {
            marker.setLatLng(ll);
        } else {
            marker = L.marker(ll, { draggable: !isReadOnly, icon: makePin(), keyboard: false }).addTo(map);
        }

        if (circle) {
            circle.setLatLng(ll);
        } else {
            circle = L.circle(ll, { radius: currentRadius() }).addTo(map);
        }

        circle.setRadius(currentRadius());
    }

    function syncInputs(mLat, mLng) {
        if (latInput) latInput.value = mLat.toFixed(6);
        if (lngInput) lngInput.value = mLng.toFixed(6);
    }

    function setPosition(mLat, mLng, opts = {}) {
        const valid = Number.isFinite(mLat) && Number.isFinite(mLng);
        if (!valid) return;

        render(mLat, mLng);
        syncInputs(mLat, mLng);

        if (opts.fly !== false) {
            map.flyTo([mLat, mLng], opts.zoom || Math.max(map.getZoom(), 15), { duration: 0.7 });
        }

        if (!isReadOnly && opts.geocode !== false) {
            scheduleReverseGeocode(mLat, mLng);
        }
    }

    function radiusChanged() {
        if (circle) circle.setRadius(currentRadius());
    }

    if (radiusInput) {
        radiusInput.addEventListener('input', radiusChanged);
        radiusInput.addEventListener('change', radiusChanged);
    }

    if (!isReadOnly) {
        map.on('click', (e) => setPosition(e.latlng.lat, e.latlng.lng));
        marker.on('dragend', () => {
            const ll = marker.getLatLng();
            syncInputs(ll.lat, ll.lng);
            if (circle) circle.setLatLng(ll);
            scheduleReverseGeocode(ll.lat, ll.lng);
        });
    }

    function scheduleReverseGeocode(mLat, mLng) {
        clearTimeout(geocodeTimer);
        geocodeTimer = setTimeout(() => reverseGeocode(mLat, mLng), 700);
    }

    function reverseGeocode(mLat, mLng) {
        const note = document.getElementById('address-geocode-note');
        if (!addressEl || !note) return;

        if (geocodeController) geocodeController.abort();
        geocodeController = new AbortController();

        const url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' +
            mLat.toFixed(6) + '&lon=' + mLng.toFixed(6) + '&zoom=18&addressdetails=1&accept-language=id';

        note.textContent = 'Mendeteksi alamat…';
        note.className = 'mt-1 text-xs text-gray-400';

        fetch(url, { signal: geocodeController.signal, headers: { Accept: 'application/json' } })
            .then((r) => {
                if (!r.ok) throw new Error('geocode-failed');
                return r.json();
            })
            .then((data) => {
                if (data && data.display_name) {
                    addressEl.value = data.display_name;
                    note.textContent = '';
                } else {
                    showGeocodeFailure();
                }
            })
            .catch((err) => {
                if (err.name !== 'AbortError') showGeocodeFailure();
            });
    }

    function showGeocodeFailure() {
        const note = document.getElementById('address-geocode-note');
        if (!note) return;
        note.textContent = 'Alamat tidak dapat dideteksi otomatis. Silakan isi alamat secara manual.';
        note.className = 'mt-1 text-xs text-amber-600';
    }

    const statusEl = document.getElementById('gps-status');
    const detectBtn = document.getElementById('btn-detect-location');
    const resetBtn = document.getElementById('btn-reset-location');
    const focusBtn = document.getElementById('btn-focus-location');

    function setDetectLoading(loading) {
        if (!detectBtn) return;
        detectBtn.disabled = loading;
        detectBtn.innerHTML = loading
            ? '<span class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span> Mendeteksi Lokasi…'
            : '📍 Deteksi Lokasi Saya';
    }

    function detectLocation() {
        if (!('geolocation' in navigator)) {
            setAccuracy(statusEl, 'Browser Anda tidak mendukung deteksi lokasi otomatis. Silakan gunakan browser modern seperti Chrome, Edge, Firefox, atau Safari.', 'error');
            return;
        }

        const info = document.getElementById('gps-permission-info');
        if (info) info.textContent = 'Browser akan meminta izin mengakses lokasi Anda.';

        setDetectLoading(true);
        setAccuracy(statusEl, '');

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude, accuracy } = position.coords;

                setPosition(latitude, longitude, { fly: true, zoom: 16, geocode: true });
                setDetectLoading(false);

                const accuracyText = 'Akurasi GPS: ±' + Math.round(accuracy) + ' meter';
                if (accuracy > 500) {
                    setAccuracy(statusEl, accuracyText + '. Akurasi lokasi rendah. Pastikan GPS aktif dan coba kembali di tempat terbuka.', 'warn');
                } else {
                    setAccuracy(statusEl, accuracyText, 'info');
                }
            },
            (error) => {
                setDetectLoading(false);
                let msg;
                if (error.code === error.PERMISSION_DENIED) {
                    msg = 'Izin lokasi ditolak. Silakan izinkan akses lokasi pada browser untuk mendeteksi lokasi secara otomatis.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    msg = 'Lokasi tidak tersedia. Pastikan GPS/perizinan lokasi perangkat aktif.';
                } else if (error.code === error.TIMEOUT) {
                    msg = 'Gagal mendapatkan lokasi dalam waktu yang ditentukan. Silakan coba lagi.';
                } else {
                    msg = 'Tidak dapat mendeteksi lokasi. Silakan coba kembali.';
                }
                setAccuracy(statusEl, msg, 'error');
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    if (detectBtn) detectBtn.addEventListener('click', detectLocation);

    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            setPosition(savedLat, savedLng, { fly: true, zoom: defaultZoom });
            setAccuracy(statusEl, 'Lokasi dikembalikan ke koordinat tersimpan.', 'info');
        });
    }

    if (focusBtn) {
        focusBtn.addEventListener('click', () => {
            const ll = marker ? marker.getLatLng() : L.latLng(savedLat, savedLng);
            map.setView(ll, Math.max(map.getZoom(), 16));
        });
    }

    render(savedLat, savedLng);
    syncInputs(savedLat, savedLng);

    setTimeout(() => map.invalidateSize(), 250);
    window.addEventListener('resize', () => map.invalidateSize());

    return { map, marker, circle, setPosition, getLatLng: () => marker.getLatLng(), invalidateSize: () => map.invalidateSize() };
}

window.initLocationMap = initLocationMap;

function bootLocationMap() {
    if (!document.getElementById('location-map')) return;
    initLocationMap({ mapEl: '#location-map' });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootLocationMap);
} else {
    bootLocationMap();
}