<?php

namespace TextGames;

use TextGames\Game;

/**
 * Class GamesAdmin
 * Manages the list of games in the TextGames application.
 */
class GamesAdmin {
  private $games;

  public function __construct() {
    // Initialize the game list
    $this->games = [];
  }

  public function listGames() {
    // Get the list of games
    $games = $this->getGames();
    if (empty($games)) {
      echo "No games available.\n";
    } else {
      echo "\n=========================\n";
      echo "Available games:";
      echo "\n=========================\n";
      foreach ($games as $key => $game) {
        echo "Name: " . $game['name'] . "\n";
        echo "ID: " . $game['id'] . "\n";
        echo "Description: " . $game['description'] . "\n";
        echo "File name: " . $game['filename'] . "\n";
        echo "type '" . $key . "' to play\n";
        echo "------------------------\n";
      }
    }
  }

  public function getGames() {
    // Read contents of the 'games' directory
    $gamesDir = __DIR__ . '/games';
    if (!is_dir($gamesDir)) {
      return [];
    }
    else {
      $files = scandir($gamesDir);
      $games = [];
      foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
          // Read json file and extract name and description
          $filePath = $gamesDir . '/' . $file;
          $jsonContent = file_get_contents($filePath);
          $jsonData = json_decode($jsonContent, true);
          if (isset($jsonData['name']) && isset($jsonData['description'])) {
            $games[] = [
              'name' => $jsonData['name'],
              'description' => $jsonData['description'],
              'id' => $jsonData['id'],
              'filename' => pathinfo($file, PATHINFO_FILENAME),
            ];
          }
        }
      }
      return $games;
    }
  }

  public function loadGame($filename) {
    $filePath = __DIR__ . '/games/' . $filename . '.json';
    if (file_exists($filePath)) {
      $jsonContent = file_get_contents($filePath);
      return json_decode($jsonContent, true);
    }
    return null;
  }

}