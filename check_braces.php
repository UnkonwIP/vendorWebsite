<?php
// Save as check_braces.php and run: php check_braces.php AccountSetup.php
$path = $argv[1] ?? die("Usage: php check_braces.php <file>\n");
$code = file_get_contents($path);
$inPhp = false; $line = 1; $stack = 0;
$len = strlen($code);
for ($i=0;$i<$len;$i++){
  if (!$inPhp && substr($code,$i,5) === '<?php'){ $inPhp = true; $i+=4; continue; }
  if ($inPhp && substr($code,$i,2) === '?>'){ $inPhp = false; $i+=1; continue; }
  $ch = $code[$i];
  if ($ch === "\n") $line++;
  if ($inPhp){
    if ($ch === '{') $stack++;
    if ($ch === '}'){
      if ($stack === 0){ echo "Unmatched } at line $line\n"; exit(1); }
      $stack--;
    }
  }
}
if ($stack > 0) echo "Unclosed { (count=$stack)\n"; else echo "Braces balanced\n";
?>