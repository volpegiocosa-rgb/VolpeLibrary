<?php
// Intestazioni HTTP per disabilitare la cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libreria Volpe Giocosa</title>
    <style>
        body { text-align: center; }
        table {
            margin: 0 auto;
            border-collapse: collapse;
            width: 80%;
        }
        th, td { padding: 10px; border: 1px solid black; }
        th { background-color: #ffc04d; }
        label, input, button { display: block; margin-bottom: 10px; }
        #inputString, button, h1 { text-align: center; margin-bottom: 10px; }
        #container label, #container input, #container button, #container h1 {
            display: block; margin: 10px auto; text-align: center;
        }
        #pagination {
            margin: 20px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

<h1>La scatola dei giochi</h1>
<img src="catalogo-volpe.jpeg" alt="Clown che esce dalla scatola dei giochi di Volpe Giocosa">

<div id="container">
    <label for="inputString">Cerca il gioco:</label>
    <input type="text" id="inputString" placeholder="Scrivi il gioco devi cercare">
    <button onclick="processInput()">Esegui</button>
</div>

<table>
    <thead>
        <tr>
            <th>Titolo</th>
            <th>Posizione</th>
            <th>E' lui?</th>
        </tr>
    </thead>
    <tbody id="risultatiTabella"></tbody>
</table>

<div id="pagination">
    <button id="prevPage" onclick="changePage(-1)">⬅️ Precedente</button>
    <span id="pageInfo">1/1</span>
    <button id="nextPage" onclick="changePage(1)">Successiva ➡️</button>
</div>

<script src="game_library-1.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const gameCountText = document.createElement("p");
    gameCountText.textContent = `Numero totale di giochi: ${gamelist.length}`;
    const container = document.getElementById("container");
    container.insertBefore(gameCountText, container.firstChild);
});

// Variabili paginazione
let currentPage = 1;
const itemsPerPage = 20;
let filteredList = [];

function processInput(){
    const inputElement = document.getElementById('inputString');
    const query = inputElement.value.toLowerCase().trim();

    if(query === '') {
        filteredList = gamelist.slice();
    } else {
        filteredList = gamelist.map(g => {
            let dist = calculateLevenshteinDistanceIgnoringSpaces(query, g.Gioco.toLowerCase());
            let exact = contieneStringa(g.Gioco.toLowerCase(), query);
            if(exact) dist = 1;
            return {...g, dist};
        });
        filteredList.sort((a,b) => a.dist - b.dist);
    }

    currentPage = 1;
    displayPage();
}

function displayPage(){
    const tbody = document.getElementById('risultatiTabella');
    tbody.innerHTML = '';

    const start = (currentPage - 1) * itemsPerPage;
    const end = Math.min(start + itemsPerPage, filteredList.length);
    const query = document.getElementById('inputString').value.toLowerCase().trim();

    for(let i = start; i < end; i++){
        const row = document.createElement('tr');
        const game = filteredList[i];

        const titleCell = document.createElement('td');
        titleCell.textContent = game.Gioco;
        row.appendChild(titleCell);

        const posCell = document.createElement('td');
        posCell.textContent = game.Posizione;
        row.appendChild(posCell);

        const distCell = document.createElement('td');
        let dist = query === '' ? 0 : game.dist;

        if(dist <= 1) distCell.textContent = '🟢';
        else if(dist <= 2) distCell.textContent = '🟠';
        else distCell.textContent = '🔴';

        if(dist === 0) row.style.fontWeight = 'bold';
        row.appendChild(distCell);

        tbody.appendChild(row);
    }

    const totalPages = Math.max(1, Math.ceil(filteredList.length / itemsPerPage));
    document.getElementById('pageInfo').textContent = `${currentPage}/${totalPages}`;
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;
}

function changePage(delta){
    currentPage += delta;
    displayPage();
}

function calculateLevenshteinDistanceIgnoringSpaces(a, b) {
    const stringWithoutSpacesA = a.replace(/[\s.,\/#!$%\^&\*;:{}=\-_`~()]/g,'');
    const stringWithoutSpacesB = b.replace(/[\s.,\/#!$%\^&\*;:{}=\-_`~()]/g,'');
    const m = stringWithoutSpacesA.length;
    const n = stringWithoutSpacesB.length;
    const matrix = Array.from({length: m+1}, ()=> Array(n+1).fill(0));

    for(let i=0;i<=m;i++) matrix[i][0]=i;
    for(let j=0;j<=n;j++) matrix[0][j]=j;

    for(let i=1;i<=m;i++){
        for(let j=1;j<=n;j++){
            const cost = stringWithoutSpacesA[i-1] === stringWithoutSpacesB[j-1] ? 0 : 1;
            matrix[i][j] = Math.min(matrix[i-1][j]+1, matrix[i][j-1]+1, matrix[i-1][j-1]+cost);
        }
    }
    return matrix[m][n];
}

function contieneStringa(base, a) {
    return base.toLowerCase().includes(a.toLowerCase()) ? 1 : 0;
}
</script>
</body>
</html>
