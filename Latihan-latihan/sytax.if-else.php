<?php
$a = 5;
$b = 7;
echo "\$a = $a <BR>";
echo "\$b = $b <BR>";
if ($a == $b):
echo '$s sama dengan $b';
elseif ($a > $b):
echo '$a lebih besar daripada $b';
else:
echo '$a lebih kecil daripada $b';
endif;
?>