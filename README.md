Battleships
===========

A set of core classes to support a game of battleships.

## Requirements

 - PHP 5.4 +
 - composer

## Install

Clone the repo and run `composer install`.

## Run the tests

`vendor/bin/phpunit`.

About the Code
==============

Each game consists of a `Game` object which contains two grids - one for each player's ship formation.

Each grid is an instance of `Smrtr\DataGrid` which has been **composered** into the project for convenience.

The grids are initialised with null values at every point, but these values may change as the game progresses.

Whenever a square is "discovered" during the game, either due to a ship being placed there or by a missile being
targeted there, the value of that point on the grid becomes a `Tile` object.

## Preparing the game

Instantiate a new `Game` object. Use the `Game` constructor to specify the grid size for this game.

By default each player will have three ships of varying sizes:

 - One ship will cover 2 squares
 - One ship will cover 3 squares
 - One ship will cover 4 squares

Make a call to `$game->setNumShips()` to specify a custom number of ships (and their respective sizes) for this game.

## Playing the game

### Phase 1: Placing ships

Call `$game->placeShip()` to place a ship on the active player's grid.

Both players need to place their ships so call `$game->changeActivePlayer()` to switch between players.

Note that once a ship has been placed you will no longer be able to modify the number of ships in the game.

Once both players have placed all of their ships you can begin the game proper by calling `$game->start()`.

### Phase 2: Game in progress

Call `$game->shoot()` to take a shot at the opponents grid at the given co-ordinates.
This method will return true for a hit and false for a miss.

Each player can only take one shot at a time, so after shooting call `$game->changeActivePlayer()` to let the opponent
have their turn.



### Phase 3: Game won

If the player's shot is a winning hit then the game will end automatically.

You can check the state of the game at the beginning or end of each turn by calling `$game->getState()`.

If the game has been won (game state = 3) then you can get the winning player by calling `$game->getWinner()`.

Ideas for improvement
=====================

## Console GUI

It would be cool to add methods for printing out a GUI on the command line. 

The `Game` object already has the methods to get all the info you need.

For example, to draw a basic view of the opponent's grid:
 
    $grid = $game->getInactivePlayerGrid();
    $line = str_repeat("-", 1 + ($grid->getInfo('columnCount') * 2));
    $grid->eachRow(function($key, $label, $row) {
        echo $line."\n";
        foreach ($row as $cell) {
            echo "|";
            if ($cell instanceof Tile && $cell->isShot()) {
                echo $cell->isOccupied() ? "H" : "M";
            } else {
                echo " ";
            }
        }
        echo "|\n";
    });
    echo $line."\n";
    
But of course this output could be greatly improved, e.g. by using the Symfony Console component table helper.

## Serializable

In it's current state the game would need to be played in a long running process like a REPL on the command line.

The `Game` object could be made serializable and this would allow the games to be saved and loaded easily. 
Then it would be easy to build a web-based GUI for the game.

## Logging

It would be possible to log all of the game actions and then at the end of the game the player
could download a transcript of the game.
