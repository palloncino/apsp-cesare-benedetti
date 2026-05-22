# APSP CSS Editor Plugin

A WordPress plugin that allows direct editing of theme CSS files from the WordPress admin with a professional interface and sidebar button.

## Features

- ✅ **Direct CSS Editing**: Edit theme's style.css file directly from WordPress admin
- ✅ **Admin Bar Button**: Quick access via WordPress admin bar
- ✅ **Auto Backup**: Automatic backup before saving changes
- ✅ **Manual Backup**: Create manual backups anytime
- ✅ **Restore Function**: Restore from any previous backup
- ✅ **CSS Snippets**: Pre-built CSS snippets for common elements
- ✅ **Live Preview**: Preview CSS changes in a new window
- ✅ **Keyboard Shortcuts**: Ctrl+S to save, Tab to indent
- ✅ **Responsive Design**: Works on desktop and mobile
- ✅ **Professional Interface**: Dark theme editor with syntax highlighting
- ✅ **File Information**: Shows file size, last modified date

## Installation

1. Upload the `apsp-css-editor` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access via "CSS Editor" in the admin menu or admin bar button

## Usage

### Accessing the Editor

**Method 1: Admin Menu**
- Go to WordPress Admin → CSS Editor

**Method 2: Admin Bar**
- Click "CSS Editor" button in the top admin bar

### Basic Operations

1. **Edit CSS**: Type or paste your CSS in the editor
2. **Save**: Click "Salva CSS" or press Ctrl+S
3. **Backup**: Click "Crea Backup" to create a manual backup
4. **Preview**: Click "Anteprima" to see changes in a new window

### CSS Snippets

Use the sidebar buttons to insert common CSS patterns:
- **Hero Section**: Complete hero section styling
- **Responsive**: Mobile-responsive design rules
- **Buttons**: Professional button styling
- **Cards**: Card component styling

### Backup & Restore

**Creating Backups:**
- Automatic backup before each save
- Manual backup via "Crea Backup" button

**Restoring Backups:**
1. Select a backup from the dropdown
2. Click "Ripristina Backup"
3. Confirm the action

### Keyboard Shortcuts

- `Ctrl+S` - Save CSS
- `Ctrl+Z` - Undo (browser default)
- `Ctrl+F` - Find (browser default)
- `Tab` - Indent (4 spaces)

## File Structure

```
apsp-css-editor/
├── apsp-css-editor.php      # Main plugin file
├── css/
│   └── admin.css            # Admin interface styles
├── js/
│   └── admin.js             # Admin functionality
└── README.md                # This file
```

## Security Features

- **Permission Check**: Only users with 'edit_theme_options' capability can access
- **Nonce Verification**: All AJAX requests are secured with nonces
- **File Validation**: Validates file paths and permissions
- **Backup Safety**: All backups are stored in wp-content directory

## Backup Storage

Backups are stored in: `/wp-content/apsp-css-backups/`
- Format: `style-backup-YYYY-MM-DD-HH-MM-SS.css`
- Automatic cleanup recommended (manual)

## Technical Details

- **Plugin Name**: APSP CSS Editor
- **Version**: 1.0.0
- **Author**: Antonio Guiotto
- **License**: GPLv2 or later
- **Requires**: WordPress 5.0+
- **Tested**: WordPress 6.8

## Browser Compatibility

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Troubleshooting

### Common Issues

**"Permission Denied" Error:**
- Ensure the theme's style.css file is writable
- Check file permissions (should be 644)

**Backup Not Working:**
- Verify wp-content directory is writable
- Check available disk space

**Editor Not Loading:**
- Disable other plugins temporarily
- Check browser console for JavaScript errors

### File Permissions

```bash
# Set correct permissions
chmod 644 wp-content/themes/your-theme/style.css
chmod 755 wp-content/apsp-css-backups/
```

## Support

For support or questions, contact the development team at APSP Grigno.

## Changelog

### Version 1.0.0
- Initial release
- Direct CSS editing functionality
- Backup and restore system
- CSS snippets library
- Admin bar integration
- Professional interface design 