<?php
// Run once to create tables. Remove or protect after use.
require 'config.php';

if (!file_exists(__DIR__.'/data/gym.sqlite')) {
    // ensure directory exists
    if (!is_dir(__DIR__.'/data')) mkdir(__DIR__.'/data', 0755, true);
}

$queries = [
"CREATE TABLE IF NOT EXISTS agencies (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT,
  email TEXT UNIQUE,
  password TEXT,
  created_at TEXT DEFAULT (datetime('now'))
);",

"CREATE TABLE IF NOT EXISTS members (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  agency_id INTEGER NOT NULL,
  full_name TEXT,
  phone TEXT,
  registration_date TEXT,
  last_payment_date TEXT,
  membership_type TEXT,
  membership_duration_months INTEGER DEFAULT 1,
  membership_price REAL DEFAULT 0,
  notes TEXT,
  created_at TEXT DEFAULT (datetime('now')),
  FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE
);",

"CREATE TABLE IF NOT EXISTS payments (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  member_id INTEGER NOT NULL,
  agency_id INTEGER NOT NULL,
  amount REAL,
  paid_at TEXT,
  created_at TEXT DEFAULT (datetime('now')),
  FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
  FOREIGN KEY (agency_id) REFERENCES agencies(id) ON DELETE CASCADE
);"
];

foreach ($queries as $q) {
    $pdo->exec($q);
}

echo "Tables created successfully.<br>";

// Create a default admin agency if not exists
$exists = $pdo->query("SELECT COUNT(*) FROM agencies")->fetchColumn();
if ($exists == 0) {
    $pw = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO agencies (name, email, password) VALUES (?, ?, ?)")->execute(['Admin Gym', 'admin@gym.local', $pw]);
    echo "Admin agency created (email: admin@gym.local / password: admin123).\n";
}
?>