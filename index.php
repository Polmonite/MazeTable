<?php
ini_set('memory_limit', '8192M');

require_once('Maze.php');

$w = isset($_GET['w']) ? $_GET['w'] : mt_rand(8, 48);
$h = isset($_GET['h']) ? $_GET['h'] : mt_rand(8, 24);
$m = new Maze($w, $h);
?>
<!doctype HTML>
<html>
	<head>
		<script
			src="http://code.jquery.com/jquery-3.2.1.min.js"
			integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
			crossorigin="anonymous"></script>
		<style>* { font-family: monospace; }td { width: 10px; height: 10px; }</style>
		<script>
			var speed = 50;
			var w = <?= $w ?>;
			var h = <?= $h ?>;
			var exit = false;
			var start = true;
			var table;
			var stack = [];

			var minotaur_pos;
			var minotaur_prec = null;
			var minotaur_bck = { background: 'white', text: ' ' };
			var minotaur_sleep = 10;
			var minotaur_sleep_every = 40;
			var minotaur_count = minotaur_sleep;
			var minotaur_is_sleeping = false;

			var outcome = function(success, msg) {
				var cont = $('h1 > span');
				cont.css('background', success ? 'green' : 'red');
				table.find('td[data-wall]').css('background', success ? 'green' : 'red');
				cont.text(' ' + msg + ' ');
			};

			var rand = function(min, max) {
  			return Math.floor(Math.random() * (max - min + 1)) + min;
			};

			var minoStep = function() {
				var pos = minotaur_pos;

				minotaur_count--;
				if (minotaur_is_sleeping) {
					if (minotaur_count == 0) {
						minotaur_is_sleeping = false;
						minotaur_count = minotaur_sleep_every;
					}
					return;
				} else if (minotaur_count == 0) {
					minotaur_is_sleeping = true;
					minotaur_count = minotaur_sleep;
					minotaur_pos.text('Z');
					minotaur_pos.css('background', 'lightgrey');
					return;
				}

				var prec = minotaur_prec;

				var x = parseInt(pos.attr('data-x'));
				var y = parseInt(pos.attr('data-y'));
				var n = table.find('#' + x + '-' + (y - 1));
				var s = table.find('#' + x + '-' + (y + 1));
				var w = table.find('#' + (x - 1) + '-' + y);
				var e = table.find('#' + (x + 1) + '-' + y);
				var cands = [n, s, w, e].filter(function(el) {
					return el.length > 0
						&& (el.text().trim() == '' || el.text().trim() == '.')
						&& (prec == null || el.attr('id') != prec.attr('id'));
				});
				if (cands.length > 0) {
					var cand = cands[rand(0, cands.length - 1)];
					minotaur_prec = pos;
					minotaur_pos = cand;
				} else {
					minotaur_prec = pos;
					minotaur_pos = prec;
				}
				minotaur_prec.text(minotaur_bck.text);
				minotaur_prec.css('background', minotaur_bck.background);
				minotaur_bck.text = minotaur_pos.text();
				minotaur_bck.background = minotaur_pos.css('background');
				minotaur_pos.text('M');
				minotaur_pos.css('background', 'grey');
			};

			var step = function(pos) {
				minoStep();
				if (pos.text() == 'X') {
					outcome(true, 'WIN!');
					return;
				}
				if (!pos || pos.text() == 'S' && !start) {
					outcome(false, 'LOST IN THE MAZE...');
					return;
				}
				if (pos.text() == 'M') {
					outcome(false, 'EATEN ALIVE...');
					return;
				}
				start = false;
				if (pos.text().trim() == '') {
					pos.text('.');
					pos.css('background', 'yellow');
				} else if (pos.text() == '.') {
					pos.css('background', 'orange');
				}
				var x = parseInt(pos.attr('data-x'));
				var y = parseInt(pos.attr('data-y'));
				var n = table.find('#' + x + '-' + (y - 1));
				var s = table.find('#' + x + '-' + (y + 1));
				var w = table.find('#' + (x - 1) + '-' + y);
				var e = table.find('#' + (x + 1) + '-' + y);
				var ads = [n, s, w, e];
				var mino = ads.filter(function(el) {
					return el.length > 0 && el.text().trim() == 'M';
				});
				if (mino.length > 0) {
					outcome(false, 'EATEN ALIVE...');
					return;
				}
				var cands = ads.filter(function(el) {
					return el.length > 0
						&& (el.text().trim() == '' || el.text().trim() == 'X' || el.text().trim() == 'Z');
				});
				if (cands.length > 0) {
					var cand = cands[rand(0, cands.length - 1)];
					stack.push(pos);
					setTimeout(function() {
						step(cand);
					}, speed);
				} else {
					pos.css('background', 'orange');
					var backtrack = stack.pop();
					setTimeout(function() {
						step(backtrack);
					}, speed);
				}
			};

			$(function() {
				table = $('table');
				var start = table.find('td[data-start]');
				minotaur_pos = table.find('td[data-minotaur]');
				step(start);
			});
		</script>
	</head>
	<body>
		<h1><?php echo "$w::$h"; ?> <span></span></h1>
<?php
//$m->prettyPrint();
$m->tablePrint();
?>
	</body>
</html>