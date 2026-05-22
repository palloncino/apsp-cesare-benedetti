# Custom Document Manager - WordPress Plugin

Un plugin WordPress per la gestione e il caricamento di documenti (PDF, XLS, DOC) nel pannello di amministrazione.

## Caratteristiche

### ✅ Funzionalità Principali
- **Caricamento documenti**: Supporta PDF, XLS, XLSX, DOC, DOCX
- **Organizzazione per categorie**: Crea sottocartelle per organizzare i documenti
- **Interfaccia amministrativa**: Tabella completa con tutte le informazioni sui file
- **Ricerca e filtri**: Filtra per nome file e categoria
- **Copia link**: Funzionalità per copiare rapidamente i link ai documenti

### 🔄 **NUOVE FUNZIONALITÀ DI ORDINAMENTO**

#### 1. **Ordinamento Predefinito per Data**
- I documenti sono ora **ordinati automaticamente per data** (più recenti prima)
- L'ordinamento viene applicato al caricamento iniziale della pagina
- Migliora l'esperienza utente mostrando sempre i documenti più recenti in cima

#### 2. **Intestazioni Colonne Cliccabili**
- **Tutte le colonne sono ora ordinabili**: Nome File, Tipo, Categoria, Dimensione, Data
- **Clicca su qualsiasi intestazione** per ordinare la tabella
- **Doppio click** sulla stessa colonna inverte l'ordine (ASC/DESC)
- **Indicatori visivi**: Frecce ▲/▼ mostrano la direzione dell'ordinamento corrente

#### 3. **Accessibilità e UX**
- **Supporto tastiera**: Usa Tab per navigare e Enter/Spazio per ordinare
- **Focus visibile**: Contorni colorati per l'accessibilità
- **Suggerimenti utente**: Messaggi informativi per guidare l'uso
- **Compatibilità**: Funziona con tutti i browser moderni

## Installazione

1. Carica la cartella `custom-document-manager` nella directory `/wp-content/plugins/`
2. Attiva il plugin dal pannello di amministrazione WordPress
3. Accedi al menu "Gestore Documenti" nel pannello di amministrazione

## Utilizzo

### Pannello di Amministrazione
1. Vai su **Gestore Documenti** nel menu di amministrazione
2. **Carica documenti** usando il form in alto
3. **Ordina la tabella** cliccando sulle intestazioni delle colonne
4. **Filtra i risultati** usando i campi di ricerca
5. **Copia i link** ai documenti per condividerli

### Shortcode
Il plugin include shortcode per visualizzare i documenti nelle pagine:

```php
[pdf_list category="trasparenza/Atti_Generali" limit="10"]
[pdf_grid category="trasparenza/Atti_Generali" columns="3"]
```

## Struttura File

```
custom-document-manager/
├── custom-pdf-manager.php    # File principale del plugin
├── shortcodes.php           # Shortcode per frontend
└── README.md               # Documentazione
```

## Tecnologie

- **Backend**: PHP 7.4+, WordPress 5.0+
- **Frontend**: JavaScript (jQuery), CSS3
- **Compatibilità**: Tutti i browser moderni

## Changelog

### Versione 1.0.2
- ✅ **NUOVO**: Ordinamento predefinito per data (più recenti prima)
- ✅ **NUOVO**: Intestazioni colonne cliccabili per ordinamento
- ✅ **NUOVO**: Indicatori visivi per direzione ordinamento
- ✅ **MIGLIORATO**: Accessibilità con supporto tastiera
- ✅ **MIGLIORATO**: UX con suggerimenti utente
- ✅ **FIX**: Ordinamento corretto per dimensioni file

### Versione 1.0.1
- Funzionalità base di caricamento e gestione documenti
- Interfaccia amministrativa completa
- Sistema di categorie e filtri
- Shortcode per frontend

## Supporto

Per supporto tecnico o segnalazione bug, contatta lo sviluppatore.

---

**Sviluppato da**: A. Guiotto & Giollins  
**Versione**: 1.0.2  
**Compatibilità WordPress**: 5.0+ 