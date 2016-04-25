<?php

namespace JoeGreen88\Battleships;

use Smrtr\DataGrid;

class Game
{
    /**
     * @var int The active player, either 1 (default) or 2.
     */
    protected $activePlayer = 1;

    /**
     * @var DataGrid containing Player 1's ship formation.
     */
    protected $grid1;

    /**
     * @var DataGrid containing Player 2's ship formation.
     */
    protected $grid2;

    /**
     * @var array A map from ship length to number of ships at that length per player.
     */
    protected $numShips = [
        2 => 1,
        3 => 1,
        4 => 1
    ];

    /**
     * @var Ship[] All of the ships placed on player 1's grid.
     */
    protected $ships1 = [];

    /**
     * @var Ship[] All of the ships placed on player 2's grid.
     */
    protected $ships2 = [];

    /**
     * @var int 0 = beginning, 1 = placing ships, 2 = game in progress, 3 = we have a winner
     */
    protected $gameState = 0;

    /**
     * Game constructor.
     *
     * @param int $numColumns Must be larger than 4.
     * @param int $numRows    Must be larger than 4.
     *
     * @throws \Exception If the parameters are invalid.
     */
    public function __construct($numColumns = 5, $numRows = 5)
    {
        if (!is_int($numColumns) or $numColumns < 5) {
            throw new \Exception("The number of columns must be an integer larger than 4");
        }
        if (!is_int($numRows) or $numRows < 5) {
            throw new \Exception("The number of rows must be an integer larger than 4");
        }
        $this->grid1 = new DataGrid;
        $this->grid2 = new DataGrid;
        while ($this->grid1->info('columnCount') < $numColumns) {
            $this->grid1->appendColumn([]);
            $this->grid2->appendColumn([]);
        }
        while ($this->grid1->info('rowCount') < $numRows) {
            $this->grid1->appendRow([]);
            $this->grid2->appendRow([]);
        }
        $this->grid1->scalarValuesOnly(false);
        $this->grid2->scalarValuesOnly(false);
    }

    /**
     * Toggle between players.
     *
     * @return static
     */
    public function changeActivePlayer()
    {
        $this->activePlayer = $this->getInactivePlayer();
        return $this;
    }

    /**
     * @return int 1 or 2
     */
    public function getActivePlayer()
    {
        return $this->activePlayer;
    }

    /**
     * @return int 1 or 2
     */
    public function getInactivePlayer()
    {
        return (1 === $this->activePlayer) ? 2 : 1;
    }

    /**
     * @return DataGrid The datagrid of the active player.
     */
    public function getActivePlayerGrid()
    {
        $property = "grid".$this->getActivePlayer();
        return $this->$property;
    }

    /**
     * @return DataGrid the datagrid of the inactive player.
     */
    public function getInactivePlayerGrid()
    {
        $property = "grid".$this->getInactivePlayer();
        return $this->$property;
    }

    /**
     * @return Ship[] All of the ships placed on the grid of the active player.
     */
    public function getActivePlayerShips()
    {
        $property = "ships".$this->getActivePlayer();
        return $this->$property;
    }

    /**
     * @return Ship[] All of the ships placed on the grid of the inactive player.
     */
    public function getInactivePlayerShips()
    {
        $property = "ships".$this->getInactivePlayer();
        return $this->$property;
    }

    /**
     * Set the number of ships for this game. Must be called before any ships are placed and before the game starts.
     *
     * @param array $numShips A map from ship length (int) to number of ships at that length per player (int).
     *
     * @return static
     *
     * @throws \Exception If the parameter fails some basic (and non-exhaustive) validation.
     */
    public function setNumShips(array $numShips)
    {
        if ($this->gameState > 0) {
            throw new \Exception("Cannot specify the number of ships - ship placement has already begun");
        }
        if (!count($numShips)) {
            throw new \Exception("The param \$numShips must not be empty");
        }
        $numRows = $this->grid1->info('rowCount');
        $numColumns = $this->grid1->info('columnCount');
        $totalSquaresCovered = 0;
        foreach ($numShips as $key => $val) {
            if (!is_int($key) or $key < 0 or !is_int($val) or $val < 0) {
                throw new \Exception("The param \$numShips must map positive integers to positive integers");
            }
            if ($key > $numRows && $key > $numColumns) {
                throw new \Exception("Invalid number of ships given - no ship of length $key will fit on the grid!");
            }
            $totalSquaresCovered += $key * $val;
        }
        if ($totalSquaresCovered >= $this->grid1->info('columnCount') * $this->grid1->info('rowCount')) {
            throw new \Exception("Too many ships specified - they would cover the entire grid!");
        }
        if ($totalSquaresCovered <= 0) {
            throw new \Exception("No ships specified!");
        }
        $this->numShips = $numShips;
        return $this;
    }

    /**
     * Get the number of ships for this game.
     *
     * @param null|int $length Null by default. Provide an int to return the number of ships at the given length.
     *
     * @return array|int Array if $length parameter is null, otherwise an integer.
     *
     * @throws \Exception If an invalid value is provided for the $length parameter.
     */
    public function getNumShips($length = null)
    {
        if (null === $length) {
            return $this->numShips;
        }
        if (!is_int($length)) {
            throw new \Exception("Integer or null value expected in parameter \$length");
        }
        return array_key_exists($length, $this->numShips) ? $this->numShips[$length] : 0;
    }

    /**
     * @return array
     */
    public function getNumShipsAwaitingPlacement()
    {
        $numShips = $this->numShips;
        foreach ($this->getActivePlayerShips() as $ship) {
            $length = $ship->getLength();
            if (array_key_exists($length, $numShips)) {
                --$numShips[$length];
            }
            if (0 === $numShips[$length]) {
                unset($numShips[$length]);
            }
        }
        return $numShips;
    }

    /**
     * Place a ship on the active player's grid.
     *
     * @param int $x The X co-ordinate
     * @param int $y The Y co-ordinate
     * @param int $length The length of the ship
     * @param string $orientation Either 'portrait' or 'landscape'
     *
     * @return static
     *
     * @throws \Exception
     */
    public function placeShip($x, $y, $length, $orientation)
    {
        if ($this->gameState > 1) {
            throw new \Exception("Cannot place ships once the game has started");
        }
        $this->validateShipAvailabilityForPlacement($length);
        static::validateShipPlacement($x, $y, $length, $orientation, $this->getActivePlayerGrid());

        $ship = new Ship($x, $y, $length, $orientation, $this->getActivePlayerGrid());
        $shipsProperty = "ships".$this->getActivePlayer();
        $this->{$shipsProperty}[] = $ship;

        $coordinates = static::getCoordinates($x, $y, $length, $orientation);
        foreach ($coordinates as $point) {
            $tile = new Tile($point[0], $point[1], $ship);
            $this->getActivePlayerGrid()->setValue($point[0], $point[1], $tile);
        }

        $this->gameState = 1;
    }

    /**
     * @param int $length The length of ship you are enquiring about.
     *
     * @return bool True on success
     *
     * @throws \Exception If there are no ships available for placement
     */
    protected function validateShipAvailabilityForPlacement($length)
    {
        $numShips = $this->getNumShipsAwaitingPlacement();
        if (!array_key_exists($length, $numShips) or $numShips[$length] < 1) {
            throw new \Exception("There are no ships of length $length available for placement");
        }
        return true;
    }

    /**
     * Utility method which determines if a proposed ship placement is valid.
     *
     * @param int $x
     * @param int $y
     * @param int $length
     * @param string $orientation
     * @param DataGrid $grid
     *
     * @return bool True on success
     *
     * @throws \Exception If the ship placement is not valid.
     */
    public static function validateShipPlacement($x, $y, $length, $orientation, DataGrid $grid)
    {
        if (!is_int($length) or $length < 1) {
            throw new \Exception("Ships must have an integer length of 1 or more");
        }
        if (!in_array($orientation, ['portrait', 'landscape'])) {
            throw new \Exception("The parameter \$orientation must take the value 'portrait' or 'landscape'");
        }
        $numColumns = $grid->info('columnCount');
        if (!is_int($x) or $x < 0 or $x >= $numColumns or ('landscape' == $orientation && $x + $length >= $numColumns)) {
            throw new \Exception("Placement not valid - the entire ship does not fit within the grid");
        }
        $numRows = $grid->info('rowCount');
        if (!is_int($y) or $y < 0 or $y >= $numRows or ('portrait' == $orientation && $y + $length >= $numRows)) {
            throw new \Exception("Placement not valid - the entire ship does not fit within the grid");
        }
        $coordinates = static::getCoordinates($x, $y, $length, $orientation);
        foreach ($coordinates as $point) {
            $_x = $point[0];
            $_y = $point[1];
            if (null !== $grid->getValue($_x, $_y)) {
                throw new \Exception("Placement not valid - overlapping another ship at point ($_x, $_y)");
            }
        }
        return true;
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $length
     * @param $orientation
     * @return array
     */
    public static function getCoordinates($x, $y, $length, $orientation)
    {
        $coordinates = [];
        for ($i = 0; $i < $length; $i++) {
            if ('portrait' === $orientation) {
                $coordinates[] = [$x, $y++];
            } else {
                $coordinates[] = [$x++, $y];
            }
        }
        return $coordinates;
    }



    public function start()
    {
        throw new \Exception("Not implemented yet");
    }
}