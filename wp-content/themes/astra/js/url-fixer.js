/**
 * Development URL Fixer
 * 
 * This script temporarily fixes URLs for the development server.
 * REMOVE THIS ENTIRE FILE WHEN MIGRATING TO PRODUCTION!
 * 
 * What it does:
 * - Fixes absolute URLs to work with the /apsp-grigno subdirectory
 * - Updates image sources, links, and other URLs
 * - Works on all pages automatically
 */

(function() {
    'use strict';
    
    // Configuration - CHANGE THESE WHEN MIGRATING
    const DEV_CONFIG = {
        // Current development setup
        currentSubdirectory: '/apsp-grigno',
        
        // URLs to fix (add more as needed)
        urlPatterns: [
            // Fix absolute URLs that should be relative
            {
                pattern: /^https:\/\/shcl-09573\.serverlet\.com\/apsp-grigno\//,
                replacement: '/apsp-grigno/'
            },
            // Fix URLs that are missing the subdirectory
            {
                pattern: /^\/wp-content\//,
                replacement: '/apsp-grigno/wp-content/'
            },
            // Fix absolute URLs that are missing the subdirectory
            {
                pattern: /^https:\/\/shcl-09573\.serverlet\.com\/wp-content\//,
                replacement: 'https://shcl-09573.serverlet.com/apsp-grigno/wp-content/'
            },
            // Fix URLs that are missing the subdirectory for pages
            {
                pattern: /^\/piano-triennale-per-la-prevenzione-della-corruzione-e-della-trasparenza/,
                replacement: '/apsp-grigno/piano-triennale-per-la-prevenzione-della-corruzione-e-della-trasparenza'
            },
            {
                pattern: /^\/disposizioni-generali/,
                replacement: '/apsp-grigno/disposizioni-generali'
            },
            {
                pattern: /^\/organizzazione/,
                replacement: '/apsp-grigno/organizzazione'
            },
            {
                pattern: /^\/consulenti-e-collaboratori/,
                replacement: '/apsp-grigno/consulenti-e-collaboratori'
            },
            {
                pattern: /^\/personale/,
                replacement: '/apsp-grigno/personale'
            },
            {
                pattern: /^\/performance/,
                replacement: '/apsp-grigno/performance'
            },
            {
                pattern: /^\/enti-controllati/,
                replacement: '/apsp-grigno/enti-controllati'
            },
            {
                pattern: /^\/attivita-e-procedimenti/,
                replacement: '/apsp-grigno/attivita-e-procedimenti'
            },
            {
                pattern: /^\/provvedimenti/,
                replacement: '/apsp-grigno/provvedimenti'
            },
            {
                pattern: /^\/bandi-di-gara-e-contratti/,
                replacement: '/apsp-grigno/bandi-di-gara-e-contratti'
            },
            {
                pattern: /^\/sovvenzioni-contributi-sussidi-vantaggi-economici/,
                replacement: '/apsp-grigno/sovvenzioni-contributi-sussidi-vantaggi-economici'
            },
            {
                pattern: /^\/bilanci/,
                replacement: '/apsp-grigno/bilanci'
            },
            {
                pattern: /^\/beni-immobili-e-gestione-patrimonio/,
                replacement: '/apsp-grigno/beni-immobili-e-gestione-patrimonio'
            },
            {
                pattern: /^\/controlli-e-rilievi-sull-amministrazione/,
                replacement: '/apsp-grigno/controlli-e-rilievi-sull-amministrazione'
            },
            {
                pattern: /^\/servizi-erogati/,
                replacement: '/apsp-grigno/servizi-erogati'
            },
            {
                pattern: /^\/pagamenti-dell-amministrazione/,
                replacement: '/apsp-grigno/pagamenti-dell-amministrazione'
            },
            {
                pattern: /^\/opere-pubbliche/,
                replacement: '/apsp-grigno/opere-pubbliche'
            },
            {
                pattern: /^\/altri-contenuti/,
                replacement: '/apsp-grigno/altri-contenuti'
            },
            {
                pattern: /^\/controlli-sulle-imprese/,
                replacement: '/apsp-grigno/controlli-sulle-imprese'
            },
            {
                pattern: /^\/bandi-di-concorso/,
                replacement: '/apsp-grigno/bandi-di-concorso'
            },
            {
                pattern: /^\/canoni-di-locazione-o-affitto/,
                replacement: '/apsp-grigno/canoni-di-locazione-o-affitto'
            },
            {
                pattern: /^\/pianificazione-e-governo-del-territorio/,
                replacement: '/apsp-grigno/pianificazione-e-governo-del-territorio'
            },
            {
                pattern: /^\/informazioni-ambientali/,
                replacement: '/apsp-grigno/informazioni-ambientali'
            },
            {
                pattern: /^\/strutture-sanitarie-private-accreditate/,
                replacement: '/apsp-grigno/strutture-sanitarie-private-accreditate'
            },
            {
                pattern: /^\/interventi-straordinari-e-di-emergenza/,
                replacement: '/apsp-grigno/interventi-straordinari-e-di-emergenza'
            }
        ]
    };
    
    /**
     * Fix a single URL according to the patterns
     */
    function fixUrl(url) {
        if (!url || typeof url !== 'string') return url;
        
        let fixedUrl = url;
        
        DEV_CONFIG.urlPatterns.forEach(pattern => {
            if (pattern.pattern.test(fixedUrl)) {
                fixedUrl = fixedUrl.replace(pattern.pattern, pattern.replacement);
            }
        });
        
        return fixedUrl;
    }
    
    /**
     * Fix URLs in an element and all its children
     */
    function fixElementUrls(element) {
        if (!element) return;
        
        // Fix href attributes
        const links = element.querySelectorAll('a[href]');
        links.forEach(link => {
            const originalHref = link.getAttribute('href');
            const fixedHref = fixUrl(originalHref);
            if (fixedHref !== originalHref) {
                link.setAttribute('href', fixedHref);
                console.log(`🔗 Fixed link: ${originalHref} → ${fixedHref}`);
            }
        });
        
        // Fix src attributes (images, scripts, etc.)
        const mediaElements = element.querySelectorAll('img[src], script[src], iframe[src], video[src], audio[src]');
        mediaElements.forEach(media => {
            const originalSrc = media.getAttribute('src');
            const fixedSrc = fixUrl(originalSrc);
            if (fixedSrc !== originalSrc) {
                media.setAttribute('src', fixedSrc);
                console.log(`🖼️ Fixed media: ${originalSrc} → ${fixedSrc}`);
            }
        });
        
        // Fix background images in inline styles
        const elementsWithStyle = element.querySelectorAll('[style*="background"]');
        elementsWithStyle.forEach(el => {
            const style = el.getAttribute('style');
            if (style && style.includes('url(')) {
                const newStyle = style.replace(/url\(['"]?([^'")\s]+)['"]?\)/g, (match, url) => {
                    const fixedUrl = fixUrl(url);
                    return `url('${fixedUrl}')`;
                });
                if (newStyle !== style) {
                    el.setAttribute('style', newStyle);
                    console.log(`🎨 Fixed background: ${style} → ${newStyle}`);
                }
            }
        });
    }
    
    /**
     * Fix URLs in the entire document
     */
    function fixAllUrls() {
        console.log('🚀 Development URL Fixer: Starting...');
        console.log(`📁 Fixing URLs for subdirectory: ${DEV_CONFIG.currentSubdirectory}`);
        
        // Fix URLs in the current document
        fixElementUrls(document);
        
        // Fix URLs in any dynamically added content
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        fixElementUrls(node);
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('✅ Development URL Fixer: Active and monitoring for new content');
        console.log('⚠️  REMEMBER: Delete this script when migrating to production!');
    }
    
    /**
     * Initialize when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fixAllUrls);
    } else {
        fixAllUrls();
    }
    
    /**
     * Also run on page load for single-page applications
     */
    window.addEventListener('load', fixAllUrls);
    
    // Make it available globally for debugging
    window.devUrlFixer = {
        fixAllUrls,
        fixUrl,
        config: DEV_CONFIG
    };
    
})();
