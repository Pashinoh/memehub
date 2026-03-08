# Changelog

## [v1.3.4] - 2026-03-08

### Changed
- Upload modal layout refined to a cleaner professional structure: compact square media preview on the left, focused title panel on the right, and hashtags section below.
- Upload media preview area resized to be smaller and visually balanced with the title section.
- Quick tips panel removed from upload modal to keep the form simpler.

### Fixed
- Feed/background videos are reliably paused while upload modal is open, preventing hidden audio playback.
- Profile grid media behavior tightened so video and GIF content remains static (no moving playback effect on profile cards).

## [v1.3.3] - 2026-03-08

### Changed
- Upload now focuses on lightweight files only: image-only input (`JPG`, `PNG`, `WEBP`) with max size `8MB`.
- Uploaded images are automatically optimized and converted to `WEBP` for faster loading and lower bandwidth usage.
- Added lightweight runtime mode for low-spec devices by reducing heavy media behavior and using native video controls fallback.
- Desktop navbar title (`MemeHub`) is clickable again and routes directly to Home.

### Fixed
- Report submissions from meme posts now always re-enter moderation as `pending` when re-reported after review/rejection.
- Bookmark page text now follows active locale (Indonesian/English) instead of hardcoded English.

## [v1.3.2] - 2026-03-05

### Fixed
- Replaced report dropdown on meme detail and home feed with popup modal flow.
- Fixed report popup layering so modal always appears above meme/media elements.
- Fixed report action behavior on home feed so it opens immediately without re-triggering the action menu.

### Changed
- Settings page now shows app version and latest changelog summary in a unified version panel.
- Documentation updated: README simplified in English and installation guide moved to GitHub Wiki.

## [v1.3.0] - 2026-03-05

### Changed
- Responsive navbar desktop/mobile refined to prevent layout collisions.
- Brand dropdown (`MemeHub`) now appears on mobile only; desktop uses plain title.
- Mobile panel transitions (brand/search/notifications/menu) smoothed with fade-slide animation.
- Feed interest sidebar remains desktop-only to avoid duplicate navigation on mobile.
