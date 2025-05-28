<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

use App\Form\ClanNameType;
use App\Service\ClanStatsService;

final class ClanStatsController extends AbstractController
{
    private LoggerInterface $logger;
    private ClanStatsService $clanStatsService;
    private SerializerInterface $serializer;

    public function __construct(ClanStatsService $clanStatsService, LoggerInterface $logger, SerializerInterface $serializer)
    {
        $this->logger = $logger;
        $this->clanStatsService = $clanStatsService;
        $this->serializer = $serializer;
    }

    #[Route("/clanstats", name: "app_clan_stats")]
    public function index(): Response
    {
        $form = $this->createForm(ClanNameType::class);
        return $this->render("clan_stats/index.html.twig", [
            "form" => $form->createView(),
        ]);
    }

    #[Route("/clanstats/search", name: "app_clan_stats_search", methods: ["POST"])]
    public function searchClanName(Request $request): JsonResponse
    {
        $form = $this->createForm(ClanNameType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->logger->info("function 'searchClanName' pour '" . $data["nomClan"] . "'.");
            $params = [
                "name" => $data["nomClan"],
                "minMembers" => $data["minMembers"],
                "maxMembers" => $data["maxMembers"],
                "minScore" => $data["minScore"]
            ];

            $clans = $this->clanStatsService->getSearchClanName($params);

            $serializedClans = $this->serializer->serialize($clans, 'json', ['groups' => 'ajaxed']);
            $htmlSearchClans = $this->renderView("clan_stats/search-clan-response.html.twig", [
                "clans" => $clans
            ]);
            return new JsonResponse([
                "success" => true,
                "clans" => $serializedClans,
                "htmlSearchClans" => $htmlSearchClans
            ], 200);
        }
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $fieldName = $error->getOrigin()->getName();
            $message = $error->getMessage();
            $errors[$fieldName] = $message;
        }
        return new JsonResponse([
            "success" => false,
            "errors" => $errors,
            "message" => "Une erreur s'est produite"
        ], 400);
    }

    #[Route("/clanstats/riverRaceLog", name: "app_clan_stats_riverRaceLog", methods: ["POST"])]
    public function riverRaceLog(Request $request): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $tag = json_decode($request->getContent(), true) ?? null;
            $this->logger->info("function 'riverRaceLog' pour '" . $tag . "'.");
            $result = $this->clanStatsService->getRiverRaceLog($tag);
            $htmlRiverRaceLogs = $this->renderView("clan_stats/river-race-log-response.html.twig", [
                "riverRaceLogs" => $result["riverRaceLogs"],
                "tag" => $tag
            ]);
            $htmlClan = $this->renderView("clan_stats/clan-info-response.html.twig", [
                "clan" => $result["clan"]
            ]);
            return new JsonResponse([
                "success" => true,
                "htmlClan" => $htmlClan,
                "htmlRiverRaceLogs" => $htmlRiverRaceLogs
            ]);
        }
        return new JsonResponse([
            "success" => false,
            "message" => "Une erreur s'est produite"
        ], 400);
    }

    #[Route("/clanstats/historiqueClanWar", name: "app_clan_stats_historiqueClanWar", methods: ["POST"])]
    public function historiqueClanWar(Request $request): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true) ?? null;
            $this->logger->info("function 'historiqueClanWar'");
            $result = $this->clanStatsService->getHistoriqueClanWar($data["clanTag"], $data["warsSelected"]);
            $serializedwarsPlayersStats = $this->serializer->serialize($result, 'json', ['groups' => 'ajaxed']);
            $htmlClan = $this->renderView("clan_stats/clan-info-response.html.twig", [
                "activeMembers" => $result["activeMembers"],
                "exMembers" => $result["exMembers"]
            ]);
            return new JsonResponse([
                "success" => true,
                "serializedwarsPlayersStats" => $serializedwarsPlayersStats
            ]);
        }
        return new JsonResponse([
            "success" => false,
            "message" => "Une erreur s'est produite"
        ], 400);
    }
}
