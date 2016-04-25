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

I chose not to use `Tile` objects at every point on the grid by default as that would be less memory-efficient.

## Preparing the game

Instantiate a new `Game` object. Use the `Game` constructor to specify the grid size for this game.

By default each player will have three ships of varying sizes:

 - One ship will cover 2 squares
 - One ship will cover 3 squares
 - One ship will cover 4 squares

Make a call to `$game->setNumShips()` to specify the number of ships (and their respective sizes) for this game.

## Playing the game

At this point you need to know that the `Game` object has two players and one of them is active at any given time.
You can change the active player, i.e. move on to the next player's turn, by calling `$game->changeActivePlayer()`.

### Phase 1: Placing ships

Call `$game->placeShip()` to place a ship on the active player's grid.

Note that once a ship has been placed you will no longer be able to modify the number of ships in the game.

Once both players have placed all of their ships you can begin the game proper by calling `$game->start()`.

### Phase 2: Game in progress

WIP.
