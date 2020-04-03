<?php
include_once 'config.php';

$debug = true;

include_once 'header.php';
?>

<style type="text/css">
	
</style>


<div id="ViewPort">

	<div id="signin" class="form">
		<form>
			<label for="nickname">Login</label>
			<input type="text" name="nickname" id="nickname">

			<label for="password">Password</label>
			<input type="password" name="password" id="password">
		</form>
	</div>

	<div id="registration" class="form">
		<form>
			
		</form>
	</div>
	
</div>

<?	 include_once 'footer.php';	?>