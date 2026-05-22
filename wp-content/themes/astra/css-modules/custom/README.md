# Dynamic CSS System

This folder contains custom CSS files that are automatically loaded by WordPress.

## How It Works

1. **Automatic Loading**: All `.css` files in this folder are automatically loaded by WordPress
2. **Dynamic Discovery**: New files are automatically detected and loaded
3. **Version Control**: Each file gets its own version based on modification time
4. **Editor Integration**: All files can be edited through the CSS Editor plugin

## File Structure

```
css-modules/custom/
├── example.css          # Example custom file
├── my-styles.css        # Your custom styles
├── components.css       # Component styles
└── README.md           # This file
```

## Creating New Files

### Method 1: CSS Editor Plugin
1. Go to WordPress Admin → CSS Editor
2. In the sidebar, enter a filename (e.g., `my-styles.css`)
3. Click "Create File"
4. The file will be created with a default template

### Method 2: Manual Creation
1. Create a new `.css` file in this folder
2. Add your CSS content
3. The file will be automatically loaded on the next page refresh

## File Naming Convention

- Use descriptive names: `header-styles.css`, `footer-custom.css`
- Use hyphens or underscores: `my-styles.css` or `my_styles.css`
- Always include `.css` extension

## Default Template

When creating files through the CSS Editor, you'll get this template:

```css
/**
 * Custom CSS File: filename.css
 * Created: 2025-01-27 10:30:00
 */

/* ===========================================
   CUSTOM STYLES
   =========================================== */

/* Add your custom styles here */

/* Responsive Design */
@media (max-width: 768px) {
    /* Mobile styles */
}
```

## Best Practices

1. **Organize by Purpose**: Create separate files for different sections
2. **Use Comments**: Document your CSS with clear comments
3. **Responsive Design**: Always include mobile styles
4. **Naming**: Use clear, descriptive filenames
5. **Backup**: Use the CSS Editor's backup feature

## Examples

### Header Styles (`header-custom.css`)
```css
/**
 * Custom Header Styles
 */

.custom-header {
    background: #2c3e50;
    padding: 20px 0;
}

.custom-header h1 {
    color: #ffffff;
    font-size: 2rem;
}
```

### Component Styles (`components.css`)
```css
/**
 * Custom Component Styles
 */

.custom-button {
    background: #3498db;
    color: #ffffff;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
}

.custom-card {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 20px;
}
```

## Management

- **Edit**: Use the CSS Editor plugin
- **Delete**: Use the delete button in CSS Editor (only for custom files)
- **Backup**: Automatic backups before saving
- **Restore**: Restore from any previous backup

## Technical Details

- **Location**: `/wp-content/themes/astra/css-modules/custom/`
- **Auto-loading**: Handled by `functions.php`
- **Versioning**: Based on file modification time
- **Security**: Only users with `edit_theme_options` capability can edit
- **Backup Location**: `/wp-content/apsp-css-backups/`

## Troubleshooting

**File not loading?**
- Check file permissions (should be 644)
- Ensure `.css` extension
- Clear WordPress cache

**Editor not working?**
- Check user permissions
- Verify plugin is activated
- Check browser console for errors

**File not saving?**
- Check disk space
- Verify file permissions
- Check WordPress debug log 