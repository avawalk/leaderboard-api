<?php
    // Allow requests from any origin (not recommended for sensitive data)
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    include './config.php';
    include './db.php';

    // craft response template
    $resp = [
      'success' => false,
      'message' => null,
    ];

    // validate
    $allowed_sort_by = [ 'score', 'hi_score' ];
    $default_sort_by = 'score';
    $default_limit = 10;

    // [GET] query board
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $board = $_GET['board'] ?? '<NOTFOUND>';
        $sort_by = $_GET['sort_by'] ?? $default_sort_by;
        $limit = $_GET['limit'] ?? $default_limit;
        $cfg = $config[$board] ?? null;

        if ($cfg === null) {
          $resp['message'] = 'board not found';
        }
        else {
          if (!in_array($sort_by, $allowed_sort_by)) $sort_by = $default_sort_by;
          if (!filter_var($limit, FILTER_VALIDATE_INT) || $limit < 1 || $limit > 1000) $limit = $default_limit;

          $sql = "SELECT `code`, `plays`, `score`, `hi_score`, `screen_time` FROM `scores` WHERE `board` = ? ORDER BY `$sort_by` DESC LIMIT $limit;";
          $stmt = $conn->prepare($sql);
          $stmt->execute([ $board ]);
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          $resp['success'] = true;
          $resp['data'] = $rows;
        }
    }

    // [POST] add score
    else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $code = $_POST['code'] ?? null;
        $score = $_POST['score'] ?? null;
        $secret = $_POST['secret'] ?? null;
        $stime = $_POST['screen_time'] ?? 0;

        $board = $_POST['board'] ?? '<NOTFOUND>';
        $cfg = $config[$board] ?? null;
        $board_secret = ($cfg === null) ? null : $cfg['secret'];

        if ($cfg === null) {
          $resp['message'] = 'board not found';
        }
        else if (($code === null) || ($score === null) || ($secret === null)) {
          $resp['message'] = 'invalid input';
        }
        else if ($secret != $board_secret) {
          $resp['message'] = 'invalid secret';
        }
        else {
            // find code
            $stmt = $conn->prepare("SELECT `plays`, `score`, `hi_score`, `screen_time` FROM `scores` WHERE `board` = ? AND `code` = ? LIMIT 1;");
            $stmt->execute([ $board, $code ]);
            $row = $stmt->fetch();

            if ($row === false) { // add new code
              $stmt = $conn->prepare("INSERT INTO `scores` (`board`, `code`, `plays`, `score`, `hi_score`, `screen_time`) VALUES (?, ?, ?, ?, ?, ?);");
              $stmt->execute([ $board, $code, 1, $score, $score, $stime ]);
            }
            else { // update existing code
              $plays = $row['plays'] + 1;
              $new_score = $row['score'] + $score;
              $hi_score = $score > $row['hi_score'] ? $score : $row['hi_score'];
              $new_stime = $row['screen_time'] + $stime;
              $stmt = $conn->prepare("UPDATE `scores` SET `plays` = ?, `score` = ?, `hi_score` = ?, `screen_time` = ?, `updated_at` = CURRENT_TIMESTAMP WHERE `board` = ? AND `code` = ?;");
              $stmt->execute([ $plays, $new_score, $hi_score, $new_stime, $board, $code ]);
            }
            // stamp success
            $resp['success'] = true;
        }
    }

    // return json
    header("Content-Type: application/json");
    echo json_encode($resp);
?>
