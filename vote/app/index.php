<?php
// Handle the POST
if (isset($_POST["vote"]) && in_array($_POST["vote"], ["a", "b"])) {
	$v = $_POST["vote"];

	// Connect to DB
	$db = pg_connect("host=172.17.0.3 dbname=postgres user=postgres") or die("Not really working for some reason");

	// Create table if it doesn't yet exist
	$q = "CREATE TABLE IF NOT EXISTS votes (id SERIAL primary key, vote VARCHAR(1) NOT NULL)";
	$r = pg_query($q) or die("Query failed: " . pg_last_error());

	// UPDATE
	if (isset($_COOKIE["vote_id"]) && is_numeric($_COOKIE["vote_id"])) {
		$q = "UPDATE votes SET vote = '" . $v . "' WHERE id = " . $_COOKIE["vote_id"];
		$r = pg_query($q) or die('Query failed: ' . pg_last_error());
	}
	// INSERT
	else {
		$q = "INSERT INTO votes (vote) VALUES ('" . $v . "') RETURNING id";
		$r = pg_query($q) or die('Query failed: ' . pg_last_error());
		$id = pg_fetch_row($r)[0];
		setcookie("vote_id", $id);
	}

	// Create or update the 'vote' cookie
	setcookie("vote", $v);

	// Free resultset and close the connection
	pg_free_result($r);
	pg_close($db);

	// Redirect so 'F5' doesn't trigger a POST. I find those kinda meh.
	header("Location: /");
	die();
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>What do you think of this presentation?</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="keywords" content="docker-compose, docker, stack">
	<meta name="author" content="witsec">
	<link rel="stylesheet" href="style.css" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
</head>
<body>

	<div id="content-container">
		<div id="content-container-center">
			<h3>What do you think of this presentation?</h3>
			<form id="choice" name="form" method="POST" action="/">
				<button id="a" type="submit" name="vote" class="a" value="a">AWESOME</button>
				<button id="b" type="submit" name="vote" class="b" value="b">LAME</button>
			</form>
			<div id="tip">(Tip: you can change your vote)</div>
			<div id="hostname">Processed by container ID <?=gethostname();?></div>
		</div>
	</div>
	<script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.js"></script>

	<?php
	if (isset($_COOKIE["vote"]) && in_array($_COOKIE["vote"], ["a", "b"])) {
		?>
		<script>
		var vote = "<?=$_COOKIE["vote"];?>";

		if(vote == "a"){
			$(".a").prop('disabled', true);
			$(".a").html('AWESOME <i class="fa fa-check-circle"></i>');
			$(".b").css('opacity','0.5');
		}
		if(vote == "b"){
			$(".b").prop('disabled', true);
			$(".b").html('LAME <i class="fa fa-check-circle"></i>');
			$(".a").css('opacity','0.5');
		}
		</script>
		<?php
	}
	?>

</body>
</html>
