# De Wit Catalog Theme conventions

This is a classic WordPress theme with WooCommerce as the data and commerce layer.
Elementor is not a runtime dependency of the catalog target.

## Architecture

WorkBase/API import → WooCommerce product data → theme query/API → small template parts → theme CSS/JavaScript.

Use WordPress and WooCommerce APIs (`WP_Query`, `get_terms()`, `wc_get_product()`, and escaping helpers) instead of direct database queries or page-builder markup.

## Naming and output

- Prefix PHP functions and hooks with `dewit_theme_`.
- Use WordPress-style snake_case for PHP names.
- Escape text, URLs, attributes, and permitted HTML at the output boundary.
- Keep markup semantic and accessible.
- Use `dewit-` BEM-like classes for theme components; avoid generic selectors.
- Keep JavaScript scoped in closures/modules and enqueue it through WordPress.

## Change order

Prefer, in order:

1. A WordPress/WooCommerce hook or filter.
2. A small theme template part.
3. A narrowly scoped WooCommerce template override only when necessary.

Do not reintroduce Elementor selectors or hard-coded Elementor IDs into the Elementor-free catalog.

## Local workflow

The LocalWP theme path is a junction to this worktree. Edit this worktree, inspect the local site, run syntax checks, then commit the feature branch only after review. The live `main` theme is the fallback and must not be changed by feature work.
