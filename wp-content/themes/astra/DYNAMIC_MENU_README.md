# Sistema di Gestione Menu Dinamico

## Panoramica

Il sistema Menu Dinamico costruisce automaticamente una struttura di menu nidificata dalle pagine WordPress per la sezione "Amministrazione Trasparente". Ora include un'interfaccia amministrativa per una gestione facile.

## Come Funziona

### Comportamento Attuale

1. **Pagine Radice**: Il sistema utilizza una lista configurabile di slug e etichette delle pagine radice
2. **Radici Mancanti**: Se una pagina radice non esiste, viene saltata (nessun fallback)
3. **Pagine Figlie**: Se aggiungi una pagina figlia a una radice esistente, SARÀ visualizzata automaticamente
4. **Nuove Radici**: Se aggiungi una pagina con uno slug che non è nell'array delle radici, NON apparirà nel menu

### Interfaccia Amministrativa

Accedi all'interfaccia amministrativa in: **Strumenti → Menu Dinamico**

#### Funzionalità:

1. **Aggiungi Nuova Pagina Radice**
   - Inserisci uno slug della pagina (es. "nuova-sezione")
   - Inserisci un titolo di visualizzazione (es. "Nuova Sezione")
   - Il sistema lo aggiungerà all'array delle radici

2. **Gestisci Pagine Radice**
   - Visualizza tutte le pagine radice attuali
   - Vedi quali pagine esistono (✓) e quali mancano (✗)
   - Modifica slug ed etichette delle pagine
   - Trascina e rilascia per riordinare le pagine
   - Aggiorna le modifiche

3. **Azioni Rapide**
   - "Crea Pagine Mancanti" - Crea automaticamente tutte le pagine mancanti con contenuto predefinito

## Istruzioni per l'Uso

### Aggiungere una Nuova Pagina Radice

1. Vai su **Strumenti → Menu Dinamico**
2. Nella sezione "Aggiungi Nuova Pagina Radice":
   - Inserisci lo slug della pagina (versione URL-friendly)
   - Inserisci il titolo di visualizzazione
   - Clicca "Aggiungi Pagina Radice"

### Creare Pagine Mancanti

1. Vai su **Strumenti → Menu Dinamico**
2. Controlla lo stato delle pagine esistenti (✓ esiste, ✗ mancante)
3. Clicca "Crea Pagine Mancanti" per creare automaticamente tutte le pagine mancanti

### Gestire Pagine Esistenti

1. Vai su **Strumenti → Menu Dinamico**
2. Nella sezione "Gestisci Pagine Radice":
   - Modifica slug ed etichette secondo necessità
   - Trascina e rilascia per riordinare
   - Clicca "Aggiorna Pagine Radice" per salvare le modifiche

## Dettagli Tecnici

### Archiviazione

- Le pagine radice sono memorizzate nelle opzioni WordPress come `at_dynamic_menu_roots`
- Formato: `array( 'slug' => 'Etichetta', ... )`

### Utilizzo del Widget

Il widget può essere aggiunto a qualsiasi area della sidebar e visualizzerà automaticamente la struttura del menu.

### Utilizzo dello Shortcode

Usa lo shortcode `[at_dynamic_menu]` in qualsiasi post o pagina per visualizzare il menu.

### Integrazione Template Pagina

Il template `page.php` mostra automaticamente la sidebar del menu per le pagine che sono:
- Pagine radice (definite nell'array delle radici)
- Pagine figlie di qualsiasi pagina radice

## Struttura File

- `inc/class-at-dynamic-menu-widget.php` - Classe principale del widget e amministrativa
- `page.php` - Template che si integra con il menu dinamico
- `style.css` - Stili CSS per il menu

## Risoluzione Problemi

### Menu Non Visualizzato

1. Controlla se lo slug della pagina è nell'array delle radici
2. Verifica che la pagina esista in WordPress
3. Controlla se il widget è correttamente aggiunto a una sidebar

### Pagine Mancanti

1. Usa la funzione "Crea Pagine Mancanti" nell'amministrazione
2. Oppure crea manualmente le pagine con gli slug corretti

### Problemi di Ordine Menu

1. Usa l'interfaccia drag-and-drop nell'amministrazione
2. Aggiorna le radici per salvare il nuovo ordine

## Miglioramenti Futuri

- Aggiungere supporto per template di pagina personalizzati
- Aggiungere icone per le voci di menu
- Aggiungere supporto per link esterni
- Aggiungere cache del menu per le prestazioni 