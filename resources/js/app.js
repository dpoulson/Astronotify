import './bootstrap';

if ('serviceWorker' in navigator && window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch((err) => {
            console.error('ServiceWorker registration failed: ', err);
        });
    });
}

window.astronotifyLocationManager = function(config = {}) {
    return {
        formOpen: Boolean(config.formOpen),
        transitModal: null,
        locating: false,
        locatingMsg: 'Locating...',
        gpsError: null,
        showPwaBanner: false,
        pwaInstalled: false,
        pwaInstallPrompt: null,
        shareCopied: false,
        initPwa() {
            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
                this.pwaInstalled = true;
                return;
            }
            if (localStorage.getItem('astronotify_dismiss_pwa') === 'true') {
                return;
            }
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.pwaInstallPrompt = e;
                this.showPwaBanner = true;
            });
            const isIos = /iPhone|iPad|iPod/.test(navigator.userAgent) && !window.MSStream;
            if (isIos && !this.pwaInstalled) {
                this.showPwaBanner = true;
            }
        },
        async installPwa() {
            if (this.pwaInstallPrompt) {
                this.pwaInstallPrompt.prompt();
                const choice = await this.pwaInstallPrompt.userChoice;
                if (choice && choice.outcome === 'accepted') {
                    this.showPwaBanner = false;
                    this.pwaInstalled = true;
                }
                this.pwaInstallPrompt = null;
            } else {
                alert('To install on iPhone/iPad: Tap the Share icon at the bottom of Safari, then choose "Add to Home Screen".');
            }
        },
        dismissPwa() {
            this.showPwaBanner = false;
            localStorage.setItem('astronotify_dismiss_pwa', 'true');
        },
        downloadIcs(summary, location, description, startTimeUtc, endTimeUtc) {
            const icsLines = [
                'BEGIN:VCALENDAR',
                'VERSION:2.0',
                'PRODID:-//Astronotify//Astronomical Transit Calendar//EN',
                'CALSCALE:GREGORIAN',
                'METHOD:PUBLISH',
                'BEGIN:VEVENT',
                `UID:transit-${Date.now()}@astronotify.org`,
                `DTSTAMP:${startTimeUtc}`,
                `DTSTART:${startTimeUtc}`,
                `DTEND:${endTimeUtc}`,
                `SUMMARY:${summary}`,
                `DESCRIPTION:${description.replace(/\n/g, '\\n')}`,
                `LOCATION:${location}`,
                'BEGIN:VALARM',
                'TRIGGER:-PT15M',
                'ACTION:DISPLAY',
                'DESCRIPTION:Reminder: ISS Transit in 15 minutes!',
                'END:VALARM',
                'END:VEVENT',
                'END:VCALENDAR'
            ];
            const blob = new Blob([icsLines.join('\r\n')], { type: 'text/calendar;charset=utf-8' });
            const link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.setAttribute('download', `iss-transit-${startTimeUtc}.ics`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
        async shareTransit(title, text) {
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: title,
                        text: text,
                        url: 'https://astronotify.org'
                    });
                    return;
                } catch(e) {}
            }
            if (navigator.clipboard) {
                await navigator.clipboard.writeText(text);
                this.shareCopied = true;
                setTimeout(() => { this.shareCopied = false; }, 3000);
            }
        },
        async getGpsLocation() {
            if (!navigator.geolocation) {
                this.gpsError = 'Geolocation is not supported by your device.';
                return;
            }
            this.locating = true;
            this.locatingMsg = 'Acquiring GPS fix...';
            this.gpsError = null;

            navigator.geolocation.getCurrentPosition(async (pos) => {
                const lat = parseFloat(pos.coords.latitude.toFixed(5));
                const lon = parseFloat(pos.coords.longitude.toFixed(5));
                let elevation = (pos.coords.altitude !== null && !isNaN(pos.coords.altitude)) ? Math.round(pos.coords.altitude) : null;

                if (elevation === null) {
                    this.locatingMsg = 'Fetching elevation...';
                    try {
                        const elevRes = await fetch(`https://api.open-meteo.com/v1/elevation?latitude=${lat}&longitude=${lon}`);
                        if (elevRes.ok) {
                            const elevData = await elevRes.json();
                            if (elevData.elevation && elevData.elevation.length > 0) {
                                elevation = Math.round(elevData.elevation[0]);
                            }
                        }
                    } catch(e) {}
                }

                this.locatingMsg = 'Finding town name...';
                let townName = '';
                try {
                    const geoRes = await fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (geoRes.ok) {
                        const geoData = await geoRes.json();
                        if (geoData.address) {
                            townName = geoData.address.city || geoData.address.town || geoData.address.village || geoData.address.suburb || geoData.address.county || '';
                        }
                    }
                } catch(e) {}

                this.locating = false;
                this.formOpen = true;
                const wire = this.$wire || (window.Livewire && this.$el ? window.Livewire.find(this.$el.closest('[wire\\:id]')?.getAttribute('wire:id')) : null);
                if (wire) {
                    wire.setGpsCoordinates(lat, lon, elevation ?? 0, townName);
                }
            }, (err) => {
                this.locating = false;
                let msg = 'Could not get GPS location.';
                if (err.code === 1) msg = 'Location permission denied. Please allow access in browser settings.';
                else if (err.code === 2) msg = 'Location unavailable. Ensure device GPS is enabled.';
                else if (err.code === 3) msg = 'Location request timed out. Please try again.';
                this.gpsError = msg;
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            });
        }
    };
};

