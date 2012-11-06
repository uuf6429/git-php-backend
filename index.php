<?php

	define('CRLF', chr(13).chr(10));
	
	define('GIT_PROJECT_ROOT', 'C:/wamp/www/git/repos');
	define('GIT_DOCUMENT_ROOT', '/git/repos');
	
	define('ProgramFiles', is_dir(getenv('ProgramFiles(x86)')) ? getenv('ProgramFiles(x86)') : getenv('ProgramFiles'));
	define('GIT_HTTP_BACKEND', '"'.ProgramFiles.'\\Git\\libexec\\git-core\\git-http-backend.exe"');
	
	require('class.ExecCommand.php');
	
	$pth = explode(GIT_DOCUMENT_ROOT, isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : '', 2);
	$pth = array_pop($pth);
	
	$env = array_merge(array(
		'PATH' => $_SERVER['PATH'],
		'GIT_PROJECT_ROOT' => GIT_PROJECT_ROOT,
		'GIT_HTTP_EXPORT_ALL' => 1,
		'PATH_INFO' => $pth,
		'REMOTE_USER' => 'TestUser',
		'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'],
		'QUERY_STRING' => $_SERVER['QUERY_STRING'],
		'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
//		'GIT_COMMITTER_NAME'
//		'GIT_COMMITTER_EMAIL'
	));
	
	$ret = new ExecCommand(GIT_HTTP_BACKEND, $env);
	$ret->run();
		
	if($ret->stderr)syslog($ret->return ? LOG_ERR : LOG_WARNING, $ret->stderr);
	
	list($head, $body, $status) = array_pad(explode(CRLF.CRLF, $ret->stdout, 2), 3, '');
	$status = preg_match('/Status: (\\d\\d\\d)/', $head, $status) ? $status[1] : null;
	foreach(explode(CRLF, $head) as $header)header($header, true, $status);
	echo $body;
	
?>