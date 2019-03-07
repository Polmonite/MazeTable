# MazeTable

Random generated maze with automatic search of the exit by the hero.
A minotaur goes around at random, trying to eat the hero; every now and then the minotaur sleeps and the hero can pass over him.

Legend:

* <span style="background:blue">`S`</span>: start;
* <span style="background:red">`X`</span>: exit;
* <span style="background:grey">`M`</span>: minotaur;
* <span style="background:lightgrey">`Z`</span>: sleeping minotaur;
* <span style="background:yellow">`.`</span>: path the hero has covered and is following;
* <span style="background:orange">`.`</span>: path the hero has backtracked.

## How to run

Simply run a server like this:

`php -S localhost`

and go to localhost on the browser.

Refresh when you want to get a new maze.

## Accepted options

Via get parameters you can specify:

* `w`: maze width;
* `h`: maze height.