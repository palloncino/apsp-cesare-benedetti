/**
 * TinyMCE Button for APSP Google Map Shortcode
 */

(function() {
    tinymce.PluginManager.add('apsp_map', function(editor, url) {
        
        // Add button to toolbar
        editor.addButton('apsp_map', {
            text: 'APSP Map',
            icon: false,
            tooltip: 'Insert APSP Google Map',
            onclick: function() {
                // Open dialog
                editor.windowManager.open({
                    title: 'Insert APSP Google Map',
                    body: [
                        {
                            type: 'textbox',
                            name: 'address',
                            label: 'Address',
                            value: 'Via Vittorio Emanuele, 131, 38055 Grigno'
                        },
                        {
                            type: 'textbox',
                            name: 'width',
                            label: 'Width (px)',
                            value: '600'
                        },
                        {
                            type: 'textbox',
                            name: 'height',
                            label: 'Height (px)',
                            value: '450'
                        },
                        {
                            type: 'listbox',
                            name: 'zoom',
                            label: 'Zoom Level',
                            values: [
                                {text: '1 - World', value: '1'},
                                {text: '5 - Country', value: '5'},
                                {text: '10 - City', value: '10'},
                                {text: '14 - Street (Default)', value: '14'},
                                {text: '16 - Building', value: '16'},
                                {text: '18 - Street Detail', value: '18'},
                                {text: '20 - Building Detail', value: '20'}
                            ]
                        },
                        {
                            type: 'listbox',
                            name: 'maptype',
                            label: 'Map Type',
                            values: [
                                {text: 'Roadmap (Default)', value: 'roadmap'},
                                {text: 'Satellite', value: 'satellite'},
                                {text: 'Hybrid', value: 'hybrid'},
                                {text: 'Terrain', value: 'terrain'}
                            ]
                        },
                        {
                            type: 'textbox',
                            name: 'title',
                            label: 'Title',
                            value: 'APSP Grigno - La nostra sede'
                        }
                    ],
                    onsubmit: function(e) {
                        // Build shortcode
                        var shortcode = '[apsp_map';
                        
                        if (e.data.address && e.data.address !== 'Via Vittorio Emanuele, 131, 38055 Grigno') {
                            shortcode += ' address="' + e.data.address + '"';
                        }
                        if (e.data.width && e.data.width !== '600') {
                            shortcode += ' width="' + e.data.width + '"';
                        }
                        if (e.data.height && e.data.height !== '450') {
                            shortcode += ' height="' + e.data.height + '"';
                        }
                        if (e.data.zoom && e.data.zoom !== '14') {
                            shortcode += ' zoom="' + e.data.zoom + '"';
                        }
                        if (e.data.maptype && e.data.maptype !== 'roadmap') {
                            shortcode += ' maptype="' + e.data.maptype + '"';
                        }
                        if (e.data.title && e.data.title !== 'APSP Grigno - La nostra sede') {
                            shortcode += ' title="' + e.data.title + '"';
                        }
                        
                        shortcode += ']';
                        
                        // Insert shortcode
                        editor.insertContent(shortcode);
                    }
                });
            }
        });
    });
})(); 