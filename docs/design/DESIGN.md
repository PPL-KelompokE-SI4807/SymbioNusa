---
version: beta
name: SymbioNusa
description: Regulator-supervised B2B platform for non-hazardous (non-B3) industrial production residue in Indonesia. Calm, credible operational UI in Bahasa Indonesia. FND v12 "Kaca" (25 Nov 2026) — glass cards over blurred real material photos, on the Senja di Sawah palette, is the design system for every screen (dashboards, forms, tables, detail pages, auth). Heavy-Clean rules (Ledger, Text budget, Data honesty, Interaction integrity) are unchanged; Elevation, Shapes and Motion are replaced by the Kaca rules. Only the Admin dashboard is built in Kaca so far; every other screen is rebuilt to it. Practical recipes are in PANDUAN_KACA.md.
colors:
  primary: "#3f6212"
  primary-hover: "#2f3d12"
  on-primary: "#ffffff"
  primary-container: "#fef3c7"
  on-primary-container: "#1c2a0a"
  secondary: "#1e2a08"
  secondary-hover: "#2f3d12"
  on-secondary: "#fef9c3"
  on-secondary-muted: "#fef9c3"
  secondary-label: "#e3d99a"
  tertiary: "#1e40af"
  on-tertiary: "#ffffff"
  neutral: "#4a4a1a"
  background: "#fdf8d8"
  surface: "#ffffff"
  surface-muted: "#fef3c7"
  on-surface: "#1c2a0a"
  on-surface-muted: "#4a4a1a"
  on-surface-subtle: "#4a4a1a"
  outline: "#e3d99a"
  outline-control: "#e3d99a"
  outline-input: "#8a8350"
  focus-ring: "#3f6212"
  error: "#b91c1c"
  error-container: "#fdf2f2"
  error-line: "#e3d99a"
  on-error-container: "#1c2a0a"
  warning: "#92400e"
  warning-container: "#fef3c7"
  warning-line: "#e3d99a"
  on-warning-container: "#1c2a0a"
  success: "#3f6212"
  success-container: "#fef3c7"
  success-line: "#e3d99a"
  on-success-container: "#1c2a0a"
  info: "#1e40af"
  info-container: "#fef3c7"
  info-line: "#e3d99a"
  on-info-container: "#1c2a0a"
  neutral-container: "#e4e4e7"
  on-neutral-container: "#3f3f46"
  band-forest-from: "#1e2a08"
  band-forest-to: "#2f3d12"
  on-band: "#ffffff"
  on-band-muted: "#fef9c3"
  insight-marker: "#92400e"
  age-bar-track: "#f6efc6"
  age-bar-fresh: "#ddd08a"
  age-bar-aging: "#fbbf24"
  age-bar-stale: "#b91c1c"
  chart-lead: "#3f6212"
  category-plastik: "#d97706"
  category-tekstil: "#ea580c"
  category-kayu: "#a16207"
  category-kertas: "#57534e"
  category-pangan: "#65a30d"
  photo-scrim: "rgba(9,26,18,0.68)"
  photo-label: "#1e2a08"
  on-photo-label: "#ffffff"
  mono-green: "#fef3c7"
  on-mono-green: "#1c2a0a"
  mono-clay: "#fef3c7"
  on-mono-clay: "#1c2a0a"
  mono-sky: "#fef3c7"
  on-mono-sky: "#1c2a0a"
  mono-sun: "#fef3c7"
  on-mono-sun: "#1c2a0a"
  mono-zinc: "#fef3c7"
  on-mono-zinc: "#1c2a0a"
  outline-hairline: "#f6efc6"
  flow-segment: "#f7f0c2"
  flow-segment-line: "#ddd08a"
  accent: "#c2410c"
  on-accent: "#ffffff"
  category-lainnya: "#ddd08a"
  sidebar-base: "#1e2a08"
  sidebar-overlay: "linear-gradient(168deg, rgba(71,88,28,.94), rgba(30,42,8,.98) 72%)"
  sidebar-text: "#fef9c3"
  sidebar-count-badge: "#d97706"
  # FND v12 Kaca: values inside .dash-kaca (resources/css/dashboard-kaca.css). Outside .dash-kaca the Senja values above apply.
  ink: "#1c2a0a"
  meta-on-glass: "#33330f"
  success-text-on-glass: "#2f3d12"
  accent-text-on-glass: "#7c2d12"
  danger-text-on-glass: "#7f1d1d"
  accent-text: "#9a3412"
  line-on-glass: "rgba(63,98,18,0.14)"
  line-strong-on-glass: "rgba(63,98,18,0.28)"
  field-on-glass: "rgba(255,255,255,0.55)"
  primary-soft: "rgba(63,98,18,0.10)"
  accent-soft: "rgba(194,65,12,0.12)"
  track: "rgba(63,98,18,0.10)"
  panel-strong: "rgba(255,253,242,0.84)"
  scrim: "rgba(28,42,10,0.28)"
  hatch: "rgba(63,98,18,0.35)"
  series-done: "#3f6212"
  series-run: "#65a30d"
  series-wait: "#d97706"
  series-plan: "#ddd08a"
  series-fail: "#b91c1c"
glass:
  hook: ".dash-kaca on the page content wrapper + .kaca-backdrop (4 blurred material photos) as its first child"
  backdrop-photos: "kain.jpg, ampas-tahu.jpg, serbuk-kayu.jpg, plastik-2.jpg — fixed size and position (PANDUAN_KACA.md §5); blur 38px, saturate 165%, drift 29–47 s"
  veil: "linear-gradient(180deg, rgba(253,248,216,.52), rgba(253,248,216,.22) 45%, rgba(253,248,216,.42))"
  grain: "SVG fractal noise, opacity .16, multiply"
  card: "linear-gradient(160deg, rgba(255,253,242,.66), rgba(255,253,242,.36) 60%, rgba(255,253,242,.50)); backdrop-filter blur(22px) saturate(150%); border 1px rgba(255,255,255,.28); inset 0 1px 0 rgba(255,255,255,.85); lit edge 140deg"
  card-shadow: "0 18px 40px -22px rgba(30,42,8,.45), 0 2px 6px -2px rgba(30,42,8,.12)"
  row: ".glass-row + field-on-glass: blur(10px) saturate(140%), lit edge, radius 16px"
  strong: "linear-gradient(165deg, rgba(255,253,242,.94), rgba(255,253,242,.86)); blur(40px) saturate(170%) — drawer, dialog, command palette, tooltip"
  sidebar: "linear-gradient(168deg, rgba(71,88,28,.84), rgba(30,42,8,.92) 72%); blur(24px) saturate(150%)"
  impact-band: "linear-gradient(135deg, rgba(30,42,8,.95), rgba(47,61,18,.90)); blur(26px) saturate(160%)"
  alpha-floor: "card alpha .66/.36/.50 and veil .52/.22/.42 are a legibility floor; never lower them, never change the photos without re-measuring contrast"
  fallback: "no backdrop-filter support or forced-colors: active → plain white Senja card, solid sidebar, backdrop hidden"
  reduced-transparency: "not honoured (PM decision 25 Nov 2026)"
typography:
  title-xl:
    fontFamily: Figtree
    fontSize: "clamp(26px, 3vw, 36px)"
    fontWeight: 900
    letterSpacing: "-0.025em"
    lineHeight: 1.1
    usage: "Kaca page title written by hand (class .title-xl), e.g. the Admin dashboard header. Pages using x-ui.page-header keep page-title."
  glass-card-title:
    fontFamily: Figtree
    fontSize: 18px
    fontWeight: 800
    lineHeight: 1.25
    usage: "Card titles on glass (class .card-title)."
  mono:
    fontFamily: "ui-monospace, Cascadia Mono, Segoe UI Mono, Consolas, monospace"
    usage: "IDs (PO-1041, DSP-0003), keyboard hints (Ctrl K, Esc) and hour labels in lists (class .mono). Never for amounts."
  page-title:
    fontFamily: Figtree
    fontSize: "clamp(25px, 3.2vw, 34px)"
    fontWeight: 900
    letterSpacing: "-0.02em"
    lineHeight: 1.15
    usage: "H1 of every application page (x-ui.page-header, class .page-title). Names the context, never a greeting."
  card-title:
    fontFamily: Figtree
    fontSize: 19px
    fontWeight: 800
    lineHeight: 1.25
    usage: "Card and zone titles (text-dash-title)."
  subtitle:
    fontFamily: Figtree
    fontSize: 16px
    fontWeight: 800
    lineHeight: 1.3
    usage: "Detail-panel titles, empty-state titles (text-dash-sub)."
  row-title:
    fontFamily: Figtree
    fontSize: 15px
    fontWeight: 700
    lineHeight: 1.3
    usage: "Queue, ledger and table row titles (text-dash-row)."
  dash-metric:
    fontFamily: Figtree
    fontSize: 32px
    fontWeight: 900
    lineHeight: 1.05
    fontFeature: '"tnum" 1'
    usage: "Summary-strip values, order-flow counts, date-block day numbers (text-dash-metric)."
  eyebrow:
    fontFamily: Figtree
    fontSize: 12px
    fontWeight: 800
    letterSpacing: "0.13em"
    textTransform: uppercase
    usage: "Dashboard zone eyebrows and sidebar group labels only."
  display-public:
    fontFamily: Figtree
    fontSize: "clamp(46px, 6vw, 92px)"
    fontWeight: 600
    lineHeight: 1.02
    usage: "Public surfaces only (landing, auth). Never in the application."
  display-band:
    fontFamily: Figtree
    fontSize: 40px
    fontWeight: 700
    lineHeight: 1.1
    fontFeature: '"tnum" 1'
    usage: "Impact-band metrics and the auth photo-panel statement only."
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
    fontWeight: 700
    lineHeight: 1.1
    fontFeature: '"tnum" 1'
  metric:
    fontFamily: Figtree
    fontSize: "28-32px"
    fontWeight: 700
    lineHeight: 1.1
    fontFeature: '"tnum" 1'
    usage: "Summary-strip values, order-flow stage counts and date-block day numbers. The only places weight 700 appears inside the application."
  list-title:
    fontFamily: Figtree
    fontSize: 15px
    fontWeight: 600
    lineHeight: 1.3
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
  sm: 9px
  md: 8px
  lg: 14px
  photo-label: 6px
  count-badge: 5px
  sidebar: 24px
  full: 9999px
  glass-card: 22px
  glass-topbar-control: 14px
  glass-row: 16px
  glass-control: "11-12px"
  glass-segment-button: "9-11px (the thumb radius --r equals it)"
  glass-tag: 6px
  glass-sheet: "24px (drawer, impact band); 26px on the mobile bottom sheet"
shadow:
  card: "0 0 0 1px {colors.outline}, 0 1px 3px rgba(62,52,10,0.04)"
  raised: "0 0 0 1px {colors.outline}, 0 18px 36px -18px rgba(62,52,10,0.16)"
  overlay: "0 16px 40px -12px rgba(30,42,8,0.24), 0 2px 6px rgba(62,52,10,0.08)"
  sidebar: "0 16px 32px -20px rgba(30,42,8,0.28)"
  glass-card: "0 18px 40px -22px rgba(30,42,8,0.45), 0 2px 6px -2px rgba(30,42,8,0.12), inset 0 1px 0 rgba(255,255,255,0.85)"
  glass-strong: "0 30px 60px -28px rgba(20,30,5,0.55), inset 0 1px 0 rgba(255,255,255,0.85)"
  glass-button: "0 6px 14px -8px rgba(30,42,8,0.8) — filled green buttons and the segment thumb on glass"
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
  card-padding-mobile: 18px
  topbar-height-mobile: 64px
  control-height: 40px
  control-height-lg: 44px
  table-row-height: 56px
  touch-target-min: 44px
motion:
  kaca-spring: "stiffness 420, damping 38, mass 0.9 (SymbioNusa Lab) — simulated by springCurve() in resources/js/dashboard/kaca.js with the same semi-implicit Euler integration as resources/js/motion/spring.js (240 Hz), emitted as a CSS linear() curve in --kaca-spring with its duration in --kaca-spring-ms; used by CSS transitions (.kaca-spring, segment thumb) and Web Animations (flip(), exit(), kacaRiver)"
  flip: "flip(container, items, mutate, nextTick): measure, mutate, invert, play on the Kaca spring; new items fade in with a 16px rise, container height animates"
  exit: "exit(els): 180ms, translate 40px + scale .96 + fade, cubic-bezier(.4,0,1,1), with a timeout guard"
  entrance: "[data-rise] 0.6s rise + fade, staggered 60ms by --i; tiles/dots pop by --d"
  spring-gentle: "stiffness 112, damping 26, mass 1 — Senja screens not yet rebuilt"
  spring-snappy: "stiffness 420, damping 34, mass 1 — press feedback (bindPress) and Senja screens not yet rebuilt"
  spring-soft-bounce: "stiffness 280, damping 19, mass 1 — success feedback only (drawn check)"
  hover-duration: 150-200ms
  hover-easing: "cubic-bezier(.22,1,.36,1) (--ease-hover) — colour, background, shadow, glass sheen; never layout"
  highlight: "±600ms soft primary-tinted fade on a changed panel or a linked row"
  reduced-motion: "Lab policy (MotionConfig reducedMotion='user'): position, scale and looping motion stop; fades (200ms), colour, bar widths and the donut draw keep animating; numbers jump to their target"
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
    note: "Stays opaque white on glass (never field-on-glass or transparent): the 3.86:1 input border and the typed text must hold on any backdrop."
    backgroundColor: "{colors.surface}"
    textColor: "{colors.on-surface}"
    borderColor: "{colors.outline-input}"
    typography: "{typography.body-md}"
    rounded: "{rounded.sm}"
    padding: 14px
    height: 44px
  card:
    background: "{glass.card}"
    textColor: "{colors.ink}"
    rounded: "{rounded.glass-card}"
    shadow: "{shadow.glass-card}"
    padding: "16px (mobile) / 20px (desktop)"
    hook: "x-ui.card (data-card) or section.glass inside .dash-kaca; outside .dash-kaca x-ui.card is the white Senja card"
  glass-row:
    background: "{colors.field-on-glass} + {glass.row}"
    borderColor: "{colors.line-on-glass}"
    rounded: "{rounded.glass-row}"
    structure: "Stand-alone decision rows (queue). Focused/selected: border accent, background panel-strong, 3px accent-soft ring."
  glass-strong:
    background: "{glass.strong}"
    shadow: "{shadow.glass-strong}"
    structure: "Drawer, dialog, command palette, chart tooltip. Never a thin glass card for an overlay."
  summary-strip:
    background: "{glass.card}"
    textColor: "{colors.ink}"
    rounded: "{rounded.glass-card}"
    padding: "16px 14-20px per metric"
    structure: "One glass surface; metrics separated by {colors.line-on-glass} hairlines (a cross in the 2×2 mobile layout); each metric = label 13px meta + value 26-30px/900 + one comparator line (arrow + percent + period). Two thin bars (current in primary, previous in series-plan) may sit under the comparator. No per-metric field, tint or icon. Sample comparisons carry the Data demo tag."
  order-flow:
    structure: "Connected proportional segments, 10 STATUS_MAP stages in 3 groups; segment width proportional to order count with a minimum width; count 28-32px/600 inside the segment; one-line official-abbreviation label below or inside, never ellipsis-truncated, may overflow the segment width; chevron cue between segments; selected segment filled {colors.primary} with {colors.on-primary} text"
    detail-panel: "Attached directly below the flow, same card; header = order count + total value only; one line per order; age shown as age-bar"
  age-bar:
    height: 6px
    rounded: "{rounded.full}"
    trackColor: "{colors.age-bar-track}"
    fillScale: "length proportional to days in stage; fill {colors.age-bar-fresh} → {colors.age-bar-aging} → {colors.age-bar-stale} by the stage age thresholds in Data honesty"
    label: "small day count (12px, meta tone) at the end of the bar; never the only carrier — the number is always printed"
  date-block:
    structure: "Day number 28-32px/600 dominant, short month + year in 12-13px meta beside or below it; no background field; used for deadlines, expiries and arrivals"
  timeline:
    structure: "Vertical rail with hour labels (09.00, 13.00) in tabular figures; entries on the rail in chronological order; the next task is the most prominent entry and carries the role's primary action"
  queue-group:
    structure: "Group title (15-18px/600) including the true count, one reason line per group, at most 3 rows visible, expansion control 'Lihat N lainnya' for the rest; every row carries the same action element"
  credential-row:
    structure: "Ledger row (name, issuer, status as coloured text) + date-block for the expiry date; warning state ≤ 30 days in {colors.warning} text, expired in {colors.error} text"
  material-composition-row:
    structure: "Ledger row: 48-60px material photo, category name, thin category-coloured bar, label with percent of total + tonnage + value"
  empty-state:
    background: "transparent on glass, 1px dashed {colors.line-strong-on-glass} border, radius 16px, 48px vertical padding"
    titleTypography: "16px / 700 ink"
    bodyTypography: "14px meta"
    structure: "title + one line of cause + primary action when the viewer can create the content; a filter that empties a list says so ('Tidak ada di kategori ini') and how to go back"
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
  status-text:
    typography: "{typography.label-md}"
    structure: "Semantic coloured text ({colors.success} / {colors.warning} / {colors.info} / {colors.error} / {colors.on-surface-subtle}) with an optional 16px glyph; no pill background, no tint field"
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
    backgroundColor: "transparent — rows sit directly on the glass card"
    dividerColor: "{colors.line-on-glass}"
    hoverColor: "{colors.primary-soft}"
    selectedColor: "{colors.panel-strong}"
    textColor: "{colors.ink}"
    typography: "{typography.data-tabular}"
    height: 56px
  segmented-control:
    track: "glass (top bar) or 1px {colors.line-strong-on-glass} border (inside a card)"
    thumbColor: "{colors.primary}"
    activeTextColor: "{colors.on-primary}"
    rounded: "track 11-14px, buttons and thumb 9-11px (--r)"
    height: "36-44px"
    structure: "Squared track; a filled green thumb (.kaca-thumb) slides under the aria-pressed button on the Kaca spring (x-kaca-seg); options are text labels with optional true counts (.kaca-count); never a capsule"
  photo-label:
    backgroundColor: "{colors.photo-label}"
    textColor: "{colors.on-photo-label}"
    typography: "{typography.label-md}"
    fontWeight: 600
    rounded: 6px
    structure: "Solid block at the photo's bottom edge, on the photo scrim; never a pill, never a translucent chip"
  toast:
    backgroundColor: "{colors.secondary}"
    textColor: "{colors.on-secondary}"
    rounded: "{rounded.sm}"
    shadow: "{shadow.overlay}"
    structure: "One short confirmation line, auto-dismiss; never the only feedback for a completed action"
---

# SymbioNusa

> **FND v12 "Kaca" — 25 Nov 2026 (PM decision, Mayhesa). This block and the sections it names are the current design system.** The PM chose the *Kaca* variant of the SymbioNusa Lab experiment (`D:\PPL\SymbioNusa-Lab`, 23 Nov 2026) and made it the standard for **the whole application**: every dashboard, form, table, detail page and the auth pages. Kaca changes the *material* of the surface, not the product rules: content sits on glass cards over four blurred real material photos, on the unchanged Senja di Sawah palette. The practical recipe book with copy-ready snippets is `docs/design/PANDUAN_KACA.md`; the source of truth for values is the shipped Admin dashboard (commit `b8a3e24`).
>
> - **Implementation status (honest).** Only the Admin dashboard is built in Kaca (`.dash-kaca` in `dashboard-overview.blade.php` for ADMIN). The sidebar turns glass only while a page carries `.dash-kaca`. The Seller, Buyer and Inspector dashboards, every form, table and detail page, and the auth pages are still Senja (white cards) and are **rebuilt to this system screen by screen**. Until a screen is rebuilt, the Senja rules it was built with remain valid for it; new and rebuilt screens follow FND v12.
> - **What FND v12 replaces.** Elevation & Depth ("No glassmorphism … or decorative blur" is withdrawn), Shapes (glass radii), Motion (the Kaca spring, FLIP and the Lab reduced-motion policy), the "two gradients only" rule (the glass gradients below are added to the list), the "no decorative or looping motion" rule (four looping exceptions, listed in Motion) and the Senja white-card specification. The sections below are rewritten accordingly.
> - **What stays.** The closed Senja palette (no new hues), locked light palette (`color-scheme: light`, no dark overrides — the Lab *Malam* variant is not ported), Figtree, Ledger (no pills in the content area, status as coloured text + glyph, colour as data ink), Text budget, Data honesty, Interaction integrity and the Action feedback loop, Accessibility, 12 px minimum text, Bahasa Indonesia copy, 360 px minimum width.
> - **Values.** Glass = Lab values: card gradient alpha 0.66 → 0.36 → 0.50 (160°), blur 22 px, saturate 150%, 1 px white 28% edge plus a lit edge, radius 22 px; cream veil 0.52 / 0.22 / 0.42 (top thicker than the Lab's 0.38 for the page header) plus the Lab grain; thick glass (`.glass-strong`) 0.94 → 0.86 with blur 40 px for overlays. Text on glass is darker than Senja so it holds on the darkest photo: meta #33330f, accent text #7c2d12, danger text #7f1d1d, green text #2f3d12. All values are in the front matter (`colors` Kaca block, `glass`, `rounded`, `shadow`, `motion`) and in code once, in `resources/css/dashboard-kaca.css`.
> - **Hook.** `.dash-kaca` on the page content wrapper with `.kaca-backdrop` (four `.kaca-blob` photos) as its first child; cards are `x-ui.card` (`data-card`) or `.glass`; the sidebar turns glass through `body:has(.dash-kaca)`. Every Kaca class works only inside `.dash-kaca`.
> - **Readability is not negotiable.** Text pairs on glass were measured on the rendered Admin dashboard (25 Nov 2026, 1440 and 504 px, several scroll positions; table in `prompts/02_ATURAN_UI_SYMBIONUSA.md`). The card and veil alphas are a floor: never lower them, and never swap the backdrop photos without re-measuring. Controls (buttons, inputs, selects, textareas) stay opaque white or filled green. Text outside cards is ink only.
> - **Fallbacks.** Without `backdrop-filter`, or under forced colours (Windows high contrast), glass becomes the plain white Senja card, the sidebar becomes solid and the photos are hidden. `prefers-reduced-transparency` is deliberately **not** honoured (PM decision 25 Nov 2026: with Windows "Transparency effects" off the variant looked flat on the PM's machine); legibility is guaranteed by the alpha floor instead. Revisit if a user reports legibility problems.
> - **Admin dashboard composition (PM decision 25 Nov 2026: "persis seperti Lab").** The Admin dashboard uses the Lab layout instead of the Three questions zones: summary strip (period comparison), "Perlu keputusan Anda" queue with decision drawer, inspections per hour, Sungai Order, supervision map, invoice aging, activity, Inspector load, tonnage per category, impact band, command palette (Ctrl K). Page title "Dashboard". `App\Services\Dashboard\AdminKaca` builds the data. The Seller, Buyer and Inspector dashboards keep the Three questions structure (Layout) in Kaca material until the PM approves a Lab-style composition for them.
> - **Data honesty on the Admin dashboard.** Real from the database: queue, Sungai Order (counts; ages from status history), inspections per hour, invoice aging (from due date; the DDL has no issue date), activity (status history), Inspector load (completed vs assigned; the DDL has no capacity), tonnage and impact. **Sample data from the Lab, tagged "Data demo" in the card:** the period comparison in the summary strip and the supervision map (the DDL has no order creation date, company city or DLH findings). No decision is taken from a dashboard: the drawer shows detail and links to the decision page where one exists; "Unduh rekap", reminders and "Buka di Pengawasan DLH" stay disabled with a stated reason until their pages exist.
> - **Not ported from the Lab.** The *Malam* variant (a dark palette is forbidden), the variant switcher and compare overlay (Lab tooling), inline approve/reject/undo and live activity updates (nothing persists them).
> - **History.** The Admin-only FND v12 amendment of 25 Nov 2026 (same day, earlier) is folded into this block. FND v11 and the older amendments below are kept as history; where they disagree with this block, the front matter or the rewritten sections, those win.

> **Amendment 22 Nov 2026 (FND v11 — "Senja di Sawah" becomes the design system, PM decision).** *(History, partly superseded by FND v12 above: the Senja palette and type stay; the white-card material, shapes and motion are replaced by Kaca.)* The PM stated that the Senja di Sawah dashboard is the UI/UX SymbioNusa keeps ("pokoknya saya udah suka sama ui/ux yang dashboard ini"). From this amendment on, the Senja di Sawah dashboard is the **visual reference for every screen** — dashboards, queues, tables, forms, detail pages, auth and the landing page — not only for `/dashboard`.
>
> - **Values.** The front matter now holds the Senja values (palette `senjasawah`): background #fdf8d8, surface #ffffff, surface2 #fef3c7, ink #1c2a0a, meta #4a4a1a (the one secondary tone), line #e3d99a, line-2 (outline-hairline) #f6efc6, forest #2f3d12 / forest2 #47581c / forestDeep #1e2a08, onForest #fef9c3, primary = success #3f6212, accent #c2410c (small accents only: the selected segment, the SEKARANG marker, the logo mark — never a second button or a large field), warning #92400e, danger #b91c1c; categories Plastik #d97706, Pangan #65a30d, Tekstil #ea580c, Kayu #a16207, Kertas #57534e. Input borders use #8a8350 (3.86:1 on white), because line #e3d99a (1.43:1) may not be the only boundary of an input. In code they live once, in the `@theme` blocks of `resources/css/app.css`, under the existing token names; `.dash-senja` no longer re-declares any token.
> - **Shapes.** Card and summary strip 14 px; button, input, select and segmented control 9 px; order-flow segment, small interactive row and alert 8 px; photo label 6 px; sidebar 24 px; sidebar count badge 5 px. Cards carry a 1 px line border (drawn as a ring) plus a very soft golden shadow; neutral rgba(16,24,40,…) shadows are retired.
> - **Type.** Figtree weights 400–900 are loaded. 900: page title (clamp 25–34 px) and metric values (32 px); 800: card and zone titles (19 px), subtitles (16 px), eyebrows; 700: row titles (15 px). The only uppercase tracked style is the 12 px / 800 eyebrow (dashboard zones, sidebar groups). Minimum text stays 12 px; the application maximum stays 40 px; display-public stays on public surfaces only.
> - **Sidebar.** The Senja sidebar (forestDeep with the 168° overlay, onForest text, inverted active item, square gold count badge, outlined user card) is the shell of every page; `resources/css/sidebar-senja.css`.
> - **Unchanged.** Every Heavy-Clean rule in this file: Ledger (no pills in the content area, status as coloured text + glyph, ledger rows on one white surface, colour as data ink), Text budget, Data honesty, Interaction integrity and the Action feedback loop, Motion (one spring solver, three presets), locked light palette, the summary strip instead of KPI tiles, two gradients only (sidebar overlay and photo scrim; the impact band uses the forest range #1e2a08 → #2f3d12).
> - **History.** The FND v10 Sage values are superseded and kept as history only (git history of this file; old → new: background #eef3ec → #fdf8d8, on-surface #16241b → #1c2a0a, meta #5f6d64 → #4a4a1a, outline #e2e8df → #e3d99a, primary #15803d → #3f6212, warning #b45309 → #92400e, sidebar #14532d → #1e2a08, categories Plastik #1e40af / Tekstil #b45309 / Kayu #92400e / Kertas #3f3f46 → #d97706 / #ea580c / #a16207 / #57534e, radii 10/14/20 → 9/8/14). Hex values quoted in the body text of the older amendments and sections below describe that history; where they disagree with the front matter or this block, this block and the front matter win.
> - **Contrast.** Recomputed from the Senja hex values with a WCAG 2.x script on 22 Nov 2026; the table is in `prompts/02_ATURAN_UI_SYMBIONUSA.md`.

> **Amendment 22 Nov 2026 — dashboard variant "Senja di Sawah" (PM decision).** The role dashboards adopt the PM-approved variant *SymbioNusa Dashboard — Varian Senja di Sawah* (palette `senjasawah` from the colour lab). **Scope: dashboard content only.** The content area of `/dashboard` is wrapped in `.dash-senja`, which re-declares the existing `--color-*` tokens with the Senja values (bg `#fdf8d8`, surface `#ffffff`, surface2 `#fef3c7`, ink `#1c2a0a`, meta `#4a4a1a`, line `#e3d99a`, forest `#2f3d12` / `#47581c` / `#1e2a08`, on-forest `#fef9c3`, primary/success `#3f6212`, accent `#c2410c` for small accents only, warning `#92400e`, danger `#b91c1c`; categories Plastik `#d97706`, Pangan `#65a30d`, Tekstil `#ea580c`, Kayu `#a16207`, Kertas `#57534e`; derived line-2 `#f6efc6`, seg-bg `#f7f0c2`, seg-line `#ddd08a`). Values live in `resources/css/dashboard-senja.css` (overrides) and the `@theme static` block in `resources/css/app.css` (dashboard-only tokens) — one place each, no second naming scheme. **Sidebar (all pages, PM decision 22 Nov 2026):** the shared sidebar and the mobile top bar take the variant style on every screen (forest-deep base with the 168° overlay `rgba(71,88,28,.94) → rgba(30,42,8,.98) 72%`, radius 24 px, accent logo mark, tagline "Sirkular industri Nusantara", tracked group eyebrows, square gold count badge, outlined user card), defined in `resources/css/sidebar-senja.css` through `data-sidebar*` hooks (the sidebar tokens are re-declared inside the sidebar only). **Unchanged (superseded by FND v11 above for the content area of non-dashboard screens):** all rules in this file (Ledger, Text budget, Data honesty, Interaction integrity, Motion with one spring solver and three presets, light-only palette, 40 px maximum in the app). Inside the dashboard, card radius is 14 px, buttons 9 px, flow segments 8 px; Figtree 800/900 are loaded for dashboard numbers and titles. **Data limits recorded with the decision:** `purchase_orders` and `impact_logs` have no date columns, so Zone 3 is titled "Gambaran total tercatat", comparators appear only where the data supports them, age bars appear only for orders with a dated `order_status_history` row, and the "Terlama · n hari" marker waits for PM-stated stage thresholds (schema request: `prompts/91_USULAN_PERUBAHAN_SKEMA.md`, Usulan 3). Rolled out per role with PM review after each, all four role dashboards (Admin, Seller, Buyer, Inspector) on 22 Nov 2026. Shared dashboard partials live in `resources/views/livewire/dashboard/partials/` (queue, running, metrics, stage-chart); role-specific visuals are the Admin order river and outcome bar, the Seller listing photo cards, the Buyer route tracker (position from manifest status, not GPS) and the Inspector hour timeline with one SEKARANG marker and a credential card. The full §7.1 action feedback loop waits for the backend actions (confirm PO, confirm receipt, assign Inspector, decide dispute, start inspection) to exist.

> **Amendment 21 Nov 2026 (FND v9).** PM (Mayhesa) approved this refinement of FND v8, following the dashboard audit of 21 Nov 2026 and the UI/UX audit of 19 Nov 2026. The identity is unchanged: Figtree, the same closed palette, radii 10/14/20, the floating forest sidebar, status as glyph + text, one filled action per region. What changes is discipline, not appearance: KPI tiles are flat instead of gradient; a display type tier is ratified for public surfaces only; a Data honesty section makes period labels, true denominators, true counts and honest inference labels mandatory; dashboards follow role-specific patterns instead of one generic pattern; a credential card, a freshness rule, a fixed material-category colour mapping, a motion budget and tightened accessibility rules are added. The FND v8 amendment of 19 Nov 2026 (FigCraft direction, colour fields, soft shapes, real material photos) remains the visual basis; older values are in git history.

> **FND v9 — revised 21 Nov 2026 (in place, PM decision).** This revision replaces the dashboard rules above with the final dashboard design approved by the PM after four review rounds on 21 Nov 2026. It does not rename the version and does not change the identity. Changes in this revision: (1) a Ledger content language for the content area — no capsule/pill shapes, no stacked tint fields, colour as data ink, two text tones only; (2) a Text budget ("show, don't narrate") — full sentences only in empty states and at most one insight line per section, no explanatory paragraph under any visualisation; (3) the dashboard structure is now the Three questions pattern for every role, replacing the per-role patterns; (4) the coloured KPI tile is replaced by the summary strip, and its per-tile tint tokens (tile-sun, tile-sky, tile-green, tile-plain and their on-tile tokens) are removed from the front matter; (5) new components with full specifications: order flow (proportional pipeline), age bar, date block, timeline, queue group, credential as ledger row + expiry date block, material composition as ledger rows; (6) a new Interaction integrity section — one list has one controller, no silent cross-filtering, explicit cross-component links only, visibly signalled state changes, one source of truth for every count, uniform row affordance. Where this revision and the original FND v9 text disagree, this revision wins.

> **Amendment 21 Nov 2026 (FND v10 — Heavy-Clean).** The PM approved the Heavy-Clean direction (preview artifact *SymbioNusa Dashboard — Versi Eksperimen*). Its one-line definition: *Heavy* means depth of motion and interactivity, *Clean* means a quiet surface — rich behaviour, silent surface, never the reverse. Heavy-Clean shares FND v9's structure (Three questions, Ledger, Text budget, Data honesty, Interaction integrity); this amendment keeps all of it and adds the Heavy layer: the action feedback loop, drill-down on every visual, interactive bar and donut charts, the Buyer route tracker, in-place expansion of timeline tasks, a spring motion system, photo-label rules and a locked light palette. Where the Heavy-Clean draft and FND v9 gave different values, the conflict was decided as follows:
>
> | Topic | FND v9 | Heavy-Clean draft | Decision in v10 | Why |
> |---|---|---|---|---|
> | Primary green | #15803d | #1a8f4a | **#15803d** | White on #1a8f4a is 4.14:1 and fails AA for 14 px button labels; #15803d is 5.02:1 |
> | Category colours | v9 set | lighter set (2.3–3.0:1) | **v9 set, Pangan changed to #65a30d** | v9 Pangan #166534 was 1.42:1 against the leading series; #65a30d is 3.09:1 on white and separable from both lead and Tekstil |
> | Font weights | 400/500/600 | 500–900 | **400/500/600 + 700 for metric values only** | Numbers must clearly outweigh labels; 700 does that with one extra font file instead of four |
> | Uppercase tracked labels | prohibited | zone eyebrows, `SEGERA` | **prohibited** | Zone titles already carry the question wording |
> | Motion | 150–200 ms ease-out, no animated numbers | spring solver, odometers, particles, FLIP | **spring solver with three presets; numbers move only when the user's own action changes them; no count-up on load; river particles optional and pausable** | Keeps the feedback loop readable without decorative motion (see Motion) |
> | Radius, sidebar, shadows | 10/14/20, 276 px, neutral shadows | 9/14/24, 268 px, green-tinted shadows | **v9** | The differences are 1–8 px and would break shipped components for no user benefit |
> | Status `info` (blue) | kept | removed | **kept** | STATUS_MAP uses it for in-progress stages |
> | Gradients | impact band + photo overlay | sidebar overlay + photo scrim | **impact band + overlays on photos (forest overlay and label scrim)** | The sidebar stays solid secondary |
> | Role switcher | none | header control for all users | **demo mode only** (`DEMO_LOGIN`) | `users.role` holds one role per account; switching roles in production would bypass authorisation |
> | Time format | mixed (`14:00` and `14.05`) | `14.05` | **dot: `14.05 WIB`** | Indonesian convention, and the freshness stamp already used it |
> | Dark mode | not stated | locked light | **locked light** | The approved appearance must not change with the device setting |
>
> Where this amendment and the amendment blocks above disagree, this amendment wins. Public surfaces, forms, tables, freshness, market rows, portfolio and loading rules are FND v9 and unchanged. An implementing agent needs only this file; no other document or history is required. The Heavy-Clean draft and the FND v9 implementation map are history, not rules.

## How to use this file

Read the rules as instructions, not as background. Every rule is stated as a permission, a prohibition, or a specification with numbers. The current system is FND v12 "Kaca" (block above); `docs/design/PANDUAN_KACA.md` turns it into copy-ready Blade/Alpine recipes with the real class and function names. Build the page from the components in Components, in the zone order in Layout, using only the tokens in the front matter. If a needed pattern is not specified here, compose it from the Ledger language, the Text budget, and Interaction integrity — do not import patterns from generic dashboard templates.

## Overview

SymbioNusa is an operational web application, not a marketing site. Four roles (Admin Platform / DLH regulator, Seller, Buyer, Inspector) clear work queues, negotiate and supervise B2B trades of non-hazardous industrial residue (kain perca, ampas tahu, serbuk kayu, cacahan plastik, kertas). The UI must feel **credible, calm and precise**. Numbers (tonnage, Rupiah, CO2e) and statuses are the product and must be scannable at a glance. Every dashboard answers, in order: what must I do, what is in progress, and what does the picture look like (see Layout, Three questions).

Two kinds of surface exist, and they do not share a scale. **Application surfaces** (dashboards, queues, tables, forms) use the type and colour rules in this file without exception. **Public surfaces** (the landing page and the auth pages) may additionally use the display type tier in Typography and large material photos; they never import application components that imply logged-in state, and every figure they show follows the same Data honesty rules as the application.

Credibility is a functional requirement, not a tone. A number without a period, a percentage without a denominator, a count that disagrees with the same count elsewhere on the page, or a control that looks actionable but leads nowhere is a defect of the same severity as a broken form. The Data honesty, Text budget and Interaction integrity sections are therefore normative, not advisory.

All interface copy is **Bahasa Indonesia**. Formats: `Rp 3.000.000` (dot thousands separator, no decimals), compact `Rp 24 jt` / `Rp 214,6 jt` (no trailing ",0"), `5.000 kg`, aggregate tonnage as `128,4 ton` (comma decimal, the word "ton" everywhere), dates `27 Okt 2026`, timestamps `27 Okt 2026, 14.00 WIB`, times with a dot (`09.00`, `13.00`). Numbers keep these formats on every frame of an animation, not only at rest. Layouts must work from 360 px wide.

## Colors

The palette is closed: the Senja di Sawah values in the front matter (FND v11), plus the Kaca text and translucent tokens (FND v12). No pink, raspberry, purple, teal, no new hue. Colour in the content area is data ink, not a background field (see Content language — Ledger). Kaca adds no colour: it adds translucency and the real photos behind the glass.

- **Ground.** On a Kaca screen the ground is the cream canvas `#fdf8d8` with four blurred material photos under a cream veil (front matter `glass`). Content never sits on the photos directly except the page header, which is therefore ink only.
- **Surfaces.** Glass cards (`glass.card`) for all content; thick glass (`glass.strong`) for drawers, dialogs, the command palette and tooltips; `field-on-glass` (white 55%) for stand-alone rows and sub-panels on a card; `panel-strong` (cream 84%) for the selected row, stage or tile. Opaque white `surface #ffffff` remains for controls (buttons, inputs, selects, textareas) and alerts.
- **Primary (#3f6212):** the one filled action per region, the segmented-control thumb, the leading series in charts, the "done" series. White on it is 7.08:1. As *text* inside `.dash-kaca`, `.text-primary` renders forest #2f3d12 (green text on glass); the fill stays #3f6212.
- **Accent (#c2410c):** small fills only — count badge, the "Sekarang" marker, the focus ring, the single "Lihat n order" insight button, the hatched stuck stage. Never a text colour on glass and never a second large button. Accent *text* is `text-accent-text` (#7c2d12 on glass, #9a3412 elsewhere).
- **Text — two tones in the content area:** ink #1c2a0a for everything the user must read and meta for labels, captions and secondary facts — #33330f on glass, #4a4a1a on Senja screens. No third grey; demote by size and weight. On the ground (outside cards) only ink.
- **Semantic text:** green #2f3d12, accent text #7c2d12 and danger #7f1d1d may sit on bare glass in short bold fragments (≥ 13 px / 700). Warning #92400e, info #1e40af and any fill colour used as text (`var(--danger)` #b91c1c, `var(--accent)`) only on `field-on-glass`, `panel-strong` or `glass-strong`. Status is always glyph + text.
- **Lines:** `line-on-glass` (green 14%) for hairlines and row dividers on glass, `line-strong-on-glass` (28%) for outlined buttons, tags and the dashed empty-state border. Inputs keep `outline-input #8a8350` (3.86:1 on white) because they stay white. Senja lines (#e3d99a, #f6efc6) apply only on screens not yet rebuilt.
- **Chart series:** done #3f6212, running #65a30d, waiting #d97706, planned #ddd08a (always hatched), failed #b91c1c. Each series is named in a legend; colour is never the only carrier.
- **Material-category colours (fixed mapping):** Plastik #d97706, Pangan #65a30d, Tekstil #ea580c, Kayu #a16207, Kertas #57534e, Lainnya #ddd08a, used through `var(--color-category-…)`. Never as text colour. Plastik, Tekstil and Kayu are one orange family: never adjacent without the category name.
- **Gradients** are allowed only here: the glass surfaces in the front matter `glass` block (card, row edge, strong glass, sidebar, veil), the impact band, and overlays on photos (forest overlay on auth photos, label scrim on in-app photos, drawer photo header). Never from one hue to another, never as decoration on a data element.
- **Light palette only.** `color-scheme: light` on `:root`; no dark palette and no `prefers-color-scheme: dark` overrides. A screenshot in device dark mode must look identical.
- **Shadows** are forest-tinted and soft (front matter `shadow`), never glows and never coloured by a semantic hue.
- **Photos carry the colour.** Real material photos (backdrop, thumbnails, composition rows, drawer header) are the colour-rich element. If a screen feels colourless, use a photo, not a tinted field.


## Typography

**Figtree** (self-hosted through the Vite font plugin), one family, weights 400–900 (FND v11). Tabular figures for every number. Monospace (`.mono`) only for IDs, keyboard hints and hour labels in lists.

- **Page title:** `x-ui.page-header` uses `.page-title` clamp(25–34 px) / 900; hand-built Kaca headers use `.title-xl` clamp(26–36 px) / 900. It names the context, never a greeting ("Dinas Lingkungan Hidup", "Kategori Material", "PO-1041"). The line under it states the screen, period and freshness ("per 25 Nov 2026, 14.05 WIB").
- **Card titles:** 18 px / 800 on glass (`.card-title`); 19 px / 800 on Senja screens (`text-dash-title`).
- **Metric values:** 26–32 px / 900, unit 14 px / 700 meta beside it. A value is always visibly heavier than its label.
- **Row titles:** 15 px / 700. **Body and cells:** 14 px. **Meta lines:** 13 px meta. **Small labels:** 12 px / 500–600 meta.
- **Hierarchy rule:** separate facts by size and weight, never by another grey.
- **Eyebrows:** the only uppercase tracked style is the 12 px / 800 eyebrow (sidebar groups, Senja zone eyebrows).
- **Display tier (public surfaces only):** display-public clamp(46–92 px) / 600 and display-band 40 px / 700 as in the front matter.
- Allowed sizes in the application: 12, 13, 14, 15, 16, 17, 18, 19, 21, 24, 26, 28, 30, 32, the two clamp titles and 40 (impact band). Never below 12 px; SVG text in charts 13 px minimum. No negative tracking under 22 px except `.mono` (−0.01em).


## Layout

App shell: a **floating forest sidebar (276 px, radius 24, 16 px from the viewport edges)** — glass (forest gradient, blur 24 px) on Kaca screens, solid Senja forest elsewhere: logo mark (accent tile) + "SymbioNusa", grouped navigation of the pages that exist, one collapsed **"Segera hadir (n)"** row for routes not built yet (never a list of dead items), and the user card at the bottom (avatar, name, role, labelled "Keluar" button). The sidebar and navigation keep their established form; the Ledger language in this revision governs the content area, not the sidebar. There is **no desktop top bar** beyond the page's own first row (on the Kaca Admin dashboard: breadcrumb, the command-palette trigger "Cari antrean atau ketik perintah · Ctrl K", the load-time chip and the bell, all as small glass controls). Each page then has its header row on the ground, in ink: title + subtitle on the left; "Data demo" tag (demo data only), search field, notification bell with a red count, and at most one primary action on the right. The subtitle carries the freshness stamp (see Components, Freshness).

**Role switcher (demo mode only).** When the application runs with `DEMO_LOGIN` enabled, the header may carry a segmented control "Tampilan peran" (Admin · Seller · Buyer · Inspector) that switches the demo account. It never appears in production: every real account has exactly one role (`users.role`), and the role is changed only by an Admin through user management. Switching roles is a layered transition (see Motion), not a hard page cut.

### Dashboards — the Three questions (mandatory structure)

Every role dashboard is built in this zone order, top to bottom. Do not order a dashboard by data type (a strip of numbers, then a list, then a composition). Order it by the user's questions, and title each zone with that question's wording:

1. **Zone 1 — "Perlu tindakan Anda".** The viewer's own pending decisions, grouped by decision type (see Components, Queue group). The group title carries the true count ("Verifikasi perusahaan · 4"). Each group has exactly one reason line. Show the top 3 items per group; the rest sit behind an expansion control "Lihat N lainnya" in the same group. Nothing else competes with this zone: it is first and it holds the page's one filled primary action.
2. **Zone 2 — "Sedang berjalan".** Work in progress, shown with a visual time anchor, not as uniform text rows: deadlines, deliveries and expiries use a date block (see Components); a field day's schedule uses the hour timeline (see Components). A row in this zone that is only text with a date typed at its end does not meet the specification.
3. **Zone 3 — "Gambaran <periode>"** (for example "Gambaran bulan ini"; the period word follows Data honesty). Context only, always last: the summary strip and, where relevant, material composition and the impact band. Every number in this zone carries a comparator — a delta against the previous period with a direction arrow, or a progress bar toward a stated target. A Zone 3 number without a comparator or a period label is not shippable. Percentages in this zone always name their denominator and are shown together with their absolute amount (tonnage and/or value) — never a percent alone.

Role notes (the structure above does not change; only the content of the zones changes):

- **Admin / DLH:** uses the Lab composition decided with FND v12 (summary strip, "Perlu keputusan Anda" queue with drawer, inspections per hour, Sungai Order, supervision map, invoice aging, activity, Inspector load, tonnage per category, impact band, Ctrl K palette), not these zones. Its queue still groups by decision type (verification, assignment, dispute, certificate) with true counts.
- **Seller:** Zone 1 includes unanswered RFQs and open invoices. Zone 2 includes listings in negotiation and shipments underway. Zone 3 includes the portfolio summary — the portfolio itself is a ledger list ("Listing saya", top N + true total + "Lihat semua →"), not a single newest item.
- **Buyer:** Zone 1 includes RFQs still "Terbuka" waiting on a Seller, so a stalled request stays tracked. Zone 2 is deliveries in progress with date blocks and, where the order state allows it, the receipt-confirmation action. Zone 3 includes "Kebutuhan saya" response counts per demand.
- **Inspector:** Zone 1 is the next assignment, with the role's primary action. Zone 2 is today's schedule as the hour timeline, today first, with a location on every entry, followed by the credential as a ledger row + expiry date block. Zone 3 is a short completed-work summary; vanity totals never lead an Inspector dashboard.

Columns stack independently so neither leaves an empty band. Side columns are 380 px where used.

Public surfaces: the landing page is a single column at up to 1280 px with full-bleed photo sections, an asymmetric editorial grid and the display-public headline; the material board on it is evidence inside the story, not the first screen. Auth pages: split screen; left 640 px photo panel (material photo under a forest overlay at 60–90%, wordmark, one statement in display-band, material chips), right the form. Under FND v12 the form moves onto a glass card over the Kaca backdrop; whether the photo panel stays beside it is open for the PM (`PANDUAN_KACA.md` §12).

Interactive charts (see Components, Bar chart and Donut chart) may sit in Zone 3; on Buyer dashboards the route tracker sits in Zone 2. They follow the Zone 3 rules on periods, denominators and comparators.

Responsive: below 1024 px the sidebar becomes a drawer (304 px) opened from a menu button in a 64 px mobile top bar (menu, logo, bell); the summary strip becomes a 2×2 arrangement inside the same glass surface (hairlines become a cross, never four separate tiles); the order flow scrolls horizontally as one connected pipeline and opens on the stage carrying the insight marker; tables become row cards; queue groups keep their 3-item limit and expansion.

Spacing: 4 px base; on Kaca screens 16 px between glass cards (`gap-4`), 16 px card padding on mobile and 20 px on desktop (24 px for the Sungai Order card and the impact band), 24 px from the page header to the first card; ledger rows 56 px unless their content requires more. Senja screens not yet rebuilt keep 24 px gaps and padding.

## Content language — Ledger

This section governs the content area of every application surface. The sidebar and navigation keep their established form and are exempt from the prohibitions below.

- **No capsule or pill shapes in the content area.** Prohibited: status badges rendered as filled pills, filter or tag chips rendered as pills on data, buttons rendered as pills, and small rounded tint boxes stacked inside a card. The only pill-shaped or round elements allowed are the sidebar count badge, the round count badge beside a card title, the "Data demo" tag, people avatars, status glyphs and tag elements on public surfaces. If an element's shape is a pill, it does not belong in the content area.
- **Status is coloured text.** A status is written as text in its semantic colour (see Colors), with an optional small glyph (16 px) beside it. It has no background field, no border and no pill. (The component keeps the name and API `x-ui.badge`; its rendering is text — see Components, Status text.)
- **Lists are ledger rows.** A list is a set of rows on one surface — the glass card on Kaca screens, white on Senja screens — separated by hairlines (`line-on-glass` / outline). Do not render each row as its own small coloured card, and do not place a tinted sub-box inside a card to group part of its content. Grouping is done by a group title and hairlines, never by a nested field.
- **Colour is data ink.** Colour may appear as: a key number, status text, a thin bar (composition bar, age bar, progress bar), a category mark, a photo, and one accent per region (the filled primary action, or the selected order-flow segment). Colour may not appear as a background field assigned per element. A row of elements each with its own tint is prohibited.
- **Two text tones only** in the content area (see Colors and Typography). Hierarchy comes from size and weight.
- **Buttons are squared:** radius 9 px (`x-ui.button`, rounded.sm) or 11–12 px for buttons drawn inside glass cards, height 40 px (36 px allowed for row actions on desktop, 44 px on mobile targets). Exactly one filled button per region (see Components, Buttons).
- **Dots carry no decoration.** A dot, point or marker may appear only when it encodes a value (a timeline node, a chart point, a status glyph). Decorative dot rows, dot separators and dotted composition segments are prohibited; composition is a continuous thin bar with labels.

## Text budget — Show, don't narrate

A visualisation that needs a paragraph to be understood has failed; fix the visualisation, not the copy.

- **Full sentences are allowed in exactly two places:** an empty state, and at most **one insight line per section**. Everywhere else, use titles, labels, numbers and form. Section titles, group titles, labels and captions are fragments, not sentences.
- **No explanatory paragraph under a diagram or visualisation** — not under the order flow, not under a chart, not under a composition bar. Data honesty (next section) is still mandatory, but it is delivered through form and labels attached to the figure itself: the period in the zone or strip label, the denominator in the composition label, the age on the age bar. It is never delivered as an essay below the figure.
- **One canonical place per fact.** The same number must not repeat in adjacent places (in the segment, again in a sentence below it, and again in the panel). State a fact once, in its component; other components that need it reference the same value (see Interaction integrity, Single source of truth).
- **Insight markers sit on the data.** An insight is a short marker attached to the element it describes: "Terlama · 9 hari" on the order-flow stage itself, a delta arrow on the metric itself, an expiry warning on the credential row itself. An insight written as a sentence below its section is prohibited.
- **Hierarchy protects the budget.** Because numbers are 28–32 px / 600 and labels 12–13 px, a user must be able to grasp a section's point in about two seconds without reading any sentence. If a test reader has to read prose to find the bottleneck, the oldest item or the overdue item, the section fails this budget.

## Data honesty

These rules govern every number, percentage, count and derived state shown anywhere in SymbioNusa, in the application and on public surfaces. They exist because honest data can still be presented in ways that mislead: all-time totals with no period, a material share computed on the five rows on screen, and a "stuck" stage inferred from a headcount. The delivery of every rule below follows the Text budget: encode it in the label or the form, adjacent to the figure — never in a footnote paragraph elsewhere on the page.

- **Every aggregate names its period.** A total is labelled "Bulan ini", "Sejak awal" or "Total tercatat" — in the strip label, the zone title or the figure's own caption, adjacent to the figure. An unlabelled aggregate is not shippable. If the underlying data has no date field, the honest label is "Total tercatat", never an implied month.
- **Every percentage names its denominator and is computed on the full total.** A share is calculated against the total of the whole set (for example, total tonnage across all materials), never against the subset currently displayed. The denominator is stated in words in the same label as the figure: "41% dari total 128,4 ton". If a bar shows only the top N, the remainder is shown as "Lainnya" or the label states the basis, so the visible parts never pretend to be the whole. A percentage is always shown with its absolute amount (see Layout, Zone 3).
- **Inferred states are labelled as heuristics, never presented as fact.** A derived condition may only be stated plainly when it is computed from a stated threshold (for example, an order stage whose oldest order exceeds a declared age limit, with the limit and the age shown on the stage marker: "Terlama · 9 hari"). Anything inferred from a proxy — such as the stage with the most orders — is labelled for what it is: "Tahap dengan order terbanyak", not "tertahan". Do not write a diagnosis the data cannot support.
- **Counts on tabs, filters and badges are true totals.** A tab count, a filter count and a sidebar badge show the real number of matching items, from a count query, not the number of items that happen to be loaded or paginated onto the screen. If only part of a list is loaded, the list itself says so ("Menampilkan 10 dari 34") with a way to see the rest.
- **Aging is first-class data.** Wherever a timestamp exists, queues and flow stages show how long items have been waiting, as an age bar with its day count (see Components, Age bar) or a "sejak" date with its meaning. Sorting a work queue by urgency means, wherever the data allows, longest-waiting first. A queue with no age information must not invent urgency ordering it cannot justify. For orders, the age in the current stage is `now − waktu` of the latest `order_status_history` row whose `ke` equals the order's current status; this only holds if every status transition writes a history row, so a stage whose transitions do not yet write history shows no age rather than a guessed one.
- **Demo and seeded figures are labelled "Data demo"** wherever they appear, including public surfaces and the impact band, in the same visual field as the figure: on Kaca screens a 6 px-radius dashed tag in 12 px meta inside the same card. Figures the DDL cannot produce (for example a period comparison without order dates) may be shown only as tagged sample data, and the service that builds them documents which columns are missing (the `AdminKaca` pattern).

## Interaction integrity

These rules govern how components on the same page may affect each other. They are the most important rules in this revision: most dashboard confusion comes not from styling but from one component quietly changing another.

- **One list, one controller.** Each list, queue or panel has exactly one control that changes what it shows (its own tab strip, its own expansion control, or its own filter). No component may filter, re-sort, hide or otherwise change another component as a side effect of interaction with the first. If two controls appear to govern the same list, one of them is a defect.
- **No silent cross-filtering.** Clicking a stage in the order flow changes the order-flow detail panel only. It must not filter the action queue below it, change a tab, or alter any count outside the flow and its panel. The same rule applies in reverse and to every other component pair on the page.
- **Cross-component links are explicit and user-invoked.** When a connection between components is genuinely useful, expose it as a labelled action the user chooses, for example a button "Lihat di antrean ↓" inside the detail panel. Activating it scrolls to the target component and highlights the matching row briefly (±600 ms). It must not hide other rows, must not change the target's filter, tab or sort, and must not leave a persistent filter banner behind. The connection exists only while the user is looking at it.
- **Every state change is visible where it happens.** When a click changes a panel's content, two things happen in the panel itself: its title updates to name the new context (the stage or group name plus its count, for example "Inspeksi · 3 order"), and the panel surface carries a brief highlight (±600 ms, a soft primary-tinted flash that fades; on Kaca screens the FLIP/fade of the new content does this job). Under `prefers-reduced-motion` the fade stays and nothing moves position (see Motion). A change the user cannot see happen is treated as no change — and as a source of confusion to be removed.
- **Single source of truth for numbers.** Every count shown on a page is derived from one source value per fact. The same fact must be identical everywhere it appears: the order-flow segment count = the detail-panel title count = the number of order rows in the panel; a queue-group title count = the number of rows in the group including those behind the expansion; a tab count = the total of the groups it covers. A mismatch between two renderings of the same count is a design defect, not a rounding detail. Do not hard-code a count in a label separately from the list it describes.
- **Uniform affordance.** Every actionable row carries the same action element in the same position and form (for example, the same "Tinjau" secondary button at the right of every queue row). A row whose action appears only on hover, only on the first row, or in a different shape from its siblings breaks the user's model of the list. Informational rows carry no action-shaped element at all (see Components, Action resolution).

### Action feedback loop (mandatory for every completable action)

Clicking an action that completes a piece of work — Seller "Konfirmasi", Buyer "Konfirmasi penerimaan", an Admin decision, Inspector "Mulai inspeksi" or "Selesaikan" — runs this sequence once the server confirms success. When the action needs input first (the goods-receipt form, an inspection result), the loop runs when that form is submitted, and the row reports the outcome the server decided (for example Selesai or Sengketa), not an assumed success:

1. **Button → drawn check.** The button turns into its success state: an SVG check whose stroke draws itself (spring-soft-bounce). No confetti, and a toast alone never counts as feedback.
2. **Row folds out.** The completed row collapses (spring-gentle on its height) and leaves its group; the rows below flow up on the same spring instead of jumping.
3. **Counts change once, together.** Every count that included the item — group title, zone, tab, sidebar badge — moves to its new value in the same interaction (spring-snappy, see Motion, Numbers). Each comes from the same source value (see Single source of truth).
4. **Done state.** A group that becomes empty shows its empty state ("Semua selesai") instead of disappearing, so finished work reads as finished, not lost.
5. **Consistency across components.** The same fact updates everywhere it appears. Example: a Seller confirming ORD-1041 moves the Admin order flow — Menunggu Konfirmasi 7 → 6, Dikonfirmasi +1 — and the flow segments, the detail panel and the queue counts all agree afterwards. Data moves on the server; the page reflects the server's new state, never a client-side guess.

If the server rejects the action, the button returns to its rest state, the row stays, no count moves, and the error appears as an alert next to the row. A button that only changes its own label, with no row or count consequence, does not implement this loop and is not shippable for a completable action.

### Drill-down

- **Every visual answers in place.** Clicking a chart bar, donut arc or its legend row, a route, a milestone, a timeline task or an order-flow stage opens the detail for that exact item in an attached or expanding panel — not a dead end and not an unexplained navigation.
- **Every drill-down has a visible way back:** clicking the same item again, a close control, or a reset control ("Tampilkan semua"). The selection is visible on the visual itself and, for charts, in the legend.
- **Drill-down highlights and expands; it never filters another component** (see No silent cross-filtering).

## Elevation & Depth

Depth comes from glass (FND v12). There are four material levels, from back to front:

1. **Ground** — the cream canvas with four blurred material photos (`.kaca-backdrop`, fixed, `z-index: -1`), a cream veil and a fine grain. Decorative, `aria-hidden`, carries no data.
2. **Glass card** — `[data-card]` / `.glass`: the translucent cream gradient, blur 22 px, saturate 150%, a 1 px white edge, a lit edge from the top left and a soft forest shadow (`shadow.glass-card`). On hover a soft sheen follows the pointer (`--mx`/`--my`, written by the page wrapper; `data-card` only). All content lives on this level.
3. **Rows on glass** — `field-on-glass` sub-surfaces: stand-alone decision rows (`.glass-row`), sub-panels, the region detail panel; `panel-strong` for the selected item. Plain list and table rows have no surface of their own: they sit on the card, divided by hairlines.
4. **Thick glass** — `.glass-strong`: drawer, dialog, command palette, chart tooltip, over a forest scrim (`--scrim`). Overlays are never thin glass.

Dark surfaces on top of this: the glass sidebar (forest gradient, blur 24 px) and the impact band (forest gradient, blur 26 px). Controls do not take part in the glass: buttons, inputs and selects are opaque white or filled green with a small forest shadow (`shadow.glass-button`). Glows, coloured shadows and blur that is not one of the surfaces above are prohibited.

**Fallbacks.** `@supports not (backdrop-filter)` and `@media (forced-colors: active)` turn every glass surface into the white Senja card (`surface`, `shadow.card`, `radius.lg`) and the sidebar into solid `secondary`, and hide the photos. `prefers-reduced-transparency` is not honoured (PM decision); do not add it.


## Photos

- **Real material photos only:** residue, warehouse and process photos of the actual material classes (plastic bales, pressed cardboard, wood offcuts, textile scraps, organic residue). No stock people, no illustrations, no AI-generated images presented as real material. Sources and licences are listed in `docs/design/CREDITS-FOTO.md`.
- **Text on a photo needs a guaranteed-contrast base.** In the application the approved pattern is a solid photo-label block (photo-label #1e2a08, white 14 px / 600, radius 6 px) at the photo's bottom edge, on the photo scrim: `linear-gradient(to top, rgba(9,26,18,.68) 0%, rgba(9,26,18,.40) 48%, rgba(9,26,18,0) 100%)` over the bottom ~64% of the photo, `pointer-events: none`, below the label. Examples: "Stok 12,4 ton", "Terverifikasi DLH". Public surfaces use the forest overlay instead (see Layout).
- **Test against the brightest photo in the set** (pale cardboard, white plastic bales), not the average one. If contrast cannot be guaranteed, move the label below the photo into the card body — never make the label brighter, bigger or a pill.
- One un-based overlay fails the page: audit every label on every photo with the same rule.

## Shapes

Kaca radii (FND v12): glass card and summary strip 22 px; drawer and impact band 24 px (26 px as a mobile bottom sheet); top-bar glass controls (search trigger, period segment, status chip) 14 px; stand-alone rows and sub-panels 16 px; buttons and controls inside a card 11–12 px; segment buttons and their thumb 9–11 px; photo thumbnails 10–12 px; ID tags, keyboard hints and the "Data demo" tag 6 px; sidebar 24 px. Form controls from `components/ui` keep their 9 px (`rounded-sm`). Segmented controls are squared tracks with a squared thumb, never capsules. No pills in the content area (see Ledger); the only rounded-full elements are the count badge in a card title, people avatars, status glyphs, dots that encode a value and thin bars (age, progress, track), which are bars, not capsules. Senja radii (14/9/8) apply only to screens not yet rebuilt.


## Motion

Motion answers the user's own action or a change in data; it never carries information on its own. The feel to reproduce is the SymbioNusa Lab Kaca variant: springs with mass that settle softly and can be redirected mid-flight.

**One Kaca spring.** Layout motion on Kaca screens uses one spring — the Lab spring, stiffness 420, damping 38, mass 0.9. `springCurve()` in `resources/js/dashboard/kaca.js` simulates it with the same semi-implicit Euler integration as the shared solver `resources/js/motion/spring.js` (240 Hz) and writes it to `<html>` as a CSS `linear()` easing (`--kaca-spring`) with its settle time (`--kaca-spring-ms`). CSS transitions use it through `.kaca-spring` and the segmented-control thumb; Web Animations use it through `flip()`, `exit()` and `kacaRiver`. Do not write a new `cubic-bezier`, `ease` or `ease-out` for a layout movement. The three solver presets (Gentle, Snappy, Soft-bounce) remain for press feedback (`bindPress`), the success check of the Action feedback loop (Soft-bounce) and Senja screens not yet rebuilt.

**What moves (Kaca):**

- **Overlays** (drawer, dialog, palette, region panel) enter with `x-transition:enter="kaca-spring"` from a small offset (drawer from the right, bottom sheet on mobile, palette and dialog from slightly above/below with scale .97–.98) and leave fast (`transition duration-150/200 ease-in`).
- **Lists that change** (queue filter, Sungai Order stage switch) use FLIP: leaving items `exit()` (180 ms, slide + scale .96 + fade), remaining items slide to their new place on the spring, new items fade in with a 16 px rise staggered 40 ms, and the container height animates. Each step has a timeout guard so a throttled background tab never leaves the UI stuck.
- **Segmented controls:** a filled green thumb slides under the `aria-pressed` button (`x-kaca-seg`).
- **In-place expansion:** `grid-template-rows` 0fr ⇄ 1fr on `.kaca-spring` with opacity; closed content is `inert`.
- **Entrance on load:** cards rise and fade once (`[data-rise]`, 0.6 s, staggered 60 ms by `--i`); map tiles and chart dots pop (`--d`); bars grow once (`kaca-grow-y`); donut arcs draw (`.kaca-arc`). Entrance runs once per page load, never on re-render.
- **Numbers:** never count up on page load. When the user's own action changes a value (period switch, feedback loop), the shown number moves to its target with Indonesian formatting on every frame (`countTo`, 850 ms).
- **Press:** buttons, options and links scale to .97 while held. **Hover:** colour, background, shadow and the glass sheen use 150–200 ms `--ease-hover`; never layout.

**Looping motion (the only exceptions to "no decorative motion").** The backdrop drift (29–47 s), the live dot ping (`.kaca-ping`, only on an item that is genuinely running or flagged), the Sungai Order flow lines (`.river-flow`) and the loading shimmer. All four stop under reduced motion and none carries data. There is no visible pause control yet (WCAG 2.2.2); see the open item in `PANDUAN_KACA.md` §12 and do not add further loops.

**Reduced motion** follows the Lab (`MotionConfig reducedMotion="user"`): under `prefers-reduced-motion: reduce` only position, scale and looping motion stop. Fades (200 ms), colour changes, bar widths and the donut draw keep animating, so nothing jumps; FLIP becomes a fade; the thumb jumps to its place; numbers jump to their target; press scale and the sheen are off. Senja's blanket `transition: none` does not apply inside `.dash-kaca` (`.dash-senja:not(.dash-kaca)`). Windows with "Animation effects" off reports reduced motion — test both settings.

**Performance.** Animate `transform`, `opacity`, `grid-template-rows` and widths of thin bars only; target 60 fps on a mid-range laptop. Loading is a shimmer over the existing layout or skeleton rows, never a spinner that replaces content.

**Verification.** Motion cannot be signed off from a screenshot. Click filters, stages and segments rapidly in succession and confirm nothing jumps, queues or teleports; repeat with reduced motion on and confirm fades still run and nothing moves position.


## Accessibility

Accessibility wiring is part of the system, not a per-form afterthought.

- **Focus:** a visible focus ring on every interactive element (2 px, 2 px offset): focus-ring green on Senja screens, accent #c2410c inside `.dash-kaca`, white on the forest sidebar and on forest fields. Rows that take focus also show the accent border and the 3 px accent-soft ring.
- **Legibility on glass:** text pairs are measured on the rendered screen, not from two hex values (`PANDUAN_KACA.md`, Lampiran A). Glass falls back to white cards under `forced-colors: active`.
- **Field descriptions are wired by the field component.** Every hint and every error is connected to its input with `aria-describedby`, and an invalid field carries `aria-invalid`. Form authors write the hint and error text; they never wire the ARIA by hand, and a field shipped without the wiring is a component bug, not a page bug.
- **Tabs follow the full tablist keyboard pattern:** the container has `role="tablist"`, each tab has `aria-selected` and `aria-controls` pointing at its panel, panels have `role="tabpanel"`, arrow keys move between tabs, and Home/End jump to the first and last tab. A tab strip that only works with a pointer is not finished.
- **Order-flow segments are buttons** with the full stage name in `aria-label` (the visible label is the official abbreviation, see Components); the selected segment carries `aria-pressed` or the equivalent selected state, and the detail panel is labelled by the selected stage's full name and count.
- **Touch targets:** controls are at least 40 px high on desktop; on mobile, row actions and any action inside a list or card — including segments, milestones, chart bars and donut legend rows — meet a **44 px minimum touch target**, even when the visible glyph is smaller.
- **Charts are operable without a pointer.** Bars, arcs and legend rows are reachable by keyboard and carry an accessible name with their exact value ("Plastik, 52,6 ton, 41% dari total 128,4 ton"); the legend row is the primary keyboard target for a donut.
- **Moving content can be paused.** Any motion that starts automatically and runs longer than 5 seconds (the order-flow particles) has a visible pause control and stops under reduced motion.
- **Success feedback is announced.** The feedback loop's result ("ORD-1041 dikonfirmasi") is announced through a polite live region, because the drawn check and the fold-out are visual only.
- **Status, category and age never rely on colour alone:** status is glyph + text, category is colour + name, age is a bar + a printed day count, a comparator is an arrow + a signed value.
- **Labels are never hidden by truncation.** Ellipsis truncation of a stage label, group title or row title that carries identifying information is a defect (see Components, Order flow); wrap, abbreviate from the official list, or let the label overflow its segment — do not clip it.
- **Disabled** elements are 50% opacity with the reason stated in text, and are removed from, or correctly exposed to, assistive technology according to whether the user still needs to discover them.

## Components

### Kaca surfaces — forms, tables, detail pages (FND v12)

These rules apply to every screen built or rebuilt in Kaca. Snippets are in `PANDUAN_KACA.md` §6.

- **Page wrapper.** The content of every Kaca screen is wrapped in `.dash-kaca` with `.kaca-backdrop` (the four photos at their fixed sizes and positions) as first child. Do not put `bg-background` or any opaque background on the wrapper.
- **Cards.** `x-ui.card` (glass through `data-card`) or `section.glass`; one card per region; padding 16 px mobile / 20 px desktop; title `.card-title`. No white card on a Kaca screen except alerts.
- **Forms.** The form sits in a glass card. Inputs, selects, textareas and buttons stay opaque white (`x-ui.input`, `x-ui.select`, `x-ui.textarea`, `x-ui.button`) with the 3.86:1 input border; never `field-on-glass` or transparent controls. Label 14 px ink above the field, helper 13 px meta, error 13 px `text-danger` (#7f1d1d on glass) with its icon under the field, wired by `x-ui.field`. Actions at the foot of the card, separated by a `line-on-glass` hairline: secondary then the one filled primary. Credential errors stay one `x-ui.alert` above the form; alerts remain opaque message fields.
- **Tables and lists.** Rows sit directly on the glass card, divided by `line-on-glass`; a clickable row hovers to `primary-soft`; the selected row is `panel-strong`; header 13 px meta; numbers right-aligned in tabular figures; status as `x-ui.badge` text + glyph; the uniform row action is an outlined button (`border-line-strong`) or the secondary `x-ui.button`. Rows are never individual white or glass cards, except stand-alone decision rows (`.glass-row` on `field-on-glass`, radius 16 px). `x-ui.table` paints its sticky first column white and `x-ui.summary-strip` is white: on Kaca screens use ledger lists and the glass summary strip until those components get a Kaca variant (open item).
- **Detail pages.** Header names the object; main glass card (facts as label/value pairs in meta/ink with hairlines, status history as bars) + action glass card (one filled action, a stated reason when it is unavailable), stacked under 1024 px. Destructive confirmations are `.glass-strong` dialogs that name the object and the consequence; the destructive button is last.
- **Drawers, dialogs, palette.** `.glass-strong` over `--scrim`, focus trapped (`x-trap.noscroll`), Esc closes, the drawer becomes a bottom sheet under 640 px. A dashboard drawer shows detail and links to the decision page; it never takes the decision itself.
- **Charts.** Inline SVG computed on the server; colours via `var(--c-*)` / `var(--color-category-*)`; axis text 13 px meta; planned values hatched; a legend with names; tooltips `.glass-strong`; keyboard-reachable legend rows with the exact value in the accessible name.
- **Empty states.** Dashed `line-strong-on-glass` border, radius 16 px, title + cause + (where possible) the primary action; a filter that empties a list says so and offers the way back.
- **Data demo tag.** A 6 px-radius dashed tag "Data demo" in 12 px meta in the same card as the sample figure. It is the one tag shape allowed in the content area.

### Component catalogue

The catalogue below predates Kaca. Where an entry says white surface, radius 10/14/20 px, a neutral shadow or a Sage hex value, read the Kaca equivalent from the subsection above and the front matter; the behaviour, content and honesty rules of each entry still apply.

- **Buttons:** primary (filled green), secondary (white, 1 px outline-control border), danger (white, red border and text), text links in green with an arrow for navigation ("Lihat 3 order →"). All buttons are squared, radius 10 px. Exactly one filled button per region: in a list, only the top item's action may be filled where the design calls for a single primary action, and queue rows otherwise use the uniform secondary action (see Queue group). Labels are verbs ("Balas", "Konfirmasi", "Tetapkan", "Periksa", "Tinjau"). No coloured shadows. Press uses the Snappy spring (see Motion). A completable action has a success state — the drawn check — and runs the Action feedback loop (see Interaction integrity).
- **Segmented control:** for switching between two to four views of the same content (Tonase · Nilai; the demo role switcher). A squared track with a squared thumb that slides on the spring — on Kaca screens the filled green `.kaca-thumb` under the `aria-pressed` button (`x-kaca-seg`), see the front matter; options are text labels; it exposes `aria-pressed` buttons in a labelled group, or the tablist pattern when it switches panels. Never a capsule.
- **Action resolution:** every element that looks like an action must resolve to a working page or control. If the destination does not exist yet, the item is shown informationally — plain text in the row, no button-shaped element, no border or fill that implies clickability, and no pseudo-action wording such as "Konfirmasi segera", "Putuskan segera" or "Tetapkan segera" rendered as if it were a command. Unbuilt destinations are grouped and labelled honestly, as the sidebar already does with "Segera hadir (n)". A dashboard must never advertise work the user cannot complete from it.
- **Status text** (replaces the tinted pill badge): the STATUS_MAP label as text in its semantic colour, optionally preceded by a 16 px glyph. Glyph by variant: warning = dashed ring (waiting for someone), info = ring with half fill (in progress), success = filled circle with check, danger = filled circle with cross, neutral = filled circle with a dash. Shape + colour + text, so it never relies on colour alone (Carbon status-indicator pattern). No background, no border, no pill. The component keeps the old API (`<x-ui.badge :variant>`); only its rendering changed.
- **Urgency:** only urgent items are flagged: a 16 px red square with a white "!" plus the date in red ("Besok · tenggat"). Non-urgent rows carry no priority label. The rule is the same on every role and on mobile.
- **Summary strip (replaces the KPI tile):** one surface (glass on Kaca screens, see the front matter; white on Senja screens) holding 3–4 metrics in a row, separated by vertical hairlines. Each metric is: a label (12–13 px, meta tone), a value (28–32 px / 600, on-surface; unit 16 px / 500), and one comparator line (delta vs the previous period with an arrow, or a progress statement toward a target). No per-metric background field, tint, icon, decorative circle or sparkline, and no gradient. A metric whose meaning is a warning may set its *value* in the semantic colour — the surface stays white. The strip lives in Zone 3 (or directly under the header where a role has no Zone 3 content); it never leads a Three-questions dashboard above Zone 1. Exception: the Admin dashboard's Lab composition (FND v12) opens with the period-comparison strip, tagged "Data demo".
- **Freshness:** every dashboard and work-queue surface states when its data is from, in the page subtitle: "per 21 Nov 2026, 14.05 WIB". A work queue must also have a way to become fresh: periodic auto-refresh or a visible reload control ("Muat ulang"). When the shown data is older than 5 minutes, a stale indicator appears next to the stamp ("Data 12 menit lalu — muat ulang untuk data terbaru") in warning text; stale data is never presented as current.
- **Order flow (Admin):** a connected proportional pipeline of the 10 STATUS_MAP stages in three groups — Kesepakatan: Draf, Menunggu Konfirmasi, Dikonfirmasi · Mutu & pembayaran: Inspeksi, Lolos Inspeksi, Menunggu Pembayaran, Dibayar · Pengiriman: Dalam Pengiriman, Diterima, Selesai. Specification:
  - Segments touch each other in one connected bar; a subtle chevron notch on each segment's right edge cues direction. Segments do not float as separate boxes.
  - Segment width is proportional to its order count, with a minimum width so a 1-order stage stays clickable (44 px target). Width encodes count only — never age, never value.
  - The order count sits inside the segment at 28–32 px / 600 and is the dominant element. Unselected segments sit on the sage tint (surface-muted) with on-surface numbers; the selected segment is filled primary with white number and label.
  - The stage label is one line, set in the official abbreviation: Draf · **Menunggu Konf.** · Dikonfirmasi · Inspeksi · **Lolos Insp.** · **Menunggu Bayar** · Dibayar · **Dalam Kirim** · Diterima · Selesai. The full name appears in the segment's `title` and `aria-label` and in the detail panel title. A label must never be truncated with an ellipsis: when a segment is narrower than its label, the label overflows the segment's width (centred on the segment) while the segment itself stays proportional. Do not widen a segment to fit its label, and do not shrink the label below 12 px.
  - Exit branches hang from their origin stage as one-line text labels below the pipeline, each with its count, in the semantic colour: **Ditolak · n** from Menunggu Konfirmasi, **Dibatalkan · n** from Dikonfirmasi, **Gagal Inspeksi · n** from Inspeksi, **Sengketa · n** from Diterima. A branch label is one line, never wrapped or clipped, and is itself selectable like a stage. Exits are never drawn as a separate row of stages and never summarised in a sentence under the flow.
  - An insight marker sits on the stage it describes (see Text budget): for example "Terlama · 9 hari" in the insight-marker colour on the stage whose oldest order exceeds its stated threshold. Only one such marker per flow; it follows the Data honesty threshold rule, never a headcount guess.
  - **Detail panel (attached):** directly below the pipeline, in the same card, with no gap component between them. Its header carries exactly two facts: the selected stage or branch name with its order count ("Inspeksi · 3 order") and the total value ("Total Rp 47,5 jt"). Tonnage and group name, if shown at all, are secondary meta in the same header line — never additional header rows. One line per order: order ID · party route (Seller → Buyer) · value. Age per order is an age bar with its day count (see Age bar), aligned at the right of the row — never a repeated sentence such as "4 hari di tahap ini" on every row.
  - Clicking a stage or a branch changes **only this panel** (see Interaction integrity): the panel title updates to the new name + count, the content slides in the direction of the flow and the panel carries the ±600 ms highlight. The panel may contain one explicit cross-link, "Lihat di antrean ↓", under the conditions in Interaction integrity. RFQ counts are shown separately, not as a stage.
  - **Particles (optional enhancement):** a canvas layer inside the segments may show a slow flow of particles whose density is proportional to each stage's order count. It never replaces the printed count, has a pause control beside the flow, stops when the tab is hidden, and is not drawn under reduced motion (see Motion, Continuous motion). Build it last, after everything else on the page passes.
  - **Counts move with the data:** when an order changes stage through the Action feedback loop, the two affected segment counts and widths move on the Snappy spring.
- **Age bar:** a thin bar (6 px, fully rounded) on the #e2e8df track. Its filled length is proportional to the item's age in its current stage, against the stage's stated threshold scale. Fill colour steps with age: pale green #86c79b while comfortably inside the threshold, amber #fbbf24 approaching it, red #b91c1c past it. The exact day count is always printed in small type (12 px, meta tone; red when past threshold) at the end of the bar. Use it in the order-flow detail panel, in queue rows and in schedule rows wherever a "waiting since" fact exists. Never replace the printed number with the bar alone, and never write the age as a full sentence per row.
- **Date block:** a date rendered as a block, not as text in a sentence: the day number at 28–32 px / 600 dominant, with the short month (and year where needed) at 12–13 px in the meta tone beside or directly below it — for example a large "15" with "Des" under it. Use it for deadlines, delivery arrivals, certificate expiries and any date the user must find by scanning. One date block per row, at the row's leading or trailing edge, consistently placed within a list. A date that is overdue sets the day number in error colour; the block gains no background field.
- **Timeline:** a vertical rail for one day's field schedule. Hour labels (09.00, 13.00, …) in tabular figures sit on the rail; each entry hangs from its hour with title, location and status text. Entries are chronological, today first. The next upcoming entry is the most prominent element on the dashboard section and carries the role's primary action (for example "Mulai inspeksi"). Completed entries recede to the meta tone. Exactly one current-time marker ("Sekarang") sits on the rail; when an inspection starts, the marker moves to that task — two markers is a defect. Clicking an entry expands its detail in place (FLIP on Gentle): location, contact, notes and the task's action, without leaving the page; clicking again collapses it. Use the timeline for Inspector schedules; do not use it for generic lists.
- **Bar chart (interactive):** daily or weekly bars in chart-lead green for the leading series, category colours for category series; a hairline baseline; value labels in tabular figures. Hover or focus shows a crosshair line and a tooltip with the exact formatted value. The squared segmented control "Tonase · Nilai" switches the measure and morphs the bars (see Motion, Charts). Clicking a bar selects that period and highlights the matching rows in the chart's own detail list below it; clicking the bar again or "Tampilkan semua" restores the full view. Every chart names its period and follows Data honesty.
- **Donut chart (interactive):** arcs in the fixed category colours with a 3 px white separation; the centre shows the total with its period label ("128,4 ton · Total tercatat"). The legend is ledger rows: category name, thin category bar, and the full Data honesty label (percent of the stated total, tonnage, value). Clicking an arc or its legend row expands the category detail below (FLIP on Gentle) with the category totals and its related listings or orders; clicking again or the close control collapses it. The selected category is marked on the arc (slightly enlarged) and in the legend (primary-container wash on the row — the one permitted tint on a data row, as selection feedback). No dot-segment compositions, no percent without its denominator.
- **Route tracker (Buyer, Zone 2):** a route line from origin to destination (the manifest's `rute`) with a marker at the shipment's current step, and the milestones that the data records below it: "Dijadwalkan" (`shipments_manifests.jadwal`) · "Dalam perjalanan" (manifest status `DALAM_PERJALANAN`, time from the order's `DALAM_PENGIRIMAN` history row) · "Tiba" (status `TIBA`; no time is recorded for it) · "Diterima" (`goods_receipts.waktu`). The marker shows the step, not a geographic position: there is no GPS or transit data, so no intermediate stops ("Transit Karawang") are drawn unless a future schema records them. Clicking a shipment focuses its route card (FLIP on Gentle) and moves the marker to its step once. Each milestone with a recorded time shows it ("Diterima · 14.20 WIB"); one without shows none rather than a guess. Completed milestones in on-surface, the current one in primary, upcoming ones in meta — always with text, never colour alone. When the shipment has arrived, the card carries "Konfirmasi penerimaan", which opens the goods-receipt form (berat diterima, berat ditolak, kadar air, kondisi). Submitting it runs the Action feedback loop with the server's outcome: within tolerance the order moves to Selesai, outside tolerance it moves to Sengketa and a dispute opens — the card states which, in status text.
- **Listing card (Seller catalogue, Buyer catalogue teaser):** a real material photo on top with the photo label ("Stok 12,4 ton") on the scrim (see Photos); then the listing name (15/600), seller + location meta line, a thin sold-progress bar, and the price line ("Rp 2,0 jt per ton · terjual 72% stok"). The whole card is one link; no chips on the photo or in the body.
- **Toast:** a short confirmation line on the secondary forest surface, radius 10 px, auto-dismissed. It never carries information the user needs later and never replaces the in-place feedback loop.
- **Queue group (the Zone 1 building block):** a group of action items that share one decision type. Structure, in order: a group title at 15–18 px / 600 that includes the true count ("Tetapkan Inspector · 2", "Verifikasi perusahaan · 4"); exactly one reason line for the whole group in the meta tone ("2 order menunggu Inspector"); at most 3 item rows; and, when the group has more, an expansion control "Lihat N lainnya" that reveals the remaining rows in place (collapsing back to "Tutup" is allowed). Item rows are ledger rows: monogram or photo, title (15/600), one meta line (parties, value, material), an age bar or date where a timestamp exists, and the group's uniform action element at the right. Every row in every group on the page carries the same action element in the same form (for example the same secondary "Tinjau" button); rows whose destination is not built follow Action resolution and carry no action-shaped element, grouped honestly instead. The queue's only controllers are its own tab strip where one exists (Semua / Mendesak, with true totals) and the groups' expansion controls — nothing outside the queue changes it (see Interaction integrity).
- **Lists (general ledger rows):** monogram or photo, title (15/600, list-title), meta (13/400, meta tone), a date element with its meaning ("tenggat", "masuk", "jatuh tempo", "diunggah") — as a date block where scanning matters — plus an age bar where a timestamp exists, and the action on the right. Actions follow Action resolution and Interaction integrity (uniform affordance). On mobile, row actions meet the 44 px touch target.
- **Portfolio (ledger list):** a list that represents the viewer's own set of items ("Listing saya", "Kebutuhan saya") shows all items, or the top N with the true total and a link to the full list ("Lihat semua 10 listing →"). It never shows a single newest item in a way that reads as the whole portfolio. Each row keeps its own status text and its own working action.
- **Market rows:** a row that offers someone else's demand or listing ("Kebutuhan Buyer" for a Seller, catalogue teasers for a Buyer) always shows the price bound next to the volume: the demand's maximum price ("maks Rp 4.000/kg") or the listing's price, in the same meta line as the quantity. Price is the first filter a counterparty applies; a market row without it is incomplete.
- **Credential (ledger row + date block):** a licence or certificate on its owner's own dashboard is a ledger row — credential name, issuer, status as coloured text (Aktif in success, Berakhir ≤ 30 hari in warning, Berakhir in error) — with a date block for the expiry date at the row's edge. Where a renewal route exists, the expired or expiring row carries the renewal action; otherwise it is informational. Inspector certificates use this row on the Inspector dashboard (Zone 2); Admin sees the same expiry information in the registry and in Zone 1/Zone 2 with the same date-block form, but the owner is never the last to know.
- **Material composition:** ledger rows, one per category, on one white surface: a 48–60 px real material photo, the category name, a thin continuous bar in the fixed category colour whose length is the share, and a label that states all three facts together — percent of the stated total, tonnage and value ("41% dari total · 52,6 ton · Rp 88,1 jt"). No dot segments, no per-row tint field, no percent without its denominator and absolutes (see Data honesty). A remainder beyond the top N is a "Lainnya" row in neutral zinc.
- **Monogram:** company initials on the company hue mixed 16% into white, initials in a dark shade of the hue (≥ 4.5:1), radius 10 px. Circles are reserved for people.
- **Material photos:** real photos of the residue in 48–60 px thumbnails, composition rows, listing cards and the auth panel, following Photos (sources, labels, scrim, brightest-photo test).
- **Impact band:** one forest field (the one permitted gradient) with 4 cells separated by hairlines; label, value + unit in display-band (40 px), caption carrying the period label. "Data demo" when the numbers are seeded. The band belongs to Zone 3.
- **Data table:** 13 px muted header, rows ≥ 56 px, numbers right-aligned, status column uses status text, last column for actions. In narrow columns the secondary figure (e.g. volume) moves under the primary one instead of adding a column. Counts in any table tab or filter follow Data honesty and Interaction integrity: true totals from one source, not loaded-row counts.
- **Tabs:** tab counts are true totals. A tab strip is the single controller of its own panel only (see Interaction integrity). Keyboard and ARIA behaviour follows the tablist pattern in Accessibility. Changing tabs fades the panel (and, on Kaca screens, moves the thumb on the Kaca spring); under `prefers-reduced-motion` the fade stays and the thumb jumps.
- **Forms:** label above every field, helper 13 px below, error 13 px red with an icon below the field it belongs to; hint and error are wired with `aria-describedby` by the field component (see Accessibility); credential errors (wrong email or password) as one alert above the form, not on a single field. Password fields have a "Tampilkan kata sandi" eye button.
- **Alerts:** light container (danger #fef2f2, success #f0fdf4, warning #fff7e6) with a 1 px border of the matching line token, a plain 20 px icon (no icon tile), bold first sentence. Alerts are messages, not data presentation; the Ledger ban on tinted fields does not apply to them, and they must not be used to present data.
- **Empty state:** every list, table and card has an empty state with three parts: a title that names what is missing, one line that explains why or what it means, and — when the viewer can create the content — the primary action to create it. Example: title "Belum ada kebutuhan terbuka", line "Ajukan kebutuhan agar Seller bisa menawarkan materialnya.", action "Ajukan kebutuhan". When the viewer cannot create the content, the state is informational only, with no button-shaped element (see Action resolution). An empty state is never a blank area, an illustration alone, or the word "Kosong". The empty state is, with the one insight line, the only place full sentences are permitted (see Text budget).
- **States:** visible focus ring (see Accessibility); disabled 50% opacity with a reason; hover darkens fills or tints secondary buttons with the sage ground; loading is skeleton rows that mirror the loaded layout.
- **Icons:** outline, 1.75 stroke, 20 px (16 px in dense rows), currentColor, one family (`x-ui.icon`). Icons are never placed inside a tinted tile or circle in the content area.

## Do's and Don'ts

- Do wrap every new or rebuilt screen in `.dash-kaca` with the standard `.kaca-backdrop`, and build content on glass cards (`x-ui.card` / `.glass`).
- Do keep buttons, inputs, selects and textareas opaque white or filled green on glass.
- Do use the Kaca text tokens on glass (meta #33330f, `.text-primary` → #2f3d12, `text-accent-text` #7c2d12, `text-danger` #7f1d1d) and ink only for text outside cards.
- Do put warning, info and fill colours used as text only on `field-on-glass`, `panel-strong` or `glass-strong`.
- Do use `.glass-strong` for every drawer, dialog, palette and tooltip.
- Do move layout with the Kaca spring (`.kaca-spring`, `flip()`, `exit()`, `x-kaca-seg`) and follow the Lab reduced-motion policy (stop position, scale and loops; keep fades).
- Do measure contrast on the rendered screen (`PANDUAN_KACA.md`, Lampiran A) before a PR, at 1440 px and a narrow width, at several scroll positions.
- Do structure the Seller, Buyer and Inspector dashboards as the Three questions until the PM approves another composition; the Admin dashboard follows the Lab layout.
- Do keep exactly one filled-green action per region.
- Do write every status as coloured text with a glyph; never colour alone, never a pill.
- Do build lists as ledger rows on the glass card with hairline dividers.
- Do give every summary number a comparator and a period label, and tag sample comparisons "Data demo".
- Do attach each insight marker to the data element it describes.
- Do show age as a bar with a printed day count wherever a timestamp exists, and "–" where it does not.
- Do give each list exactly one controller; make any cross-component link an explicit labelled action.
- Do signal every click-driven change inside the panel that changed (title + count, highlight or FLIP).
- Do derive every rendering of a count from one source value.
- Do title pages with the context (organisation, person or object), not a greeting.
- Do label every aggregate with its period and every percentage with its denominator and absolute amount.
- Do show true totals on tabs, filters and badges; if a list is partial, say "Menampilkan 6 dari 34".
- Do make every action-shaped element resolve to a working page or control; show unbuilt destinations disabled with the reason.
- Do stamp dashboards and queues with their freshness time ("Dimuat 14.05 WIB" / "per … WIB").
- Do use the fixed material-category colours with the category name always labelled.
- Do write empty states as title + cause + action where the viewer can act.
- Do wire hint and error text through the field component; build tabs and segmented controls with the right ARIA (`aria-pressed`, tablist).
- Do use tabular numbers and Indonesian formats.
- Do keep sidebar menu names exactly as in STITCH_BRIEF.md; collapse unbuilt routes into "Segera hadir".
- Do run the full Action feedback loop for every completable action.
- Do keep the light palette on every device (`color-scheme: light`).
- Don't lower the glass or veil alphas, swap the backdrop photos, or add new hues, without re-measuring contrast and PM approval.
- Don't make controls translucent, and don't put meta or semantic-coloured text directly on the photo ground.
- Don't use `var(--danger)` or `var(--accent)` as a text colour; they are fills.
- Don't use thin glass for overlays, or add blur, glow or gradients beyond the surfaces listed in Elevation & Depth and Colors.
- Don't use capsule or pill shapes in the content area (the "Data demo" tag and count badges excepted).
- Don't give data elements their own tinted fields; colour is ink, photos carry the colour.
- Don't write an explanatory paragraph under a chart or the order flow; encode honesty in labels.
- Don't let one component silently filter, re-sort or hide another component's content.
- Don't show a summary number without a comparator, or a percentage without its denominator.
- Don't truncate identifying labels with an ellipsis where the Text budget forbids it.
- Don't count numbers up on load, write new CSS easings for layout motion, add looping motion beyond the four listed in Motion, or switch every transition off under reduced motion.
- Don't honour `prefers-reduced-transparency` or add a dark palette.
- Don't use display-public inside the application, or text below 12 px (SVG text below 13 px).
- Don't present an unlabelled all-time total as this month's, a subset share as the whole, or a headcount inference as a diagnosis.
- Don't take a decision from a dashboard drawer; link to the decision page.
- Don't reuse `kacaAdmin` on another screen; build a small component with `flip()`/`exit()`.
- Don't use stock illustrations, stock people or marketing heroes inside the app.
- Don't present demo figures as real; label them "Data demo".


## Implementation notes

**Stack fit (Laravel 13 + Livewire 4 + Alpine + Tailwind 4).** Livewire morphs the DOM on every render, which resets in-flight animation. Therefore:

- Kaca lives in `resources/css/dashboard-kaca.css` (imported by `resources/css/app.css`) and `resources/js/dashboard/kaca.js`. `resources/js/app.js` registers `kacaAdmin`, `kacaRiver` and the `x-kaca-seg` directive; `flip`, `exit` and `springCurve` are module exports to import in new components. `kacaAdmin` is specific to the Admin dashboard and must not be reused.
- Render data on the server. A screen's data comes from a service in the `AdminKaca` style (`app/Services/<Modul>/`, one `build()` returning a view-shaped array, sample parts documented and tagged "Data demo"); views only loop and format (`App\Support\Format`). Charts are inline SVG computed in Blade/PHP; `App\Support\DotMatrix::layout()` draws impact-band numbers.
- Client-side state (filters over rendered rows, selected stage, drawer, palette) lives in Alpine; FLIP runs only on client-side changes. Lists changed by Livewire use `wire:key` on every row and fade in; never mix client and server filtering on one list.
- The Action feedback loop runs after the Livewire action returns success, never optimistically.
- Numbers are formatted with `Intl.NumberFormat('id-ID')` on every frame.
- Arbitrary classes used in `x-transition` must be written out in full in Blade so Tailwind generates them.

**Order of work for a screen.**

1. Wrapper (`.dash-kaca` + backdrop) and the region grid with plain structure.
2. Data service and one source of truth for counts, before visuals.
3. Glass cards and components from this file, tokens only.
4. Interactions: feedback loop, drill-downs with a way back, drawers.
5. Motion last, from the Kaca spring.
6. "Data demo" tags where data is sampled.
7. Verification checklist below, then `PANDUAN_KACA.md` §10.

**Verification checklist (all must pass).**

- [ ] Kaca: wrapper and backdrop present; sidebar glass; no white card left except alerts and controls; fallback checked with forced colours.
- [ ] Palette: identical with the device in dark mode; no dark overrides.
- [ ] Contrast: measured on the rendered screen (1440 px and narrow, several scroll positions); every text pair ≥ 4.5:1 (≥ 3:1 for large text), button labels and photo labels included; results in the PR.
- [ ] Capsule audit: no pill-shaped element in the content area besides the "Data demo" tag and count badges.
- [ ] Text tones: only ink and meta in body text; coloured text is semantic and follows the glass rules.
- [ ] 360 px: no horizontal page scroll; wide charts scroll inside their card.
- [ ] Counts: facts that appear twice are identical and come from one source.
- [ ] Interaction integrity: every stage, bar, tile and filter changes only its own panel.
- [ ] Motion by hand: rapid clicks bend motion without jumps; with reduced motion, positions stop and fades still run.
- [ ] Data honesty: every aggregate has a period, every percent a denominator and absolutes, every age a source, sample data is tagged.
- [ ] Accessibility: focus visible (2 px accent ring on glass), overlays trap focus and close on Esc, segmented controls expose `aria-pressed`, charts and legends operable by keyboard, 44 px targets on mobile, success announced in a live region.
