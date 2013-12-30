<?php

class query {
    var $sql_query;    
	var $bindFields; 
	var $Fields;  
	var $types;
	var $oneMany;
			//....................................................................................................
	function execute($sql_query,$bindFields,$Fields,$types,$oneMany)
	{
		$db = db::getInstance();
		$utf        = $db->prepare('SET NAMES utf8');
		$utf->execute();
		$sth	    = $db->prepare($sql_query);
		if ($bindFields!='')
		{
			$bindArray=split('/',$bindFields);
			$fieldArray=split('/',$Fields);
			$typeArray=split('/',$types);
			for($i=0;$i< count($bindArray);$i++)
			{
				if($typeArray[$i] == 'int')
					$sth->bindParam($bindArray[$i],$fieldArray[$i], PDO::PARAM_INT);
				elseif ($typeArray[$i] == 'str')
					$sth->bindParam($bindArray[$i],$fieldArray[$i], PDO::PARAM_STR, 256);
			}
		}
		$sth->execute(); 
		if ($oneMany == 'fetch')
			return stripslashes_deep($sth->fetch());
		else if ($oneMany == '')
			return;
		else if ($oneMany == 'fetchAll')
			return stripslashes_deep($sth->fetchAll());
	}			
		//...............................................................................................................
}//end of class

function stripslashes_deep($value)
{
    $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);

    return $value;
}
?>