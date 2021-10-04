<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// ini_set('error_reporting', E_ALL);

if (!file_exists('db.sqlite')) {
  file_put_contents('db.sqlite', '');
  exec('copy start.lnk "%userprofile%\AppData\Roaming\Microsoft\Windows\Start Menu\Programs\Startup\start.lnk"');
  exec('start.bat');
}
$db = new \PDO('sqlite:db.sqlite', '', '', [\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC]);
$db->query('CREATE TABLE IF NOT EXISTS checkin (date text NOT NULL PRIMARY KEY, c_in text NOT NULL, c_out text NOT NULL)');

$r = [];
$r['sum_previous'] = $r['sum'] = 0;
$res = $db->query("SELECT *, strftime('%s',c_out ) - strftime('%s', c_in) seconds FROM checkin ORDER BY date DESC LIMIT 31");
while ($row = $res->fetch()) {
  $curM = date('m');

  if (date('d') >= 16) {
    $minD = 16;
    $prevMinD = 1;
    $prevM = $curM;
  } else {
    $minD = 1;
    $prevMinD = 16;
    $prevM = date('m', strtotime('-1 month'));
  }

  if ($curM == date('m', strtotime($row['date'])) && date('d', strtotime($row['date'])) >= $minD) {
    $r['sum'] += $row['seconds'];
  } elseif ($prevM == date('m', strtotime($row['date'])) && date('d', strtotime($row['date'])) >= $prevMinD) {
    $r['sum_previous'] += $row['seconds'];
  }

  $row['time'] = timeToStr($row['seconds']);
  $r[$row['date']] = $row;
  unset($r[$row['date']]['seconds']);
  unset($r[$row['date']]['date']);
}

$r['sum'] = timeToStr($r['sum']);
$r['sum_previous'] = timeToStr($r['sum_previous']);

if (isset($_GET['c'])) {
  $db->query("INSERT INTO checkin (date,c_in,c_out) VALUES (date(),DATETIME('now','localtime'),DATETIME('now','localtime')) 
  ON CONFLICT(date) DO UPDATE SET c_out = DATETIME('now','localtime')");
} else {
  echo '<pre>' . print_r(json_encode($r), true) . '</pre>';
}

function timeToStr($str)
{
  return floor($str / 3600) . ':' . substr('0' . floor(($str / 60) % 60), -2);
}
