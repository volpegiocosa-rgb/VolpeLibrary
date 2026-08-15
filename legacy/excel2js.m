function excel2js(excel_file, js_file)
  % excel_file : percorso del file Excel (input)
  % js_file    : percorso del file JS (output)
  %
  % La funzione legge le colonne A (Gioco) e B (Posizione)
  % e genera un file game_library.js compatibile.
  
  % apro paccchetto
  pkg load io

  % Legge il file Excel
  [~, ~, raw] = xlsread(excel_file);

  % Rimuove eventuali righe vuote
  raw = raw(~all(cellfun(@(x) isempty(x), raw), 2), :);

  % Apertura file di output
  fid = fopen(js_file, 'w');
  if fid == -1
    error('Impossibile aprire il file di output');
  end

  % Scrive l'intestazione
  fprintf(fid, "const gamelist = [\n");

  % Itera sulle righe del file Excel
  for i = 1:size(raw,1) % salto riga intestazione
    gioco = raw{i,1};
    posizione = raw{i,2};

    % Gestisce eventuali valori vuoti
    if isempty(gioco), gioco = ""; end
    if isempty(posizione), posizione = ""; end

    % Scrittura in formato JSON-like
    fprintf(fid, "  {\n");
    fprintf(fid, '    "Gioco": "%s",\n', gioco);
    fprintf(fid, '    "Posizione": "%s"\n', posizione);

    if i < size(raw,1)
      fprintf(fid, "  },\n");
    else
      fprintf(fid, "  }\n");
    end
  end

  % Chiude l'array e il file
  fprintf(fid, "];\n");
  fclose(fid);

  printf("✅ File '%s' generato con successo.\n", js_file);
end
