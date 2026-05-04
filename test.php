<?php
require 'config/database.php';

$d2 = $db->prepare('DELETE FROM products WHERE id = 1');
if (!$d2->execute()) {
    echo "Error: " . $db->error;
} else {
    echo "Deleted!";
}
