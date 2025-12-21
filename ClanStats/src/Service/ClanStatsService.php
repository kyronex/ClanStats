<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use App\Service\ClanStatsTools;
use App\Service\ClashRoyaleApi;
use App\Service\ClashRoyale\ClashRoyaleResponseProcessor;
use App\Service\ClashRoyale\ClashRoyaleWarTools;
use App\Service\ClashRoyale\Analysis\AnalysisPlayersStats;

use App\Dto\ClashRoyale\Clan;

class ClanStatsService
{
    private ClanStatsTools $clanStatsTools;
    private ClashRoyaleApi $apiClashRoyale;
    private ClashRoyaleResponseProcessor $clashRoyaleRespProcess;
    private ClashRoyaleWarTools $clashRoyaleWarTools;
    private AnalysisPlayersStats $playersStatsAnalysis;
    private LoggerInterface $logger;

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

    public function getSearchClanName(array $params): array
    {
        $this->logger->info("Lancement de : class 'ClanStatsService' function 'getSearchClanName'.");
        $apiResponse = $this->apiClashRoyale->searchClans($params);
        return $this->clashRoyaleRespProcess->processSearchClansResponse($apiResponse);
    }

    public function getClan(string $tag): Clan
    {
        $this->logger->info("Lancement de : class 'ClanStatsService' function 'getClan'.");
        $apiResponse = $this->apiClashRoyale->getClan($tag);
        return $this->clashRoyaleRespProcess->processGetClanResponse($apiResponse);
    }

    public function getRiverRaceLog(string $tag): array
    {
        $this->logger->info("Lancement de : class 'ClanStatsService' function 'getRiverRaceLog'.");
        $apiResponse = $this->apiClashRoyale->getRiverRaceLog($tag);
        return $this->clashRoyaleRespProcess->processGetRiverRaceLogResponse($apiResponse);
    }

    /**
     * Récupère l'historique des guerres de clan avec les statistiques des joueurs
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
     * Compare l'historique des guerres de clan avec les statistiques des joueurs
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
