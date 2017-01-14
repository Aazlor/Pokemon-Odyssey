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

$attackPreModifiers = [
	8 => [
		'10,000,000 Volt Thunderbolt' => 2,
		'Aeroblast' => 1,
		'Air Cutter' => 1,
		'Attack Order' => 1,
		'Blaze Kick' => 1,
		'Crabhammer' => 1,
		'Cross Chop' => 1,
		'Cross Poison' => 1,
		'Drill Run' => 1,
		'Karate Chop' => 1,
		'Leaf Blade' => 1,
		'Night Slash' => 1,
		'Poison Tail' => 1,
		'Psycho Cut' => 1,
		'Razor Leaf' => 1,
		'Razor Wind' => 1,
		'Shadow Blast' => 1,
		'Shadow Claw' => 1,
		'Sky Attack' => 1,
		'Slash' => 1,
		'Spacial Rend' => 1,
		'Stone Edge' => 1,
		'Frost Breath' => 3,
		'Storm Throw' => 3,
	],
	'multi-hit' => [
		'Arm Thrust' => '%5',
		'Barrage' => '%5',
		'Bone Rush' => '%5',
		'Bullet Seed' => '%5',
		'Comet Punch' => '%5',
		'Double Slap' => '%5',
		'Fury Attack' => '%5',
		'Fury Swipes' => '%5',
		'Icicle Spear' => '%5',
		'Pin Missile' => '%5',
		'Rock Blast' => '%5',
		'Spike Cannon' => '%5',
		'Tail Slap' => '%5',
		'Water Shuriken' => '%5',
		'Bonemerang' => 2,
		'Double Hit' => 2,
		'Double Kick' => 2,
		'Dual Chop' => 2,
		'Gear Grind' => 2,
		'Twineedle' => 2,
		'Triple Kick' => 3,
		'Beat Up' => '%PartySize',
	],
];

?>