<?php
/**
 * @package		CF
 * @subpackage	Generate Tree Helper
 * @author		CF Team
 * @link		http://codefansi.com
 */

// Include tree helper
include_once 'Tree.php';

final class GenerateTree{
    /**
     * Render layout (may recursively)
     * 
     * @staticvar array $renderedItems
     * @staticvar int $_step
     * @param array $items
     * @param misc $template
     * @param boolean $recur
     * @param int $step
     * return void
     */
    public static function renderLayout($template, $items = array(), $recur = false, $return = false){
        $layout = array();
        if( !$items ){
            $layout = self::_renderLayout($template);
            if( ! $return == true ){
                echo $layout;
            }
            return $layout;
        }

        !is_array($items) && ($items = array($items));
        foreach($items as $k => $item){
            if( ! $recur == true ){
                $layout[] = self::_renderLayout($template, $item);
            }
            else{
                if( empty($tree) ){
                    $tree = new Tree($items);
                }
                $layoutItemKey = $tree->generateNodeKey($item);
                $step = count(explode('_', $layoutItemKey))-1;
                $step > 0 && $step--;
                $layout[$layoutItemKey] = self::_renderLayout($template, $item, $step);
            }
        }
        ksort($layout);
        
        // Echo output
        $layout = implode("\n", $layout);
        if( $return == true ){
            return $layout;
        }
        echo $layout;
    }
    
    /**
     * Render layout
     * 
     * @param misc $template
     * @param misc $item
     * @return string
     */
    protected static function _renderLayout($template, $item = null, $step = 0){
        ob_start();
        if( is_string($template) && is_file($template) && is_readable($template) ){
            include $template;
        }
        else if(is_callable($template) ){
            echo self::callbackCall($template, $item, $step);
        }
        if( is_scalar($template)){
            echo $template;
        }
        $html = ob_get_clean();
        return $html;
    }
    
    public function renderSelectOption($template, $items, $selected = null, $recur = false, $return = false) {
        if( !$template ){
            $tree = new Tree($items);
            $template = function($item, $step) use ($recur, $selected, $tree){
                $itemId = $tree->getItemId($item);
                $selectedId = $tree->getItemId($selected);
                $selectedPid = $tree->getItemPid($selected);
                
                // Generate option tag
                $option = '<option value="' .$itemId. '"';
                if( $recur == true && $selected instanceof CF_Base && $itemId === $selectedPid ){
                    $option .= ' selected';
                }
                
                if( $selectedId === $itemId || $tree->checkIsDescendant($item, $selected) ){
                    $option .= 'disabled';
                }
                
                $option .= '>';
                ($label = $tree->getItemField($item, 'label'))
                || ($label = $tree->getItemField($item, 'name'))
                || ($label = $tree->getItemField($item, 'title'));
                
                if( $recur == true ){
                    $option .= str_repeat('&mdash;', 2*$step).'&nbsp'.htmlspecialchars($label);
                }
                else{
                    $option .= htmlspecialchars($label);
                }
                $option .= '</option>';
                return $option;
            };
        }
        
        $html = self::renderLayout($template, $items, $recur, true);
        if( $return == true ){
            return $html;
        }
        echo $html;
    }
    
    /**
    * Try to call a callback function
    */
   public static function &callbackCall($callback, &$refenrnce = null, $args = null){
       $ret = null;
       if( ! is_callable($callback) ){
           return $ret;
       }

       // Get extra arguments for callback
       $args = func_get_args();
       $args = array_splice($args, 2);
       // Get arguments pass via callback
       if( is_array($callback) && count($callback) > 2 ){
           $args = array_merge($args, array_splice($callback, 2));
       }

       // Set reference as frist argument
       array_unshift($args, null);
       $args[0] =& $refenrnce;

       $ret = call_user_func_array($callback, $args);
       return $ret;
   }
}
