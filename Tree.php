<?php
/**
 * @package		CF
 * @subpackage	Tree Helper
 * @author		CF Team
 * @link		http://codefansi.com
 */

// Include baseObject
include_once 'BaseObject.php';

class Tree extends BaseObject{
    /**
     * Tree | Nodes list
     * @var type array
     */
    private $_tree = array();
    /**
     * Node id list
     * @var type array
     */
    private $_nodeListId = array();  
    
    public function __construct() {
        $args = func_get_args();
        if( $nodes = (array)array_shift($args) ){
            $this->addNode($nodes);
        }
        // legacy level construct
        $this->_construct();
    }
    
    /**
     * Add node to tree
     * 
     * @param array $items
     * @return object Tree
     */
    public function addNode(array $items = array()){
        foreach($items as $item){
            $itemId = $this->getItemId($item);
            $this->_tree[$itemId] = $item;
            $this->_nodeListId[] = $itemId;
        }
        return $this;
    }
    
    /**
     * Check if an item is a node of a tree
     * 
     * @param array|object|integer $item
     * @return boolean
     */
    public function hasNode($item){
        return in_array($this->getItemId($item), $this->_nodeListId);
    }
    
    /**
     * Get tree node
     * @param array|object|integer $item
     * @return misc $node
     */
    public function getNode($item){
        $itemId = $this->getItemId($item);
        return isset($this->_tree[$itemId]) ? $this->_tree[$itemId] : null;
    }
    
    /**
     * Get node's parent
     * 
     * @param array|object|integer $node
     * @return type misc
     */
    public function getParent($node){
        foreach($this->_nodeListId as $nodeId){
            if( $nodeId === $this->getItemPid($node) ){
                return $this->_tree[$nodeId];
            }
        }
        return null;
    }
    
    /**
     * Get node children
     * 
     * @param array|object|integer $node
     * @return array $children
     */
    public function getChildren($node){
        $children = array();
        $nodeId = $this->getItemId($node);
        foreach($this->_tree as $item){
            if( $nodeId === $item->getPid() ){
                $children[$item->getId()] = $item;
            }
        }
        return $children;
    }
    
    /**
     * Check if a node is descendant of one another
     * 
     * @param array|object|integer $maybeChild
     * @param array|object|integer $maybeParent
     * @param boolean $flag
     * @return boolean
     */
    public function checkIsDescendant($maybeChild, $maybeParent, $flag = false){
        if( ! $parent = $this->getParent($maybeChild) ){
            return false;
        }
        
        if( $this->getItemPid($maybeChild) === $this->getItemId($maybeParent) ){
            return true;
        }
        
        if( ! $flag == true ){
            $flag = $this->checkIsDescendant($parent, $maybeParent, $flag);
        }
        
        return $flag;
    }
    
    /**
     * Generate node key as a deep level string format,
     * so that we can get tree structure by sorting an array of generated keys
     * 
     * @param misc $node
     * @param boolean $recur
     * @return string
     */
    public function generateNodeKey($node, $recur = false){
        $key = $this->getItemId($node);
        $parent = $this->getParent($node);
        if( $parent ){
            // generate key recusively
            $key = $this->generateNodeKey($parent, true) . '_'.$key;
        }
        else{
            $key = $this->getItemPid($node) .'_'. $key;
            if( $recur == true ){
                return $key;
            }
        }
        return $key;
    }
    
    /**
     * Get node id
     * 
     * @param misc $node
     * @return integer
     */
    public function getItemId($node){
        return $this->getNumericField($node, 'id');
    }
    
    /**
     * get node pid
     * 
     * @param misc $node
     * @return integer
     */
    public function getItemPid($node){
        return $this->getNumericField($node, 'pid');
    }
    
    protected function getNumericField($node, $field){
        $value = null;
        if( $node instanceof BaseObject){
            $value = $node->getData($field);
        }
        elseif(is_array($node)){
            if( isset($node[$field]) ){
                $value = $node[$field];
            }
        }
        elseif(is_numeric($node) ){
            $value = $node;
        }
        return $value;
    }
}