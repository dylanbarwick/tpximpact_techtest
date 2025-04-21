<?php

namespace TextGames;

use TextGames\GamesAdmin;

/**
 * Class Game
 * Represents a game in the TextGames application.
 */
class Game
{
  private $id;
  private $gamesAdmin;

  public function __construct($id)
  {
    $this->id = $id;
    $this->gamesAdmin = new GamesAdmin();
  }

  public function getId()
  {
    return $this->id;
  }

  public function getGame()
  {
    $gameData = $this->gamesAdmin->loadGame($this->id);
    if ($gameData) {
      return $gameData;
    } else {
      return null;
    }
  }

  public function getEncounters()
  {
    $gameData = $this->getGame();
    if ($gameData) {
      return $gameData['encounters'];
    } else {
      return [];
    }
  }

  public function getEncounter($encounterId)
  {
    $encounters = $this->getEncounters();
    foreach ($encounters as $encounter) {
      if ($encounter['id'] === $encounterId) {
        return $encounter;
      }
    }
    return null;
  }

  public function getPlayerAttributes()
  {
    $gameData = $this->getGame();
    if ($gameData && isset($gameData['player_attributes'])) {
      $attributes = [];
      foreach ($gameData['player_attributes'] as $key => $attribute) {
        if (isset($attribute['name']) && isset($attribute['default_value'])) {
          $attributes[$attribute['name']] = $attribute;
        }
      }
      return $attributes;
    } else {
      return [];
    }
  }

  public function renderPlayerAttributes(array $current_attributes)
  {
    if ($current_attributes && count($current_attributes) > 0) {
      echo "\n\n==ATTRIBUTES=============\n";
      foreach ($current_attributes as $attribute) {
        if (isset($attribute['name']) && isset($attribute['current_value'])) {
          echo $attribute['name'] . ": " . $attribute['current_value'] . "\n";
        }
      }
      echo "=========================\n";
    }
  }

  public function getPlayerInventory()
  {
    $gameData = $this->getGame();
    if ($gameData && isset($gameData['player_inventory'])) {
      $inventory = [];
      foreach ($gameData['player_inventory'] as $key => $inventory_item) {
        if (isset($inventory_item['name'])) {
          $inventory[$inventory_item['name']] = $inventory_item;
        }
      }
      return $inventory;
    } else {
      return [];
    }
  }

  public function renderPlayerInventory(array $current_inventory)
  {
    if ($current_inventory && count($current_inventory) > 0) {
      echo "\n==INVENTORY==============\n";
      foreach ($current_inventory as $item) {
        if (isset($item['name'])) {
          echo $item['name'] . "\n";
          echo " - " . $item['description'] . "\n";
        }
      }
      echo "=========================\n\n";
    }
  }
}
