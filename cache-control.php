<?php
class Cache {
    private $_salt = "RANDOM TEXT HERE"; // <-- change before usage!
    private $_name;
    private $_dir;
    private $_extension;
    private $_path;
    public function __construct($name = "default", $dir = "tmp/", $extension = ".cache") 
    {
        if(md5($this->_salt) == "233abed7ee9945c0429047405d864283") throw new Exception("Change _salt value before usage! (line 5)");
        if($name == null) throw new Exception("Invalid name argument (empty or null)");
        if($dir == null) throw new Exception("Invalid dir argument (empty or null)");
        if($extension == null) throw new Exception("Invalid extension argument (empty or null)");
        $dir = str_replace("\\", "/", $dir);
        if(preg_match("/[^a-z0-9\.]/i", $name, $matches)) throw new Exception("Invalid name argument (must be alphanumeric)");
        if(preg_match("/[^a-z0-9\/]/i", $dir, $matches)) throw new Exception("Invalid dir argument (must be alphanumeric)");
        if(preg_match("/[^a-z0-9\.]/i", $extension, $matches)) throw new Exception("Invalid extension argument (must be alphanumeric)");
        if(!$this->endsWith($dir, "/"))
        {
            $dir .= "/";
        }
        $this->_name = $name;
        $this->_dir = $dir;
        $this->_extension = $extension;
        $this->_path = $this->getPath();
        $this->checkDir();
    }
    public function set($key, $value, $ttl = -1)
    {
        $data = [
            "t" => time(),
            "e" => $ttl,
            "v" => serialize($value),
        ];
        $cache = $this->getCache();
        if($cache == null)
        {
            $cache = [
                $key => $data,
            ];
        }
        else
        {
            $cache[$key] = $data;
        }
        $this->setCache($cache);
    }
    public function get($key, &$out)
    {
        $cache = $this->getCache();
        if(!is_array($cache)) return false;
        if(!array_key_exists($key, $cache)) return false;
        $data = $cache[$key];
        if($this->isExpired($data))
        {
            unset($cache[$key]);
            $this->setCache($cache);
            return false;
        }
        $out = unserialize($data["v"]);
        return true;
    }
	
	public function remove($key)
	{
		$cache = $this->getCache();
        if(!is_array($cache)) return false;
        if(!array_key_exists($key, $cache)) return false;
		
		unset($cache[$key]);
		$this->setCache($cache);
		return true;
	}
    private function isExpired($data)
    {
        if($data["e"] == -1) return false;
        $expiresOn = $data["t"] + $data["e"];
        return $expiresOn < time();
    }
    private function setCache($json)
    {
        if(!is_array($json)) throw new Exception("Invalid cache (not an array?)");
        $content = json_encode($json);
        file_put_contents($this->_path, $content);
    }
    private function getCache()
    {
        if(!file_exists($this->_path)) return null;
        $content = file_get_contents($this->_path);
        return json_decode($content, true);
    }
    private function getPath() 
    {
        return $this->_dir . md5($this->_name . $this->_salt) . $this->_extension;
    }
    private function checkDir()
    {
        if(!is_dir($this->_dir) && !mkdir($this->_dir, 0775, true))
        {
            throw new Exception("Unable to create cache directory ($this->_dir)");
        }
        if(!is_readable($this->_dir) || !is_writable($this->_dir))
        {
            if(!chmod($this->_dir, 0775))
            {
                throw new Exception("Cache directory must be readable and writable ($this->_dir)");
            }
        }
        return true;
    }
    private function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return $length === 0 || (substr($haystack, -$length) === $needle);
    }
}
