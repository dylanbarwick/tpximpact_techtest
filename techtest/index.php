<?php

namespace TextGames;

use TextGames\GamesAdmin;
use TextGames\Game;

// Autoload the Game admin class
include 'GamesAdmin.php';
include 'Game.php';

$running = true;
$finished = true;

const MAX_HEARTS = 3;

$gamesadmin = new GamesAdmin();
$list_of_games = $gamesadmin->getGames();


// Main loop to keep the program running
while ($running) {
  // List all available games
  $gamesadmin->listGames();
  // Prompt the user for input
  echo "\nEnter the game number or 'q' to quit: ";
  $input = trim(fgets(STDIN));

  // Check if the user wants to exit
  if (strtolower($input) === 'q') {
    $finished = true;
    echo "Quitting the program.\n";
    $running = false;
    break;
  }

  // Check if the input matches any game name
  $gameFound = false;
  foreach ($list_of_games as $key => $game) {
    if ($input === (string) $key) {
      // If a game is found, load it and set the variables.
      $gameFound = true;
      $gameId = $list_of_games[$key]['id'];
      $game = new Game($gameId);
      $gameData = $game->getGame($gameId);
      $encounters = [];
      foreach ($gameData['encounters'] as $encounter) {
        $encounters[$encounter['id']] = $encounter;
      }
      // Reset the variables in case the user ducked out of this game and started again.
      $currentEncounter = 1;
      $player_attributes = $game->getPlayerAttributes();
      $player_inventory = $game->getPlayerInventory();

      // Display game name and description.
      echo "\n=========================\n";
      echo "Game: " . $gameData['name'] . "\n";
      echo "Introduction: " . $gameData['introduction'] . "\n";
      echo "Note: press `x` to finish this game and return to the list of games.\n";
      echo "=========================\n";
      $finished = false;
    }
  }

  // If no game was found, display an error message
  if (!$gameFound) {
    echo "Game not found. Please try again.\n";
  }

  // Main loop to keep a game running
  while (!$finished) {
    if ($gameData && $encounters) {
      $game = new Game($gameId);
      // Display current player attributes
      $game->renderPlayerAttributes($player_attributes);
      // Display current player inventory
      $game->renderPlayerInventory($player_inventory);
      // Display current encounter
      echo print_r($encounters[$currentEncounter]['text'], true) . "\n\n";

      // If it's the-end then close gracefully
      if (isset($encounters[$currentEncounter]['the-end']) && $encounters[$currentEncounter]['the-end'] === true) {
        echo "This is the end of this game but that's no reason for the fun to stop. Why not select from the list of other games on offer?\n\n";
        $finished = true;
        break;
      }

      // Display encounter options
      if (isset($encounters[$currentEncounter]['choices']) && count($encounters[$currentEncounter]['choices']) > 0) {
        echo "Options:\n";
        foreach ($encounters[$currentEncounter]['choices'] as $key => $option) {
          echo "- " . $option['text'] . " [" . $key . "]\n";
        }
        // Prompt the user for input
        echo "Which option do you choose: ";
        $gameInput = trim(fgets(STDIN));
      }


      // Check if the user wants to quit this game by pressing 'x'
      if (strtolower($gameInput) === 'x') {
        $finished = true;
        echo "Exiting the game.\n";
        // List all available games
        $gamesadmin->listGames();
        break;
      }

      // Check if the input is valid
      if (isset($encounters[$currentEncounter]['choices'][$gameInput])) {
        // Process the chosen option
        $chosenOption = $encounters[$currentEncounter]['choices'][$gameInput];
        echo "\n--\nYou chose: " . $chosenOption['text'] . "\n";
        echo "Result: " . $chosenOption['result']['text'] . "\n";

        // Apply consequences to player attributes
        if (isset($chosenOption['result']['consequences'])) {
          foreach ($chosenOption['result']['consequences'] as $key => $value) {
            $value_type = $value['type'];
            // If the value is an integer then we adjust an attribute...
            if (is_int($value['value'])) {
              $player_attributes[$value_type]['current_value'] += $value['value'];
            }
            else {
              // If it's a string...
              switch ($value['value']) {
                case 'die':
                  $player_attributes[$value_type]['current_value'] = 0;
                  break;
                case 'full-recovery':
                  $player_attributes[$value_type]['current_value'] = $player_attributes[$value_type]['default_value'];
                  break;
                default:
                  echo "Invalid " . $value_type . " value.\n";
              }
            }
          }
        }

        // Add swag to player inventory
        if (isset($chosenOption['result']['swag'])) {
          foreach ($chosenOption['result']['swag'] as $key => $value) {
            $player_inventory[$value['name']] = $value;
          }
        }
        // Update variables based on the chosen option
        if (isset($chosenOption['nextEncounterId'])) {
          $currentEncounter = $chosenOption['nextEncounterId'];
        }
        else {
          echo "No next encounter defined.\n";
        }
      }
      else {
        echo "Invalid option [" . $gameInput . "]. Please try again.\n";
      }

      // Check the player has at least one of each attribute to continue.
      foreach ($player_attributes as $key => $value) {
        if ($value['current_value'] <= 0) {
          echo "You have no " . $key . " left. Game over.\n";
          $finished = true;
        }
      }

      // Prompt the user for input
      echo "Hit `enter` to continue: ";
      $continueInput = trim(fgets(STDIN));
    }
    else {
      // If the game data is not loaded, display an error message to fail gracefully
      echo "Failed to load game data.\n";
    }
  }
}
