<?


///SQL QUERY TO CREATE NEW TRAINER
$table_name = 'player_'.$playerID
$query = "CREATE TABLE `$table_name` (
	`uuid` 			varchar(64) UNIQUE,		#	MD5(date(U) * M_PI) + '_'+$playerID+'_'+$pokemon['number'];
	`#` 			varchar(3),
	`name` 			varchar(50),
	`nickname` 		varchar(50),
	`type`			varchar(100),
	`gender`		enum('male','female') DEFAULT NULL,
	`nature` 		varchar(20),
	`ability`		varchar(20),
	`totals`		varchar(50),
	`ivs`			varchar(50),
	`evs`			varchar(50),
	`ev_total`		tinyint,
	`level`			tinyint,
	`exp`			int,
	`exp_to_lvl`	int,
	`happiness`		tinyint,
	`caught_by`		varchar(50) NOT NULL DEFAULT '???',
	`location`		tinyint,
	`held_item`		varchar(50) DEFAULT NULL,
	`caught_at`		varchar(50)	DEFAULT NULL,
	`movelist`		text,
	`moveset`		varchar(255),
	`height`		varchar(50),
	`weight`		varchar(50),
	`caught`		timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1";



/// GET TRAINER TEAM
$mysqli->select_db('odyssey_trainers');

$trainer = 'player_1';											///////	REPLACE WITH TRAINER AUTHENTICATED ID
$query = "SELECT * FROM $trainer WHERE location = 0";
echo $query;
$get = $mysqli->query($query);
pre($get);

while($a = $get->fetch_assoc()){
	$team[] = $a;
}

pre(json_encode($team));

?>