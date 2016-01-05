<?php
switch($data['status']){
  case 'success':
    echo '<span  style="color:green" >'.$data['message'].'</span>';
    break;
  
  case 'fail':
    echo '<span  style="color:red" >'.$data['message'].'</span>';
    break;
  
  case 'result':
    echo $data['message'];
}
?>