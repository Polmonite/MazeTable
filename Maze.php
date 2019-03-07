<?php

class MazeNode {

	public $x, $y, $n, $s, $w, $e, $depth;

	public function __construct(int $x, $y) {
		$this->x = $x;
		$this->y = $y;
	}

	public function isConnected() {
		return $this->n !== null || $this->s !== null || $this->w !== null || $this->e !== null;
	}

	public function coord() : array {
		return [$this->x, $this->y];
	}

}

class Maze {

	protected $width,
						$height,
						$maze_nodes,
						$maze,
						$exit,
						$start,
						$minotaur,
						$leaves;

	protected function getMazeXY(MazeNode $node) {
		$x = ($node->x * 2) + 1;
		$y = ($node->y * 2) + 1;
		return [$x, $y];
	}

	protected function getMazeXYs(MazeNode $node) {
		$coords = [];
		list($x, $y) = $this->getMazeXY($node);
		$coords[] = [$x, $y];
		if ($node->n !== null) {
			$coords[] = [$x, $y - 1];
		}
		if ($node->s !== null) {
			$coords[] = [$x, $y + 1];
		}
		if ($node->w !== null) {
			$coords[] = [$x - 1, $y];
		}
		if ($node->e !== null) {
			$coords[] = [$x + 1, $y];
		}
		return $coords;
	}

	protected function getNode(int $x, int $y) {
		if ($x >= 0 && $y >= 0 && $x < $this->width && $y < $this->height) {
			return $this->maze_nodes[$y][$x];
		}
		return null;
	}

	protected function setNode(MazeNode $node) {
		$x = $node->x;
		$y = $node->y;
		$this->maze_nodes[$y][$x] = $node;
	}

	protected function getAdiacentNodes(int $x, int $y) : array {
		$adiacent_nodes = [];
		$tmp = [
			['dir' => 'n', 'node' => $this->getNode($x, $y - 1)],
			['dir' => 's', 'node' => $this->getNode($x, $y + 1)],
			['dir' => 'w', 'node' => $this->getNode($x - 1, $y)],
			['dir' => 'e', 'node' => $this->getNode($x + 1, $y)]
		];
		shuffle($tmp);
		foreach ($tmp as $ad) {
			if ($ad['node'] !== null) {
				$node = $ad['node'];
				$adiacent_nodes[] = [
					'dir'	=> $ad['dir'],
					'coord'	=> [$node->x, $node->y]
				];
			}
		}
		return $adiacent_nodes;
	}

	protected static function invdir(string $dir) {
		switch ($dir) {
			case 'n':
				return 's';
			case 's':
				return 'n';
			case 'w':
				return 'e';
			case 'e':
				return 'w';
		}
	}

	protected function drill(array $xy, $depth = 0) : int {
		$x = $xy[0];
		$y = $xy[1];
		$node = $this->getNode($x, $y);
		$node->depth = $depth;
		$max_depth = $depth;
		$adiacent_nodes = $this->getAdiacentNodes($x, $y);
		$at_least_one = false;
		foreach ($adiacent_nodes as $coord) {
			$dir = $coord['dir'];
			$an_coord = $coord['coord'];
			$adiacent_node = $this->getNode($an_coord[0], $an_coord[1]);
			if (!$adiacent_node->isConnected()) {
				$at_least_one = true;
				$node->{$dir} = $adiacent_node;
				$inv_dir = static::invdir($dir);
				$adiacent_node->{$inv_dir} = $node;
				$this->setNode($node);
				$this->setNode($adiacent_node);
				$child_depth = $this->drill($adiacent_node->coord(), $depth + 1);
				if ($child_depth > $max_depth) {
					$max_depth = $child_depth;
				}
			}
		}
		if (!$at_least_one) {
			$this->leaves[] = $node;
		}
		return $max_depth;
	}

	protected function walkNodes(Callable $func) {
		foreach ($this->maze_nodes as $row) {
			foreach ($row as $node) {
				$func($node);
			}
		}
	}

	public function __construct(int $width = 8, int $height = 8) {
		$this->width = $width;
		$this->height = $height;
		$this->maze_nodes = [];
		$this->leaves = [];
		$edges = [];
		for ($y = 0; $y < $height; ++$y) {
			$row = [];
			for ($x = 0; $x < $width; ++$x) {
				if ($y == 0 || $y == $height - 1 || $x == 0 || $x == $width - 1) {
					$edges[] = [$x, $y];
				}
				$row[] = new MazeNode($x, $y);
			}
			$this->maze_nodes[] = $row;
		}

		$exit_coord = $edges[mt_rand(0, count($edges) - 1)];
		$this->exit = $this->getNode($exit_coord[0], $exit_coord[1]);
		$max_depth = $this->drill($exit_coord);
		usort($this->leaves, function($a, $b) {
			if ($a->depth < $b->depth) {
				return -1;
			} else if ($a->depth > $b->depth) {
				return 1;
			}
			return 0;
		});
		$this->start = array_pop($this->leaves);
		shuffle($this->leaves);
		$this->minotaur = $this->leaves[0];
		// add walls
		$wall = array_fill(0, ($width * 2) + 1, 0);
		$maze = array_fill(0, ($height * 2) + 1, $wall);
		$passages = [];
		$this->walkNodes(function($node) use(&$maze) {
			$coords = $this->getMazeXYs($node);
			foreach ($coords as $coord) {
				$x = $coord[0];
				$y = $coord[1];
				$maze[$y][$x] = 1;
			}
		});
		list($x, $y) = $this->getMazeXY($this->start);
		$maze[$y][$x] = 2;
		$real_exit_xy = $this->getMazeXY($this->exit);
		if ($real_exit_xy[0] == 1) {
			$real_exit_xy[0] = 0;
		} else if ($real_exit_xy[0] == ($this->width * 2) - 1) {
			$real_exit_xy[0] = ($this->width * 2);
		} else if ($real_exit_xy[1] == 1) {
			$real_exit_xy[1] = 0;
		} else if ($real_exit_xy[1] == ($this->height * 2) - 1) {
			$real_exit_xy[1] = ($this->height * 2);
		}
		$maze[$real_exit_xy[1]][$real_exit_xy[0]] = 3;
		list($xm, $ym) = $this->getMazeXY($this->minotaur);
		$maze[$ym][$xm] = 4;
		$this->maze = $maze;
	}

	public function prettyPrint() {
		foreach ($this->maze as $row) {
			foreach ($row as $val) {
				switch ($val) {
					case 0:
						echo '#';
						break;
					case 1:
						echo "&nbsp;";
						break;
					case 2:
						echo "S";
						break;
					case 3:
						echo "X";
						break;
					case 4:
						echo "M";
						break;
				}
			}
			echo "<br/>\n";
		}
	}

	public function tablePrint() {
		echo '<table style="border-collapse:collapse">';
		foreach ($this->maze as $y => $row) {
			echo '<tr>';
			foreach ($row as $x => $val) {
				$id = $x . '-' . $y;
				switch ($val) {
					case 0:
						echo "<td data-wall data-x=\"$x\" data-y=\"$y\" id=\"$id\" style=\"background:black\">#</td>";
						break;
					case 1:
						echo "<td data-x=\"$x\" data-y=\"$y\" id=\"$id\" style=\"background:white\">&nbsp;</td>";
						break;
					case 2:
						echo "<td data-start data-x=\"$x\" data-y=\"$y\" id=\"$id\" style=\"background:blue\">S</td>";
						break;
					case 3:
						echo "<td data-exit data-x=\"$x\" data-y=\"$y\" id=\"$id\" style=\"background:red\">X</td>";
						break;
					case 4:
						echo "<td data-minotaur data-x=\"$x\" data-y=\"$y\" id=\"$id\" style=\"background:grey\">M</td>";
						break;
				}
			}
			echo '</tr>';
		}
		echo '</table>';
	}

}