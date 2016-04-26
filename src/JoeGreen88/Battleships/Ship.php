<?php

namespace JoeGreen88\Battleships;

use Smrtr\DataGrid;

class Ship
{
    /**
     * @var int The number of tiles occupied by the ship.
     */
    protected $length;

    /**
     * @var string Must contain either 'portrait' or 'landscape' (default).
     */
    protected $orientation;

    /**
     * @var array The X and Y position of the top-most left-most square being occupied by the ship.
     */
    protected $position = [0, 0];

    /**
     * @var DataGrid
     */
    protected $grid;

    /**
     * @var bool
     */
    protected $isHit = false;

    /**
     * @var bool
     */
    protected $isSunk = false;

    /**
     * Ship constructor.
     *
     * @param int $x The x co-ordinate
     * @param int $y The y co-ordinate
     * @param int $length The length of the ship
     * @param string $orientation The orientation of the ship; either 'portrait' or 'landscape'
     * @param DataGrid $grid The grid on which the ship is placed.
     *
     * @throws \Exception If any of the parameters fail validation.
     */
    public function __construct($x, $y, $length, $orientation, DataGrid $grid)
    {
        if (!is_int($length) or $length < 2) {
            throw new \Exception("Parameter \$length must be an integer larger than 1");
        }
        if (!in_array($orientation, ['portrait', 'landscape'])) {
            throw new \Exception("Parameter \$orientation must contain 'portrait' or 'landscape'");
        }
        Game::validateShipPlacement($x, $y, $length, $orientation, $grid);

        $this->position = [$x, $y];
        $this->length = $length;
        $this->orientation = $orientation;
        $this->grid = $grid;
    }

    /**
     * @return int The number of tiles occupied by the ship.
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return string Either 'portrait' or 'landscape'.
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * @return array The X and Y position of the top-most left-most square being occupied by the ship.
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int The X position of the top-most left-most square being occupied by the ship.
     */
    public function getX()
    {
        return $this->position[0];
    }

    /**
     * @return int The Y position of the top-most left-most square being occupied by the ship.
     */
    public function getY()
    {
        return $this->position[1];
    }

    /**
     * @return array An array of (x, y) pairs.
     */
    public function getCoordinates()
    {
        return Game::getCoordinates($this->getX(), $this->getY(), $this->getLength(), $this->getOrientation());
    }

    /**
     * @return DataGrid
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * @return Tile[] An array of Tile objects spanning the ship's location.
     */
    public function getTiles()
    {
        $tiles = [];
        foreach ($this->getCoordinates() as $point) {
            $tiles[] = $this->getGrid()->getValue($point[0], $point[1]);
        }
        return $tiles;
    }

    /**
     * @return bool
     */
    public function isHit()
    {
        if (false === $this->isHit) {
            foreach ($this->getTiles() as $tile) {
                if ($tile->isShot()) {
                    $this->isHit = true;
                }
            }
        }
        return $this->isHit;
    }

    /**
     * @return bool
     */
    public function isSunk()
    {
        if (false === $this->isSunk) {
            $isSunk = true;
            foreach ($this->getTiles() as $tile) {
                if (!$tile->isShot()) {
                    $isSunk = false;
                    break;
                }
            }
            $this->isSunk = $isSunk;
        }
        return $this->isSunk;
    }

    /**
     * @return int A rounded percentage illustrating the number of the ship's tiles that have been shot.
     */
    public function getPercentageHit()
    {
        $tiles = $this->getTiles();
        $denominator = count($tiles);
        $numerator = 0;
        foreach ($tiles as $tile) {
            if ($tile->isShot()) {
                $numerator++;
            }
        }
        return (int) round(($numerator * 100) / $denominator);
    }
}