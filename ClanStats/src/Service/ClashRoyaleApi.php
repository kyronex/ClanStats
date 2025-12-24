<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Psr\Log\LoggerInterface;

/**
 * Client HTTP pour l'API officielle Clash Royale.
 *
 * Gère l'authentification, la construction des requêtes et la récupération
 * des données brutes de clans, joueurs et guerres de rivière.
 */
class ClashRoyaleApi
{
    private $parameterBag;
    private string  $apiKey;
    private string  $baseUrl;
    private ?string  $finalUrl;
    private HttpClientInterface $client;
    private LoggerInterface $logger;

    /**
     * Initialise le client API avec les paramètres de configuration.
     *
     * @param string $apiKey Clé d'API Clash Royale officielle (Bearer token)
     * @param string $baseUrl URL de base de l'API (ex: https://api.clashroyale.com/v1)
     * @param HttpClientInterface $client Client HTTP Symfony pour les requêtes
     * @param ParameterBagInterface $parameterBag Conteneur de paramètres de configuration
     * @param LoggerInterface $logger Logger Symfony pour traçabilité
     */
    public function __construct(string $apiKey, string $baseUrl, HttpClientInterface $client, ParameterBagInterface $parameterBag, LoggerInterface $logger)
    {
        $this->parameterBag = $parameterBag;
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
        $this->finalUrl = null;
        $this->logger = $logger;
        $this->logger->info("Initialisation de : 'class ClashRoyaleApi'.");
    }

    /**
     * Recherche des clans par critères multiples.
     *
     * @param array<string, mixed> $params Critères de recherche avec clés :
     *
     * @return array<string, mixed> Réponse JSON décodée de l'API
     */
    public function searchClans($params): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleApi' function 'searchClans'.");
        $query = ["name" => $params["name"]];
        if (isset($params["minMembers"])) {
            if ($params["minMembers"] >= $this->parameterBag->get("clash_royale.clan.min_member")) {
                $query["minMembers"] = $params["minMembers"];
            }
        }
        if (isset($params["maxMembers"])) {
            if ($params["maxMembers"] <= $this->parameterBag->get("clash_royale.clan.max_member")) {
                $query["maxMembers"] = $params["maxMembers"];
            }
        }
        if (isset($params["minScore"])) {
            if ($params["minScore"] >= $this->parameterBag->get("clash_royale.clan.min_score")) {
                $query["minScore"] = $params["minScore"];
            }
        }
        $options = $this->setupOptionApi();
        $options["query"] = $query;
        $url = $this->baseUrl . "/clans";
        return $this->callApi($url, $options);
    }

    /**
     * Récupère les statistiques complètes d'un joueur.
     *
     * @param string $tag Identifiant unique du joueur
     *
     * @return array<string, mixed> Données du joueur
     */
    public function getPlayer(string $tag): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleApi' function 'getPlayer'.");
        $options = $this->setupOptionApi();
        $url = $this->baseUrl . "/players/" . urlencode($tag);
        return $this->callApi($url, $options);
    }

    /**
     * Récupère les informations détaillées d'un clan.
     *
     * @param string $tag Identifiant unique du clan
     *
     * @return array<string, mixed> Données du clan
     */
    public function getClan(string $tag): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleApi' function 'getClan'.");
        $options = $this->setupOptionApi();
        $url = $this->baseUrl . "/clans/" . urlencode($tag);
        return $this->callApi($url, $options);
    }

    /**
     * Récupère uniquement la liste des membres d'un clan.
     *
     * @param string $tag Identifiant unique du clan
     *
     * @return array<string, mixed> Réponse API
     */
    public function getClanMembers(string $tag): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleApi' function 'getClanMembers'.");
        $options = $this->setupOptionApi();
        $url = $this->baseUrl . "/clans/" . urlencode($tag) . "/members";
        return $this->callApi($url, $options);
    }

    /**
     * Récupère l'historique des guerres de rivière d'un clan.
     *
     * Retourne les 10 dernières saisons de guerre de rivière avec les statistiques de participation de tous les clans participants.
     *
     * @param string $tag Identifiant unique du clan
     *
     * @return array<string, mixed> Réponse API
     */
    public function getRiverRaceLog(string $tag): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleApi' function 'getRiverRaceLog'.");
        $options = $this->setupOptionApi();
        $url = $this->baseUrl . "/clans/" . urlencode($tag) . "/riverracelog";
        return $this->callApi($url, $options);
    }

    /**
     * Prépare les options HTTP pour les appels API.
     *
     * Configure l'authentification Bearer et le format de réponse JSON.
     *
     * @return array<string, array<string, string>> Options HTTP avec structure :
     *         - headers : array<string, string> En-têtes HTTP avec :
     *           - Authorization : string Token Bearer pour authentification
     *           - Accept : string Type MIME accepté (application/json)
     */
    public function setupOptionApi(): array
    {
        $options = [
            "headers" => [
                "Authorization" => "Bearer " . $this->apiKey,
                "Accept" => "application/json"
            ]
        ];
        return $options;
    }

    /**
     * Exécute un appel HTTP GET vers l'API Clash Royale.
     *
     * Gère automatiquement la désérialisation JSON et la validation du code de statut HTTP (doit être 200 OK).
     *
     * @param string $url URL complète de l'endpoint API
     * @param array<string, mixed> $options Options HTTP (headers, query, etc.)
     *
     * @return array<string, mixed> Réponse JSON décodée en tableau associatif
     */
    public function callApi(string $url, array $options): array
    {
        $response = $this->client->request("GET", $url, $options);
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $errorContent = $response->getContent(false);
            $this->logger->error("Erreur API: " . $errorContent);
            throw new \Exception("Erreur : " . $response->getStatusCode());
        }
        return json_decode($response->getContent(), true);
    }
}
