# Design System & UI Specifications (DESIGN_SYSTEM.md)
## Project: Invoicy by RedMaple — Commercial Multi-Tenant Invoicing SaaS

**Document Version:** 2.0.0  
**Status:** Approved & Live in Production  
**Design Standard:** Modern SaaS Utility System (Tailwind CSS 3.x + Phosphor Icons)  

---

## 1. Brand Philosophy & Design Principles

1. **Financial Clarity & Trust:** Financial interfaces must never create ambiguity. Numbers, currency symbols, and invoice totals must have prominent typographic weight, clear tabular alignment, and zero visual clutter.
2. **Speed & Zero Cognitive Friction:** Interactions must feel instantaneous. High-frequency actions (adding line items, switching tabs, issuing invoices) should occur with zero perceived lag.
3. **Global Inclusivity (LTR & RTL):** Layouts must gracefully support both Left-to-Right (English, Spanish, French, German, Turkish, Hindi, Chinese) and Right-to-Left (Urdu, Arabic) without broken flex directions or misplaced icons.
4. **Hierarchy & Purposeful Color:** Color is never purely decorative. Crimson/Red signals the brand and critical primary triggers; Emerald indicates paid balances and positive growth; Amber signals pending or suspended items; Rose denotes overdue or canceled states.

---

## 2. Color Palette & Token Architecture

### 2.1 Primary Brand Crimson
The hallmark brand color of Invoicy by RedMaple.

| Token | Hex Value | Usage |
| :--- | :--- | :--- |
| `red-50` | `#fef2f2` | Primary active pill backgrounds, subtle table highlights |
| `red-100` | `#fee2e2` | Hover borders, badge backgrounds |
| `red-500` | `#ef4444` | Vector icon highlights |
| `red-600` | `#dc2626` | **Core Primary:** Buttons, active navigation pills, brand leaf |
| `red-700` | `#b91c1c` | Button hover state, pressed states |
| `red-900` | `#7f1d1d` | High-contrast dark brand accents |

### 2.2 Deep Slate Neutrals
Provides a grounded, authoritative backdrop for sidebar navigation and typography.

| Token | Hex Value | Usage |
| :--- | :--- | :--- |
| `slate-50` | `#f8fafc` | Application global background |
| `slate-100` | `#f1f5f9` | Card header backgrounds, subtle button backgrounds |
| `slate-200` | `#e2e8f0` | Standard border lines, divider rules |
| `slate-300` | `#cbd5e1` | Input borders, icon strokes |
| `slate-400` | `#94a3b8` | Placeholder text, secondary metadata |
| `slate-500` | `#64748b` | Field labels, descriptive subtext |
| `slate-700` | `#334155` | Secondary button labels, body text |
| `slate-800` | `#1e293b` | Dark container backgrounds, sidebar dividers |
| `slate-900` | `#0f172a` | **Sidebar Background:** Primary dark surface, main headlines |
| `slate-950` | `#020617` | Highest contrast text, deep modal overlays |

### 2.3 Semantic Status Palette

```
  EMERALD (Success / Active / Paid)
  ├── 50:  #ecfdf5  ─── Badge Background
  ├── 200: #a7f3d0  ─── Badge Border
  └── 700: #047857  ─── Text & Icons

  AMBER (Warning / Suspended / Trialing)
  ├── 50:  #fffbeb  ─── Badge Background
  ├── 200: #fde68a  ─── Badge Border
  └── 700: #b45309  ─── Text & Icons

  ROSE (Danger / Overdue / Canceled)
  ├── 50:  #fff1f2  ─── Badge Background
  ├── 200: #fecdd3  ─── Badge Border
  └── 700: #be123c  ─── Text & Icons

  SKY / PURPLE (Info / Pro Plan / Enterprise)
  ├── Sky-50:    #f0f9ff  ─── Starter Tier
  ├── Amber-50:  #fefce8  ─── Pro Business Tier
  └── Purple-50: #faf5ff  ─── Enterprise Scale Tier
```

---

## 3. Typography Scale & Font Architecture

### 3.1 Font Families
- **Primary Body & UI Stack:**  
  `system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif`
- **Monospace Financial Stack (Invoice Numbers, Currency, Tokens):**  
  `ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', monospace`
- **Right-to-Left (RTL) Localization:**  
  `'Noto Sans Arabic', 'Noto Nastaliq Urdu', Tahoma, sans-serif`

### 3.2 Type Scale

| Class | Font Size | Line Height | Typical Usage |
| :--- | :--- | :--- | :--- |
| `text-[10px]` | 10px | 14px | Micro tags, uppercase column headers, badge counters |
| `text-xs` | 12px | 16px | Field labels, table data cells, helper captions |
| `text-sm` | 14px | 20px | Navigation items, body paragraphs, form inputs |
| `text-base` | 16px | 24px | Card subheadings, modal titles, summary labels |
| `text-lg` | 18px | 28px | Section titles, plan names |
| `text-xl` | 20px | 28px | Dashboard metric numbers, modal headers |
| `text-2xl` | 24px | 32px | Page titles, major invoice totals |
| `text-3xl` | 30px | 36px | Pricing table figures, hero metrics |

---

## 4. Iconography Standards

Invoicy utilizes **Phosphor Icons (`@phosphor-icons/web@2.1.1`)**:

1. **Primary Navigation & Controls:** Dual-weight system. Regular (`ph`) for passive states, Bold (`ph-bold`) for active selection.
2. **Standard Sizing:**
   - Small (`text-sm` / 14px): Inside action buttons, table cell actions, list bullet points.
   - Medium (`text-lg` / 18px): Navigation sidebar links, form input prefixes.
   - Large (`text-2xl` / 24px): Metric KPI headers, modal headers.
3. **Semantic Icon Mapping:**
   - `ph-receipt`: Invoices
   - `ph-crown`: Pro Plans, Subscriptions
   - `ph-users`: Client CRM
   - `ph-bell-ringing`: Automated Reminders
   - `ph-arrow-u-up-left`: Credit Notes
   - `ph-package`: Catalog & Presets
   - `ph-prohibit`: Cancel Subscription
   - `ph-check-circle`: Paid, Active, Verified

---

## 5. UI Component Specifications

### 5.1 Action Buttons

```html
<!-- Primary Brand Button -->
<button class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2">
    <i class="ph ph-check text-sm"></i> Save Changes
</button>

<!-- Secondary Neutral Button -->
<button class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-200 transition">
    Cancel
</button>

<!-- Danger / Cancel Action Button -->
<button class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition flex items-center gap-1 shadow-sm">
    <i class="ph ph-prohibit text-sm"></i> Cancel
</button>
```

### 5.2 Status Badges

```html
<!-- Active / Paid Badge -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
    Active
</span>

<!-- Suspended Badge -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 uppercase tracking-wider">
    Suspended
</span>

<!-- Canceled Badge -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 uppercase tracking-wider">
    Canceled
</span>
```

### 5.3 Form Inputs
All form inputs maintain a uniform height, border radius, and active red focus ring:
```html
<input type="text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold bg-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition">
```

---

## 6. Layout Spacing, Elevation & Corner Radii

- **Card Radius:** Standard `rounded-2xl` (16px) across cards, dashboards, and tables.
- **Inner Element Radius:** `rounded-xl` (12px) for inputs, buttons, and alert boxes.
- **Pills & Badges:** `rounded-full` (9999px) for status badges and tags.
- **Shadow Elevations:**
  - `shadow-sm`: Standard cards, table rows, button states.
  - `shadow-md`: Floating panels, active pricing cards.
  - `shadow-xl`: Modal dialogs, dropdown menus.
  - `shadow-2xl`: High-focus confirmation cards.

---

## 7. Internationalization (RTL) Guidelines

When the user selects Urdu or Arabic, the layout shifts to `dir="rtl"`:
1. **Sidebar Position:** Stays docked to the logical start edge.
2. **Text Alignment:** `text-left` becomes `text-right` dynamically.
3. **Numerical Tables:** Numerical balances and monetary figures retain Left-to-Right (`ltr-text`) rendering to preserve integer readability:
   ```css
   [dir="rtl"] { text-align: right; }
   [dir="rtl"] .ltr-text { direction: ltr; text-align: left; }
   ```
4. **Icons:** Directional chevrons (`ph-caret-right` / `ph-caret-left`) flip automatically.
