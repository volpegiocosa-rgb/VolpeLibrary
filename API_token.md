# Token API BGG

Placeholder. Quando avrai il token approvato da BoardGameGeek, impostalo come variabile d'ambiente prima di eseguire `scripts/bgg_lookup.py`:

```bash
export BGG_API_TOKEN="<il-tuo-token>"
```

Lo script legge il token da questa variabile d'ambiente (`os.environ["BGG_API_TOKEN"]`) e lo invia come header `Authorization: Bearer <token>`. Non è ancora stato testato contro l'API reale: appena il token è disponibile, verificalo con un gioco noto, es.:

```bash
python3 scripts/bgg_lookup.py "Catan" --json
```
