# Access Statistics Plugin

A lightweight WordPress plugin for tracking and displaying website access statistics with automatic year reset functionality.

## Features

### 📊 **Automatic Statistics Tracking**
- Tracks all website visits automatically
- Counts unique visitors based on IP address (one count per IP per day)
- Excludes bots and crawlers from statistics
- Updates statistics daily via cron job

### 📆 **Automatic Year Reset**
- **No manual intervention required** - counters reset automatically every January 1st
- Detects year changes on every page load (frontend and backend)
- Clears all visit data and starts fresh for the new year
- Uses WordPress options to track the current year
- **Simple and lightweight** - no complex database structures
- **Historical data preserved** - previous years' data remains intact

### 🎯 **Current Year Focus**
- Admin dashboard shows "Statistiche per il [CURRENT_YEAR]" (Italian)
- Current year statistics appear only when actual visits are recorded
- Shortcodes default to current year when no year is specified
- Form automatically pre-fills with current year
- Real-time updates as new visits are tracked

### 📝 **Shortcodes**
- `[access_stats]` - Shows all available statistics (all years) in table format
- `[access_stats year="2025"]` - Shows statistics for specific year
- `[access_total_visits]` - Shows total visits for current year
- `[access_unique_visits]` - Shows unique visitors for current year

### 🎨 **Admin Interface**
- Clean, modern dashboard design
- Current year prominently displayed in Italian
- Real-time statistics table with automatic updates
- Shortcode reference moved to bottom for better UX
- Visual separation between main content and reference material

## Installation

1. Upload the plugin files to `/wp-content/plugins/access-statistics/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access the plugin via 'Statistiche Accessi' in the admin menu

## Usage

### Automatic Tracking
The plugin automatically tracks all website visits. No configuration needed.

### Manual Statistics
1. Go to 'Statistiche Accessi' in the admin menu
2. Use the form to add manual statistics for the current year
3. Statistics are automatically associated with the current year

### Displaying Statistics
Use shortcodes in your pages, posts, or widgets:

```
[access_stats]                    // All available statistics (all years)
[access_stats year="2025"]        // Specific year statistics
[access_total_visits]             // Total visits this year
[access_unique_visits]            // Unique visitors this year
```

## Technical Details

### Database Tables
- `wp_access_statistics` - Manual statistics data
- `wp_access_statistics_visits` - Automatic visit tracking

### Automatic Reset Process
1. On every page load, the plugin checks if the current year differs from the stored year
2. If a year change is detected:
   - All visit data is cleared (TRUNCATE)
   - Manual statistics for the new year are cleared
   - The stored year is updated
3. Statistics start counting from zero for the new year

### Real-time Updates
- Statistics are updated immediately when new visits are tracked
- Current year entries are created only when actual visits occur
- Admin table shows live data including automatic statistics
- Shortcodes always return current year values
- No manual refresh required

### Performance
- Lightweight implementation with minimal database overhead
- Historical data preserved for reference
- Efficient queries with proper indexing
- Bot detection to avoid counting crawlers

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Version History

### v1.0.13
- ✅ Added automatic year detection and counter reset
- ✅ Display current year in admin dashboard (Italian translation)
- ✅ Moved shortcode legend to bottom
- ✅ Focus on current year statistics only
- ✅ Simplified implementation (no complex archives)
- ✅ Real-time statistics updates
- ✅ Dynamic admin table with live data
- ✅ Historical data preloaded (2017-2024)
- ✅ Current year entries created only when visits occur

### v1.0.12
- Initial release with basic statistics tracking

## Support

For support or feature requests, please contact the plugin developers.

## License

GPL v2 or later 