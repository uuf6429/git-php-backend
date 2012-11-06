<?php
	
	/**
	 * A class to wrap arround shell execution functionality.
	 */
	class ExecCommand {
		/**
		 * The exit code returned by the command. Only 0 is deemed as a success.
		 * @var integer
		 */
		public $return = 0;
		
		/**
		 * The output as returned by the command.
		 * @var type 
		 */
		public $stdout = '';
		
		/**
		 * The error as returned by the command.
		 * @var string 
		 */
		public $stderr = '';
		
		/**
		 * The command to run.
		 * @var string
		 */
		public $command = '';
		
		/**
		 * Environment variables (null to use existing).
		 * @var array|null 
		 */
		public $environment = null;
		
		/**
		 * The number of seconds taken to execute command.
		 * @var float
		 */
		public $taken = 0;
		
		/**
		 * Construct new instance.
		 * @param string $command (Optional) The command line to execute.
		 * @param array $environment (Optional) Environment variables.
		 */
		public function __construct($command=null, $environment=null){
			if($command)$this->command = $command;
			if($environment)$this->environment = $environment;
		}
		
		/**
		 * Runs command.
		 * @return ExecCommand The current object (for chaining).
		 */
		public function run(){
			$cmd = $this->command;
			
			// This hack fixes a legacy issue in popen not handling escaped command filenames on Windows.
			// Basically, if we're on windows and the first command part is double quoted, we CD into the
			// directory and execute the command from there.
			// Example: '"C:\a test\b.exe" -h'  ->  'cd "C:\a test\" && b.exe -h'
			$uname = strtolower(php_uname());
			$is_win = (strpos($uname,'win')!==false) && (strpos($uname,'darwin')===false);
			if($is_win && is_string($ok = preg_replace(
					'/^(\s*)"([^"]*\\\\)(.*?)"(.*)/s', // pattern
					'$1cd "$2" && "$3" $4',            // replacement
					$cmd ))) $cmd = $ok;               // success!
			
			// start profiling execution
			$this->taken  = microtime(true);
			
			// the pipes we will be using
			$pipes = array();
			$desc = array(
				0 => array('pipe', 'r'), // STDIN
				1 => array('pipe', 'w'), // STDOUT
				2 => array('pipe', 'w')  // STDERR
			);
			
			// create the process
			$proc = proc_open($cmd, $desc, $pipes, null, $this->environment);

			// this code is a bit faulty - it blocks on input, leading to a deadlock
			$this->stdout = stream_get_contents($pipes[1]);
			$this->stderr = stream_get_contents($pipes[2]);

			// close used resources
			foreach($pipes as $pipe)fclose($pipe);
			$this->return = proc_close($proc);

			// calculate time taken
			$this->taken = microtime(true) - $this->taken;

			// return result
			return $this;
		}
	}

?>