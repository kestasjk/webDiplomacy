<?php

class libCache
{
	public static function dirName($name)
	{
		static $cache;

		if( !isset($cache) ) $cache=array();
		if( !isset($cache[$name]) ) $cache[$name]=self::dir('cache', array($name) );

		return $cache[$name];
	}

	public static function wipeDir($dir, $glob='*.*')
	{
		if( $files = glob($dir.'/'.$glob) )
			foreach($files as $file)
				unlink($file);
	}

	public static function privateFilename($dir, $filename)
	{
		if( !file_exists($dir.'/index.html') )
			file_put_contents($dir.'/index.html', '');

		$hash=md5($dir.$filename.Config::$secret);

		return $filename.'-'.$hash;
	}

	static public function getCacheID($base, $id, $filename, $private=false)
	{
		$dir = self::dirID($base, $id);

		if( $private )
			$filename = self::privateFilename($dir, $filename);

		if( file_exists($dir.'/'.$filename) )
			return file_get_contents($dir.'/'.$filename);
		else
			return false;
	}

	static public function putCacheID($base, $id, $filename, $data, $private=false)
	{
		$dir = self::dirID($base, $id);

		if( $private )
			$filename = self::privateFilename($dir, $filename);

		file_put_contents($dir.'/'.$filename, $data);
	}

	public static function dirID($base, $id, $absolute=false)
	{
		static $cache;

		if( !isset($cache) ) $cache=array();
		if( !isset($cache[$base]) ) $cache[$base]=array();
		if( !isset($cache[$base][$id]) )
		{
			if( $absolute )
				$cache[$base][$id]=self::dir($base, array($id, floor($id/100)) );
			else
				$cache[$base][$id]=self::dir('cache', array($id, floor($id/100), $base) );
		}

		return $cache[$base][$id];
	}

	public static function dir($dir, array $dirParts)
	{
		$name=array_pop($dirParts);

		if( is_null($name) ) return $dir;

		if( !is_dir($dir.'/'.$name) && !mkdir($dir.'/'.$name, 0775, true) )
			throw new Exception(l_t("Couldn't make cache directory '%s'.",$dir.'/'.$name));

		return self::dir($dir.'/'.$name, $dirParts);
	}

}

?>