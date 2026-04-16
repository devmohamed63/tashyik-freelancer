<?php
$file = 'storage/framework/views/8ae1005cf30495cf29f6fc6efcd29606.php';
$code = file_get_contents($file);

// Count if/endif at PHP token level
$tokens = token_get_all($code);
$ifStack = [];
$ifCount = 0;
$endifCount = 0;

foreach ($tokens as $i => $token) {
    if (is_array($token)) {
        $tokenName = token_name($token[0]);
        $line = $token[2];
        
        if ($token[0] === T_IF) {
            $ifCount++;
            // Check if this is colon-style
            $snippet = substr($token[1], 0, 20);
            echo "IF at line $line: " . trim($snippet) . "\n";
        }
        if ($token[0] === T_ENDIF) {
            $endifCount++;
            echo "ENDIF at line $line\n";
        }
        if ($token[0] === T_ELSEIF) {
            echo "ELSEIF at line $line\n";
        }
    }
}

echo "\nTotal IF: $ifCount, Total ENDIF: $endifCount\n";
echo "Mismatch: " . ($ifCount - $endifCount) . "\n";
