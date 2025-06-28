<?php
define('MODE_COMPOUND', 'compound');
define('MODE_HI_SCORE', 'hi_score');

$config = [
  "dev-sum" => [
    "mode"    => MODE_COMPOUND,
    "secret"  => "1234",
  ],
  "dev-max" => [
    "mode"    => MODE_HI_SCORE,
    "secret"  => "5678",
  ],
];
?>
