<?php
//print_r(date_time::year_array());
class date_time {
 function year_array($start = 1350,$end = 1390){
   for($i = $start ;$i <= $end ;$i++)
     $temp[] = $i;
   return $temp;	 
 }
}
?>