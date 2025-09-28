# Vibe Coding SEO – Repository Starter

This repo contains:
- `plugin/` – WordPress plugin source.
- GitHub Actions workflow to build the ZIP and attach it to a GitHub Release.
- `CHANGELOG.md` – Write changes per release.
- `.version` – Source of truth for the plugin version (synced automatically).

## Release Flow (No Coding Needed)
1. Edit plugin as needed (or ask your AI teammate to do it).
2. Update **.version** to the new version, e.g. `0.1.4`.
3. Update **CHANGELOG.md** with the changes.
4. Create a Git tag `v0.1.4` and push. The workflow will:
   - Zip the plugin
   - Attach it to a GitHub Release
   - Print a download link

### Upload to Freemius
- Download the ZIP from the Release page.
- Upload to Freemius as a new version. Customers will get auto-update.

## Local Build
Run `bash tools/build.sh` to produce `dist/vibe-coding-seo-<version>.zip`.
