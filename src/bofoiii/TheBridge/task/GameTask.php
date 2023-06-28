<?php

namespace bofoiii\TheBridge\task;

use jackmd\scorefactory\ScoreFactory;
use pocketmine\player\GameMode;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\world\sound\PopSound;
use bofoiii\TheBridge\game\Game;
use bofoiii\TheBridge\TheBridge;
use bofoiii\TheBridge\utils\Utils;

class GameTask extends Task
{

    public function __construct(private Game $game)
    {
    }

    public function onRun(): void
    {
        $players = $this->game->getPlayers();
        switch ($this->game->phase) {
            case Game::PHASE_LOBBY:
                foreach ($players as $player) {
                    if ($player->isOnline()) {
                        ScoreFactory::setObjective($player, TextFormat::YELLOW . TextFormat::BOLD . "THE BRIDGE");
                        ScoreFactory::setScoreLine($player, 1, TextFormat::WHITE . "Players: " . TextFormat::GREEN . count($players) . "/2");
                        ScoreFactory::setScoreLine($player, 2, TextFormat::WHITE . "Map: " . TextFormat::GREEN . $this->game->getName());
                        ScoreFactory::setScoreLine($player, 3, " ");
                        ScoreFactory::setScoreLine($player, 4, TextFormat::RED . "Waiting for more players..");
                        ScoreFactory::setScoreLine($player, 5, "      ");
                        ScoreFactory::setScoreLine($player, 6, "Mode: " . TextFormat::GREEN . "Solo");
                        ScoreFactory::setScoreLine($player, 7, "    ");
                        ScoreFactory::setScoreLine($player, 8, TheBridge::getInstance()->getConfig()->get("server-ip"));
                        ScoreFactory::sendObjective($player);
                        ScoreFactory::sendLines($player);
                    }
                }
                break;
            case Game::PHASE_COUNTDOWN:
                foreach ($players as $player) {
                    if ($player->isOnline()) {
                        ScoreFactory::setObjective($player, TextFormat::YELLOW . TextFormat::BOLD . "THE BRIDGE");
                        ScoreFactory::setScoreLine($player, 1, TextFormat::WHITE . "Players: " . TextFormat::GREEN . count($players) . "/2");
                        ScoreFactory::setScoreLine($player, 2, TextFormat::WHITE . "Map: " . TextFormat::GREEN . $this->game->getName());
                        ScoreFactory::setScoreLine($player, 3, "    ");
                        ScoreFactory::setScoreLine($player, 4, "Starting in " . TextFormat::GREEN . $this->game->getCountdown() . "s");
                        ScoreFactory::setScoreLine($player, 5, "  ");
                        ScoreFactory::setScoreLine($player, 6, "Mode: " . TextFormat::GREEN . "Solo");
                        ScoreFactory::setScoreLine($player, 7, " ");
                        ScoreFactory::setScoreLine($player, 8, TheBridge::getInstance()->getConfig()->get("server-ip"));
                        ScoreFactory::sendObjective($player);
                        ScoreFactory::sendLines($player);
                    }
                    if ($this->game->getCountdown() <= 5) {
                        $player->sendTitle(TextFormat::YELLOW . $this->game->getCountdown());
                        $player->getWorld()->addSound($player->getPosition(), new PopSound());
                    }
                    if ($this->game->getCountdown() <= 0) {
                        $this->game->phase = Game::PHASE_RUNNING;
                        $this->game->sendAllCages();
                    }
                }
                $this->game->decrementCountdown();
                break;
            case Game::PHASE_RUNNING:
                foreach ($players as $player) {
                    if ($player->isOnline()) {
                        if ($this->game->hasCage()) {
                            $player->sendTitle($this->game->scoredname !== null ? Utils::teamToColor($this->game->getTeamByPlayerName(strtolower($this->game->scoredname))) . $this->game->scoredname . TextFormat::GRAY . " scored" : "", TextFormat::GRAY . "Cages will open in " . TextFormat::GREEN . $this->game->getCageCountdown());
                            $player->getWorld()->addSound($player->getPosition(), new PopSound());
                        }
                        ScoreFactory::setObjective($player, TextFormat::YELLOW . TextFormat::BOLD . "THE BRIDGE");
                        ScoreFactory::setScoreLine($player, 1, TextFormat::WHITE . "Time left: " . TextFormat::GREEN . Utils::intToString($this->game->getTimer()));
                        ScoreFactory::setScoreLine($player, 2, " ");
                        ScoreFactory::setScoreLine($player, 3, TextFormat::RED . TextFormat::BOLD . "[R]" . TextFormat::RESET . Utils::RintToPoint($this->game->getPointByTeam("red") ?? 0));
                        ScoreFactory::setScoreLine($player, 4, TextFormat::BLUE . TextFormat::BOLD . "[B]" . TextFormat::RESET . Utils::BintToPoint($this->game->getPointByTeam("blue") ?? 0));
                        ScoreFactory::setScoreLine($player, 5, "   ");
                        ScoreFactory::setScoreLine($player, 6, TextFormat::WHITE . "Kills: " . TextFormat::GREEN . $this->game->getPlayerKills($player));
                        ScoreFactory::setScoreLine($player, 7, TextFormat::WHITE . "Goals: " . TextFormat::GREEN . $this->game->getPlayerGolas($player));
                        ScoreFactory::setScoreLine($player, 8, "  ");
                        ScoreFactory::setScoreLine($player, 9, TextFormat::WHITE . "Map: §a" . $this->game->getName());
                        ScoreFactory::setScoreLine($player, 10, TextFormat::WHITE . "Mode: §aSolo");
                        ScoreFactory::setScoreLine($player, 11, " ");
                        ScoreFactory::setScoreLine($player, 12, TheBridge::getInstance()->getConfig()->get("server-ip"));
                        ScoreFactory::sendObjective($player);
                        ScoreFactory::sendLines($player);
                    }
                }
                if ($this->game->getCageCountdown() <= 0) {
                    $this->game->setCage(false);
                    $this->game->resetCageCountdown();
                    $this->game->removeAllCages();
                }
                if ($this->game->hasCage()) {
                    $this->game->decrementCageCountdown();
                }
                if ($this->game->getTimer() <= 0) {
                    $this->game->phase = Game::PHASE_RESTARTING;
                    foreach ($players as $player) {
                        $player->setGamemode(GameMode::ADVENTURE());
                    }
                }
                $this->game->decrementTimer();
                break;
            case Game::PHASE_RESTARTING:
                foreach ($players as $player) {
                    if ($player->isOnline()) {
                        ScoreFactory::setObjective($player, TextFormat::YELLOW . TextFormat::BOLD . "THE BRIDGE");
                        ScoreFactory::setScoreLine($player, 1, TextFormat::WHITE . "Restarting in " . TextFormat::GREEN . $this->game->getRestartCountdown());
                        ScoreFactory::setScoreLine($player, 2, " ");
                        ScoreFactory::setScoreLine($player, 3, TextFormat::RED . TextFormat::BOLD . "[R]" . TextFormat::RESET . Utils::RintToPoint($this->game->getPointByTeam("red") ?? 0));
                        ScoreFactory::setScoreLine($player, 4, TextFormat::BLUE . TextFormat::BOLD . "[B]" . TextFormat::RESET . Utils::BintToPoint($this->game->getPointByTeam("blue") ?? 0));
                        ScoreFactory::setScoreLine($player, 5, "   ");
                        ScoreFactory::setScoreLine($player, 6, TextFormat::WHITE . "Kills: " . TextFormat::GREEN . $this->game->getPlayerKills($player));
                        ScoreFactory::setScoreLine($player, 7, TextFormat::WHITE . "Goals: " . TextFormat::GREEN . $this->game->getPlayerGolas($player));
                        ScoreFactory::setScoreLine($player, 8, "  ");
                        ScoreFactory::setScoreLine($player, 9, TextFormat::WHITE . "Map: §a" . $this->game->getName());
                        ScoreFactory::setScoreLine($player, 10, TextFormat::WHITE . "Mode: §aSolo");
                        ScoreFactory::setScoreLine($player, 11, " ");
                        ScoreFactory::setScoreLine($player, 12, TheBridge::getInstance()->getConfig()->get("server-ip"));
                        ScoreFactory::sendObjective($player);
                        ScoreFactory::sendLines($player);
                    }
                }
                $this->game->decrementtRestartCountdown();
                if ($this->game->getRestartCountdown() <= 0) {
                    $this->game->resetRestartCountdown();
                    $game = 0;
                    foreach (TheBridge::getInstance()->getGames() as $games) {
                        if ($games->isRunning()) {
                            ++$game;
                            foreach ($players as $player) {
                                $games->addPlayer($player);
                                $this->game->removePlayer($player);
                                ScoreFactory::removeObjective($player);
                            }
                        }
                    }
                    if ($game <= 0) {
                        foreach ($players as $player) {
                            ScoreFactory::removeObjective($player);
                            $player->sendMessage(TextFormat::RED . "No arena is running!");
                        }
                    }
                    $this->game->restart();
                }
        }
    }
}
