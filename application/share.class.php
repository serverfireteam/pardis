<?php
class share {
function news(){
		  
    $sql_category = "SELECT * FROM `category` ORDER BY `id` DESC"; 
	$result       = $db->query($sql_category);
	$news         = array();
	foreach ($result as $row) {
		$sql_news        = "select * from `news` where category='$row[name]' ORDER BY `id` DESC";
		$result_news     = $db->query($sql_news);
		$i               = 0;
		foreach ($result_news as $row_news) {
		  $category                        = $row['name'];// for array index
		  $news[$category][$i]['id']       = $row_news['id'];
		  $news[$category][$i]['name']     = $row_news['name'];
		  $news[$category][$i]['category'] = $row_news['category'];
		  $news[$category][$i]['text']     = $row_news['text'];
		  $i++;
		}//end foreach
    }//end foreach
	$sql_navitication = "select * from `navitication` order by `id` desc";
	$result_navit     = $db->query($sql_navitication);
	$navit            = array();
	$i=0;
	foreach($result_navit as $row_nativ){
	   $navit[$i]['id']                 = $row_nativ['id'];
	   $navit[$i]['name']               = $row_nativ['name'];
       $navit[$i]['title']              = $row_nativ['title'];
   	   $navit[$i]['text']               = $row_nativ['text']; 
	   $i++;  
	}//end foreach
    $this->registry->template->navit_result = $navit;	
	//echo '<pre>';
	//print_r($navit);
	$this->registry->template->news_result = $news;
	}


}

?>

