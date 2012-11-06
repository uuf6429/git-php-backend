<?php

/* In all honesty, I forgot how some of C works, so I did what I used to do
 * in such cases, convert the code to something I understand better. It seems to
 * work since, although I'm still not sure what this function does, I've at least
 * found a way to curcumvent it: PATH_INFO must always start with a "\".
 * 
 * The code below is a direct translation from the C equivalent as seen here:
 * https://github.com/git/git/blob/master/path.c#L650
 * This function is being used in git-http-backend, as shown here:
 * https://github.com/git/git/blob/master/http-backend.c#L504
 */

/**
 * Returns whether path is an alias or not (?).
 * @param string $p Original path.
 * @return integer A value of -1 is true, whereas 0 is false.
 */
function daemon_avoid_alias($p)
{
	$sl = 0; 
	$ndot = 0;

	/*
	 * This resurrects the belts and suspenders paranoia check by HPA
	 * done in <435560F7.4080006@zytor.com> thread, now enter_repo()
	 * does not do getcwd() based path canonicalization.
	 *
	 * sl becomes true immediately after seeing '/' and continues to
	 * be true as long as dots continue after that without intervening
	 * non-dot character.
	 */
	if (!$p || ($p[0] != '/' && $p[0] != '~'))
		return -1;
	$sl = 1;
	$ndot = 0;
	$p++;// ??

	while (true) {
		$ch = $p++; // ??
		if ($sl) {
			if ($ch == '.')
				$ndot++;
			else if ($ch == '/') {
				if ($ndot < 3)
					/* reject //, /./ and /../ */
					return -1;
				$ndot = 0;
			}
			else if ($ch == 0) {
				if (0 < $ndot && $ndot < 3)
					/* reject /.$ and /..$ */
					return -1;
				return 0;
			}
			else
				$sl = $ndot = 0;
		}
		else if ($ch == 0)
			return 0;
		else if ($ch == '/') {
			$sl = 1;
			$ndot = 0;
		}
	}
}

?><pre><?php
foreach(array(
	'',
	'/',
	'../',
	'C:\\Windows\\',
	'/var/www/html',
) as $test){
	$r = daemon_avoid_alias($test);
	echo 'daemon_avoid_alias("'.$test.'") = '
		.var_export($r, true)
		.' ['.($r ? 'T' : 'F').']'.PHP_EOL;
}
?></pre>