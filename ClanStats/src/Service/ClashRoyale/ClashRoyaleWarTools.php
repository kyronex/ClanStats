<?php

namespace App\Service\ClashRoyale;

use Symfony\Component\Serializer\SerializerInterface;
use App\Dto\ClashRoyale\RiverRace\RiverRaceLog;
use App\Dto\ClashRoyale\Clan;
use Psr\Log\LoggerInterface;

use App\Dto\ClashRoyale\Analysis\PlayerStatsHistoriqueClanWar;
use App\Dto\ClashRoyale\Analysis\War;
use App\Dto\ClashRoyale\Analysis\WarStatsHistoriqueClanWar;

use App\Enum\PlayerMetric;

class ClashRoyaleWarTools
{
    private LoggerInterface $logger;
    private SerializerInterface $serializer;
    private const TARGETS_STATS_WARS = [
        "fame",
        "boatAttacks",
        "decksUsed"
    ];

    public function __construct(LoggerInterface $logger, SerializerInterface $serializer)
    {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->logger->info("Initialisation de : class 'ClashRoyaleWarTools'.");
    }

    public function processGetWarsSelected(array $warsSelected, array $riverRaceLog)
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'processGetWarsSelected'.");
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
        $this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'processGetWarsByClan'.");
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
        $this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'processGetWarsPlayersStats'.");
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
        $activeMembers = array_filter($playersStats, function ($player) {
            return $player['currentPlayer'] === true;
        });
        $exMembers = array_filter($playersStats, function ($player) {
            return $player['currentPlayer'] === false;
        });
        return [
            "activeMembers" => $activeMembers,
            "exMembers" => $exMembers
        ];
    }

    public function processGetStatsHistoriqueClanWar(array $playersStats, array $warsSelected): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'processGetStatsHistoriqueClanWar'.");
        $initWarStat = [
            "reelMaxFame" => 1,
            "reelMinFame" => PHP_INT_MAX,
            "reelMinBoatAttacks" => PHP_INT_MAX,
            "reelMaxBoatAttacks" => 1,
            "reelMinDecksUsed" => PHP_INT_MAX,
            "reelMaxDecksUsed" => 1,
            "players" => []
        ];

        $warsStat = ["all" => $initWarStat];
        foreach ($warsSelected as $key => $war) {
            $warsStat[$war] = $initWarStat;
        }

        $listWars = [];
        $playersDto = [];
        foreach ($playersStats as $playerKey => $stats) {
            foreach ($stats as $key => $value) {
                if (preg_match('/^(\d+)_(\d+)$/', $key, $matches)) {
                    if ($value["decksUsed"] > 0) {
                        $warsStat = $this->updateReelWarStat($warsStat, $key, $value);
                        $dataWar = array_merge($value, ["sessionId" => $key]);
                        $listWars[$playerKey][$key] = new War($dataWar);
                    }
                }
            }
            if (isset($listWars[$playerKey])) {
                $dataPlayer = array_merge(["warList" => $listWars[$playerKey]], ["tag" => $playerKey], ["name" => $stats["name"]], ["currentPlayer" => $stats["currentPlayer"]]);
                $playersDto[$playerKey] = new PlayerStatsHistoriqueClanWar($dataPlayer);
            }
        }

        $warsDto = [];
        foreach (self::TARGETS_STATS_WARS as $target) {
            $metric = PlayerMetric::from($target);
            $warsStat = $this->updateMedianWarStat($metric, $warsStat, $playersDto);
        }

        foreach ($warsStat as $key => $stat) {
            $data = array_merge($stat, ["sessionId" => $key]);
            $warsDto[$key] = new WarStatsHistoriqueClanWar($data);
        }
        $result = array_merge(["warsStats" => $warsDto], ["playersStats" =>  $playersDto]);
        return $result;
    }

    /**
     * @param PlayerMetric $metric La métrique à analyser
     * @param WarStatsHistoriqueClanWar> $warsStats
     * @param  PlayerStatsHistoriqueClanWar $playersStats
     * @return array<string, int>
     */
    private function updateMedianWarStat(PlayerMetric $metric, array $warsStat,  array $playersDto)
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'updateMedianWarStat'.");
        foreach ($warsStat as $warKey => $warStat) {
            if ($warKey !== "all") {
                $scores = [];
                foreach ($warStat["players"] as $playerKey) {
                    $scores[] = $metric->getValue($playersDto[$playerKey], $warKey);
                }
                $median = $this->calculateMedian($scores);
                $medianKey = "median" . ucfirst($metric->value);
                $warsStat[$warKey][$medianKey] = $median;
            }
        }
        return $warsStat;
    }

    private function calculateMedian(array $values): float
    {
        sort($values, SORT_NUMERIC);
        $total = count($values);
        $milieu = floor($total / 2);
        if ($total % 2 === 0) {
            return ($values[$milieu - 1] + $values[$milieu]) / 2;
        } else {
            return $values[$milieu];
        }
    }

    private function updateReelWarStat(array $warStat, string $key, array $data): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'updateReelWarStat'.");
        $targets = ["fame", "boatAttacks", "decksUsed"];
        foreach ($targets as $target) {
            if ($warStat[$key]["reelMax" . ucfirst($target)] < $data[$target]) {
                $warStat[$key]["reelMax" . ucfirst($target)] = $data[$target];
                if ($warStat["all"]["reelMax" . ucfirst($target)] < $data[$target]) {
                    $warStat["all"]["reelMax" . ucfirst($target)] = $data[$target];
                }
            }
            if ($warStat[$key]["reelMin" . ucfirst($target)] > $data[$target] && $data[$target] > 0) {
                $warStat[$key]["reelMin" . ucfirst($target)] = $data[$target];
                if ($warStat["all"]["reelMin" . ucfirst($target)] > $data[$target]) {
                    $warStat["all"]["reelMin" . ucfirst($target)] = $data[$target];
                }
            }
        }
        if (!in_array($data["tag"], $warStat[$key]["players"])) {
            $warStat[$key]["players"][] = $data["tag"];
        }
        return $warStat;
    }
}
