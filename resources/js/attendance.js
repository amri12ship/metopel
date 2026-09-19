import { Html5Qrcode } from 'html5-qrcode';

(function () {
    const scannerArea = document.getElementById('attendance-scanner-area');

    if (!scannerArea) {
        return;
    }

    const form = document.getElementById('attendance-form');
    const tokenField = document.getElementById('attendance-token');
    const latField = document.getElementById('attendance-latitude');
    const lngField = document.getElementById('attendance-longitude');
    const scanStatus = document.getElementById('attendance-scan-status');
    const startBtn = document.getElementById('attendance-start-camera');
    const manualForm = document.getElementById('attendance-manual-form');
    const manualToken = document.getElementById('attendance-manual-token');
    const submitBtn = document.getElementById('attendance-submit');
    const locationStatus = document.getElementById('attendance-location-status');

    if (!form) {
        return;
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const submitUrl = form.dataset.submit;
    const coordinateAccuracy = document.getElementById('attendance-accuracy');
    const overlay = document.getElementById('attendance-overlay');
    const overlayText = document.getElementById('attendance-overlay-text');

    function showOverlay(message) {
        if (!overlay) return;
        overlayText.textContent = message;
        overlay.classList.remove('hidden');
    }

    function hideOverlay() {
        if (!overlay) return;
        overlay.classList.add('hidden');
    }

    let html5QrCode = null;
    let scanning = false;

    function showScanStatus(message, isError = false) {
        if (!scanStatus) return;
        scanStatus.textContent = message;
        scanStatus.classList.toggle('text-red-600', isError);
        scanStatus.classList.toggle('text-gray-600', !isError);
    }

    async function stopScanner() {
        if (html5QrCode && scanning) {
            try {
                await html5QrCode.stop();
                html5QrCode.clear();
            } catch (e) {
                // kamera sudah berhenti
            }
            scanning = false;
        }
        if (startBtn) startBtn.classList.remove('hidden');
    }

    async function startScanner() {
        if (!("mediaDevices" in navigator) || !navigator.mediaDevices.getUserMedia) {
            showScanStatus('Kamera tidak didukung pada perangkat ini. Gunakan tombol input manual.', true);
            return;
        }

        manualForm.classList.add('hidden');
        showScanStatus('Menyalakan kamera… izinkan akses kamera bila diminta.');
        showOverlay('Memeriksa kamera…');

        try {
            html5QrCode = new Html5Qrcode('attendance-scanner-area');

            await html5QrCode.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                (decodedText) => {
                    stopScanner();
                    extractToken(decodedText);
                },
                () => {
                    // frame tidak berisi QR - abaikan
                },
            );

            scanning = true;
            if (startBtn) startBtn.classList.add('hidden');
            showScanStatus('Arahkan kamera ke kode QR lokasi kantor.');
            hideOverlay();
        } catch (err) {
            console.error(err);
            showScanStatus('Gagal menyalakan kamera. Gunakan input kode manual.', true);
            manualForm.classList.remove('hidden');
            hideOverlay();
        }
    }

    function extractToken(text) {
        const trimmed = text.trim();

        let token = trimmed;
        const match = trimmed.match(/absensi\/scan\/([A-Za-z0-9]+)$/);
        if (match) {
            token = match[1];
        }

        tokenField.value = token;
        showScanStatus('QR terbaca! Menentukan lokasi Anda…');
        showOverlay('Lokasi QR valid. Menentukan posisi Anda…');
        getLocation();
    }

    function getLocation() {
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mendapatkan lokasi…';
        }

        if (!("geolocation" in navigator)) {
            toast('Browser tidak mendukung lokasi GPS.', true);
            if (submitBtn) submitBtn.disabled = false;
            return;
        }

        const toastEl = document.getElementById('attendance-toast');

        navigator.geolocation.getCurrentPosition(
            (position) => {
                latField.value = position.coords.latitude;
                lngField.value = position.coords.longitude;

                if (coordinateAccuracy) {
                    coordinateAccuracy.textContent = 'Akurasi ±' + Math.round(position.coords.accuracy) + ' m';
                }
                if (locationStatus) {
                    locationStatus.textContent = 'Lokasi terdeteksi: ' + position.coords.latitude.toFixed(6) + ', ' + position.coords.longitude.toFixed(6);
                }

                hideOverlay();
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'KONFIRMASI ABSENSI';
                }
                if (form) form.classList.remove('hidden');
                if (toastEl) toastEl.classList.add('hidden');
            },
            (error) => {
                let message = 'Gagal mengambil lokasi.';
                if (error.code === 1) message = 'Akses lokasi ditolak. Izinkan akses lokasi di pengaturan browser.';
                if (error.code === 2) message = 'Lokasi tidak tersedia. Coba lagi.';
                if (error.code === 3) message = 'Waktu mendapat lokasi habis. Coba lagi.';

                showScanStatus(message, true);
                hideOverlay();

                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Coba Lagi';
                }
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 },
        );
    }

    function toast(message, isError = false) {
        const toastEl = document.getElementById('attendance-toast');
        if (!toastEl) {
            alert(message);
            return;
        }
        toastEl.textContent = message;
        toastEl.classList.remove('hidden');
        toastEl.classList.toggle('bg-red-50', isError);
        toastEl.classList.toggle('text-red-700', isError);
        toastEl.classList.toggle('border-red-200', isError);
        toastEl.classList.toggle('bg-green-50', !isError);
        toastEl.classList.toggle('text-green-700', !isError);
        toastEl.classList.toggle('border-green-200', !isError);
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses…';
        }
        showOverlay('Memverifikasi lokasi Anda di server…');

        try {
            const response = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    token: tokenField.value,
                    latitude: latField.value,
                    longitude: lngField.value,
                }),
            });

            const data = await response.json();

            if (data.success) {
                showOverlay(data.message);
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else {
                hideOverlay();
                toast(data.message || 'Terjadi kesalahan.', true);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Coba Lagi';
                }
            }
        } catch (error) {
            hideOverlay();
            toast('Gagal terhubung ke server. Coba lagi.', true);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Coba Lagi';
            }
        }
    });

    if (startBtn) {
        startBtn.addEventListener('click', startScanner);
    }

    if (manualToken) {
        manualToken.addEventListener('input', () => {
            if (manualToken.value.trim().length > 0) {
                tokenField.value = manualToken.value.trim();
            }
        });
    }
})();