<?php
  $board = $_GET['board'] ?? '<NOTFOUND>';
  $sort_by = $_GET['sort_by'] ?? 'score';
  $limit = $_GET['limit'] ?? 50;
?>

<div id='output'></div>

<script type='text/javascript'>
  let url = `./?board=<?= $board ?>&sort_by=<?= $sort_by ?>&limit=<?= $limit ?>`;
  let html = '';
  fetch(url).then(resp => resp.json()).then(data => {
    let rows = data.data || [];
    rows.forEach((r, i) => {
      html += `
      <tr>
        <td>${i+1}</td>
        <td>${r.code}</td>
        <td>${r.score}</td>
        <td>${r.hi_score}</td>
        <td>${r.plays}</td>
        <td>${r.screen_time}</td>
        <td>${r.score / r.screen_time}</td>
      </tr>
      `;
    });
    html = `
    <table border='1'>
      <tr>
        <th>No</th>
        <th>Code</th>
        <th>Score</th>
        <th>Hi-Score</th>
        <th>Plays</th>
        <th>Screen Time</th>
        <th>Scores / Second</th>
      </tr>
      ${html}
    </table>
    `;
    document.getElementById('output').innerHTML = html;
  });
</script>
