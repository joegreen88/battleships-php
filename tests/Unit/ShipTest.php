<?php

/**
 * This test case is concerned with the functionality of Ship objects.
 *
 * @author Joe Green <joe.green@smrtr.co.uk>
 */
class ShipTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \JoeGreen88\Battleships\Game
     */
    protected $game;

    protected function setUp()
    {
        parent::setUp();

        $game = new \JoeGreen88\Battleships\Game;
        // Player 1 ship placement
        $game->placeShip(0, 0, 2, 'landscape');
        $game->placeShip(0, 1, 3, 'portrait');
        $game->placeShip(1, 4, 4, 'landscape');
        $game->changeActivePlayer();
        // Player 2 ship placement
        $game->placeShip(4, 3, 2, 'portrait');
        $game->placeShip(0, 1, 3, 'landscape');
        $game->placeShip(1, 2, 4, 'landscape');
        $game->changeActivePlayer();
        // Start game
        $this->game = $game->start();
    }

    public function testGetLength()
    {
        $this->assertSame(2, $this->game->getActivePlayerShips()[0]->getLength());
        $this->assertSame(3, $this->game->getActivePlayerShips()[1]->getLength());
        $this->assertSame(4, $this->game->getActivePlayerShips()[2]->getLength());
    }

    public function testGetOrientation()
    {
        $this->assertSame('landscape', $this->game->getActivePlayerShips()[0]->getOrientation());
        $this->assertSame('portrait', $this->game->getActivePlayerShips()[1]->getOrientation());
        $this->assertSame('landscape', $this->game->getActivePlayerShips()[2]->getOrientation());
    }

    public function testGetPosition()
    {
        $this->assertTrue([0,0] === $this->game->getActivePlayerShips()[0]->getPosition());
        $this->assertTrue([0,1] === $this->game->getActivePlayerShips()[1]->getPosition());
        $this->assertTrue([1,4] === $this->game->getActivePlayerShips()[2]->getPosition());
    }

    public function testGetCoordinates()
    {
        $ship1 = $this->game->getActivePlayerShips()[0];
        $this->assertTrue([[0,0], [1,0]] === $ship1->getCoordinates());

        $ship2 = $this->game->getActivePlayerShips()[1];
        $this->assertTrue([[0,1], [0,2], [0,3]] === $ship2->getCoordinates());

        $ship3 = $this->game->getActivePlayerShips()[2];
        $this->assertTrue([[1,4], [2,4], [3,4], [4,4]] === $ship3->getCoordinates());
    }

    public function testIsHit()
    {
        $ship = $this->game->getInactivePlayerShips()[0];
        $this->assertFalse($ship->isHit());
        $this->assertTrue($this->game->shoot(4,3));
        $this->assertTrue($ship->isHit());
    }

    public function testIsSunk()
    {
        $ship = $this->game->getInactivePlayerShips()[0];
        $this->assertFalse($ship->isSunk());
        $this->game->shoot(4,3);
        $this->assertFalse($ship->isSunk());
        $this->game->changeActivePlayer();
        $this->game->shoot(4,4);
        $this->game->changeActivePlayer();
        $this->game->shoot(4,4);
        $this->assertTrue($ship->isSunk());
    }

    public function testGetPercentageHit()
    {
        $ship = $this->game->getInactivePlayerShips()[0];
        $this->assertSame(0, $ship->getPercentageHit());
        $this->game->shoot(4,3);
        $this->assertSame(50, $ship->getPercentageHit());
        $this->game->changeActivePlayer();
        $this->game->shoot(4,4);
        $this->game->changeActivePlayer();
        $this->game->shoot(4,4);
        $this->assertSame(100, $ship->getPercentageHit());
    }
}