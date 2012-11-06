git-php-backend
===============

This is a quick test of how a PHP script can be  used as the glue between apache and git-http-backend (Git Smart HTTP).

The .htaccess is required for routing 404 (fake) requests like, eg; /repos/HEAD to index.php, for processing.

For this to work, you need to have a `repos/` folder inside this projet, with your test repositories inside it.

Also note that the setup is a bit fragile. It expects git to be installed and git-http-backend available in the system PATH variable. As it is, this is not the default behavior for git on windows - either modify the variable yourself, or change the code to fake it (`$_SERVER['PATH'].';C:\\path\\to\\git'`).
Finally, it is expected that this system is run from a folder named git inside your document root.