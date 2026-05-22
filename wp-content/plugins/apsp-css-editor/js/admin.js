/**
 * APSP CSS Editor Admin JavaScript
 */

jQuery(document).ready(function($) {
    
    var editor = $('#css-editor');
    var statusMessage = $('#save-status');
    
    // CSS Snippets - Esempi Pronti per Principianti
    var snippets = {
        'hero-section': `/* 🎯 SEZIONE HERO - La parte grande in alto della pagina */
/* Come il titolo di un libro, ma per il sito web */

.hero-section {
    width: 100%;                    /* Occupa tutta la larghezza */
    padding: 130px 20px;            /* Spazio interno: alto e basso 130px, destra e sinistra 20px */
    text-align: center;             /* Centra tutto il testo */
    background-image: url('your-image.jpg');  /* Immagine di sfondo - cambia 'your-image.jpg' con il tuo file */
    background-size: cover;         /* L'immagine copre tutto lo spazio */
    background-position: center;    /* L'immagine è centrata */
    background-repeat: no-repeat;   /* L'immagine non si ripete */
    position: relative;             /* Posizione relativa per il contenuto interno */
}

.hero-section h1 {
    font-size: 2.2rem;              /* Dimensione del titolo: molto grande */
    margin-bottom: 15px;            /* Spazio sotto il titolo */
    color: #ffffff;                 /* Colore del testo: bianco */
    font-weight: bold;              /* Testo in grassetto */
    text-shadow: 1px 1px 3px #000000;  /* Ombra del testo per farlo risaltare */
}

.hero-section p {
    font-size: 1.1rem;              /* Dimensione del paragrafo: un po' più grande del normale */
    max-width: 800px;               /* Larghezza massima del testo */
    margin: 0 auto;                 /* Centra il paragrafo */
    color: #ffffff;                 /* Colore del testo: bianco */
    line-height: 1.6;               /* Spazio tra le righe */
    text-shadow: 1px 1px 2px #000000;  /* Ombra del testo */
}`,
        
        'responsive': `/* 📱 DESIGN RESPONSIVE - Per tablet e telefoni */
/* Queste regole si attivano quando lo schermo è più piccolo */

@media (max-width: 768px) {          /* Quando lo schermo è più piccolo di 768px (tablet) */
    .container {
        padding: 0 15px;             /* Meno spazio ai lati */
    }
    
    .hero-section {
        padding: 60px 15px;          /* Meno spazio interno */
    }
    
    .hero-section h1 {
        font-size: 1.8rem;           /* Titolo più piccolo */
    }
    
    .hero-section p {
        font-size: 1rem;             /* Testo più piccolo */
    }
}

@media (max-width: 480px) {          /* Quando lo schermo è più piccolo di 480px (telefono) */
    .hero-section h1 {
        font-size: 1.5rem;           /* Titolo ancora più piccolo */
    }
}`,
        
        'buttons': `/* 🔘 PULSANTI - I bottoni del sito web */
/* Come i pulsanti di un telecomando, ma per il sito */

.btn {
    display: inline-block;           /* Si comporta come un blocco in linea */
    padding: 12px 24px;             /* Spazio interno: 12px alto/basso, 24px destra/sinistra */
    background: #3498db;            /* Colore di sfondo: blu */
    color: #ffffff;                 /* Colore del testo: bianco */
    text-decoration: none;          /* Niente sottolineatura */
    border-radius: 6px;             /* Angoli arrotondati */
    font-weight: 500;               /* Testo semi-grassetto */
    transition: all 0.3s ease;      /* Animazione fluida di 0.3 secondi */
    border: none;                   /* Nessun bordo */
    cursor: pointer;                /* Cursore a mano quando ci passi sopra */
}

.btn:hover {                        /* Quando passi il mouse sopra il pulsante */
    background: #2980b9;            /* Blu più scuro */
    transform: translateY(-2px);    /* Si solleva di 2 pixel */
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);  /* Ombra blu */
}

.btn-secondary {                    /* Pulsante secondario (grigio) */
    background: #95a5a6;
}

.btn-secondary:hover {              /* Quando passi il mouse sopra */
    background: #7f8c8d;            /* Grigio più scuro */
}`,
        
        'cards': `/* 🃏 CARD - Le scatole che contengono informazioni */
/* Come le pagine di un catalogo o le schede di un gioco */

.card {
    background: #ffffff;             /* Sfondo bianco */
    border-radius: 12px;             /* Angoli molto arrotondati */
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);  /* Ombra leggera */
    padding: 25px;                   /* Spazio interno */
    margin-bottom: 25px;             /* Spazio sotto la card */
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.card-title {
    color: #2c3e50;
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 15px;
}`
    };
    
    // Save CSS
    $('#save-css').on('click', function() {
        console.log('=== SAVE BUTTON CLICKED ===');
        
        var cssContent = editor.val();
        var fileType = editor.data('file');
        var button = $(this);
        var originalText = button.text();
        
        console.log('Save: fileType =', fileType);
        console.log('Save: content length =', cssContent.length);
        

        
        // Allow empty content (user might want to clear the file)
        // if (!cssContent.trim()) {
        //     statusMessage.addClass('error').text('Il contenuto CSS non può essere vuoto');
        //     return;
        // }
        
        button.text(apsp_css_editor.strings.saving).prop('disabled', true);
        statusMessage.removeClass('success error').text('');
        
        console.log('Save: Sending AJAX request...');
        
        $.ajax({
            url: apsp_css_editor.ajax_url,
            type: 'POST',
            data: {
                action: 'save_css',
                nonce: apsp_css_editor.nonce,
                css: cssContent,
                file_type: fileType
            },
            success: function(response) {
                console.log('Save: AJAX response received:', response);
                if (response.success) {
                    console.log('Save: SUCCESS');
                    statusMessage.addClass('success').text(response.data);
                } else {
                    console.log('Save: ERROR -', response.data);
                    statusMessage.addClass('error').text(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log('Save: AJAX ERROR -', xhr, status, error);
                statusMessage.addClass('error').text(apsp_css_editor.strings.error);
            },
            complete: function() {
                button.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Create Backup
    $('#backup-css').on('click', function() {
        var button = $(this);
        var originalText = button.text();
        
        button.text('Creando backup...').prop('disabled', true);
        
        $.ajax({
            url: apsp_css_editor.ajax_url,
            type: 'POST',
            data: {
                action: 'backup_css',
                nonce: apsp_css_editor.nonce
            },
            success: function(response) {
                if (response.success) {
                    statusMessage.addClass('success').text(response.data);
                    // Reload page to refresh backup list
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    statusMessage.addClass('error').text(response.data);
                }
            },
            error: function() {
                statusMessage.addClass('error').text('Errore nella creazione del backup');
            },
            complete: function() {
                button.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Restore Backup
    $('#restore-backup').on('click', function() {
        var backupFile = $('#backup-select').val();
        
        if (!backupFile) {
            statusMessage.addClass('error').text('Seleziona un backup da ripristinare');
            return;
        }
        
        if (!confirm(apsp_css_editor.strings.restore_confirm)) {
            return;
        }
        
        var button = $(this);
        var originalText = button.text();
        
        button.text('Ripristinando...').prop('disabled', true);
        
        $.ajax({
            url: apsp_css_editor.ajax_url,
            type: 'POST',
            data: {
                action: 'restore_css',
                nonce: apsp_css_editor.nonce,
                backup_file: backupFile
            },
            success: function(response) {
                if (response.success) {
                    statusMessage.addClass('success').text(response.data);
                    // Reload page to show restored content
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    statusMessage.addClass('error').text(response.data);
                }
            },
            error: function() {
                statusMessage.addClass('error').text('Errore nel ripristino del backup');
            },
            complete: function() {
                button.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Preview CSS
    $('#preview-css').on('click', function() {
        var css = editor.val();
        var previewWindow = window.open('', '_blank');
        
        previewWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>CSS Preview</title>
                <style>${css}</style>
            </head>
            <body>
                <div style="padding: 20px;">
                    <h1>CSS Preview</h1>
                    <p>This is a preview of your CSS changes.</p>
                    <div class="hero-section">
                        <h1>Hero Section Example</h1>
                        <p>This is how your hero section might look.</p>
                    </div>
                    <div class="card">
                        <h3 class="card-title">Card Example</h3>
                        <p>This is how your cards might look.</p>
                    </div>
                    <button class="btn">Button Example</button>
                </div>
            </body>
            </html>
        `);
        previewWindow.document.close();
    });
    
    // Insert CSS Snippets
    $('.snippet-btn').on('click', function() {
        var snippetType = $(this).data('snippet');
        var snippet = snippets[snippetType];
        
        if (snippet) {
            var currentPos = editor[0].selectionStart;
            var currentContent = editor.val();
            var newContent = currentContent.substring(0, currentPos) + '\n\n' + snippet + '\n\n' + currentContent.substring(currentPos);
            
            editor.val(newContent);
            editor.focus();
            
            statusMessage.addClass('success').text('Snippet inserito con successo!');
        }
    });
    
    // Keyboard shortcuts
    editor.on('keydown', function(e) {
        // Ctrl+S to save
        if (e.ctrlKey && e.keyCode === 83) {
            e.preventDefault();
            $('#save-css').click();
        }
        
        // Tab to indent
        if (e.keyCode === 9) {
            e.preventDefault();
            var start = this.selectionStart;
            var end = this.selectionEnd;
            var value = $(this).val();
            
            $(this).val(value.substring(0, start) + '    ' + value.substring(end));
            this.selectionStart = this.selectionEnd = start + 4;
        }
    });
    
    // Auto-save reminder
    var autoSaveTimer;
    editor.on('input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            statusMessage.removeClass('success error').addClass('info').text('Modifiche non salvate. Premi Ctrl+S per salvare.');
        }, 3000);
    });
    
    // Clear status message when starting to type
    editor.on('keydown', function() {
        if (statusMessage.hasClass('info')) {
            statusMessage.removeClass('info').text('');
        }
    });
    
    // File info hover
    $('.apsp-css-editor-panel').hover(
        function() {
            $(this).addClass('hover');
        },
        function() {
            $(this).removeClass('hover');
        }
    );
    
    // Responsive textarea
    function adjustTextareaHeight() {
        var windowHeight = $(window).height();
        var offset = editor.offset().top;
        var minHeight = windowHeight * 0.8; // 80% of viewport height
        var maxHeight = windowHeight - offset - 100;
        
        editor.css('min-height', Math.max(minHeight, maxHeight) + 'px');
    }
    
    $(window).on('resize', adjustTextareaHeight);
    adjustTextareaHeight();
    
    // File selector functionality
    $('#file-selector').on('change', function() {
        var selectedFile = $(this).val();
        var currentUrl = new URL(window.location);
        currentUrl.searchParams.set('file', selectedFile);
        window.location.href = currentUrl.toString();
    });
    
    // Create new file
    $('#create-file').on('click', function() {
        var filename = $('#new-filename').val().trim();
        
        console.log('Create file clicked, filename:', filename);
        
        if (!filename) {
            statusMessage.addClass('error').text('📝 Inserisci un nome per il file');
            return;
        }
        
        var button = $(this);
        var originalText = button.text();
        
        button.text('Creating...').prop('disabled', true);
        
        $.ajax({
            url: apsp_css_editor.ajax_url,
            type: 'POST',
            data: {
                action: 'create_css_file',
                nonce: apsp_css_editor.nonce,
                filename: filename
            },
            success: function(response) {
                if (response.success) {
                    statusMessage.addClass('success').text('✨ File creato con successo!');
                    $('#new-filename').val('');
                    // Reload page to show new file
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    statusMessage.addClass('error').text(response.data);
                }
            },
            error: function() {
                statusMessage.addClass('error').text('❌ Errore nella creazione del file');
            },
            complete: function() {
                button.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Delete file
    $('#delete-file').on('click', function() {
        if (!confirm('⚠️ Sei sicuro di voler eliminare questo file? Questa azione non si può annullare!')) {
            return;
        }
        
        var button = $(this);
        var originalText = button.text();
        var currentFile = editor.data('file');
        
        button.text('Deleting...').prop('disabled', true);
        
        $.ajax({
            url: apsp_css_editor.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_css_file',
                nonce: apsp_css_editor.nonce,
                file_type: currentFile
            },
            success: function(response) {
                if (response.success) {
                    statusMessage.addClass('success').text('🗑️ File eliminato con successo!');
                    // Redirect to main style file
                    setTimeout(function() {
                        window.location.href = window.location.pathname + '?page=apsp-css-editor&file=style';
                    }, 1500);
                } else {
                    statusMessage.addClass('error').text(response.data);
                }
            },
            error: function() {
                statusMessage.addClass('error').text('❌ Errore nell\'eliminazione del file');
            },
            complete: function() {
                button.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Focus on editor when page loads
    editor.focus();
    
    // Show welcome message
    if (editor.val().trim() === '') {
        statusMessage.addClass('info').text('Benvenuto nel CSS Editor! Inizia a scrivere il tuo CSS.');
    }
}); 