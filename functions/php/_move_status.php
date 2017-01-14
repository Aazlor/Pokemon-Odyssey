<?

/*
	0 => 0,		//HP
	1 => 0,		//ATK
	2 => 0,		//DEF
	3 => 0,		//SP ATK
	4 => 0,		//SP DEF
	5 => 0,		//SPEED
	6 => 0,		//ACCURACY
	7 => 0,		//EVASION
	8 => 0,		//CRITICAL
*/



// $statusMoves = [
// 	'Growl' => [
// 		'target' => 'enemy',
// 		'stat' => 1,
// 		'impact' => -1,
// 		'chance' => 1,
// 	],
// 	'Sand Attack' => [
// 		'target' => 'enemy',
// 		'stat' => 6,
// 		'impact' => -1,
// 		'chance' => 1,
// 	],
// ];

$secondaryEffects = [];



$target_list = [
	'user',
	'opponent',
	'all',
];

$unique = [
	'Confuse Ray' => [
		'target' => '{status:confusion}',
	],
	'Conversion' => [
		'target' => '~~ Get type of first move in moveset, set pokemons type to same type as move ~~',
	],
	'Defense Curl' => [
		'user' => '{active: swap out}',
	],
	'Disable' => [
		'target' => '~~ Get last move used and prevent use for d8 turns ~~',
	],
	'Focus Energy' => [
		'user' => '{active: +critical}',
	],
	'Glare' => [
		'target' => '{status:paralysis}'
	],
	'Growth' => [
		'condition' => 'strong sunlight',
		'user' => '{1:1,2:0,3:1,4:0,5:0,6:0,7:0,8:0}',
	],
	'Haze' => [
		'all' => 'unset all modifiers',
	],
	'Hypnosis' => [
		'target' => '{status:sleep}',
	],
	'Leech Seed' => [
		'target' => 'Takes 1/8 MAX HP dmg each turn, heals users poke (or any that replace it) for amount drained'
	],
	'Light Screen' => [
		'user' => 'Special Attack Damage * .5 to all pokes on users side of field',
		'duration' => '5 turns',
	],
	'Lovely Kiss' => [
		'target' => '{status:sleep}',		
	],
	'Metronome' => [
		'user' => 'Performs any move in game at random',
	],
	'Mimic' => [
		'user' => '{active: swap out}',
		'effect' => '{mimic} becomes last move used by target pokemon w/ 5pp',
		'limitations' => 'Chatter, Metronome, Sketch, Struggle',	#Can't copy those 4 moves
	],
	'Minimize' => [
		'user' => '{active: battle}',
	],
	'Mirror Move' => [
		'effect' => 'User executes last move used by target',
	],
	'Mist' => [
		'user' => '{active: swap out}',
		'effect' => 'Protects user from negative stat change by opponent',
	],
	'Poison Gas' => [
		'target' => '{status: poison}',
	],
	'Poison Powder' => [
		'target' => '{status: poison}',
	],
	'Recover' => [
		'user' => 'HP = ((HP + ($poke[totals][0] / 2)) <= $poke[totals][0]) ? HP + ($poke[totals][0] / 2) : $poke[totals][0]',
	],
	'Reflect' => [
		'user' => 'Attack Damage * .5 to all pokes on users side of field',
		'duration' => '5 turns',
	],
	'Rest' => [
		'user' => '{status: sleep, hp: $poke[totals][0]}',
		'duration' => '2 turns',
	],
	'Roar' => [
		'target' => '($targetPoke[level] < $poke[level]) ? Roar[effect] : fail',
		'effect' => '($targetPoke == wild) ? endBattle : switchOut;',
	],
	'Sing' => [
		'target' => '{status: sleep}',
	],
	'Sleep Powder' => [
		'target' => '{status: sleep}',		
	],
	'Soft-Boiled' => [
		'user' => 'HP = ((HP + ($poke[totals][0] / 2)) <= $poke[totals][0]) ? HP + ($poke[totals][0] / 2) : $poke[totals][0]',
	],
	'Splash' => [''],
	'Spore' => [
		'target' => '{status: sleep}',		
	],
	'Stun Spore' => [
		'target' => '{status:paralysis}'
	],
	'Substitute' => [
		'user' => 'Fuggin Complicated',
	],
	'Supersonic' => [
		'target' => '{status:confusion}',
	],
	'Teleport' => [
		'user' => 'Flee from wild encounter',
	],
	'Thunder Wave' => [
		'target' => '{status:paralysis}'
	],
	'Toxic' => [
		'target' => '{status:badly poisoned}'
	],
	'Transform' => [
		'user' => 'Takes on form and attacks of opponent',
	],
	'Whirlwind' => [
		'target' => '($targetPoke[level] < $poke[level]) ? Roar[effect] : fail',
		'effect' => '($targetPoke == wild) ? endBattle : switchOut;',		
	]
];

$outsideBattle = [
	'Flash',
	'Teleport',
];

$statusMoves = [
	'Acid Armor' => [
		'user' => '{"1":"0","2":"2","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Agility' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"2","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Amnesia' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"2","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Barrier' => [
		'user' => '{"1":"0","2":"2","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Confuse Ray' => [
		'unique' => 1,
	],
	'Conversion' => [
		'unique' => 1,
	],
	'Defense Curl' => [
		'user' => '{"1":"0","2":"1","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 1,
	],
	'Disable' => [
		'unique' => 1,
	],
	'Double Team' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"1","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Flash' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"-1","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Focus Energy' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"1"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 1,
	],
	'Glare' => [
		'unique' => 1,
	],
	'Growl' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"-1","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Growth' => [
		'user' => '{"1":"1","2":"0","3":"1","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 1,
	],
	'Harden' => [
		'user' => '{"1":"0","2":"1","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Haze' => [
		'unique' => 1,
	],
	'Hypnosis' => [
		'unique' => 1,
	],
	'Kinesis' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"-1","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Leech Seed' => [
		'unique' => 1,
	],
	'Leer' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"-1","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Light Screen' => [
		'unique' => 1,
	],
	'Lovely Kiss' => [
		'unique' => 1,
	],
	'Meditate' => [
		'user' => '{"1":"1","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Metronome' => [
		'unique' => 1,
	],
	'Mimic' => [
		'unique' => 1,
	],
	'Minimize' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"2","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 1,
	],
	'Mirror Move' => [
		'unique' => 1,
	],
	'Mist' => [
		'unique' => 1,
	],
	'Poison Gas' => [
		'unique' => 1,
	],
	'Poison Powder' => [
		'unique' => 1,
	],
	'Recover' => [
		'unique' => 1,
	],
	'Reflect' => [
		'unique' => 1,
	],
	'Rest' => [
		'unique' => 1,
	],
	'Roar' => [
		'unique' => 1,
	],
	'Sand Attack' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"-1","7":"0","8":"0"}',
		'unique' => 1,
	],
	'Screech' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"-2","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Sharpen' => [
		'user' => '{"1":"1","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Sing' => [
		'unique' => 1,
	],
	'Sleep Powder' => [
		'unique' => 1,
	],
	'Smokescreen' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"-1","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Soft-Boiled' => [
		'unique' => 1,
	],
	'Splash' => [
		'unique' => 1,
	],
	'Spore' => [
		'unique' => 1,
	],
	'String Shot' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"-2","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Stun Spore' => [
		'unique' => 1,
	],
	'Substitute' => [
		'unique' => 1,
	],
	'Supersonic' => [
		'unique' => 1,
	],
	'Swords Dance' => [
		'user' => '{"1":"2","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Tail Whip' => [
		'user' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"-1","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
	'Teleport' => [
		'unique' => 1,
	],
	'Thunder Wave' => [
		'unique' => 1,
	],
	'Toxic' => [
		'unique' => 1,
	],
	'Transform' => [
		'unique' => 1,
	],
	'Whirlwind' => [
		'unique' => 1,
	],
	'Withdraw' => [
		'user' => '{"1":"0","2":"1","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'opponent' => '{"1":"0","2":"0","3":"0","4":"0","5":"0","6":"0","7":"0","8":"0"}',
		'unique' => 0,
	],
];



?>