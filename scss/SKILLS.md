
# SCSS Maintenance Notes

## File organization

- Keep sections in page-flow order: shell, navigation, title/meta, media, main content, supporting content, and exploration/progression.
- Use one clear divider per section and short headings that describe the rendered responsibility.
- Keep component modifiers and their nested states beside the component they modify.
- Keep WordPress/editor overrides nested under the page component that owns them; this limits specificity and prevents style leakage.

## Formatting

- Use four spaces for indentation and preserve the existing brace style.
- Order declarations consistently: layout/display, sizing, spacing, typography, color, positioning, then interaction/animation details where practical.
- Use logical properties such as `inline-size`, `block-size`, `margin-block`, and `padding-inline` for writing-mode and responsive safety.
- Prefer existing design tokens (`var(--ui-...)`, `var(--spacing-...)`, and Sass variables) over new literal values.
- Keep comments short and explain constraints, browser fallbacks, specificity decisions, or non-obvious layout math rather than narrating declarations.

## Techniques used here

- Preserve behavior during cleanup by keeping selectors, values, nesting, media queries, and rule order intact unless a cascade change is intentional.
- Use `@supports` for progressive enhancement so the base layout remains functional without scroll-driven animation support.
- Use `:is()` and `:where()` to share rules while controlling specificity deliberately.
- Use logical sizing and safe-area insets for resilient mobile layouts.
- Use grid for the lesson/content-aside relationship and flex for one-dimensional controls and stacked regions.
- Use stable aspect ratios and overflow rules for embedded media and sticky regions.
- Compile the Sass entrypoint after edits, then inspect the generated diff for unintended selector or value changes.
