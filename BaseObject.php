<?php
/**
 * @package		CF
 * @subpackage	BaseObject - Standard
 * @author		CF Team
 * @link		http://codefansi.com
 */

class BaseObject{
    /**
     * Flag to determine that object data can be changeable or not
     * 
     * @var type boolean
     */
    protected $_isChangeable = true;
    
    /**
     *
     * @var type Object attribute
     * @var array
     */
	protected $_data = array();
    
    /**
     * Data changes flag (true after setData|unsetData call)
     * @var $_hasDataChange bool
     */
    protected $_hasDataChanges = false;
    
    /**
    * Original data that was loaded
    *
    * @var array
    */
    protected $_origData;
    
    /**
     * Object delete flag
     *
     * @var boolean
     */
    protected $_isDeleted = false;
    
	/**
     * Setter/Getter underscore transformation cache
     *
     * @var array
     */
    protected static $_underscoreCache = array();
    
 	public function __construct(){
        $args = func_get_args();
        $this->_init((array)array_shift($args));
        // legacy level construct
        $this->_construct();
 	}
	
    protected function _construct(){}
    
    protected function _init(array $arguments = array()){
        // Set private static properties
        $c = new \ReflectionClass(get_class($this));
        $statics_key = $c->getStaticProperties();
        if( $arguments ){
            $data = array();
            foreach($arguments as $k => $v){
                if(property_exists($this, $k) ){
                    $this->{$k} = $v;
                }
                elseif( in_array($k, $statics_key) ){
                    self::$$k = $v;
                }
                else{
                    $data[$k] = $v;
                }
            }
            $this->assign($data);
        }
    }
    
    /**
     * Add data to the object.
     *
     * Retains previous data in the object.
     *
     * @param array $arr
     * @return Varien_Object
     */
    public function addData(array $arr)
    {
        foreach($arr as $index=>$value) {
            $this->setData($index, $value);
        }
        return $this;
    }

    /**
     * Overwrite data in the object.
     *
     * $key can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will merge with the data in the object.
     *
     * @param string|array $key
     * @param mixed $value
     * @return Varien_Object
     */
    public function setData($key, $value=null)
    {
        if( !$this->_isChangeable ){
            return $this;
        }
        
        $this->_hasDataChanges = true;
        if(is_array($key)) {
            $this->_data = array_merge($this->_data, $key);
            $this->_addFullNames();
        } else {
            $this->_data[$key] = $value;
        }
        return $this;
    }

    /**
     * Unset data from the object.
     *
     * $key can be a string only. Array will be ignored.
     *
     * @param string $key
     * @return Varien_Object
     */
    public function unsetData($key=null)
    {
        $this->_hasDataChanges = true;
        if (is_null($key)) {
            $this->_data = array();
        } else {
            unset($this->_data[$key]);
        }
        return $this;
    }
    
    /**
     * Retrieves data from the object
     *
     * If $key is empty will return all the data as an array
     * Otherwise it will return value of the attribute specified by $key
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member.
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key='', $default=null)
    {
        if (''===$key) {
            return $this->_data;
        }
        
        // accept a/b/c as ['a']['b']['c']
        if (strpos($key,'/')) {
            $keyArr = explode('/', $key);
            $data = $this->_data;
            foreach ($keyArr as $i=>$k) {
                if ($k==='') {
                    return $default;
                }
                if (is_array($data)) {
                    if (!isset($data[$k])) {
                        return $default;
                    }
                    $data = $data[$k];
                } elseif ($data instanceof Base) {
                    $data = $data->getData($k);
                } else {
                    return $default;
                }
            }
            return $data;
        }
        
        if( $default !== NULL ){
            return !empty($this->_data[$key]) ? $this->_data[$key] : $default;
        }
        
        return isset($this->_data[$key]) ? $this->_data[$key] : $default;
    }

    /**
     * Get value from _data array without parse key
     *
     * @param   string $key
     * @return  mixed
     */
    protected function _getData($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }
	
    /**
     * Set _isDeleted flag value (if $isDeleted param is defined) and return current flag value
     *
     * @param boolean $isDeleted
     * @return boolean
     */
    public function isDeleted($isDeleted=null)
    {
        $result = $this->_isDeleted;
        if (!is_null($isDeleted)) {
            $this->_isDeleted = $isDeleted;
        }
        return $result;
    }

    /**
     * Get data change status
     *
     * @return bool
     */
    public function hasDataChanges()
    {
        return $this->_hasDataChanges;
    }
    
	/**
	 * Add more param to data array to use in View
	 */

	 private function mergeData($data, $replace = true){
	 	if( is_object($data) ){
	 		$data = get_object_vars($data);
	 	}
		
		if( ! $replace == true ){
			foreach((array)$data as $key => $value){
				$this->setData($key, $value);
			}
		}
		else{
			$this->_data = array_merge($this->_data, (array)$data);
		}

		return $this;
	}
	
	/**
	 * An alias of merge_data method
	 */
	public function assign($data, $replace = true){
		if(is_string($data)){
			$args = func_get_args();
			
			if( isset($args[1]) ){
				$replace = isset($args[2]) ? (bool) $args[2] : true;
				$this->mergeData(array($args[0] => $args[1]), $replace);
			}
			return $this;
		}
		
		$this->mergeData((array)$data, $replace);
		return $this;
	}
	
    /**
     * If $key is empty, checks whether there's any data in the object
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     * @return boolean
     */
    public function hasData($key='')
    {
        if (empty($key) || !is_string($key)) {
            return !empty($this->_data);
        }
        return array_key_exists($key, $this->_data);
    }
    
	/**
	 * Check if a property is public or not
	 */
	private function _is_public_property($property){
		$ref = new \ReflectionClass($this);
		$properties = array_merge($ref->getProperties(\ReflectionProperty::IS_PROTECTED), $ref->getProperties(\ReflectionProperty::IS_PRIVATE));
		
		foreach( $properties as &$p){
			if( $property === $p->name ){
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Check if a property is public or not
	 */
	private function _is_public_method($method){
		$ref = new \ReflectionClass($this);
		$methods = array_merge($ref->getMethods(\ReflectionClass::IS_PROTECTED, $ref->getMethods(\ReflectionClass::IS_PRIVATE)));
		
		foreach( $methods as &$m){
			if( $method === $m->name ){
				return false;
			}
		}
		
		return true;
	}
	
	/**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get' :
                $key = $this->_underscore(substr($method,3));
                $data = $this->getData($key, isset($args[0]) ? $args[0] : null);
                return $data;

            case 'set' :
                $key = $this->_underscore(substr($method,3));
                $result = $this->setData($key, isset($args[0]) ? $args[0] : null);
                return $result;

            case 'uns' :
                $key = $this->_underscore(substr($method,3));
                $result = $this->unsetData($key);
                return $result;

            case 'has' :
                $key = $this->_underscore(substr($method,3));
                return isset($this->_data[$key]);
        }
    }

    /**
     * Attribute getter (deprecated)
     *
     * @param string $var
     * @return mixed
     */

    public function __get($var)
    {
        $var = $this->_underscore($var);
        return $this->getData($var);
    }

    /**
     * Attribute setter (deprecated)
     *
     * @param string $var
     * @param mixed $value
     */
    public function __set($var, $value)
    {
        $var = $this->_underscore($var);
        $this->setData($var, $value);
    }
    
    public function _clone(){
        return clone $this;
    }
    
    /**
     * checks whether the object is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        if (empty($this->_data)) {
            return true;
        }
        return false;
    }

    /**
     * Converts field names for setters and geters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unneccessary preg_replace
     *
     * @param string $name
     * @return string
     */
    public function _underscore($name)
    {
        if (isset(self::$_underscoreCache[$name])) {
            return self::$_underscoreCache[$name];
        }
        
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        self::$_underscoreCache[$name] = $result;

        return $result;
    }
    
 }

