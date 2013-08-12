<?php

namespace CodeMedic;

class Log
{
	# const _EMERG   = LOG_EMERG;
	# const _ALERT   = LOG_ALERT;
	# const _CRIT    = LOG_CRIT;
	# const _ERR     = LOG_ERR;
	# const _WARNING = LOG_WARNING;
	# const _NOTICE  = LOG_NOTICE;
	# const _INFO    = LOG_INFO;
	# const _DEBUG   = LOG_DEBUG;
	
	private static $_level = LOG_NOTICE;
	private static $_ident = 'codemedic';
	private static $_option = 0;
	private static $_facility = LOG_USER;
	
	private static $_instance = null;
	
	public static function Init($level = null, $ident = null, $option = null, $facility = null)
	{
		if (static::$_instance === null)
		{
			if (null !== $level && $level >= LOG_EMERG && $level <= LOG_DEBUG)
				static::$_level = $level;
				
			if (null !== $ident)
				static::$_ident = $ident;
	
			if (null !== $option)
				static::$_option = $option;
			static::$_option |= (LOG_ODELAY | LOG_PID);
	
			if (null !== $facility)
				static::$_facility = $facility;
				
			static::$_instance = new static();
		}
		
		return static::$_instance;
	}
	
	private function __construct()
	{
		if (false === openlog(static::$_ident, static::$_option, static::$_facility))
			trigger_error("openlog failed", E_USER_ERROR);

	}
	
	public function __destruct()
	{
		closelog();
	}
	
	private static function ToString($data)
	{
		if ($data === null)
			return '(null)';
			
		if (is_bool($data))
			return $data? 'true' : 'false';
		
		if (is_resource($data))
			return 'res('.get_resource_type($data).') #'.intval($data);
			
		if (is_object($data))
		{
			if (method_exists($data, '__toString'))
				return (string)$data;
				
			if (is_a($data, 'Exception'))
			{
				$file = realpath($data->getFile());
				$docRoot = isset($_SERVER['DOCUMENT_ROOT'])? realpath($_SERVER['DOCUMENT_ROOT']): null;
				if ($docRoot !== null && $file === strstr($file, $docRoot))
					$file = substr($file, strlen($docRoot));
				
				return sprintf('[%s code:%s] %s at %s:%d', get_class($data), $data->getCode(), $data->getMessage(), $file, $data->getLine());
			}
			
			return json_encode($data);
		}
		
		return $data;
	}
	
	public function write($data, $level)
	{
		static::WriteLine($level - 1, $data);
	}
	
	public static function WriteLine($level, $data)
	{
		if ($level > static::$_level)
			return;
			
		if (false === syslog($level, static::ToString($data)))
			trigger_error("syslog() failed", E_USER_ERROR);
	}
	
	private static function _NameToLevel($name)
	{
		static $map = array(
			'emergency' => LOG_EMERG,
			'alert' => LOG_ALERT,
			'critical' => LOG_CRIT,
			'error' => LOG_ERR,
			'warning' => LOG_WARNING,
			'notice' => LOG_NOTICE,
			'message' => LOG_NOTICE,
			'info' => LOG_INFO,
			'verbose' => LOG_INFO,
			'debug' => LOG_DEBUG,
		);
		
		$name = strtolower($name);
		if (isset($map[$name]))
			return $map[$name];

		return LOG_DEBUG;
	}
	
	public static function __callStatic($name, $args)
	{
		static::WriteLine(static::_NameToLevel($name), isset($args[0])? $args[0]: '');
	}
}
