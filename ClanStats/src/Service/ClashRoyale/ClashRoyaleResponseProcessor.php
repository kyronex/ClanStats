<?php

namespace App\Service\ClashRoyale;

use App\Dto\ClashRoyale\Search\Clan as SearchClan;
use App\Dto\ClashRoyale\Clan;
use App\Dto\ClashRoyale\Member;
use App\Dto\ClashRoyale\RiverRace\RiverRaceLog;
use Psr\Log\LoggerInterface;

//TODO finaliser processGetPlayerResponse

class ClashRoyaleResponseProcessor
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logger->info("Initialisation de : class 'ClashRoyaleResponseProcessor'.");
    }

    public function processSearchClansResponse(array $apiResponse): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleResponseProcessor' function 'processSearchClansResponse'.");
        $clans = [];
        if (isset($apiResponse["items"]) && is_array($apiResponse["items"])) {
            foreach ($apiResponse["items"] as $clanData) {
                $clans[] = new SearchClan($clanData);
            }
        }
        return $clans;
    }

    public function processGetClanResponse(array $apiResponse): Clan
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleResponseProcessor' function 'processGetClanResponse'.");
        $clan = [];
        $clan = new Clan($apiResponse);
        return $clan;
    }

    public function processGetClanMembersResponse(array $apiResponse): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleResponseProcessor' function 'processGetClanMembersResponse'.");
        $clanMembers = [];
        if (isset($apiResponse["items"]) && is_array($apiResponse["items"])) {
            foreach ($apiResponse["items"] as $ClanMemberData) {
                $clanMembers[] = new Member($ClanMemberData);
            }
        }
        return $clanMembers;
    }

    public function processGetRiverRaceLogResponse(array $apiResponse): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleResponseProcessor' function 'processGetRiverRaceLogResponse'.");
        //$this->logger->info(json_encode($apiResponse));
        $riverRaceLog = [];
        if (isset($apiResponse["items"]) && is_array($apiResponse["items"])) {
            foreach ($apiResponse["items"] as $riverRaceLogData) {
                $riverRaceLog[] = new RiverRaceLog($riverRaceLogData);
            }
        }
        return $riverRaceLog;
    }
}
