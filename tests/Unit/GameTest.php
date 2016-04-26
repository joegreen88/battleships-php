<?php

/**
 * This test case is concerned with the high level actions involved in setting up and playing a game.
 *
 * @author Joe Green <joe.green@smrtr.co.uk>
 */
class GameTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return array[] Each array contains arguments for the method testGridInitialisation
     */
    public function gameConfigurations()
    {
        return [
            [false, 5, 5],
            [true, 5, 5],
            [true, 7, 7],
            [true, 10, 10],
        ];
    }

    /**
     * @dataProvider gameConfigurations
     */
    public function testGridInitialisation($specifyGridSize, $numColumns, $numRows)
    {
        if ($specifyGridSize) {
            $game = new \JoeGreen88\Battleships\Game($numColumns, $numRows);
        } else {
            $game = new \JoeGreen88\Battleships\Game;
        }
        $this->assertSame($numColumns, $game->getActivePlayerGrid()->info('columnCount'));
        $this->assertSame($numColumns, $game->getInactivePlayerGrid()->info('columnCount'));
        $this->assertSame($numRows, $game->getActivePlayerGrid()->info('rowCount'));
        $this->assertSame($numRows, $game->getInactivePlayerGrid()->info('rowCount'));
    }

    /**
     * Ensure that the grid references and active player states are correctly handled when switching between players.
     */
    public function testActivePlayerControls()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $grid1 = $game->getActivePlayerGrid();
        $grid2 = $game->getInactivePlayerGrid();
        $this->assertInstanceOf("\\Smrtr\\DataGrid", $grid1);
        $this->assertInstanceOf("\\Smrtr\\DataGrid", $grid2);

        $this->assertSame(1, $game->getActivePlayer());
        $this->assertSame(2, $game->getInactivePlayer());
        $this->assertSame($grid1, $game->getActivePlayerGrid());
        $this->assertSame($grid2, $game->getInactivePlayerGrid());

        $game->changeActivePlayer();

        $this->assertSame(1, $game->getInactivePlayer());
        $this->assertSame(2, $game->getActivePlayer());
        $this->assertSame($grid1, $game->getInactivePlayerGrid());
        $this->assertSame($grid2, $game->getActivePlayerGrid());

        $game->changeActivePlayer();

        $this->assertSame(1, $game->getActivePlayer());
        $this->assertSame(2, $game->getInactivePlayer());
        $this->assertSame($grid1, $game->getActivePlayerGrid());
        $this->assertSame($grid2, $game->getInactivePlayerGrid());
    }

    /**
     * Make sure that getting and setting the number of ships works as expected within desired parameters.
     */
    public function testGettingAndSettingNumberOfShips()
    {
        $game = new \JoeGreen88\Battleships\Game;
        // Default configuration
        $this->assertSame(
            [2 => 1, 3 => 1, 4 => 1],
            $game->getNumShips()
        );
        $this->assertSame(0, $game->getNumShips(1));
        $this->assertSame(1, $game->getNumShips(2));
        $this->assertSame(1, $game->getNumShips(3));
        $this->assertSame(1, $game->getNumShips(4));
        $this->assertSame(0, $game->getNumShips(5));
        // Custom Configuration
        $customNumShips = [3 => 2, 4 => 1];
        $game->setNumShips($customNumShips);
        $this->assertSame($customNumShips, $game->getNumShips());
        $this->assertSame(0, $game->getNumShips(1));
        $this->assertSame(0, $game->getNumShips(2));
        $this->assertSame(2, $game->getNumShips(3));
        $this->assertSame(1, $game->getNumShips(4));
        $this->assertSame(0, $game->getNumShips(5));
    }

    /**
     * Placing some battleships in a valid formation on the grid.
     */
    public function testPlaceShipsSuccessfullyAndThenDetectThem()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $this->assertSame(0, count($game->getActivePlayerShips()));
        $this->assertSame(null, $game->getActivePlayerGrid()->getValue(0, 0));

        $game->placeShip(0, 0, 4, 'portrait');
        $this->assertSame(1, count($game->getActivePlayerShips()));
        $this->assertInstanceOf("\\JoeGreen88\\Battleships\\Tile", $game->getActivePlayerGrid()->getValue(0, 0));
        /** @var \JoeGreen88\Battleships\Tile $tile */
        $tile = $game->getActivePlayerGrid()->getValue(0, 0);
        $this->assertSame(0, $tile->getX());
        $this->assertSame(0, $tile->getY());
        $this->assertTrue($tile->isOccupied());
        $this->assertFalse($tile->isShot());

        $game->placeShip(0, 4, 3, 'landscape');
        $this->assertSame(2, count($game->getActivePlayerShips()));
        $this->assertInstanceOf("\\JoeGreen88\\Battleships\\Tile", $game->getActivePlayerGrid()->getValue(2, 4));
        /** @var \JoeGreen88\Battleships\Tile $tile */
        $tile = $game->getActivePlayerGrid()->getValue(2, 4);
        $this->assertSame(2, $tile->getX());
        $this->assertSame(4, $tile->getY());
        $this->assertTrue($tile->isOccupied());
        $this->assertFalse($tile->isShot());
    }

    /**
     * Make sure that we can get accurate information on the number of ships to be placed.
     */
    public function testGettingNumberOfShipsAvailableForPlacement()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $this->assertSame([2 => 1, 3 => 1, 4 => 1], $game->getNumShips());
        $this->assertSame([2 => 1, 3 => 1, 4 => 1], $game->getNumShipsAwaitingPlacement());

        $game->placeShip(0, 0, 3, 'portrait');
        $this->assertSame([2 => 1, 3 => 1, 4 => 1], $game->getNumShips());
        $this->assertSame([2 => 1, 4 => 1], $game->getNumShipsAwaitingPlacement());
    }

    public function testStartGame()
    {
        $game = new \JoeGreen88\Battleships\Game;

    }
}
