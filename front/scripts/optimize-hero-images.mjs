/**
 * One-off asset pipeline: generates display-sized WebP variants next to full-resolution sources.
 * Run from repo root: node scripts/optimize-hero-images.mjs
 * Requires source files in public/images (commit or copy before running in CI).
 */
import { existsSync } from 'node:fs';
import { mkdir } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import sharp from 'sharp';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const publicImages = path.join(__dirname, '../public/images');

async function main() {
  try {
    await mkdir(publicImages, { recursive: true });
  } catch {
    /* exists */
  }

  const heroSrc = path.join(publicImages, 'home-hero-section.webp');
  const mobileSrc = path.join(publicImages, 'mobile-app.webp');

  if (!existsSync(heroSrc)) {
    console.warn('[optimize-hero-images] Skip hero: missing', heroSrc);
  } else {
    await sharp(heroSrc)
      .resize(622, 512, { fit: 'inside', withoutEnlargement: true })
      .webp({ quality: 85 })
      .toFile(path.join(publicImages, 'home-hero-section-622.webp'));
    console.log('[optimize-hero-images] Wrote home-hero-section-622.webp');
  }

  if (!existsSync(mobileSrc)) {
    console.warn('[optimize-hero-images] Skip mobile: missing', mobileSrc);
  } else {
    await sharp(mobileSrc)
      .resize(622, 819, { fit: 'inside', withoutEnlargement: true })
      .webp({ quality: 85 })
      .toFile(path.join(publicImages, 'mobile-app-622.webp'));
    console.log('[optimize-hero-images] Wrote mobile-app-622.webp');
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
