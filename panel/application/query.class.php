<?php

class query {
	var $sql_query;
	var $bindFields;
	var $Fields;
	var $types;
	var $oneMany;

	//....................................................................................................
	function execute($sql_query, $bindFields, $Fields, $types, $oneMany)
	{
		$db  = db::getInstance();
		$utf = $db->prepare('SET NAMES utf8');
		$utf->execute();
		$sth = $db->prepare($sql_query);
		if ($bindFields != '') {
			$bindArray  = split('/',$bindFields);
			$fieldArray = split('/',$Fields);
			$typeArray  = split('/',$types);
			for ($i = 0; $i < count($bindArray); $i++) {
				if ($typeArray[$i] == 'int') {
					$sth->bindParam($bindArray[$i], $fieldArray[$i], PDO::PARAM_INT);
				} elseif ($typeArray[$i] == 'str') {
					$sth->bindParam($bindArray[$i], $fieldArray[$i], PDO::PARAM_STR, 256);
				}
			}
		}
		$sth->execute();
		if ($oneMany == 'fetch') {
			return $sth->fetch(PDO::FETCH_ASSOC);
		} else if ($oneMany == '') {
			return;
		} else if ($oneMany == 'fetchAll') {
			return $sth->fetchAll();
		}
	}
	//...............................................................................................................
};//end of class
?>
