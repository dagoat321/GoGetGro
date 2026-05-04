<?php
$sql = file_get_contents("gogetgro_data_apex_clean.sql");

// Replace datetime strings with TO_TIMESTAMP
$sql = preg_replace("/'(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})'/", "TO_TIMESTAMP('$1', 'YYYY-MM-DD HH24:MI:SS')", $sql);

// Wrap in BEGIN ... END;
$lines = explode("\n", $sql);
$out = "BEGIN\n";
foreach($lines as $line) {
    if(trim($line) !== "" && strpos($line, "--") !== 0) {
        $out .= $line . "\n";
    }
}
$out .= "COMMIT;\nEND;\n/";

file_put_contents("gogetgro_data_apex_final.sql", $out);
?>
