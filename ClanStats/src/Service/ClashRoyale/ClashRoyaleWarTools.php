<?php

namespace App\Service\ClashRoyale;

use Symfony\Component\Serializer\SerializerInterface;
use App\Dto\ClashRoyale\RiverRace\RiverRaceLog;
use App\Dto\ClashRoyale\Clan;
use Psr\Log\LoggerInterface;

use App\Dto\ClashRoyale\Analysis\PlayerStatsHistoriqueClanWar;
use App\Dto\ClashRoyale\Analysis\War;
use App\Dto\ClashRoyale\Analysis\WarStatsHistoriqueClanWar;
use App\Dto\ClashRoyale\Analysis\PlayerWarsSummary;

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
    private const AGGREGATION_TYPES = ["median", "average"];

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

    /**
     * @param Clan $currentClan
     * @param array<int, RiverRaceLog> $riverRaceLog
     *
     * @return array{activeMembers: array<string, PlayerWarsSummary>, exMembers: array<string, PlayerWarsSummary>}
     */
    public function processGetWarsPlayersStats(Clan $currentClan, array $riverRaceLog): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'processGetWarsPlayersStats'.");
        $playerTags = array_map(fn($player) => $player->getTag(), $currentClan->getMemberList());
        $participants = $this->collectParticipants($riverRaceLog);
        $playersSummaries = [];
        foreach ($participants as $tag => $wars) {
            $playersSummaries[$tag] = $this->buildPlayerSummary($tag, $wars, $playerTags);
        }
        return $this->separateByMemberStatus($playersSummaries);
    }

    /**
     * Collecte toutes les participations groupées par joueur.
     *
     * @param array<int, RiverRaceLog> $riverRaceLog
     * @return array<string, array<string, Participant>>
     */
    private function collectParticipants(array $riverRaceLog): array
    {
        //$this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'collectParticipants'.");
        $participants = [];
        foreach ($riverRaceLog as $riverRace) {
            $warKey = $riverRace->getSeasonId() . "_" . $riverRace->getSectionIndex();
            foreach ($riverRace->getClans() as $clan) {
                foreach ($clan->getParticipants() as $participant) {
                    if ($participant->getFame() === 0 && $participant->getBoatAttacks() === 0 && $participant->getDecksUsed() === 0) {
                        continue;
                    }
                    $participants[$participant->getTag()][$warKey] = $participant;
                }
            }
        }
        return $participants;
    }

    /**
     * Construit un PlayerWarsSummary à partir des participations d'un joueur.
     *
     * @param string $tag
     * @param array<string, Participant> $wars
     * @param array<string> $playerTags
     * @return PlayerWarsSummary
     */
    private function buildPlayerSummary(string $tag, array $wars, array $playerTags): PlayerWarsSummary
    {
        //$this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'buildPlayerSummary'.");
        $totals = $this->calculateTotals($wars);
        $warList = $this->buildWarList($wars);
        $warsCount = count($wars);
        return new PlayerWarsSummary([
            "tag" => $tag,
            "name" => $this->extractPlayerName($wars),
            "currentPlayer" => in_array($tag, $playerTags, true),
            "totalWarsParticipated" => $warsCount,
            "totalWarsFame" => $totals["fame"],
            "totalWarsBoatAttacks" => $totals["boatAttacks"],
            "totalWarsDecksUsed" => $totals["decksUsed"],
            "averageWarsFame" => $warsCount > 0 ? round($totals["fame"] / $warsCount, 4) : 0,
            "averageWarsBoatAttacks" => $warsCount > 0 ? round($totals["boatAttacks"] / $warsCount, 4) : 0,
            "averageWarsDecksUsed" => $warsCount > 0 ? round($totals["decksUsed"] / $warsCount, 4) : 0,
            "warList" => $warList,
        ]);
    }

    /**
     * Construit la liste des guerres pour le DTO War.
     *
     * @param array<string, Participant> $wars
     * @return array<string, array{sessionId: string, fame: int, boatAttacks: int, decksUsed: int}>
     */
    private function buildWarList(array $wars): array
    {
        $warList = [];
        foreach ($wars as $warKey => $participant) {
            $warList[$warKey] = [
                "sessionId" => $warKey,
                "fame" => $participant->getFame(),
                "boatAttacks" => $participant->getBoatAttacks(),
                "decksUsed" => $participant->getDecksUsed(),
            ];
        }
        return $warList;
    }

    /**
     * Calcule les totaux des stats de guerre.
     *
     * @param array<string, Participant> $wars
     * @return array{fame: int, boatAttacks: int, decksUsed: int}
     */
    private function calculateTotals(array $wars): array
    {
        //$this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'calculateTotals'.");
        $totals = ["fame" => 0, "boatAttacks" => 0, "decksUsed" => 0];
        foreach ($wars as $participant) {
            $totals["fame"] += $participant->getFame();
            $totals["boatAttacks"] += $participant->getBoatAttacks();
            $totals["decksUsed"] += $participant->getDecksUsed();
        }
        return $totals;
    }

    /**
     * Extrait le nom du joueur depuis ses participations.
     *
     * @param array<string, Participant> $wars
     * @return string
     */
    private function extractPlayerName(array $wars): string
    {
        $firstParticipation = reset($wars);
        return $firstParticipation ? $firstParticipation->getName() : "";
    }

    /**
     * Sépare les joueurs par statut membre actif/ancien.
     *
     * @param array<string, PlayerWarsSummary> $playersSummaries
     * @return array{activeMembers: array<string, PlayerWarsSummary>, exMembers: array<string, PlayerWarsSummary>}
     */
    private function separateByMemberStatus(array $playersSummaries): array
    {
        //$this->logger->info("Lancement de : class 'ClashRoyaleWarTools' function 'separateByMemberStatus'.");
        $activeMembers = [];
        $exMembers = [];
        foreach ($playersSummaries as $tag => $summary) {
            if ($summary->getCurrentPlayer()) {
                $activeMembers[$tag] = $summary->toArray();
            } else {
                $exMembers[$tag] = $summary->toArray();
            }
        }
        return [
            "activeMembers" => $activeMembers,
            "exMembers" => $exMembers,
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
            "players" => [],
            "medianFame" => 0,
            "medianBoatAttacks" => 0,
            "medianDecksUsed" => 0,
            "medianContinuity" => 0,
            "averageFame" => 0,
            "averageBoatAttacks" => 0,
            "averageDecksUsed" => 0,
            "averageContinuity" => 0,
        ];

        $warsStat = ["all" => $initWarStat];
        foreach ($warsSelected as $key => $war) {
            $warsStat[$war] = $initWarStat;
        }
        $listWars = [];
        $playersDto = [];
        foreach ($playersStats as $playerKey => $stats) {
            $wars = $stats["wars"] ?? [];
            foreach ($wars as $key => $value) {
                if (preg_match('/^(\d+)_(\d+)$/', $key, $matches)) {
                    if ($value["decksUsed"] > 0) {
                        $valueTag = array_merge($value, ["tag" => $stats["tag"]]);
                        $warsStat = $this->updateReelWarStat($warsStat, $key, $valueTag);
                        $dataWar = array_merge($value, ["sessionId" => $key]);
                        $listWars[$playerKey][$key] = new War($dataWar);
                    }
                }
            }
            if (isset($listWars[$playerKey])) {
                $dataPlayer = array_merge(["warList" => $listWars[$playerKey]], ["tag" => $stats["tag"] ?? $playerKey], ["name" => $stats["name"]], ["currentPlayer" => $stats["currentPlayer"]]);
                $playersDto[$playerKey] = new PlayerStatsHistoriqueClanWar($dataPlayer);
            }
        }
        $warsDto = [];
        foreach (self::TARGETS_STATS_WARS as $target) {
            $metric = PlayerMetric::from($target);
            foreach (self::AGGREGATION_TYPES as $type) {
                $warsStat = $this->updateAggregatedWarStat($metric, $warsStat, $playersDto, $type);
            }
        }
        foreach ($warsStat as $key => $stat) {
            $data = array_merge($stat, ["sessionId" => $key]);
            $warsDto[$key] = new WarStatsHistoriqueClanWar($data);
        }
        return [
            "warsStats" => $warsDto,
            "playersStats" => $playersDto
        ];
    }

    /**
     * Calcule et injecte une statistique agrégée pour chaque guerre.
     *
     * @param PlayerMetric $metric Métrique à analyser
     * @param array<string, array<string, mixed>> $warsStat Statistiques des guerres
     * @param array<string, PlayerStatsHistoriqueClanWar> $playersDto DTOs des joueurs
     * @param string $aggregationType Type d'agrégation ("average" ou "median")
     *
     * @return array<string, array<string, mixed>> Statistiques enrichies
     */
    private function updateAggregatedWarStat(PlayerMetric $metric, array $warsStat, array $playersDto, string $aggregationType): array
    {
        foreach ($warsStat as $warKey => $warStat) {
            if ($warKey === "all") {
                continue;
            }
            $scores = [];
            foreach ($warStat["players"] as $playerKey) {
                $scores[] = $metric->getValue($playersDto[$playerKey], $warKey);
            }
            $value = match ($aggregationType) {
                "average" => $this->analysisTools->calculateAverage($scores),
                "median" => $this->analysisTools->calculateMedian($scores),
                default => throw new \InvalidArgumentException("Type inconnu: $aggregationType")
            };
            $statKey = $aggregationType . ucfirst($metric->value);
            $warsStat[$warKey][$statKey] = $value;
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
        foreach (self::TARGETS_STATS_WARS as $target) {
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
