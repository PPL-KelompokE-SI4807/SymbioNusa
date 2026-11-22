---
version: beta
name: SymbioNusa
description: Regulator-supervised B2B platform for non-hazardous (non-B3) industrial production residue in Indonesia. Calm, credible operational UI in Bahasa Indonesia. Amended 19 Nov 2026 to the PM-approved FigCraft direction "FND v8" (colour fields, soft shapes, real material photos).
colors:
  primary: "#15803d"
  primary-hover: "#166534"
  on-primary: "#ffffff"
  primary-container: "#dcfce7"
  on-primary-container: "#14532d"
  secondary: "#14532d"
  secondary-hover: "#1b5e37"
  on-secondary: "#ffffff"
  on-secondary-muted: "#cfeedd"
  secondary-label: "#a7e0b8"
  tertiary: "#1e40af"
  on-tertiary: "#ffffff"
  neutral: "#5f6d64"
  background: "#eef3ec"
  surface: "#ffffff"
  surface-muted: "#eef3ec"
  on-surface: "#16241b"
  on-surface-muted: "#4b5a50"
  on-surface-subtle: "#5f6d64"
  outline: "#e2e8df"
  outline-control: "#c9d3c6"
  outline-input: "#7a8a7f"
  focus-ring: "#15803d"
  error: "#b91c1c"
  error-container: "#fef2f2"
  on-error-container: "#7f1d1d"
  warning: "#b45309"
  warning-container: "#fff7e6"
  on-warning-container: "#78350f"
  success: "#15803d"
  success-container: "#f0fdf4"
  on-success-container: "#14532d"
  info: "#1e40af"
  info-container: "#dbeafe"
  on-info-container: "#1e40af"
  neutral-container: "#e4e4e7"
  on-neutral-container: "#3f3f46"
  tile-sun-from: "#f59e0b"
  tile-sun-to: "#fbbf24"
  on-tile-sun: "#3d2600"
  tile-green-from: "#166534"
  tile-green-to: "#15803d"
  tile-sky-from: "#075985"
  tile-sky-to: "#0369a1"
  tile-forest-from: "#0f3f22"
  tile-forest-to: "#14532d"
  on-tile: "#ffffff"
  on-tile-muted: "#cfeedd"
  stage-stuck: "#fbbf24"
  chart-lead: "#15803d"
  chart-2: "#3f3f46"
  chart-3: "#52525b"
  chart-4: "#71717a"
  chart-5: "#8a8a93"
typography:
  headline-lg:
    fontFamily: Figtree
    fontSize: 28px
    fontWeight: 600
    lineHeight: 1.2
  headline-md:
    fontFamily: Figtree
    fontSize: 18px
    fontWeight: 600
    lineHeight: 1.3
  display-metric:
    fontFamily: Figtree
    fontSize: 32px
    fontWeight: 600
    lineHeight: 1.1
    fontFeature: '"tnum" 1'
  body-lg:
    fontFamily: Figtree
    fontSize: 16px
    fontWeight: 400
    lineHeight: 1.5
  body-md:
    fontFamily: Figtree
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.5
  body-sm:
    fontFamily: Figtree
    fontSize: 13px
    fontWeight: 400
    lineHeight: 1.45
  label-md:
    fontFamily: Figtree
    fontSize: 14px
    fontWeight: 500
    lineHeight: 1.2
  label-sm:
    fontFamily: Figtree
    fontSize: 12px
    fontWeight: 500
    lineHeight: 1.3
  label-group:
    fontFamily: Figtree
    fontSize: 13px
    fontWeight: 500
    lineHeight: 1.2
  data-tabular:
    fontFamily: Figtree
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.4
    fontFeature: '"tnum" 1'
rounded:
  sm: 10px
  md: 14px
  lg: 20px
  full: 9999px
shadow:
  card: "0 8px 24px -8px rgba(16,24,40,0.10), 0 1px 2px rgba(16,24,40,0.05)"
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  2xl: 48px
  gutter: 24px
  page-margin: 16px
  sidebar-width: 276px
  topbar-height-mobile: 64px
  control-height: 40px
  control-height-lg: 44px
  table-row-height: 56px
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.label-md}"
    rounded: "{rounded.sm}"
    padding: 16px
    height: 40px
  button-primary-hover:
    backgroundColor: "{colors.primary-hover}"
  button-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    borderColor: "{colors.outline-control}"
    typography: "{typography.label-md}"
    rounded: "{rounded.sm}"
    padding: 16px
    height: 40px
  button-danger:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.error}"
    typography: "{typography.label-md}"
    rounded: "{rounded.sm}"
    padding: 16px
    height: 40px
  input-field:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    borderColor: "{colors.outline-input}"
    typography: "{typography.body-md}"
    rounded: "{rounded.sm}"
    padding: 14px
    height: 44px
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    rounded: "{rounded.lg}"
    shadow: "{shadow.card}"
    padding: 24px
  kpi-tile:
    backgroundColor: "gradient {colors.tile-*-from} → {colors.tile-*-to}, or {colors.surface} for neutral/zero"
    textColor: "{colors.on-tile} (sun tile: {colors.on-tile-sun})"
    typography: "{typography.display-metric}"
    rounded: "{rounded.lg}"
    padding: 20px
    height: 128px
  nav-item:
    backgroundColor: transparent
    textColor: "{colors.on-secondary}"
    typography: "{typography.label-md}"
    rounded: "{rounded.sm}"
    padding: 12px
    height: 40px
  nav-item-active:
    backgroundColor: "{colors.primary-container}"
    textColor: "{colors.on-primary-container}"
    typography: "{typography.label-md}"
  status-indicator:
    glyphSize: 16px
    textColor: "{colors.on-surface}"
    typography: "{typography.label-md}"
    glyph-warning: "dashed ring {colors.warning}"
    glyph-info: "ring + half fill {colors.info}"
    glyph-success: "filled circle + check {colors.success}"
    glyph-danger: "filled circle + cross {colors.error}"
    glyph-neutral: "filled circle + dash {colors.on-surface-subtle}"
  urgency-flag:
    backgroundColor: "{colors.error}"
    textColor: "{colors.error}"
    size: 16px
    rounded: 4px
  monogram:
    backgroundColor: "brand hue mixed 16% into white"
    textColor: "dark shade of the brand hue (≥ 4.5:1)"
    size: 40px
    rounded: "{rounded.sm}"
  table-header:
    textColor: "{colors.on-surface-subtle}"
    typography: "{typography.body-sm}"
    height: 40px
  table-row:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    typography: "{typography.data-tabular}"
    height: 56px
---

# SymbioNusa

> **Amandemen 19 Nov 2026 (FND v8).** PM (Mayhesa) approved the FigCraft mockup "SymbioNusa FND - v8 (setelah audit)" after two audit rounds (Impeccable critique + unslop). This file now describes that direction. Main changes from the Oct 2026 version: Figtree instead of Inter; sage page background; colour as large fields (gradient KPI tiles, floating forest sidebar, impact band); radii 10/14/20; soft neutral card shadow; status shown as a shape glyph + text instead of a tinted pill; urgency flag; tinted company monograms; real material photos allowed. Older values are in git history.

## Overview

SymbioNusa is an operational web application, not a marketing site. Four roles (Admin Platform / DLH regulator, Seller, Buyer, Inspector) clear work queues, negotiate and supervise B2B trades of non-hazardous industrial residue (kain perca, ampas tahu, serbuk kayu, cacahan plastik, kertas). The UI must feel **credible, calm and precise**, with warmth from colour fields and photos of the material itself. Numbers (tonnage, Rupiah, CO2e) and statuses are the product and must be scannable at a glance. Every screen answers "what needs my action" before "what happened".

All interface copy is **Bahasa Indonesia**. Formats: `Rp 3.000.000` (dot thousands separator, no decimals), compact `Rp 24 jt` / `Rp 214,6 jt` (no trailing ",0"), `5.000 kg`, aggregate tonnage as `128,4 ton` (comma decimal, the word "ton" everywhere), dates `27 Okt 2026, 14:00` in WIB. Layouts must work from 360 px wide.

## Colors

Chrome stays quiet (white surfaces, sage ground, one green accent); colour is spent in a few large fields that carry meaning.

- **Primary (#15803d):** the one filled action per view region, links, the leading series in charts. White text on it passes AA (5.0:1).
- **Primary container (#dcfce7) + on-primary-container (#14532d):** active nav item, count badges in the sidebar, avatars.
- **Secondary (#14532d):** the floating sidebar and the impact band. On it: text white (9.1:1), group labels **secondary-label (#a7e0b8)** (6.1:1), helper text **on-secondary-muted (#cfeedd)**.
- **Background (#eef3ec):** sage page ground. Cards are **surface (#ffffff)**.
- **Text:** on-surface **#16241b**, on-surface-muted **#4b5a50**, on-surface-subtle **#5f6d64** (placeholders, captions; 4.8:1 on the ground, 5.4:1 on white).
- **Lines:** outline **#e2e8df** for hairlines, outline-control **#c9d3c6** for button borders, outline-input **#7a8a7f** for input borders (≥ 3:1 non-text contrast).
- **KPI tile fields (gradient within one hue, top-left → bottom-right):** sun #f59e0b → #fbbf24 with dark text #3d2600; green #166534 → #15803d; sky #075985 → #0369a1; forest #0f3f22 → #14532d; white text on the last three. Tile colour has a fixed meaning:
  - **sun** = the viewer's own pending action with a count above 0 (RFQ belum dibalas, Tagihan terbuka, Inspeksi hari ini);
  - **sky** = in progress / waiting on someone else (RFQ terbuka, Invoice menunggu Buyer);
  - **green** = done or positive (Stok listing aktif, Selesai);
  - **plain white tile** = neutral counts and any zero value (a zero shows its number in green).
  - At most one tile per hue per row where possible; never four saturated tiles that all mean different things.
- **Status colours** (glyph colour, see Components): warning #b45309, info #1e40af, success #15803d, danger #b91c1c, neutral #5f6d64. Red #b91c1c is also the only error / destructive colour (no other reds).
- **Stuck stage (#fbbf24 fill, #3d2600 text):** the one stuck stage in the order flow. Amber fills appear only in sun tiles and the stuck stage; elsewhere amber is a foreground (warning icon, warning text).
- **Charts and composition bars:** the leading series in primary green, the rest in neutral steps #3f3f46 / #52525b / #71717a / #8a8a93 (all ≥ 3:1 on white); never five shades of green.
- **Shadows** are neutral (rgba(16,24,40,…)), never green-tinted. No glows.
- The palette is closed: green family, sage/zinc neutrals, blue, amber, red. No pink, raspberry, purple, teal.

## Typography

**Figtree** (self-hosted through the Vite font plugin), one family, weights **400, 500, 600 only**. Tabular figures for every number.

- **Page title (headline-lg):** 28 px / 600. It names the context, not a greeting: the organisation ("Dinas Lingkungan Hidup", "PT Plastik Makmur Sejahtera") or the person for an Inspector. The subtitle below says the screen and time: "Dashboard Seller · per 19 Nov 2026, 09.40 WIB".
- **Card titles (headline-md):** 18 px / 600.
- **KPI values (display-metric):** 32 px / 600, unit in 16 px / 500 next to it. No unit that repeats the label ("PO baru · 1", not "1 PO").
- **Body / table cells:** 14 px; meta lines and captions 13 px / 400; small labels 12 px / 500.
- **Sidebar group labels (label-group):** 13 px / 500, sentence case ("Master data"), no letter-spacing. No uppercase tracked labels anywhere.
- Allowed sizes: 12, 13, 14, 15, 16, 18, 22, 28, 32 (40 only on the auth photo panel). Never below 12 px. No negative letter-spacing under 22 px.

## Layout

App shell: a **floating forest sidebar (276 px, radius 20, 16 px from the viewport edges)** on the sage ground: logo mark (mint tile) + "SymbioNusa", grouped navigation of the pages that exist, one collapsed **"Segera hadir (n)"** row for routes not built yet (never a list of dead items), and the user card at the bottom (avatar, name, role, labelled "Keluar" button). There is **no desktop top bar**: each page starts with its header row: title + subtitle on the left; "Data demo" tag (demo data only), search field, notification bell with a red count, and at most one primary action on the right.

Dashboard pattern: header → (role dashboards) a row of 4 KPI tiles → two columns: main column (≈ 2/3: "Perlu tindakan"/queue, then the order table) and side column (380 px: one photo card + one compact list). Columns stack independently so neither leaves an empty band. The **Admin dashboard leads with action**: header → Alur order → Antrean tindakan (2/3) + Material (1/3) → "Dampak bulan ini" impact band at the bottom.

Auth pages: split screen; left 640 px photo panel (material photo under a forest overlay at 60–90%, wordmark, one statement, material chips), right a white card form on the sage ground.

Responsive: below 1024 px the sidebar becomes a drawer (304 px) opened from a menu button in a 64 px mobile top bar (menu, logo, bell); tiles become a 2×2 grid; the order-flow strip scrolls horizontally and opens on the stuck stage; tables become row cards.

Spacing: 4 px base; 24 px between cards; 24 px card padding (20 px in tiles); rows 56 px.

## Elevation & Depth

Cards are white with a soft neutral shadow (shadow.card) on the sage ground; hairline borders only inside cards. No glassmorphism, glows, coloured shadows or decorative blur. Overlays (dropdown, dialog) use a larger neutral shadow and a dark scrim.

## Shapes

10 px radius on buttons, inputs, nav items, monograms and photo thumbnails; 14 px on inner panels, stage boxes and alerts; 20 px on cards, tiles, the sidebar and dialogs; full pills only for count badges, tags and the "Data demo" chip. Circles only for people (avatars) and status glyphs.

## Components

- **Buttons:** primary (filled green), secondary (white, 1 px outline-control border), danger (white, red border and text), text links in green with an arrow for navigation ("Lihat 3 order →"). Exactly one filled button per region: in a list, only the top item's action is filled, the rest are secondary. Labels are verbs ("Balas", "Konfirmasi", "Tetapkan", "Periksa"). No coloured shadows.
- **Status indicator** (replaces the tinted pill badge): a 16 px glyph + the STATUS_MAP label in on-surface text. Glyph by variant: warning = dashed ring (waiting for someone), info = ring with half fill (in progress), success = filled circle with check, danger = filled circle with cross, neutral = filled circle with a dash. Shape + colour + text, so it never relies on colour alone (Carbon status-indicator pattern). The component keeps the old API (`<x-ui.badge :variant>`).
- **Urgency:** only urgent items are flagged: a 16 px red square with a white "!" plus the date in red ("Besok · tenggat"). Non-urgent rows carry no priority label. The rule is the same on every role and on mobile.
- **KPI tile:** label (14/500) top-left, value + unit at the bottom, one caption line (13/400). No icons, no decorative circles, no sparklines.
- **Order flow (Admin):** the 10 STATUS_MAP stages in three groups (Kesepakatan: Draf, Menunggu Konfirmasi, Dikonfirmasi · Mutu & pembayaran: Inspeksi, Lolos Inspeksi, Menunggu Pembayaran, Dibayar · Pengiriman: Dalam Pengiriman, Diterima, Selesai). Stage boxes on the sage tint; the stuck stage in sun; Selesai in mint with a green number. Exits in one muted line; a stuck callout with a text link that filters the queue (no second filled button). RFQ counts are shown separately, not as a stage.
- **Lists ("Perlu tindakan", antrean, jadwal):** monogram or photo, title (15/600), meta (13/400 subtle), a date column with its meaning underneath ("tenggat", "masuk", "jatuh tempo", "diunggah"), action on the right.
- **Monogram:** company initials on the company hue mixed 16% into white, initials in a dark shade of the hue (≥ 4.5:1). Circles are reserved for people.
- **Material photos:** real photos of the residue (see `docs/design/CREDITS-FOTO.md` for sources and licences) in 48–60 px thumbnails, photo cards and the auth panel. No stock people, no illustrations of generic "eco" themes.
- **Impact band:** one forest field with 4 cells separated by hairlines; label, value + unit, caption. "data demo" when the numbers are seeded.
- **Data table:** 13 px muted header, rows ≥ 56 px, numbers right-aligned, status column uses the status indicator, last column for actions. In narrow columns the secondary figure (e.g. volume) moves under the primary one instead of adding a column.
- **Forms:** label above every field, helper 13 px below, error 13 px red with an icon below the field it belongs to; credential errors (wrong email or password) as one alert above the form, not on a single field. Password fields have a "Tampilkan kata sandi" eye button.
- **Alerts:** light container (danger #fef2f2, success #f0fdf4, warning #fff7e6) with a 1 px border of the same hue, a plain 20 px icon (no icon tile), bold first sentence.
- **States:** visible focus ring (2 px focus-ring, 2 px offset; white on the forest sidebar); disabled 50% opacity with a reason; hover darkens fills or tints secondary buttons with the sage ground.
- **Icons:** outline, 1.75 stroke, 20 px (16 px in dense rows), currentColor, one family (`x-ui.icon`).

## Do's and Don'ts

- Do keep exactly one filled-green action per region.
- Do use the status indicator (glyph + text) for every status; never colour alone.
- Do give tile colour a meaning from the Colors section; a zero is a white tile.
- Do title pages with the context (organisation or person), not a greeting.
- Do label every date with what it means.
- Do use tabular numbers and Indonesian formats; "ton" for aggregate tonnage.
- Do keep sidebar menu names exactly as in STITCH_BRIEF.md; collapse unbuilt routes into "Segera hadir".
- Do design empty, loading (skeleton rows) and error states for every list and table.
- Don't add icons, circles or sparklines to KPI tiles.
- Don't use gradients outside KPI tiles, the impact band and the forest overlay on auth photos, and never from one hue to another.
- Don't use uppercase tracked labels, weights above 600, or text below 12 px.
- Don't use stock illustrations, stock people or marketing heroes inside the app.
- Don't present demo figures as real impact; label them "data demo".
