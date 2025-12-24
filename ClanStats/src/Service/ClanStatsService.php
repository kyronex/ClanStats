<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use App\Service\ClanStatsTools;
use App\Service\ClashRoyaleApi;
use App\Service\ClashRoyale\ClashRoyaleResponseProcessor;
use App\Service\ClashRoyale\ClashRoyaleWarTools;
use App\Service\ClashRoyale\Analysis\AnalysisPlayersStats;

use App\Dto\ClashRoyale\Clan;

/**
 * Service orchestrateur pour les opérations d'analyse de clan Clash Royale.
 *
 * Point d'entrée principal coordonnant :
 * - Récupération de données via API officielle
 * - Transformation en DTOs typés
 * - Filtrage et agrégation des guerres de clan
 * - Analyse statistique des performances joueurs
 * - Gestion du stockage temporaire asynchrone
 *
 * Architecture en couches :
 * 1. **API Layer** : ClashRoyaleApi → Données brutes
 * 2. **Processing Layer** : ClashRoyaleResponseProcessor → DTOs
 * 3. **Analysis Layer** : ClashRoyaleWarTools + AnalysisPlayersStats → Métriques
 * 4. **Storage Layer** : ClanStatsTools → Persistance temporaire
 */
class ClanStatsService
{
    private ClanStatsTools $clanStatsTools;
    private ClashRoyaleApi $apiClashRoyale;
    private ClashRoyaleResponseProcessor $clashRoyaleRespProcess;
    private ClashRoyaleWarTools $clashRoyaleWarTools;
    private AnalysisPlayersStats $playersStatsAnalysis;
    private LoggerInterface $logger;

    /**
     * Initialise le service avec toutes ses dépendances.
     *
     * @param AnalysisPlayersStats $playersStatsAnalysis Service d'analyse des performances joueurs
     * @param ClanStatsTools $clanStatsTools Utilitaires de gestion du stockage temporaire
     * @param ClashRoyaleApi $apiClashRoyale Client API Clash Royale officielle
     * @param ClashRoyaleResponseProcessor $clashRoyaleRespProcess Transformateur de réponses API en DTOs
     * @param ClashRoyaleWarTools $clashRoyaleWarTools Outils de traitement des guerres de clan
     * @param LoggerInterface $logger Logger Symfony pour traçabilité
     */
    public function __construct(AnalysisPlayersStats $playersStatsAnalysis, ClanStatsTools $clanStatsTools, ClashRoyaleApi $apiClashRoyale, ClashRoyaleResponseProcessor $clashRoyaleRespProcess, ClashRoyaleWarTools $clashRoyaleWarTools, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->clanStatsTools = $clanStatsTools;
        $this->apiClashRoyale = $apiClashRoyale;
        $this->clashRoyaleRespProcess = $clashRoyaleRespProcess;
        $this->clashRoyaleWarTools = $clashRoyaleWarTools;
        $this->playersStatsAnalysis = $playersStatsAnalysis;
        $this->logger->info("Initialisation de : class 'ClanStatsService'.");
    }

    /**
     * Recherche des clans par critères multiples.
     *
     * @param array<string, mixed> $params Critères de recherche
     *
     * @return array<int, SearchClan> Liste de DTOs SearchClan
     */
    public function getSearchClanName(array $params): array
    {
        $this->logger->info("Lancement de : class 'ClanStatsService' function 'getSearchClanName'.");
        $apiResponse = $this->apiClashRoyale->searchClans($params);
        return $this->clashRoyaleRespProcess->processSearchClansResponse($apiResponse);
    }

    /**
     * Récupère les informations détaillées d'un clan spécifique.
     *
     * @param string $tag Identifiant unique du clan
     *
     * @return Clan DTO complet
     */
    public function getClan(string $tag): Clan
    {
        $this->logger->info("Lancement de : class 'ClanStatsService' function 'getClan'.");
        $apiResponse = $this->apiClashRoyale->getClan($tag);
        return $this->clashRoyaleRespProcess->processGetClanResponse($apiResponse);
    }

    /**
     * Récupère l'historique complet des guerres de rivière d'un clan.
     *
     * @param string $tag Identifiant unique du clan
     *
     * @return array<int, RiverRaceLog> Liste chronologique des 10 dernières guerres
     */
    public function getRiverRaceLog(string $tag): array
    {
        $this->logger->info("Lancement de : class 'ClanStatsService' function 'getRiverRaceLog'.");
        $apiResponse = $this->apiClashRoyale->getRiverRaceLog($tag);
        return $this->clashRoyaleRespProcess->processGetRiverRaceLogResponse($apiResponse);
    }

    /**
     * Récupère et structure l'historique des guerres avec statistiques agrégées des joueurs.
     *
     * Pipeline de traitement :
     * 1. **Récupération historique** : getRiverRaceLog() → 10 dernières guerres
     * 2. **Filtrage guerres** : processGetWarsSelected() → Garde uniquement les guerres demandées
     * 3. **Extraction clan** : processGetWarsByClan() → Isole les données du clan cible
     * 4. **Récupération membres** : getClan() → Liste des membres actuels
     * 5. **Agrégation stats** : processGetWarsPlayersStats() → Calcul totaux/moyennes/participation
     *
     * @param string $clanTag Identifiant unique du clan
     * @param array<int, string> $warsSelected Liste des identifiants de guerre à analyser (format: "{seasonId}_{sectionIndex}")
     *
     * @return array{
     *     warsSelected: array<int, RiverRaceLog>,
     *     activeMembers: array<string, array<string, mixed>>,
     *     exMembers: array<string, array<string, mixed>>,
     *     clanInfo: array<string, mixed>
     * } Historique structuré avec séparation membres actifs/anciens
     */
    public function getHistoriqueClanWar(string $clanTag, array $warsSelected): array
    {
        $this->logger->info("Lancement de : class 'ClanStatsService' function 'getHistoriqueClanWar'.");

        $riverRaceLogs = $this->getRiverRaceLog($clanTag);
        $warsSelected = $this->clashRoyaleWarTools->processGetWarsSelected($warsSelected, $riverRaceLogs);
        $warsSelected = $this->clashRoyaleWarTools->processGetWarsByClan($clanTag, $warsSelected);

        $currentClan = $this->getClan($clanTag);
        $warsPlayersStats = $this->clashRoyaleWarTools->processGetWarsPlayersStats($currentClan, $warsSelected);
        return $warsPlayersStats;
    }

    /**
     * Analyse complète des guerres avec scoring et classement des joueurs.
     *
     * @param string $taskId Identifiant de la tâche asynchrone contenant les données préparées par getHistoriqueClanWar()
     *
     * @return array{
     *     warsAnalysis: array<string, WarStatsHistoriqueClanWar>,
     *     playersAnalysis: array<string, PlayerStatsHistoriqueClanWar>
     * } Analyse complète avec scoring et classement, ou tableau vide si tâche introuvable
     */
    public function getStatsHistoriqueClanWar(string $taskId)
    {
        $this->logger->info("Lancement de : class 'ClanStatsService' function 'getStatsHistoriqueClanWar'.");
        $taskData = $this->clanStatsTools->loadTaskData($taskId);
        if (!$taskData) {
            return [];
        }
        $this->clanStatsTools->updateTaskData($taskId, [
            "status" => "processing",
            "processing_at" => time()
        ]);
        $playersStats = array_merge($taskData["data"]["activeMembers"], $taskData["data"]["exMembers"]);
        $statsHistoriqueClanWar = $this->clashRoyaleWarTools->processGetStatsHistoriqueClanWar($playersStats, $taskData["data"]["warsSelected"]);
        return $this->playersStatsAnalysis->getPlayersAnalysisStats($statsHistoriqueClanWar["warsStats"], $statsHistoriqueClanWar["playersStats"]);
    }
}
