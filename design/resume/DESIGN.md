---
name: Epicurean Modern
colors:
  surface: '#f9f9f9'
  surface-dim: '#dadada'
  surface-bright: '#f9f9f9'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f3f4'
  surface-container: '#eeeeee'
  surface-container-high: '#e8e8e8'
  surface-container-highest: '#e2e2e2'
  on-surface: '#1a1c1c'
  on-surface-variant: '#44474c'
  inverse-surface: '#2f3131'
  inverse-on-surface: '#f0f1f1'
  outline: '#74777d'
  outline-variant: '#c4c6cd'
  surface-tint: '#505f76'
  primary: '#08192c'
  on-primary: '#ffffff'
  primary-container: '#1e2e42'
  on-primary-container: '#8596ae'
  inverse-primary: '#b7c8e1'
  secondary: '#a23f00'
  on-secondary: '#ffffff'
  secondary-container: '#fc7127'
  on-secondary-container: '#5c2000'
  tertiary: '#141920'
  on-tertiary: '#ffffff'
  tertiary-container: '#282e35'
  on-tertiary-container: '#90959e'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#d3e4fe'
  primary-fixed-dim: '#b7c8e1'
  on-primary-fixed: '#0b1c30'
  on-primary-fixed-variant: '#38485d'
  secondary-fixed: '#ffdbcd'
  secondary-fixed-dim: '#ffb595'
  on-secondary-fixed: '#351000'
  on-secondary-fixed-variant: '#7c2e00'
  tertiary-fixed: '#dee3ed'
  tertiary-fixed-dim: '#c1c7d0'
  on-tertiary-fixed: '#161c23'
  on-tertiary-fixed-variant: '#41474f'
  background: '#f9f9f9'
  on-background: '#1a1c1c'
  surface-variant: '#e2e2e2'
  terracotta-accent: '#D35400'
  deep-navy: '#1E2E42'
  soft-blue-gray: '#E8EDF7'
  slate-text: '#4A5568'
typography:
  display-lg:
    fontFamily: Hanken Grotesk
    fontSize: 48px
    fontWeight: '700'
    lineHeight: 56px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Hanken Grotesk
    fontSize: 32px
    fontWeight: '600'
    lineHeight: 40px
  headline-lg-mobile:
    fontFamily: Hanken Grotesk
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-md:
    fontFamily: Hanken Grotesk
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Source Sans 3
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Source Sans 3
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  label-lg:
    fontFamily: Hanken Grotesk
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 20px
    letterSpacing: 0.05em
  label-md:
    fontFamily: Hanken Grotesk
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  unit: 8px
  container-max-width: 1280px
  gutter: 24px
  margin-desktop: 64px
  margin-mobile: 16px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 32px
---

## Brand & Style

This design system is built for a premium, digital-first dining experience. It balances the high-utility expectations of modern POS systems with the atmospheric elegance of fine dining. The aesthetic is rooted in **Minimalism** with a **Corporate Modern** structure, ensuring that the interface never competes with high-quality food photography.

The target audience is the discerning diner who values speed and clarity but expects a sophisticated brand presence. The UI should evoke a sense of calm, precision, and appetite-driven luxury. Visual weight is distributed through generous whitespace, crisp typography, and intentional use of a single vibrant accent color to guide the user's journey from discovery to checkout.

## Colors

The color palette is anchored by **Deep Navy (#1E2E42)** for primary structural elements and high-level information, providing a more sophisticated alternative to pure black. The **Terracotta Accent (#D35400)** is used sparingly for high-value actions (Add to Cart, Checkout) and to highlight seasonal specialties, drawing on the warmth of clay and fire.

**Soft Blue Gray (#E8EDF7)** serves as the primary background for secondary containers, creating subtle distinction without the harshness of high-contrast borders. Typography primarily uses Deep Navy for headings and Slate Text for body copy to maintain a soft, readable hierarchy.

## Typography

The typography system uses **Hanken Grotesk** for headings and interactive labels to provide a sharp, modern, and slightly technical feel reminiscent of high-end fintech and POS systems. **Source Sans 3** is utilized for body text and descriptions, chosen for its exceptional legibility and neutral character, ensuring menu descriptions are easy to scan on small screens.

To maintain the "premium" feel, display headings use tight letter spacing and bold weights. Label styles for dietary tags (e.g., VEGAN, GLUTEN-FREE) use uppercase with increased letter spacing to ensure they are distinct from functional UI labels.

## Layout & Spacing

This design system employs a **Fluid Grid** model with a fixed maximum container width for desktop viewing to preserve the editorial feel of the menu. 

- **Desktop (1280px+):** A 12-column grid with 24px gutters. Product cards typically span 3 or 4 columns.
- **Tablet (768px - 1024px):** An 8-column grid with 20px gutters.
- **Mobile (<768px):** A 4-column fluid grid. Menu categories reflow into a horizontal scrolling tab bar for thumb-friendly navigation.

Spacing follows an 8px base unit. Generous vertical spacing (stack-lg) is used between menu sections to prevent visual clutter, while related information like price and dish name use tight spacing (stack-sm) to create clear grouping.

## Elevation & Depth

Visual hierarchy is established primarily through **Tonal Layers** and **Low-contrast outlines** rather than heavy shadows. 

- **Level 0 (Background):** Pure White (#FFFFFF) for the main canvas.
- **Level 1 (Cards/Containers):** Soft Blue Gray (#E8EDF7) backgrounds or 1px borders in the same shade.
- **Level 2 (Active States/Floating Buttons):** Uses an "Ambient Shadow" — a very soft, high-diffusion shadow (0px 8px 24px) with a 5% opacity of the Deep Navy color. This is reserved for persistent elements like the "View Order" floating bar.

This approach keeps the UI feeling light and "app-like," prioritizing the vivid colors of the food photography over artificial depth.

## Shapes

The shape language is **Rounded**, utilizing a 0.5rem (8px) base radius. This creates a friendly and accessible feel that balances the sharp typography. 

- **Standard Elements:** Buttons, input fields, and small dish cards use 8px corners.
- **Large Elements:** Featured "Hero" cards or category banners use `rounded-lg` (16px) to feel more inviting.
- **Special Elements:** Search bars and "Add" buttons may utilize a full pill-shape (999px) to indicate high interactivity and tactile ease.

## Components

### Buttons
- **Primary:** Terracotta background with white text. High-contrast, 8px rounded corners. Used for "Add to Order" and "Checkout."
- **Secondary:** Deep Navy outline with Deep Navy text. Used for "Customize" or "View Details."
- **Ghost:** No background or border, used for utility actions like "Clear All."

### Menu Cards
- **Structure:** Top-aligned image (aspect ratio 4:3 or 1:1), followed by the dish name in `headline-md` and description in `body-md`. 
- **Price:** Positioned at the bottom right in `label-lg` using the Terracotta color for emphasis.

### Chips & Tags
- Used for dietary restrictions or "Chef's Choice." 
- Style: Background color set to a 10% opacity version of the Deep Navy, with text in `label-md`.

### Input Fields
- Flat design with a Soft Blue Gray background. 
- Bottom-border only focus state in Terracotta to minimize visual noise while typing special instructions.

### Navigation
- A persistent "Bottom Sheet" or floating bar on mobile for the "View Order" action, using a blur-behind (Backdrop Filter) effect to maintain context of the menu beneath.