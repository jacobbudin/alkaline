<?php

$src = new PDO('mysql:host=localhost;dbname=alkaline', 'alkaline', 'm902j2JK91kaO', array(PDO::ATTR_PERSISTENT => true));
$dest = new PDO('sqlite:/var/www/vhosts/alkalineapp.com/beta/assets/alkaline5.db', null, null, array(PDO::ATTR_PERSISTENT => true));
$batch_rows = 10;

if(!$src){ echo 'Source refused.'; }
if(!$dest){ echo 'Destination refused.'; }

$sql = 'SHOW TABLES';
$query = $src->prepare($sql);
$query->execute();
$tables = $query->fetchAll();

$table_names = array();

foreach($tables as $table){
	$table_names[] = $table[0];
}

foreach($table_names as $table){
	$sql = 'SELECT COUNT(*) AS count FROM ' . $table;
	$query = $src->prepare($sql);
	$query->execute();
	$count = $query->fetchAll();
	$count = $count[0]['count'];
	
	$i = 0;
	
	while($i < $count){
		$sql = 'SELECT * FROM ' . $table . ' LIMIT ' . $i . ', ' . $batch_rows;
		$query = $src->prepare($sql);
		$query->execute();
		$rows = $query->fetchAll();
		foreach($rows as $row){
			$columns = array();
			$values = array();
			foreach($row as $column => $value){
				if(is_string($column)){
					$columns[] = $column;
					$values[] = $value;
				}
			}
			
			$value_slots = array_fill(0, count($values), '?');
			
			$sql = 'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $value_slots) . ');';
			$query = $dest->prepare($sql);
			if($query->execute($values)){
				echo '.';
			}
			else{
				var_dump($dest->errorInfo());
			}
		}
		$i += $batch_rows;
	}
}

?>