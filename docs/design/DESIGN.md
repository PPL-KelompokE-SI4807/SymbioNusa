---
version: alpha
name: SymbioNusa
description: Regulator-supervised B2B platform for non-hazardous (non-B3) industrial production residue in Indonesia. Calm, credible operational UI in Bahasa Indonesia, derived from the approved proposal mockups (Oct 2026).
colors:
  primary: "#15803d"
  primary-hover: "#166534"
  on-primary: "#ffffff"
  primary-container: "#dcfce7"
  on-primary-container: "#166534"
  secondary: "#14532d"
  secondary-hover: "#166534"
  on-secondary: "#ffffff"
  on-secondary-muted: "#bbf7d0"
  tertiary: "#1e40af"
  on-tertiary: "#ffffff"
  neutral: "#71717a"
  background: "#f4f4f5"
  surface: "#ffffff"
  on-surface: "#18181b"
  on-surface-muted: "#52525b"
  outline: "#e4e4e7"
  outline-input: "#71717a"
  focus-ring: "#15803d"
  error: "#b91c1c"
  error-container: "#fee2e2"
  on-error-container: "#991b1b"
  warning: "#b45309"
  warning-container: "#fef3c7"
  on-warning-container: "#92400e"
  success-container: "#dcfce7"
  on-success-container: "#166534"
  info-container: "#dbeafe"
  on-info-container: "#1e40af"
  neutral-container: "#e4e4e7"
  on-neutral-container: "#3f3f46"
typography:
  headline-lg:
    fontFamily: Inter
    fontSize: 26px
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: 600
    lineHeight: 1.3
  display-metric:
    fontFamily: Inter
    fontSize: 28px
    fontWeight: 700
    lineHeight: 1.1
    fontFeature: '"tnum" 1'
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: 400
    lineHeight: 1.5
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.5
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: 400
    lineHeight: 1.45
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: 500
    lineHeight: 1.2
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: 500
    lineHeight: 1.3
  label-caps:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: 0.06em
  data-tabular:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.4
    fontFeature: '"tnum" 1'
rounded:
  sm: 6px
  md: 8px
  lg: 12px
  full: 9999px
spacing:
  base: 4px
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
  2xl: 48px
  gutter: 16px
  page-margin: 32px
  sidebar-width: 240px
  topbar-height: 64px
  control-height: 40px
  table-row-height: 52px
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.label-md}"
    rounded: "{rounded.sm}"
    padding: 12px
    height: 40px
  button-primary-hover:
    backgroundColor: "{colors.primary-hover}"
  button-secondary:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    typography: "{typography.label-md}"
    rounded: "{rounded.sm}"
    padding: 12px
    height: 40px
  button-danger:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.error}"
    typography: "{typography.label-md}"
    rounded: "{rounded.sm}"
    padding: 12px
    height: 40px
  input-field:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-md}"
    rounded: "{rounded.sm}"
    padding: 12px
    height: 40px
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    rounded: "{rounded.md}"
    padding: 24px
  kpi-card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    typography: "{typography.display-metric}"
    rounded: "{rounded.md}"
    padding: 20px
  nav-item:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    typography: "{typography.body-md}"
    rounded: "{rounded.sm}"
    padding: 12px
    height: 40px
  nav-item-active:
    backgroundColor: "{colors.primary-container}"
    textColor: "{colors.on-primary-container}"
    typography: "{typography.label-md}"
  badge-success:
    backgroundColor: "{colors.success-container}"
    textColor: "{colors.on-success-container}"
    typography: "{typography.label-sm}"
    rounded: "{rounded.full}"
    padding: 8px
  badge-warning:
    backgroundColor: "{colors.warning-container}"
    textColor: "{colors.on-warning-container}"
    typography: "{typography.label-sm}"
    rounded: "{rounded.full}"
    padding: 8px
  badge-danger:
    backgroundColor: "{colors.error-container}"
    textColor: "{colors.on-error-container}"
    typography: "{typography.label-sm}"
    rounded: "{rounded.full}"
    padding: 8px
  badge-info:
    backgroundColor: "{colors.info-container}"
    textColor: "{colors.on-info-container}"
    typography: "{typography.label-sm}"
    rounded: "{rounded.full}"
    padding: 8px
  badge-neutral:
    backgroundColor: "{colors.neutral-container}"
    textColor: "{colors.on-neutral-container}"
    typography: "{typography.label-sm}"
    rounded: "{rounded.full}"
    padding: 8px
  table-header:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface-muted}"
    typography: "{typography.label-sm}"
    height: 44px
  table-row:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    typography: "{typography.data-tabular}"
    height: 52px
---

# SymbioNusa

## Overview

SymbioNusa is an operational web application, not a marketing site. Four roles (Admin Platform / DLH regulator, Seller, Buyer, Inspector) clear work queues, negotiate and supervise B2B trades of non-hazardous industrial residue (kain perca, ampas tahu, serbuk kayu, cacahan plastik). The UI must feel **credible, calm and precise**: a regulator-grade tool that is approachable rather than bureaucratic. Numbers (tonnage in kg/ton, Rupiah, CO2e) and statuses are the product and must be scannable at a glance. Every screen answers "what needs my action" before "what happened".

All interface copy is **Bahasa Indonesia**. Formats: `Rp 3.000.000` (dot thousands separator, no decimals), `5.000 kg`, `128,40 ton` (comma decimal), dates `27 Okt 2026, 14:00` in WIB. Layouts must work from 360 px wide.

## Colors

A single green signals action and "verified / safe"; everything else is quiet zinc neutrals, so status colours stand out.

- **Primary (#15803d):** Forest green for the one primary action per view, active navigation and the logo mark. White text on it passes WCAG AA (5.0:1).
- **Primary container (#dcfce7):** Pale mint behind the active sidebar item and success badges.
- **Secondary (#14532d):** Deep forest green for inverted surfaces only: an optional dark sidebar, the logo tile, a dark "inverted" button. Never a second accent on light surfaces.
- **On a dark (secondary) sidebar:** item text and icons white (**on-secondary**, 9.1:1); group labels and helper text **on-secondary-muted (#bbf7d0)** (7.5:1), never a zinc grey (zinc-400 is only 3.6:1 there); hover background **secondary-hover (#166534)**; the active item keeps the mint **primary-container** with **on-primary-container** text.
- **Tertiary (#1e40af):** Deep blue, used sparingly for informational accents: "Dalam Pengiriman" / "Inspeksi" states and the second data series in charts (e.g. target vs. realisation).
- **Neutral (#71717a):** Pure zinc grey (no green or warm tint) that all greys derive from.
- **Background (#f4f4f5):** Light zinc page background; content sits on **Surface (#ffffff)** cards.
- The palette is closed: green, zinc, blue, plus the amber and red status pairs below. No pink, raspberry, purple, teal or gradients.
- **On-surface (#18181b):** Near-black for headings and body. **On-surface-muted (#52525b)** for helper text, captions and table headers.
- **Outline (#e4e4e7):** Hairline borders of cards, tables and dividers. **Outline-input (#71717a)** for form control borders (3:1 non-text contrast).
- **Status pairs** (container + text): success green, warning amber (#fef3c7 / #92400e), danger red (#fee2e2 / #991b1b), info blue (#dbeafe / #1e40af), neutral zinc (#e4e4e7 / #3f3f46). Red (#b91c1c) is reserved for destructive actions and errors.
- **Warning (#b45309):** amber as a foreground colour only, for "stuck / needs attention" marks: the stuck stage in the order-flow band, a warning icon, an SLA bar close to its limit (5.0:1 on white). Never a button fill or a large background.
- **Focus ring (#15803d):** the visible keyboard focus outline on every interactive element. Use white on the dark sidebar.

## Typography

**Inter** throughout, with tabular figures for every number in tables, KPI cards and money.

- **Page title (headline-lg):** 26 px semibold, one per page, with a one-line muted subtitle below it.
- **Card and section titles (headline-md):** 18 px semibold.
- **KPI values (display-metric):** 28 px bold, tabular, always on one line. Use compact units in KPI cards ("Rp 214,6 jt", "96,3 t CO2e", "128,4 ton") and show the exact value in a tooltip or the line below.
- **Body and table cells (body-md / data-tabular):** 14 px; long reading text 16 px.
- **Sidebar group labels (label-caps):** 12 px semibold uppercase with 0.06em tracking, muted colour (UTAMA, MASTER DATA, TRANSAKSI…).
- Never go below 12 px, including chart axes, legends, KPI captions and chips.

## Layout

App shell: fixed **left sidebar (240 px)** on white with logo block (green "S" square + "SymbioNusa" + role subtitle), grouped navigation, and a user card pinned at the bottom (avatar initials, company/role name, "Keluar"). A **top bar (64 px)** holds breadcrumbs on the left and "Notifikasi" with a red count badge plus the avatar on the right. Content area on the zinc background with **32 px page margins**.

Page pattern: page title + subtitle + primary action button top-right → optional row of 4 KPI cards → filter bar (search input, select filters, "Reset") → white card containing a data table → pagination footer "Menampilkan 1–6 dari 18 …" with numbered page buttons. Detail pages use a two-column split: main content card (≈ 2/3) and an actions card on the right (≈ 1/3). A 4 px spacing base; 16 px gutters; 24 px card padding.

Responsive: below 1024 px the sidebar collapses to a drawer opened from a menu button in the top bar; KPI cards wrap to 2 columns then 1; tables scroll horizontally inside their card with the first column sticky; the action card moves below the main card.

## Elevation & Depth

Flat and tonal. Hierarchy comes from white cards on the zinc background with 1 px #e4e4e7 borders, not from shadows. Shadows only for overlays: dropdowns and popovers (small), modal dialogs (medium, with a dark scrim). No glassmorphism, gradients or decorative blur.

## Shapes

Softly squared: 6 px radius on buttons, inputs and nav items; 8 px on cards and tables; 12 px on dialogs; fully rounded pills for status badges and count badges. Do not mix sharp and pill shapes on the same control type.

## Components

- **Buttons:** primary (filled green, white text), secondary (white with 1 px outline-input border), danger (white with red border and red text, e.g. "Tolak PO"), text links in green for row actions ("Lihat", "Buat PO") and red for destructive row actions ("Tarik", "Batalkan"). Icon-only buttons need a visible tooltip. Labels are verbs: "Konfirmasi PO", "Terbitkan Invoice", "Ajukan RFQ".
- **Status badges:** pill with container colour + dark text of the same hue; always text, never colour alone. Mapping: Terbuka / Menunggu … / Draf / Segera Berakhir → warning; Disepakati / Dikonfirmasi / Lolos / Aktif / Selesai / Dibayar → success; Ditolak / Gagal / Sengketa / Tidak lolos → danger; Dalam Pengiriman / Inspeksi → info; Kedaluwarsa / Ditarik / Nonaktif / Dibatalkan → neutral.
- **Order stepper:** horizontal steps with numbered circles; completed = filled green with check, current = green outline + bold label, upcoming = grey; terminal branches (Ditolak, Dibatalkan) explained in a muted line below.
- **KPI card:** muted label on top, large tabular value, muted source/description line below.
- **Data table:** muted 12 px header row, rows at least 52 px tall (a row grows when its text or chips wrap; never clip content), hairlines between rows, numbers right-aligned, actions in the last column, empty state with explanation and a primary action.
- **Forms:** label above every field (never placeholder-only), helper text below in muted 13 px, error text in red below the field, required fields marked, read-only auto-filled values shown as plain text blocks; form actions bottom-right: "Simpan Draf" (secondary) + main action (primary).
- **Dialogs:** destructive confirmations name the object ("Void invoice INV-2026-0012?"), explain consequences, require a reason when the proposal says so, and put the destructive button last.
- **Split views:** list or tree on the left, editor on the right (categories, document verification, DLH findings).
- **States:** every interactive element shows a visible focus ring (2 px **focus-ring**, 2 px offset; white on the dark sidebar). Disabled controls are 50% opacity with a not-allowed cursor and stay readable; explain why an action is disabled when it is not obvious. Hover darkens fills (primary → primary-hover) or tints outlined controls (secondary button → page background, danger button → error-container).
- **Icons:** outline style, 1.5–2 px stroke, 20 px (16 px inside dense tables and badges), drawn in currentColor so they take the text colour. One icon family across the app; icon-only buttons need a visible tooltip.

## Do's and Don'ts

- Do keep exactly one filled-green primary action per view region.
- Do show every status as a labelled badge; never colour alone.
- Do use tabular numbers and Indonesian formats (Rp 3.000.000, 5.000 kg, 27 Okt 2026).
- Do put muted text (#52525b) on white or zinc; don't use #71717a for small text on the #f4f4f5 page background (it falls just under 4.5:1).
- Do keep sidebar menu names exactly as specified per role.
- Do keep the page header simple: title, one-line subtitle, at most one filter (period) and one primary action. No decorative chips beside the title.
- Do make card rows wrap to the next line; nothing may overflow or be cut off horizontally at any width from 360 px to 1440 px.
- Do use one priority style per list (e.g. a small priority badge + SLA progress bar), not several competing colour treatments.
- Do give chart series meaningful contrast: every bar, line and legend swatch at least 3:1 against the card background.
- Do design empty, loading (skeleton rows) and error states for every table.
- Don't use gradients, glassmorphism, emoji icons, stock illustrations or marketing-style heroes inside the app.
- Don't hide primary actions in menus or behind hover only; touch targets at least 24 px (44 px on mobile).
- Don't present demo figures as real impact; label them "data demo" where relevant.
