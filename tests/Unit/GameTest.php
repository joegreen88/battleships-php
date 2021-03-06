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
        foreach ($game->getActivePlayerShips() as $ship) {
            $this->assertInstanceOf("\\JoeGreen88\\Battleships\\Ship", $ship);
        }
        $this->assertInstanceOf("\\JoeGreen88\\Battleships\\Tile", $game->getActivePlayerGrid()->getValue(0, 0));
        /** @var \JoeGreen88\Battleships\Tile $tile */
        $tile = $game->getActivePlayerGrid()->getValue(0, 0);
        $this->assertSame(0, $tile->getX());
        $this->assertSame(0, $tile->getY());
        $this->assertTrue($tile->isOccupied());
        $this->assertFalse($tile->isShot());

        $game->placeShip(0, 4, 3, 'landscape');
        $this->assertSame(2, count($game->getActivePlayerShips()));
        foreach ($game->getActivePlayerShips() as $ship) {
            $this->assertInstanceOf("\\JoeGreen88\\Battleships\\Ship", $ship);
        }
        $this->assertInstanceOf("\\JoeGreen88\\Battleships\\Tile", $game->getActivePlayerGrid()->getValue(2, 4));
        /** @var \JoeGreen88\Battleships\Tile $tile */
        $tile = $game->getActivePlayerGrid()->getValue(2, 4);
        $this->assertSame(2, $tile->getX());
        $this->assertSame(4, $tile->getY());
        $this->assertTrue($tile->isOccupied());
        $this->assertFalse($tile->isShot());
    }

    /**
     * Ensure that a ships can be placed such that they touch the far edge of the grid as long as they don't cross it.
     */
    public function testPlaceShipTouchingGridBorder()
    {
        $game = new \JoeGreen88\Battleships\Game(6, 6); // so grid keys from 0 to 5 inclusive
        $game->setNumShips([5 => 1]);
        $game->placeShip(5, 1, 5, 'portrait');
        $this->assertSame(1, count($game->getActivePlayerShips()));
        $this->assertSame(
            [ [5, 1], [5, 2], [5, 3], [5, 4], [5, 5] ],
            $game->getActivePlayerShips()[0]->getCoordinates()
        );
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

    /**
     * @return \JoeGreen88\Battleships\Game
     */
    protected function getNewGameInProgress()
    {
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
        return $game->start();
    }

    /**
     * Starting the game after all ships have been placed sets the gameState to 2 (game in progress).
     */
    public function testStartGame()
    {
        $game = $this->getNewGameInProgress();
        $this->assertSame(2, $game->getState());
        $this->assertSame("Game in progress", $game->getState(true));
    }

    /**
     * Once the game proper has started we can begin firing shots.
     */
    public function testShootingBackAndForth()
    {
        $game = $this->getNewGameInProgress();

        // miss
        $this->assertFalse($game->shoot(3, 0));
        $tile = $game->getInactivePlayerGrid()->getValue(3, 0);
        $this->assertInstanceOf("\\JoeGreen88\\Battleships\\Tile", $tile);
        $this->assertTrue($tile->isShot());
        $this->assertFalse($tile->isOccupied());
        $game->changeActivePlayer();

        // miss
        $this->assertFalse($game->shoot(3, 0));
        $tile = $game->getInactivePlayerGrid()->getValue(3, 0);
        $this->assertInstanceOf("\\JoeGreen88\\Battleships\\Tile", $tile);
        $this->assertTrue($tile->isShot());
        $this->assertFalse($tile->isOccupied());
        $game->changeActivePlayer();

        // hit
        $this->assertTrue($game->shoot(4, 3));
        $tile = $game->getInactivePlayerGrid()->getValue(4, 3);
        $this->assertInstanceOf("\\JoeGreen88\\Battleships\\Tile", $tile);
        $this->assertTrue($tile->isShot());
        $this->assertTrue($tile->isOccupied());
        $game->changeActivePlayer();

        // hit
        $this->assertTrue($game->shoot(0, 0));
        $tile = $game->getInactivePlayerGrid()->getValue(0, 0);
        $this->assertInstanceOf("\\JoeGreen88\\Battleships\\Tile", $tile);
        $this->assertTrue($tile->isShot());
        $this->assertTrue($tile->isOccupied());
    }

    /**
     * When a player shoots all of the opponents ships down then the game should change state automatically.
     */
    public function testWinningTheGame()
    {
        $game = $this->getNewGameInProgress();
        $targets = [ // these are all of the locations of player 2's ships.. the perfect game from player 1 :-)
            [4,3], [4,4],
            [0,1], [1,1], [2,1],
            [1,2], [2,2], [3,2], [4,2],
        ];
        // player 1 has ships in different locations, so use the same shots for both players and player 1 will win
        while (count($targets)) {
            $this->assertSame(2, $game->getState());
            list($x, $y) = array_shift($targets);
            $game->shoot($x, $y);
            if (!count($targets)) {
                break;
            }
            $game->changeActivePlayer();
            $game->shoot($x, $y);
            $game->changeActivePlayer();
        }
        $this->assertSame(3, $game->getState());
        $this->assertSame(1, $game->getWinner());
    }

    /**
     * Walk through some game actions and make sure the score tracking is on point throughout.
     */
    public function testScoreTracking()
    {
        $game = $this->getNewGameInProgress();
        $score = [
            1 => [
                'shots' => 0,
                'hits' => 0,
                'kills' => 0
            ],
            2 => [
                'shots' => 0,
                'hits' => 0,
                'kills' => 0
            ]
        ];
        $this->assertTrue($score === $game->getScore());

        // hit
        $game->shoot(4, 3); ++$score[1]['shots']; ++$score[1]['hits'];
        $this->assertTrue($score === $game->getScore());

        // miss
        $game->changeActivePlayer()->shoot(0, 4); ++$score[2]['shots'];
        $this->assertTrue($score === $game->getScore());

        // hit, sink
        $game->changeActivePlayer()->shoot(4, 4); ++$score[1]['shots']; ++$score[1]['hits']; ++$score[1]['kills'];
        $this->assertTrue($score === $game->getScore());
    }
}
