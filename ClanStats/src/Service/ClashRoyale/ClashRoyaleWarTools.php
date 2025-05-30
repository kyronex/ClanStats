<?php

// src/Service/ClashRoyale/ClashRoyaleWarTools.php
namespace App\Service\ClashRoyale;

use Symfony\Component\Serializer\SerializerInterface;
use App\Dto\ClashRoyale\RiverRace\RiverRaceLog;
use App\Dto\ClashRoyale\Clan;
use Psr\Log\LoggerInterface;

class ClashRoyaleWarTools
{
    private LoggerInterface $logger;
    private SerializerInterface $serializer;

    public function __construct(LoggerInterface $logger, SerializerInterface $serializer)
    {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->logger->info("Initialisation de : 'class ClashRoyaleWarTools'.");
    }

    public function processGetWarsSelected(array $warsSelected, array $riverRaceLog)
    {
        $this->logger->info("Lancement de : 'function processGetWarsSelected'.");
        $warsFiltered = [];
        foreach ($riverRaceLog as $riverRace) {
            if (in_array($riverRace->getSeasonId() . "_" . $riverRace->getSectionIndex(), $warsSelected)) {
                $warsFiltered[] = $riverRace;
            }
        }
        return $warsFiltered;
    }

    public function processGetWarsByClan(string $clanTag, array $riverRaceLog)
    {
        $this->logger->info("Lancement de : 'function processGetWarsByClan'.");
        $warsFiltered = [];
        foreach ($riverRaceLog as $riverRace) {
            foreach ($riverRace->getClans() as $clan) {
                if ($clan->getTag() == $clanTag) {
                    $riverRaceJson = $this->serializer->serialize($riverRace, "json", ["groups" => "riverRaceLogInfo"]);
                    $riverRaceArray = json_decode($riverRaceJson, true);
                    $clanJsonFlat = $this->serializer->serialize($clan, "json", ["groups" => "clanInfoFlat"]);
                    $clanJsonDeep = $this->serializer->serialize($clan, "json", ["groups" => "clanInfoDeep"]);
                    $participantsJson = $this->serializer->serialize($clan->getParticipants(), "json", ["groups" => "ajaxed"]);
                    $clanArray = json_decode($clanJsonFlat, true);
                    $clanArray["clan"] = json_decode($clanJsonDeep, true);
                    $clanArray["clan"]["participants"] = json_decode($participantsJson, true);
                    $riverRaceArray["standings"] = [$clanArray];
                    $filteredRiverRace = new RiverRaceLog($riverRaceArray);
                    $warsFiltered[] = $filteredRiverRace;
                    break;
                }
            }
        }
        return $warsFiltered;
    }

    public function processGetWarsPlayersStats(Clan $currentClan, array $riverRaceLog)
    {
        $this->logger->info("Lancement de : 'function processGetWarsPlayersStats'.");
        $playerTags = array_map(function ($player) {
            return $player->getTag();
        }, $currentClan->getMembersList());
        $playersStats = [];
        foreach ($riverRaceLog as $riverRace) {
            foreach ($riverRace->getClans() as $clan) {
                foreach ($clan->getParticipants() as $participant) {
                    $playersStats[$participant->getTag()][$riverRace->getSeasonId() . "_" . $riverRace->getSectionIndex()] = $participant;
                }
            }
        }
        foreach ($playersStats as $player => $wars) {
            if (in_array($player, $playerTags)) {
                $playersStats[$player]["currentPlayer"] = true;
            } else {
                $playersStats[$player]["currentPlayer"] = false;
            }
            $playersStats[$player]["name"] = "";
            $playersStats[$player]["totalWarsParticipated"] = count($wars);
            $totalWarsFame = 0;
            $totalWarsBoatAttacks = 0;
            $totalWarsDecksUsed = 0;
            foreach ($wars as $stats) {
                if ($playersStats[$player]["name"] == "") {
                    $playersStats[$player]["name"] = $stats->getName();
                }
                $totalWarsFame += $stats->getFame();
                $totalWarsBoatAttacks += $stats->getBoatAttacks();
                $totalWarsDecksUsed += $stats->getDecksUsed();
            }
            $playersStats[$player]["totalWarsFame"] = $totalWarsFame;
            $playersStats[$player]["totalWarsBoatAttacks"] = $totalWarsBoatAttacks;
            $playersStats[$player]["totalWarsDecksUsed"] = $totalWarsDecksUsed;
            $playersStats[$player]["averageWarsFame"] = round($playersStats[$player]["totalWarsFame"] / $playersStats[$player]["totalWarsParticipated"], 4);
            $playersStats[$player]["averageWarsBoatAttacks"] = round($playersStats[$player]["totalWarsBoatAttacks"] / $playersStats[$player]["totalWarsParticipated"], 4);
            $playersStats[$player]["averageWarsDecksUsed"] = round($playersStats[$player]["totalWarsDecksUsed"] / $playersStats[$player]["totalWarsParticipated"], 4);
        }
        $activeMembers = array_filter($playersStats, function($player) {
            return $player['currentPlayer'] === true;
        });
        $exMembers = array_filter($playersStats, function($player) {
            return $player['currentPlayer'] === false;
        });
        return [
            "activeMembers" => $activeMembers,
            "exMembers" => $exMembers
        ];
    }
}
