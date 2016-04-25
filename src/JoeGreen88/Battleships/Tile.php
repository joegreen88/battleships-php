<?php

namespace JoeGreen88\Battleships;

class Tile
{
    /**
     * @var int
     */
    protected $x = 0;

    /**
     * @var int
     */
    protected $y = 0;

    /**
     * @var Ship|null
     */
    protected $ship;

    /**
     * @var bool Whether or not this tile has been hit.
     */
    protected $isShot = false;

    /**
     * Tile constructor.
     *
     * @param int $x
     * @param int $y
     * @param Ship|null $ship optionally assign this tile to a ship.
     */
    public function __construct($x, $y, Ship $ship = null)
    {
        $this->x = $x;
        $this->y = $y;
        $this->ship = $ship;
    }

    /**
     * @return bool
     */
    public function isOccupied()
    {
        return $this->ship instanceof Ship;
    }

    /**
     * @return bool
     */
    public function isShot()
    {
        return $this->isShot;
    }

    /**
     * @return Ship|null
     */
    public function getShip()
    {
        return $this->ship;
    }

    /**
     * @return int[] A pair (x, y)
     */
    public function getCoordinates()
    {
        return [$this->x, $this->y];
    }

    /**
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Mark this tile as shot.
     *
     * @return static
     */
    public function shoot()
    {
        $this->isShot = true;

        if (null !== $this->ship) {

        }

        return $this;
    }
}