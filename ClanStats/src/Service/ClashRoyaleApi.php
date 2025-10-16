<?php

// src/Service/ClashRoyaleApi.php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use Psr\Log\LoggerInterface;

class ClashRoyaleApi
{
    private $parameterBag;
    private string  $apiKey;
    private string  $baseUrl;
    private ?string  $finalUrl;
    private HttpClientInterface $client;
    private LoggerInterface $logger;

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

    public function getPlayer(string $tag): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleApi' function 'getPlayer'.");
        $options = $this->setupOptionApi();
        $url = $this->baseUrl . "/players/" . urlencode($tag);
        return $this->callApi($url, $options);
    }

    public function getClan(string $tag): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleApi' function 'getClan'.");
        $options = $this->setupOptionApi();
        $url = $this->baseUrl . "/clans/" . urlencode($tag);
        return $this->callApi($url, $options);
    }

    public function getClanMembers(string $tag): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleApi' function 'getClanMembers'.");
        $options = $this->setupOptionApi();
        $url = $this->baseUrl . "/clans/" . urlencode($tag) . "/members";
        return $this->callApi($url, $options);
    }

    public function getRiverRaceLog(string $tag): array
    {
        $this->logger->info("Lancement de : class 'ClashRoyaleApi' function 'getRiverRaceLog'.");
        $options = $this->setupOptionApi();
        $url = $this->baseUrl . "/clans/" . urlencode($tag) . "/riverracelog";
        return $this->callApi($url, $options);
    }

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
