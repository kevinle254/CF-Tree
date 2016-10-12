<?php
/**
 * @package		CF
 * @subpackage	Tree Helper Example
 * @author		CF Team
 * @link		http://codefansi.com
 */

// Include generate tree helper
include_once 'GenerateTree.php';

$options[] = [
    'id' => 1,
    'pid' => 0,
    'value' => 1,
    'lable' => 'Option 01'
];
$options[] = [
    'id' => 2,
    'pid' => 1,
    'value' => 2,
    'lable' => 'Option 02'
];
$options[] = [
    'id' => 3,
    'pid' => 2,
    'value' => 3,
    'lable' => 'Option 03'
];
$options[] = [
    'id' => 4,
    'pid' => 2,
    'value' => 4,
    'lable' => 'Option 04'
];
$options[] = [
    'id' => 5,
    'pid' => 3,
    'value' => 5,
    'lable' => 'Option 05'
];
?>
<select>
    <option>Select one</option>
    <?php GenerateTree::renderLayout(function($option, $step){?>
    <option value="<?php echo $option['value'] ?>"><?php echo str_repeat('&mdash;', 2*$step).'&nbsp'.htmlspecialchars($option['lable']) ?></option>
    <?php }, $options, true) ?>
</select>