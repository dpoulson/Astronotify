# Astronotify — TODO

## 🔔 Notifications
- [x] H **Per-location notification toggles** — on/off switch per location for stargazing alerts (separate from ISS transit toggles which already exist)
- [ ] L **Notification quiet hours** — let users set a "do not disturb" window so alerts don't arrive at 3am
- [x] M **Test notification button** — send a test email from the location card to confirm delivery is working
- [x] H **Unsubscribe / re-subscribe link** in email footers so users can opt out without logging in
- [x] H **Notification deduplication & overcast suppression** — track `notified_at` on transits and weather conditions; suppress transit alerts when overcast (≥90% cloud cover) with dashboard warnings

## 🛠️ Admin
- [ ] L **Fix stats / metrics** — verify system_metrics and daily_metrics are incrementing correctly; display graphs on admin dashboard
- [x] H **API usage tracker** — show total Open-Meteo calls this month vs free tier limit (1,000/day)
- [ ] L **Per-user location count** and last-active date in user table
- [x] H **Manual trigger buttons** in admin UI for `weather:fetch` and `weather:iss-transits` (instead of SSH + artisan)
- [x] M **Failed job queue** — surface any queued email failures in the admin dashboard with retry button
- [x] H **Anti spam accounts** — honeypot field, minimum form submission timing check, and email verification

## 📄 Pages
- [x] M **Update About page** — reflect current feature set (ISS transits, elevation support, solar/lunar path diagrams, etc.)
- [x] M **Add a Help / FAQ page** — explain what separation degrees means, what counts as a transit vs conjunction, how to set thresholds, and solar safety
- [x] M **Landing / marketing page** — modern redesign of welcome page with transit previews, feature pillars, and navigation

## 🗺️ Dashboard UX
- [x] L **Fix jerk on Add Location expand/collapse** — stabilized layout with fixed column width transitions and sticky positioning
- [x] H **Mobile layout pass** — mobile-friendly GPS auto-fill, edge-to-edge support, touch targets, and responsive sizing
- [x] L **Transit card grid columns** — switched to 2-col layout with larger diagrams and responsive spacing
- [x] L **Animate the transit modal** — replaced scale clipping with mobile translate-y animation and viewport scroll constraint

## 🔭 ISS / Orbital
- [x] H **Re-run `weather:iss-transits` after adding a new location** — currently the user has to trigger this manually; consider auto-triggering it from the `save()` Livewire action (queue a job)
- [x] L **Path point density** — the coarse 10-second sampling gives very few path points for fast-moving ISS passes; consider a finer pass (~2s) within ±30s of the closest approach to get a more accurate chord
- [x] M **Conjunction threshold setting** — currently hard-coded at 0.75°; expose this in the admin settings page
- [x] H **ISS pass schedule view** — a simple table of all upcoming passes (not just transits) for each location with AOS/LOS time and max elevation
- [x] L **Hardcoded values in SunCalc** — There are a lot of hardcoded values in SunCalc.php functions, should these be replaced with named constants?

## 🌤️ Weather
- [ ] L **forecast_days admin cap warning** — the setting is currently 16 but Open-Meteo caps at 16 and we need +2 buffer, so we silently cap at 14 nights; surface this limit in the admin settings UI with a note
- [ ] L **Weather data browse page** — allow users to browse the full hourly forecast for upcoming nights (not just the 7-day card grid)
- [x] M **Moon phase display** — show moon phase icon on each forecast day card (affects naked-eye astronomy)
- [x] M **Light pollution overlay** — link to or embed a Bortle scale indicator for each location
- [x] H **Recheck on location change** — If a user changes their viewing requirements, the forecast doesn't update to check for optimal days

## 🔧 Technical / Housekeeping
- [x] H **Remove test login route** — ensure `/login-as-test-user` is not present on production (currently removed but worth a deploy checklist item)
- [ ] L **Scheduler verification** — confirm `php artisan schedule:run` is wired up in the hosting cron job and both `weather:fetch` and `weather:iss-transits` fire daily
- [ ] L **Queue worker** — ensure `php artisan queue:work` (or `queue:listen`) is running as a persistent process on the shared host, or switch to `QUEUE_CONNECTION=sync` if a worker can't be kept alive
- [ ] L **`.env` secrets audit** — double-check `APP_KEY`, mail credentials, and `APP_DEBUG=false` before going live
- [x] M **Error page branding** — style the 404 / 500 pages to match the dark theme
