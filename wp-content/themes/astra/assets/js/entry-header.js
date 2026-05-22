/**
 * Entry Header Component
 * Handles breadcrumb generation and header restructuring
 */
jQuery(document).ready(function($) {
    // Add breadcrumb and restructure entry header
    $('.entry-header').each(function() {
        var $header = $(this);
        var $title = $header.find('.page-title');
        var $meta = $header.find('.page-meta');
        
        if ($title.length && $meta.length) {
            // Generate breadcrumb
            var breadcrumb = generateBreadcrumb();
            
            // Restructure HTML with left/right layout
            var newHtml = '<div class="header-top">' +
                '<div class="header-left">' +
                '<h1 class="page-title">' + $title.html() + '</h1>' +
                '<div class="header-bottom">' +
                '<div class="breadcrumb">' + breadcrumb + '</div>' +
                '</div>' +
                '</div>' +
                '<div class="header-right">' +
                '<div class="page-meta">' + $meta.html() + '</div>' +
                '</div>' +
                '</div>';
            
            $header.html(newHtml);
        }
    });
    
    /**
     * Generate breadcrumb navigation based on actual page hierarchy
     */
    function generateBreadcrumb() {
        var breadcrumb = '';
        
        // Check if we have breadcrumb data from PHP
        if (typeof apspBreadcrumbData !== 'undefined' && apspBreadcrumbData.breadcrumbs && apspBreadcrumbData.breadcrumbs.length > 0) {
            // Use the breadcrumb data from PHP (based on actual page hierarchy)
            var breadcrumbs = apspBreadcrumbData.breadcrumbs;
            
            for (var i = 0; i < breadcrumbs.length; i++) {
                var item = breadcrumbs[i];
                
                if (i > 0) {
                    breadcrumb += '<span class="breadcrumb-separator">›</span>';
                }
                
                // If it's the current page, don't make it a link
                if (item.current) {
                    breadcrumb += '<span>' + item.title + '</span>';
                } else {
                    breadcrumb += '<a href="' + item.url + '">' + item.title + '</a>';
                }
            }
        } else {
            // Fallback: just show Home and current page title
            var homeUrl = (typeof apspBreadcrumbData !== 'undefined' && apspBreadcrumbData.homeUrl) 
                ? apspBreadcrumbData.homeUrl 
                : '/';
            breadcrumb = '<a href="' + homeUrl + '">Home</a>';
            
            var currentTitle = $('.page-title').text().trim();
            if (currentTitle) {
                breadcrumb += '<span class="breadcrumb-separator">›</span>';
                breadcrumb += '<span>' + currentTitle + '</span>';
            }
        }
        
        return breadcrumb;
    }
    
    /**
     * Fix sidebar nesting issue - ensure sidebar is always a direct child of .row
     */
    function fixSidebarNesting() {
        var $entryContent = $('.entry-content');
        if ($entryContent.length === 0) return;
        
        var $row = $entryContent.find('> .row');
        if ($row.length === 0) return;
        
        // Check if sidebar is nested inside the first column (incorrect structure)
        var $contentColumn = $row.find('> .col-md-6:first-child, > .col-md-6.content-column');
        var $nestedSidebar = $contentColumn.find('.col-md-6.sidebar-column, .sidebar-column, .dynamic-menu-sidebar').closest('.col-md-6.sidebar-column, .col-md-6:has(.sidebar-column), .col-md-6:has(.dynamic-menu-sidebar)');
        
        if ($nestedSidebar.length > 0) {
            // Sidebar is incorrectly nested - move it out
            console.log('Fixing nested sidebar structure');
            
            // Find the actual sidebar div (could be .col-md-6.sidebar-column or just .sidebar-column)
            var $actualSidebar = $nestedSidebar;
            if (!$actualSidebar.hasClass('sidebar-column')) {
                // It might be a wrapper, find the actual sidebar column
                $actualSidebar = $nestedSidebar.filter('.col-md-6').first();
            }
            
            if ($actualSidebar.length > 0) {
                // Clone the sidebar with all its content
                var $sidebarClone = $actualSidebar.clone(true);
                
                // Remove the nested sidebar
                $actualSidebar.remove();
                
                // Append sidebar as a direct child of row
                $row.append($sidebarClone);
            }
        }
        
        // Double-check: ensure sidebar is a direct child of row and has correct classes
        var $directSidebar = $row.find('> .col-md-6.sidebar-column');
        if ($directSidebar.length === 0) {
            // Look for any sidebar that's not a direct child of row
            var $misplacedSidebar = $entryContent.find('.sidebar-column').closest('.col-md-6');
            if ($misplacedSidebar.length > 0 && !$misplacedSidebar.parent().hasClass('row')) {
                // Check if it's inside a col-md-6 (nested)
                if ($misplacedSidebar.closest('.col-md-6').length > 1) {
                    // Move it to the correct position
                    var $correctSidebar = $misplacedSidebar.clone(true);
                    $misplacedSidebar.remove();
                    
                    // Ensure it has the right classes
                    $correctSidebar.addClass('sidebar-column');
                    if (!$correctSidebar.hasClass('col-md-6')) {
                        $correctSidebar.addClass('col-md-6');
                    }
                    
                    $row.append($correctSidebar);
                }
            }
        }
        
        // Final check: ensure there's only one sidebar and it's correctly positioned
        var $allSidebars = $entryContent.find('.sidebar-column, .col-md-6:has(.dynamic-menu-sidebar)');
        if ($allSidebars.length > 1) {
            // Multiple sidebars found - keep only the one that's a direct child of row
            $allSidebars.each(function() {
                var $sidebar = $(this);
                if (!$sidebar.parent().hasClass('row')) {
                    $sidebar.remove(); // Remove nested ones
                }
            });
        }
    }
    
    // Fix sidebar nesting on page load
    fixSidebarNesting();
    
    // Also run after a short delay to catch any dynamically loaded content
    setTimeout(fixSidebarNesting, 100);
    setTimeout(fixSidebarNesting, 500);
});
