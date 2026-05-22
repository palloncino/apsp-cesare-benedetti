# APSP Google Map Shortcode Plugin

A WordPress plugin that adds a customizable Google Map embed via shortcode for APSP Grigno.

## Features

- ✅ Easy-to-use shortcode `[apsp_map]`
- ✅ Customizable address, size, zoom level, and map type
- ✅ Professional styling with responsive design
- ✅ TinyMCE editor button for easy insertion
- ✅ Admin settings page with usage instructions
- ✅ SEO-friendly and accessible
- ✅ Mobile-responsive design

## Installation

1. Upload the `apsp-google-map` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use the shortcode `[apsp_map]` in your posts and pages

## Usage

### Basic Usage
```
[apsp_map]
```
This will display a map with the default APSP Grigno address.

### Advanced Usage
```
[apsp_map address="Via Vittorio Emanuele, 131, 38055 Grigno" width="800" height="400" zoom="15" title="APSP Grigno"]
```

### Available Parameters

| Parameter | Default | Description |
|-----------|---------|-------------|
| `address` | `Via Vittorio Emanuele, 131, 38055 Grigno` | The address to display on the map |
| `width` | `600` | Map width in pixels |
| `height` | `450` | Map height in pixels |
| `zoom` | `14` | Zoom level (1-20) |
| `maptype` | `roadmap` | Map type: roadmap, satellite, hybrid, terrain |
| `title` | `APSP Grigno - La nostra sede` | Title displayed above the map |

### Examples

**Default map:**
```
[apsp_map]
```

**Large satellite map:**
```
[apsp_map width="800" height="500" maptype="satellite" zoom="16"]
```

**Custom location:**
```
[apsp_map address="Piazza del Duomo, Milano" title="Duomo di Milano"]
```

## Admin Interface

The plugin adds an "APSP Map" menu item under Settings in the WordPress admin. This page provides:
- Usage instructions
- Parameter documentation
- Example shortcodes

## TinyMCE Editor Button

When editing posts or pages, you'll see an "APSP Map" button in the toolbar. Click it to:
- Open a dialog with all map options
- Configure the map settings visually
- Insert the shortcode automatically

## Styling

The plugin includes professional CSS styling with:
- Responsive design for mobile devices
- Hover effects and animations
- Professional color scheme
- Accessibility improvements
- Print-friendly styles

## Technical Details

- **Plugin Name**: APSP Google Map Shortcode
- **Version**: 1.0.0
- **Author**: Antonio Guiotto
- **License**: GPLv2 or later
- **Requires**: WordPress 5.0+
- **Tested**: WordPress 6.8

## Support

For support or questions, contact the development team at APSP Grigno.

## Changelog

### Version 1.0.0
- Initial release
- Basic shortcode functionality
- Admin interface
- TinyMCE button
- Responsive styling 