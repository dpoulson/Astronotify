# Astronotify 🌌

[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue.svg)](https://www.php.net/)
[![Laravel Framework](https://img.shields.io/badge/laravel-11.x%20%2F%2013.x-red.svg)](https://laravel.com/)
[![Livewire](https://img.shields.io/badge/livewire-3.x-pink.svg)](https://livewire.laravel.com/)
[![Tailwind CSS](https://img.shields.io/badge/tailwind-3.x-38bdf8.svg)](https://tailwindcss.com/)
[![Code Style: Pint](https://img.shields.io/badge/code%20style-pint-green.svg)](https://github.com/laravel/pint)
[![License: GPL v2](https://img.shields.io/badge/license-GPLv2-blue.svg)](LICENSE)

> Automated clear-sky stargazing alerts, International Space Station (ISS) solar and lunar transit predictions, and curated dark-sky discovery for astronomers and astrophotographers.

---

## 🔭 Overview

**Astronotify** is a personal, open-source astrophotography and stargazing observation assistant. It continuously monitors weather conditions against customizable personal observing thresholds and accurately computes celestial passes of the International Space Station against the Sun and Moon using high-precision SGP4 orbital propagation.

### Key Highlights
- **ISS Solar & Lunar Transits**: Predicts upcoming transits and close conjunctions with dynamic SVG orbital chord diagrams, angular separation calculations, altitude/azimuth readouts, and 1-click Google Calendar / iCal exports.
- **Precision Clear-Sky Alerts**: Pulls meteorological data from Open-Meteo to evaluate cloud cover, transparency, wind speed, and temperature against user-defined criteria.
- **Overcast Suppression & Deduplication**: Prevents alert fatigue by tracking notification timestamps and suppressing transit warnings if local conditions are overcast (&ge;90% cloud cover).
- **Curated Stargazing Spots Directory**: Over 100 verified dark-sky discovery sites worldwide featuring Bortle darkness scales, interactive Leaflet maps, and on-demand forecast hydration.
- **Privacy by Design**: Zero third-party trackers, no invasive analytics, and immediate, irreversible account deletion with complete data removal.

---

## 🛠️ Tech Stack

- **Backend Framework**: [Laravel](https://laravel.com) with [Laravel Jetstream](https://jetstream.laravel.com/) & [Livewire 3](https://livewire.laravel.com)
- **Frontend / Styling**: Vanilla CSS & [Tailwind CSS](https://tailwindcss.com), [Alpine.js](https://alpinejs.dev), [Leaflet.js](https://leafletjs.com)
- **Orbital Mechanics & Astrodynamics**:
  - SGP4 orbital propagation algorithms with two-line element sets (TLEs)
  - SunCalc solar & lunar ephemeris modeling
  - JPL DE421 ephemeris integration
- **Weather Services**: High-resolution meteorological data via [Open-Meteo](https://open-meteo.com)
- **Code Quality & CI**: [Laravel Pint](https://github.com/laravel/pint), [PHPUnit](https://phpunit.de), GitHub Actions & Gitea Actions

---

## 🚀 Quick Start

### Prerequisites
- **PHP** &ge; 8.3 with `pdo`, `sqlite3` or `pdo_mysql`, `curl`, and `mbstring` extensions
- **Composer** &ge; 2.2
- **Node.js** &ge; 18 & **npm**

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/dpoulson/astronotify.git
   cd astronotify
   ```

2. **Automated Setup**:
   ```bash
   composer setup
   ```
   *This command runs `composer install`, copies `.env.example` to `.env` if missing, generates the application key, runs database migrations, and builds frontend assets.*

3. **Configure Environment (`.env`)**:
   Verify or customize your settings:
   ```env
   APP_NAME="Astronotify"
   APP_URL=http://localhost:8000
   DB_CONNECTION=sqlite
   ```

4. **Seed Stargazing Spots (Optional)**:
   ```bash
   php artisan db:seed
   ```

5. **Start Development Server**:
   ```bash
   npm run dev &
   php artisan serve
   ```
   Visit `http://localhost:8000` in your browser.

---

## ⚙️ Background Tasks & Commands

Astronotify relies on Artisan commands to sync forecast data, update orbital models, and dispatch email alerts:

| Command | Frequency | Description |
|---|---|---|
| `php artisan weather:fetch` | Hourly / Daily | Fetches updated 7-day weather forecasts for all active locations |
| `php artisan weather:iss-transits` | Daily | Calculates solar/lunar ISS passes for all tracked locations & spots |
| `php artisan schedule:run` | Every minute | Native Laravel task scheduler orchestrating background jobs |
| `php artisan queue:work` | Continuous | Processes queued notification emails and webhook alerts |

Configure the host cron job to run the scheduler every minute:
```bash
* * * * * cd /path/to/astronotify && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🧪 Testing & Code Quality

Astronotify includes test coverage across all features:

```bash
# Run unit and feature test suite
php artisan test

# Run code style linting with Pint
./vendor/bin/pint --test

# Automatically fix code styling issues
./vendor/bin/pint
```

Continuous integration is automated via GitHub Actions and Gitea Actions workflows.

---

## 💬 Community & Feedback

- **In-App Feedback**: Submit spot suggestions and bug reports directly via the feedback modal or admin portal at `/admin/requests`.

---

## 📄 License

Astronotify is open-sourced software licensed under the [GNU General Public License v2.0](LICENSE).
