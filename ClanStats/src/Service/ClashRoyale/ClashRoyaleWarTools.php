<?php

namespace App\Service\ClashRoyale;

use Symfony\Component\Serializer\SerializerInterface;
use App\Dto\ClashRoyale\RiverRace\RiverRaceLog;
use App\Dto\ClashRoyale\Clan;
use Psr\Log\LoggerInterface;

use App\Dto\ClashRoyale\Analysis\PlayerStatsHistoriqueClanWar;
use App\Dto\ClashRoyale\Analysis\War;
use App\Dto\ClashRoyale\Analysis\WarStatsHistoriqueClanWar;

use App\Service\ClashRoyale\Analysis\AnalysisTools;

use App\Enum\PlayerMetric;

/**
 * Gestionnaire des opérations de filtrage et d'analyse des guerres de clan.
 *
 * Responsabilités :
 * - Filtrage des guerres par saison et clan spécifique
 * - Extraction et agrégation des statistiques de participants
 * - Calcul de métriques (totaux, moyennes, min/max réels, médianes)
 * - Transformation de données brutes en DTOs typés pour l'analyse
 *
 */
class ClashRoyaleWarTools
{
    private LoggerInterface $logger;
    private SerializerInterface $serializer;
    private AnalysisTools $analysisTools;
    private const TARGETS_STATS_WARS = [
        "fame",
        "boatAttacks",
        "decksUsed"
    ];

    /**
     * Initialise le gestionnaire avec dépendances pour sérialisation et calculs statistiques.
     *
     * @param LoggerInterface $logger Logger Symfony pour traçabilité des opérations
     * @param SerializerInterface $serializer Serializer Symfony pour transformation DTO → JSON → Array
     * @param AnalysisTools $analysisTools Service d'analyse pour calculs de médianes
     */
    public function __construct(LoggerInterface $logger, SerializerInterface $serializer, AnalysisTools $analysisTools)
    {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->analysisTools = $analysisTools;
        $this->logger->info("Initialisation de : class 'ClashRoyaleWarTools'.");
    }

    /**
     * Filtre les guerres selon une liste de saisons sélectionnées.
     *
     * @param array<int, string> $warsSelected Liste des identifiants de guerre au format "{seasonId}_{sectionIndex}"
     * @param array<int, RiverRaceLog> $riverRaceLog Historique complet des guerres de rivière
     *
     * @return array<int, RiverRaceLog> Guerres filtrées correspondant aux saisons demandées
     */
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

    /**
     * Extrait les guerres concernant un clan spécifique avec restructuration des données.
     *
     * Processus de transformation :
     * 1. Localise le clan dans chaque guerre (standings)
     * 2. Sérialise les données avec groupes de normalisation :
     *    - "riverRaceLogInfo" : Métadonnées de la guerre (saison, date)
     *    - "clanInfoFlat" : Stats du clan (fame, trophies)
     *    - "clanInfoDeep" : Infos détaillées du clan
     *    - "ajaxed" : Participants avec statistiques
     * 3. Reconstruit un RiverRaceLog contenant uniquement ce clan
     *
     * Résultat : Chaque guerre ne contient qu'un seul standing (celui du clan demandé)
     *
     * @param string $clanTag Tag du clan à extraire
     * @param array<int, RiverRaceLog> $riverRaceLog Historique complet avec tous les clans
     *
     * @return array<int, RiverRaceLog> Guerres filtrées et restructurées (1 standing par guerre)
     */
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

//TODO mise en Place d'un DTO pour "WarsPlayersStats"

    /**
     * Agrège les statistiques de tous les participants aux guerres avec distinction actifs/anciens.
     *
     * Calculs effectués par joueur :
     * - Totaux : totalWarsFame, totalWarsBoatAttacks, totalWarsDecksUsed
     * - Moyennes : averageWarsFame, averageWarsBoatAttacks, averageWarsDecksUsed
     * - Participation : totalWarsParticipated (nombre de guerres jouées)
     * - Statut : currentPlayer (true si membre actuel du clan)
     *
     * @param Clan $currentClan Clan actuel avec liste des membres pour déterminer le statut
     * @param array<int, RiverRaceLog> $riverRaceLog Historique complet des guerres à analyser
     *
     * @return array{activeMembers: array<string, array>, exMembers: array<string, array>} Statistiques agrégées séparées par statut
     */
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
            return $player["currentPlayer"] === true;
        });
        $exMembers = array_filter($playersStats, function ($player) {
            return $player["currentPlayer"] === false;
        });
        return [
            "activeMembers" => $activeMembers,
            "exMembers" => $exMembers
        ];
    }

    /**
     * Génère les statistiques complètes des guerres avec métriques agrégées et transformations DTO.
     *
     * @param array<string, array<string, mixed>> $playersStats Statistiques brutes des joueurs (structure de processGetWarsPlayersStats)
     * @param array<int, string> $warsSelected Liste des identifiants de guerre à analyser (format "{seasonId}_{sectionIndex}")
     *
     * @return array{warsStats: array<string, WarStatsHistoriqueClanWar>, playersStats: array<string, PlayerStatsHistoriqueClanWar>} Statistiques complètes transformées en DTOs
     */
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
            $warsStat = $this->updateAverageWarStat($metric, $warsStat, $playersDto);
        }

        foreach ($warsStat as $key => $stat) {
            $data = array_merge($stat, ["sessionId" => $key]);
            $warsDto[$key] = new WarStatsHistoriqueClanWar($data);
        }
        //return array_merge(["warsStats" => $warsDto], ["playersStats" =>  $playersDto]);
        return [
            "warsStats" => $warsDto,
            "playersStats" => $playersDto
        ];
    }

    /**
     * Calcule et injecte les moyennes d'une métrique spécifique pour chaque guerre.
     *
     * Algorithme :
     * 1. Pour chaque guerre (sauf "all")
     * 2. Extrait les scores de tous les joueurs pour la métrique
     * 3. Calcule la moyennes via AnalysisTools::calculateAverage()
     * 4. Stocke dans "average{Metric}" (ex: averageFame)
     *
     * @param PlayerMetric $metric Métrique à analyser
     * @param array<string, array<string, mixed>> $warsStat Statistiques des guerres à enrichir
     * @param array<string, PlayerStatsHistoriqueClanWar> $playersDto DTOs des joueurs avec historique de guerres
     *
     * @return array<string, array<string, mixed>> Statistiques enrichies avec moyennes calculées
     */
    private function updateAverageWarStat(PlayerMetric $metric, array $warsStat,  array $playersDto)
    {
        //$this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'updateAverageWarStat'.");
        foreach ($warsStat as $warKey => $warStat) {
            if ($warKey !== "all") {
                $scores = [];
                foreach ($warStat["players"] as $playerKey) {
                    $scores[] = $metric->getValue($playersDto[$playerKey], $warKey);
                }
                $average = $this->analysisTools->calculateAverage($scores);
                $averageKey = "average" . ucfirst($metric->value);
                $warsStat[$warKey][$averageKey] = $average;
            }
        }
        return $warsStat;
    }

    /**
     * Calcule et injecte les médianes d'une métrique spécifique pour chaque guerre.
     *
     * Algorithme :
     * 1. Pour chaque guerre (sauf "all")
     * 2. Extrait les scores de tous les joueurs pour la métrique
     * 3. Calcule la médiane via AnalysisTools::calculateMedian()
     * 4. Stocke dans "median{Metric}" (ex: medianFame)
     *
     * @param PlayerMetric $metric Métrique à analyser
     * @param array<string, array<string, mixed>> $warsStat Statistiques des guerres à enrichir
     * @param array<string, PlayerStatsHistoriqueClanWar> $playersDto DTOs des joueurs avec historique de guerres
     *
     * @return array<string, array<string, mixed>> Statistiques enrichies avec médianes calculées
     */
    private function updateMedianWarStat(PlayerMetric $metric, array $warsStat,  array $playersDto)
    {
        //$this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'updateMedianWarStat'.");
        foreach ($warsStat as $warKey => $warStat) {
            if ($warKey !== "all") {
                $scores = [];
                foreach ($warStat["players"] as $playerKey) {
                    $scores[] = $metric->getValue($playersDto[$playerKey], $warKey);
                }
                $median = $this->analysisTools->calculateMedian($scores);
                $medianKey = "median" . ucfirst($metric->value);
                $warsStat[$warKey][$medianKey] = $median;
            }
        }
        return $warsStat;
    }

    /**
     * Met à jour les min/max réels d'une guerre et de la vue globale ("all").
     *
     * @param array<string, array<string, mixed>> $warStat Statistiques des guerres (dont "all")
     * @param string $key Identifiant de la guerre (format "{seasonId}_{sectionIndex}")
     * @param array<string, mixed> $data Données du participant avec tag, fame, boatAttacks, decksUsed
     *
     * @return array<string, array<string, mixed>> Statistiques mises à jour
     */
    private function updateReelWarStat(array $warStat, string $key, array $data): array
    {
        //$this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'updateReelWarStat'.");
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
