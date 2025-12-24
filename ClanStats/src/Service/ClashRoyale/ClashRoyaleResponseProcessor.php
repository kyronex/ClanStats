<?php

namespace App\Service\ClashRoyale;

use App\Dto\ClashRoyale\Search\Clan as SearchClan;
use App\Dto\ClashRoyale\Clan;
use App\Dto\ClashRoyale\Member;
use App\Dto\ClashRoyale\RiverRace\RiverRaceLog;
use Psr\Log\LoggerInterface;

//TODO finaliser processGetPlayerResponse

/**
 * Transformateur de réponses brutes de l'API Clash Royale en objets DTO typés.
 *
 * Responsabilités :
 * - Désérialisation des réponses JSON de l'API officielle
 * - Instanciation des DTOs correspondants (Clan, Member, RiverRaceLog, etc.)
 * - Validation implicite via constructeurs de DTOs
 * - Gestion des collections (items) avec typage fort
 *
 * Architecture :
 * - Une méthode par endpoint API
 * - Retours typés (DTO unique ou array<DTO>)
 * - Logging systématique des transformations
 */
class ClashRoyaleResponseProcessor
{
    private LoggerInterface $logger;

    /**
     * Initialise le processeur avec un logger pour traçabilité.
     *
     * @param LoggerInterface $logger Logger Symfony pour suivi des transformations
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->logger->info("Initialisation de : class 'ClashRoyaleResponseProcessor'.");
    }

    /**
     * Transforme la réponse de recherche de clans en collection de DTOs SearchClan.
     *
     * @param array<string, mixed> $apiResponse Réponse brute de l'endpoint /clans (search)
     *
     * @return array<int, SearchClan> Collection de clans trouvés, tableau vide si aucun résultat
     */
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

    /**
     * Transforme la réponse détaillée d'un clan en DTO Clan.
     *
     * @param array<string, mixed> $apiResponse Réponse brute de l'endpoint /clans/{tag}
     *
     * @return Clan Objet DTO représentant le clan avec toutes ses propriétés
     */
    public function processGetClanResponse(array $apiResponse): Clan
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleResponseProcessor' function 'processGetClanResponse'.");
        $clan = [];
        $clan = new Clan($apiResponse);
        return $clan;
    }

    /**
     * Transforme la liste des membres d'un clan en collection de DTOs Member.
     *
     * @param array<string, mixed> $apiResponse Réponse brute de l'endpoint /clans/{tag}/members
     *
     * @return array<int, Member> Collection de membres avec statistiques individuelles, tableau vide si clan vide
     */
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

    /**
     * Transforme l'historique des guerres de rivière en collection de DTOs RiverRaceLog.
     *
     * Chaque RiverRaceLog contient :
     * - Identifiants de saison (seasonId, sectionIndex)
     * - Classements des clans participants
     * - Statistiques détaillées de chaque participant
     *
     * @param array<string, mixed> $apiResponse Réponse brute de l'endpoint /clans/{tag}/riverracelog
     *
     * @return array<int, RiverRaceLog> Collection des saisons de guerre, tableau vide si aucun historique
     */
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
