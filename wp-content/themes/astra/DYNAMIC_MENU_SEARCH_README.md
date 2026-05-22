# Dynamic Menu Search Feature

## Overview

The Dynamic Menu plugin now includes a search feature that allows users to filter menu items in real-time. The search functionality is enabled by default but can be disabled if needed.

## How It Works

The search feature filters the dynamic menu results based on what you type. It searches through both parent menu items and their children, showing only the items that match your search term.

### Search Behavior

1. **Real-time filtering**: As you type, the menu items are filtered immediately
2. **Case-insensitive**: The search is not case-sensitive
3. **Partial matching**: Items are shown if they contain the search term anywhere in their text
4. **Parent-child relationship**: If a child item matches, its parent is also shown
5. **Empty search**: When the search field is empty, all items are shown

## Usage

### Shortcode Usage

The search feature can be controlled via the shortcode parameter:

```php
// With search enabled (default)
[at_dynamic_menu title="Amministrazione Trasparente"]

// With search disabled
[at_dynamic_menu title="Amministrazione Trasparente" search="false"]
```

### Widget Usage

When using the widget in the sidebar:

1. Go to **Appearance → Widgets**
2. Add the "Amministrazione Trasparente (Dynamic)" widget
3. Configure the title
4. Set "Mostra ricerca" to "Sì" (Yes) or "No" (No)
5. Save the widget

## Features

### Search Input Field

- **Placeholder text**: "Cerca nel menu..." (Search in menu...)
- **Styling**: Clean, modern design that matches the theme
- **Focus states**: Blue border when focused
- **Responsive**: Full width on all devices

### JavaScript Functionality

- **jQuery-based**: Uses jQuery for cross-browser compatibility
- **Event handling**: Responds to input events
- **Performance**: Efficient filtering without page reloads
- **Smooth transitions**: CSS transitions for showing/hiding items

### CSS Styling

```css
.at-dynamic-menu-search {
    margin-bottom: 15px;
}
.at-dynamic-menu-search-field {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}
.at-dynamic-menu-search-field:focus {
    outline: none;
    border-color: #0073aa;
    box-shadow: 0 0 0 1px #0073aa;
}
.at-dynamic-menu-widget .nav li {
    transition: opacity 0.2s ease;
}
```

## Technical Implementation

### Shortcode Parameters

- `title`: Custom title for the menu (optional)
- `search`: Enable/disable search feature ("true" or "false", default: "true")

### Widget Settings

- **Title**: Custom title for the menu
- **Mostra ricerca**: Show/hide search field ("Sì" or "No")

### JavaScript Logic

1. **Event listener**: Listens for input events on the search field
2. **Text matching**: Uses `includes()` method for case-insensitive matching
3. **DOM manipulation**: Shows/hides menu items based on search results
4. **Child handling**: Shows parent items if children match the search

### Database Structure

The search feature works with the existing menu structure stored in the WordPress options table:

- **Option name**: `at_dynamic_menu_structure`
- **Data format**: Array of menu items with nested children
- **Search scope**: Searches through item labels only

## Examples

### Example 1: Basic Usage
```php
[at_dynamic_menu]
```
Shows the menu with search enabled by default.

### Example 2: Custom Title with Search
```php
[at_dynamic_menu title="Menu Amministrazione" search="true"]
```
Shows the menu with a custom title and search enabled.

### Example 3: Menu Without Search
```php
[at_dynamic_menu title="Menu Amministrazione" search="false"]
```
Shows the menu with a custom title but without the search feature.

## Browser Compatibility

- **Chrome**: Full support
- **Firefox**: Full support
- **Safari**: Full support
- **Edge**: Full support
- **Internet Explorer**: 11+ (with jQuery)

## Performance Considerations

- **Lightweight**: Minimal JavaScript code
- **No AJAX**: All filtering happens client-side
- **Efficient**: Uses native DOM methods for performance
- **Memory friendly**: No unnecessary event listeners

## Troubleshooting

### Search Not Working

1. **Check jQuery**: Ensure jQuery is loaded on the page
2. **Check console**: Look for JavaScript errors in browser console
3. **Check IDs**: Ensure unique IDs for multiple widgets on the same page
4. **Check CSS**: Ensure CSS is not hiding the search field

### Search Field Not Visible

1. **Check shortcode parameter**: Ensure `search="true"` or parameter is omitted
2. **Check widget settings**: Ensure "Mostra ricerca" is set to "Sì"
3. **Check CSS**: Ensure no CSS is hiding the search container

### Items Not Filtering

1. **Check menu structure**: Ensure menu items exist in the database
2. **Check JavaScript**: Look for console errors
3. **Check selectors**: Ensure CSS selectors match the menu structure

## Future Enhancements

Potential improvements for future versions:

- **Highlight matching text**: Highlight the matched search terms
- **Search in content**: Search in page content, not just menu labels
- **Advanced filters**: Filter by page type, date, etc.
- **Search history**: Remember recent searches
- **Keyboard navigation**: Navigate results with arrow keys
- **Search suggestions**: Auto-complete based on menu items 