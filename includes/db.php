<?php
/** Database helpers (PDO) — works with MySQL and SQLite */

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    if (DB_DRIVER === 'sqlite') {
        $dir = dirname(DB_SQLITE_PATH);
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null, $opts);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');
    } else {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
        } catch (PDOException $e) {
            if (defined('INSTALLING')) throw $e;
            db_error_page($e);
        }
    }
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function row(string $sql, array $params = []): ?array
{
    $r = q($sql, $params)->fetch();
    return $r ?: null;
}

function rows(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function val(string $sql, array $params = [])
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

function insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = "INSERT INTO $table (" . implode(',', $cols) . ") VALUES (" . implode(',', array_fill(0, count($cols), '?')) . ")";
    q($sql, array_values($data));
    return (int) db()->lastInsertId();
}

function update(string $table, array $data, string $where, array $whereParams = []): int
{
    $set = implode(',', array_map(fn($c) => "$c = ?", array_keys($data)));
    return q("UPDATE $table SET $set WHERE $where", array_merge(array_values($data), $whereParams))->rowCount();
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

/** Friendly page when MySQL is off or the database name in config.php is wrong */
function db_error_page(PDOException $e): void
{
    http_response_code(500);
    $code = (int)($e->errorInfo[1] ?? $e->getCode());
    if ($code === 1049) {
        $title = 'Database “' . DB_NAME . '” was not found';
        $help = 'Open phpMyAdmin and import the file <b>pretty_layers.sql</b> (Import tab → Choose file → Go), '
              . 'or change <code>DB_NAME</code> in <code>config.php</code> to your database name.';
    } elseif ($code === 1045) {
        $title = 'Wrong database username or password';
        $help = 'Check <code>DB_USER</code> and <code>DB_PASS</code> in <code>config.php</code>.';
    } else {
        $title = 'Cannot connect to MySQL';
        $help = 'Make sure <b>MySQL</b> is started in the XAMPP Control Panel, then refresh this page.';
    }
    echo '<!DOCTYPE html><meta charset="utf-8"><title>Database error</title><body style="font-family:system-ui;background:#FCF6FB;display:grid;place-items:center;min-height:100vh;margin:0">'
       . '<div style="max-width:520px;background:#fff;padding:32px;border-radius:20px;box-shadow:0 20px 50px rgba(75,22,72,.15);border-top:6px solid #B01FB3">'
       . '<h2 style="color:#4B1648;margin-top:0">' . htmlspecialchars($title) . '</h2><p style="line-height:1.6">' . $help . '</p>'
       . (DEBUG ? '<p style="color:#999;font-size:13px">' . htmlspecialchars($e->getMessage()) . '</p>' : '') . '</div>';
    exit;
}
