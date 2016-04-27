<?php

/**
 * This test case is concerned with ensuring that the proper exceptions are thrown due to user error.
 *
 * @author Joe Green <joe.green@smrtr.co.uk>
 */
class GameErrorsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The number of columns must be an integer larger than 4
     */
    public function testInvalidGridInitialisation()
    {
        new \JoeGreen88\Battleships\Game("foobar");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The param $numShips must map positive integers to positive integers
     */
    public function testSettingInvalidNumberOfShips()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $game->setNumShips(["foo" => "bar"]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Too many ships specified - they would cover the entire grid!
     */
    public function testSettingNumberOfShipsTooLargeForGrid()
    {
        $game = new \JoeGreen88\Battleships\Game(10, 10); // 100 squares
        $game->setNumShips([
            3 => 10, // 30
            6 => 10, // 60
            5 => 6   // 30
        ]);          // 120 squares > 100 squares
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid number of ships given - no ship of length 12 will fit on the grid!
     */
    public function testSettingNumberOfShipsWithShipsTooLongForGrid()
    {
        $game = new \JoeGreen88\Battleships\Game(10, 10);
        $game->setNumShips([12 => 1]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The param $numShips must not be empty
     */
    public function testSettingNumberOfShipsWithZeroShips()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $game->setNumShips([]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Placement not valid - the entire ship does not fit within the grid
     */
    public function testPlaceShipCompletelyOutOfBounds()
    {
        $game = new \JoeGreen88\Battleships\Game; // 5 x 5 grid by default
        $game->placeShip(6, 6, 3, 'landscape');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Placement not valid - the entire ship does not fit within the grid
     */
    public function testPlaceShipPartiallyOutOfBounds()
    {
        $game = new \JoeGreen88\Battleships\Game; // 5 x 5 grid by default
        $game->placeShip(4, 4, 3, 'landscape');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Placement not valid - overlapping another ship at point (0, 0)
     */
    public function testPlaceShipOverlappingAnotherShip()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $game->placeShip(0, 0, 2, 'portrait');
        $game->placeShip(0, 0, 3, 'landscape');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot specify the number of ships - ship placement has already begun
     */
    public function testSetNumShipsAfterShipPlacementHasBegun()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $game->placeShip(0, 0, 3, 'landscape');
        $game->setNumShips([3 => 5]);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage There are no ships of length 5 available for placement
     */
    public function testPlaceShipWhenNoneAreAvailableForPlacement()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $game->placeShip(0, 0, 5, 'landscape');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage There are no ships of length 3 available for placement
     */
    public function testPlaceTwoShipsWhenOnlyOneIsAvailableForPlacement()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $game->setNumShips([2 => 2, 3 => 1, 4 => 2]);
        // this first one will succeed, there is one 3-ship available for placement
        $game->placeShip(0, 1, 3, 'landscape');
        // this second one will fail as the only 3-ship has already been placed above
        $game->placeShip(0, 0, 3, 'landscape');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Cannot place ships once the game has started
     */
    public function testPlaceShipWhenGameAlreadyStarted()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $modifyGameState = \Closure::bind(
            function() {
                $this->gameState = 2;
            },
            $game,
            $game
        );
        $modifyGameState();
        $game->placeShip(0, 0, 3, 'portrait');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The game cannot start until both players have placed all of their ships
     */
    public function testStartGameWhenShipsAwaitPlacement()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $game->start();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The game has already started
     */
    public function testStartGameWhenGameAlreadyStarted()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $modifyGameState = \Closure::bind(
            function() {
                $this->gameState = 2;
            },
            $game,
            $game
        );
        $modifyGameState($game);
        $game->start();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage You may not start shooting until the game is in progress
     */
    public function testShootWhenGameNotStarted()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $game->shoot(0, 0);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage You may not shoot twice in a row - it is the other player's turn
     */
    public function testShootTwiceInARow()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $game->setNumShips([2 => 1]);
        $game->placeShip(2, 2, 2, "portrait")->changeActivePlayer()->placeShip(2, 2, 2, "landscape");
        $game->start();
        $game->shoot(0, 0);
        $game->shoot(1, 1);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage This target has already been shot
     */
    public function testShootAtTargetThatHasAlreadyBeenShot()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $game->setNumShips([2 => 1]);
        $game->placeShip(2, 2, 2, "portrait")->changeActivePlayer()->placeShip(2, 2, 2, "landscape");
        $game->start();
        $game->shoot(0, 0);
        $game->changeActivePlayer()->shoot(0, 0);
        $game->changeActivePlayer()->shoot(0, 0);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The game is won. The winner is player 2
     */
    public function testShootWhenGameWon()
    {
        $game = new \JoeGreen88\Battleships\Game;
        $modifyGameState = \Closure::bind(
            function() {
                $this->gameState = 3;
                $this->winner = 2;
            },
            $game,
            $game
        );
        $modifyGameState($game);
        $game->shoot(0, 0);
    }
}
