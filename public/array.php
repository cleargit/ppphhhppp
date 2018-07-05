<?php
/**
 * Created by PhpStorm.
 * User: sham
 * Date: 2018/6/13
 * Time: 12:21
 */
$arr=array('sham'=>array('age'=>'20','name'=>'sham','height'=>'00'),
            'king'=>array('age'=>'00','name'=>'asd','height'=>'00'));
var_dump($arr);
echo '<hr/>';
var_dump(in_array(array('age'=>'20','name'=>'sham','height'=>'00'),$arr,true));