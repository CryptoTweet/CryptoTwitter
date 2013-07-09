<?php
/**
* Error Logging Interface
*
* We use this as a simple mechanism to access the logging
* class and send messages to be logged.
*
* @access	public
* @return	void
*/
if ( ! function_exists('log_message'))
{
	function log_message($level = 'error', $message, $php_error = FALSE)
	{
		static $_log;

		if (config_item('log_threshold') == 0)
		{
			return;
		}

		$_log =& load_class('Log');
        $backtrace = debug_backtrace();
        
		$_log->write_log($level, $message."\r\n".print_r($backtrace,true), $php_error);
	}
}
?>
