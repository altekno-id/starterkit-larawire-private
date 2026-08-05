---
description: Conventions for creating and modifying Livewire PowerGrid tables
---

# Livewire PowerGrid Conventions

When generating or modifying Livewire PowerGrid components in this starter kit, strictly follow these rules:

### 1. Action Buttons & Dropdowns
*   **Icon Only**: Row action buttons must be icon-only (e.g., vertical dots or chevron down). Do not use text labels or borders.
*   **Alpine.js**: Always use Alpine.js for dropdowns (`x-data`, `@click`, etc.). **Do not** use Bootstrap's `data-bs-toggle="dropdown"`, as it conflicts with Livewire's DOM morphing.
*   **Teleport**: Dropdown menus must use `x-teleport="body"` to prevent z-index clipping and table overflow issues.

### 2. Column Ordering & Terminology
*   **Action Column Position**: The action column must be placed at the 2nd index, right after the checkbox. In the code, this means `Column::action('Aksi')` should be defined as the very first element in the `columns()` array (since PowerGrid automatically prepends the Checkbox column).
*   **Action Column Label**: Always use the label "Aksi", not "Aksi Massal" or other variations.

### 3. Filters
*   **Date/Time Ranges**: Any column displaying time or date data must use a date range picker. Always append `->params(['mode' => 'range'])` to the `Filter::datetimepicker` or `Filter::datepicker` method.

*(Note: Global UI styling, such as Tabler colors, header borders, margin alignments, and "Clear All" translations, are handled automatically by the global `starter.css` file and do not require manual intervention.)*
