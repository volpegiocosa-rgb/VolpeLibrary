# Libreria Volpe Giocosa

Strumenti per arricchire il catalogo della libreria di giochi da tavolo con dati presi da [BoardGameGeek](https://boardgamegeek.com) (giocatori, durata, complessità, tipo, categorie, meccaniche) e per pubblicare una pagina di ricerca mobile-friendly.

## Struttura del progetto

```
legacy/                  Sito originale e registro giochi (sola lettura, non modificarlo mai)
  Registro_giochi.xlsx    Il registro che compili a mano (foglio "Tabella": Gioco | Posizione | Numero)
scripts/
  bgg_lookup.py           Script Python: legge il registro, interroga BGG, scrive il catalogo arricchito
output/
  catalogo_bgg.json        Catalogo generato dallo script (non modificarlo a mano)
web/
  catalogo.html / .php     Il front-end pubblicato (usa una copia di catalogo_bgg.json)
requirements.txt          Dipendenze Python
API_token.md               Il tuo token BGG (non è su git)
```

## 1. Prerequisiti

- Python 3.10 o superiore
- Un token API BGG **approvato** (vedi punto 3)

## 2. Preparare l'ambiente

Dalla cartella del progetto:

```bash
python3 -m venv venv
source venv/bin/activate       # su Windows: venv\Scripts\activate
pip install -r requirements.txt
```

Se hai già una `venv/`, ti basta il `pip install -r requirements.txt` per essere sicuro di avere `requests` e `openpyxl` aggiornati.

## 3. Ottenere e impostare il token BGG

Dal 2025 BoardGameGeek richiede un token di autorizzazione per usare la XML API2 (prima era libera). Vai su [boardgamegeek.com/using_the_xml_api](https://boardgamegeek.com/using_the_xml_api), registra la tua applicazione e attendi l'approvazione.

Una volta ottenuto il token, impostalo come variabile d'ambiente prima di lanciare lo script:

```bash
export BGG_API_TOKEN="il-tuo-token"
```

In alternativa puoi tenerlo scritto in `API_token.md` (file già escluso da git, quindi al sicuro) e copiarlo da lì ogni volta con:

```bash
export BGG_API_TOKEN=$(sed -n 's/^export BGG_API_TOKEN="\(.*\)"$/\1/p' API_token.md)
```

## 4. Usare lo script

Tutti i comandi vanno lanciati dalla cartella principale del progetto, con l'ambiente virtuale attivo e `BGG_API_TOKEN` impostato.

### Provare lo script su un singolo gioco

Utile per verificare che il token funzioni prima di lanciare tutto il registro:

```bash
python3 scripts/bgg_lookup.py --game "Catan" --json
```

Stampa a schermo giocatori, durata, peso, tipo, categorie, meccaniche e famiglie trovate su BGG per quel gioco, senza toccare il registro né scrivere file.

### Arricchire tutto il registro

```bash
python3 scripts/bgg_lookup.py
```

Legge `legacy/Registro_giochi.xlsx`, cerca ogni gioco su BGG e scrive il risultato in `output/catalogo_bgg.json`. Con ~570 giochi e le pause necessarie per rispettare i limiti di BGG, il primo giro completo richiede circa 30-60 minuti. Il file di output viene salvato dopo ogni gioco, quindi puoi interromperlo (`Ctrl+C`) senza perdere il lavoro fatto.

### Riprendere un'elaborazione interrotta o aggiornare dopo modifiche al registro

```bash
python3 scripts/bgg_lookup.py --resume
```

`--resume` è anche il modo giusto per **aggiornare il catalogo dopo aver modificato `Registro_giochi.xlsx`** (aggiunto, eliminato o spostato/rinominato un gioco):

- i giochi già presenti e riusciti **non vengono ri-scaricati**, per non sprecare tempo e chiamate a BGG;
- i giochi che in precedenza avevano dato errore vengono ritentati;
- i giochi **eliminati** dal registro spariscono dal catalogo;
- i giochi **rinominati o spostati di posizione** vengono ri-scaricati sotto la nuova voce (la vecchia sparisce);
- i giochi **nuovi** vengono aggiunti.

### Provare su poche righe prima di lanciare tutto

```bash
python3 scripts/bgg_lookup.py --limit 5
```

Utile per verificare velocemente che tutto funzioni. **Nota**: con `--limit` lo script non elimina dal catalogo i giochi che non rientrano nel sottoinsieme limitato (altrimenti cancellerebbe per errore dati validi) — la pulizia delle voci eliminate/rinominate avviene solo su un giro completo, senza `--limit`.

### Altre opzioni utili

| Opzione | Effetto | Default |
|---|---|---|
| `--xlsx PATH` | percorso del registro xlsx | `legacy/Registro_giochi.xlsx` |
| `--sheet NOME` | foglio da leggere | `Tabella` |
| `--output PATH` | dove scrivere il JSON | `output/catalogo_bgg.json` |
| `--delay SECONDI` | pausa base tra un gioco e l'altro (con jitter casuale) | `5.0` |
| `--limit N` | elabora solo le prime N righe | tutte |
| `--resume` | riprende/aggiorna invece di ripartire da zero | disattivo |
| `--game "Nome"` | ricerca puntuale invece del registro completo | — |
| `--json` | con `--game`, stampa il risultato in JSON | testo semplice |

## 5. Formato del catalogo generato

`output/catalogo_bgg.json` è un array di oggetti, uno per gioco:

```json
{
  "Gioco": "Catan",
  "Posizione": "Cubo 1",
  "annotazioni": null,
  "bgg_id": "13",
  "giocatori_min": 3,
  "giocatori_max": 4,
  "durata_minuti": 120,
  "peso": 2.28,
  "tipo": ["Strategy Game"],
  "categorie": ["Economic", "Negotiation"],
  "meccaniche": ["Dice Rolling", "Trading", "..."],
  "famiglie": ["Game: Catan", "..."],
  "bgg_errore": null
}
```

- `Gioco` e `Posizione` sono sempre il testo esatto del registro.
- `annotazioni` contiene testo tra parentesi o virgolette estratto dal nome (es. `Kites "il tempo vola"` → `annotazioni: "il tempo vola"`), se presente.
- `bgg_errore` è `null` se la ricerca è andata a buon fine, altrimenti contiene il messaggio d'errore (es. "Nessun gioco trovato su BGG"): capita per titoli con refusi non riconosciuti, espansioni di nicchia o autoprodotti non presenti su BGG.

## 6. Pubblicare il front-end aggiornato

Dopo aver rigenerato `output/catalogo_bgg.json`, copialo nella cartella `web/` (che ha una copia propria, per essere autosufficiente quando la carichi sul tuo hosting):

```bash
cp output/catalogo_bgg.json web/catalogo_bgg.json
```

Per provare la pagina in locale prima di pubblicarla:

```bash
cd web && python3 -m http.server 8000
```

e apri `http://localhost:8000/catalogo.html` nel browser.

Per pubblicare: carica **tutto il contenuto** della cartella `web/` (`catalogo.html`, `catalogo.php`, `catalogo_bgg.json`, `catalogo-volpe.jpeg`) nella stessa cartella sul tuo hosting, sovrascrivendo i file precedenti. Il sito si apre da `catalogo.php`.

## Note

- `legacy/` non va mai modificato dagli strumenti in questo repo: è il dato sorgente (il registro che compili) e il sito originale, tenuto come riferimento.
- Non committare mai `API_token.md` con un token reale (è già in `.gitignore`).
