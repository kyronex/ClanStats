<?php

// src/Service/ClanStatsService.php
namespace App\Service;

use Psr\Log\LoggerInterface;
use App\Service\ClashRoyaleApi;
use App\Service\ClashRoyale\ClashRoyaleResponseProcessor;
use App\Service\ClashRoyale\ClashRoyaleWarTools;

class ClanStatsService
{
    private ClashRoyaleApi $apiClashRoyale;
    private ClashRoyaleResponseProcessor $clashRoyaleRespProcess;
    private ClashRoyaleWarTools $clashRoyaleWarTools;
    private LoggerInterface $logger;

    public function __construct(ClashRoyaleApi $apiClashRoyale, ClashRoyaleResponseProcessor $clashRoyaleRespProcess, ClashRoyaleWarTools $clashRoyaleWarTools, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->apiClashRoyale = $apiClashRoyale;
        $this->clashRoyaleRespProcess = $clashRoyaleRespProcess;
        $this->clashRoyaleWarTools = $clashRoyaleWarTools;
        $this->logger->info("Initialisation de : 'class ClanStatsService'.");
    }

    public function getSearchClanName(array $params): array
    {
        $this->logger->info("Lancement de : 'function getSearchClanName'.");
        $apiResponse = $this->apiClashRoyale->searchClans($params);
        return $this->clashRoyaleRespProcess->processSearchClansResponse($apiResponse);
    }

    public function getRiverRaceLog(string $tag): array
    {
        $this->logger->info("Lancement de : 'function getRiverRaceLog'.");
        $apiResponse = $this->apiClashRoyale->getRiverRaceLog($tag);
        $riverRaceLogs = $this->clashRoyaleRespProcess->processGetRiverRaceLogResponse($apiResponse);
        
        $apiResponse = $this->apiClashRoyale->getClan($tag);
        $clan = $this->clashRoyaleRespProcess->processGetClanResponse($apiResponse);
        return [
            "riverRaceLogs" => $riverRaceLogs,
            "clan" => $clan
        ];
    }

    /**
     * Récupère l'historique des guerres de clan avec les statistiques des joueurs
     */
    public function getHistoriqueClanWar(string $clanTag , array $warsSelected): array
    {
        $this->logger->info("Lancement de : 'function getHistoriqueClanWar'.");

        $apiResponse = $this->apiClashRoyale->getRiverRaceLog($clanTag);
        $riverRaceLogs = $this->clashRoyaleRespProcess->processGetRiverRaceLogResponse($apiResponse);
        $warsSelected = $this->clashRoyaleWarTools->processGetWarsSelected($warsSelected, $riverRaceLogs);
        $warsSelected = $this->clashRoyaleWarTools->processGetWarsByClan($clanTag, $warsSelected);
        
        $apiResponse = $this->apiClashRoyale->getClan($clanTag);
        $currentClan = $this->clashRoyaleRespProcess->processGetClanResponse($apiResponse);
        $warsPlayersStats = $this->clashRoyaleWarTools->processGetWarsPlayersStats($currentClan, $warsSelected);
        return $warsPlayersStats ;
    }
}
