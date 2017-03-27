<?php

$input = file_get_contents("php://input");
if (!$input) {
	die('error: input empty');
}

echo "ok\n";
echo '<pre>'.print_r(json_decode($input),1).'</pre>';
