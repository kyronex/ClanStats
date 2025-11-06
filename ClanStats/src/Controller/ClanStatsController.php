<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use App\Form\ClanNameType;

use App\Dto\React\ClanSearchForm;


use App\Service\ClanStatsService;

final class ClanStatsController extends AbstractController
{
    private LoggerInterface $logger;
    private ClanStatsService $clanStatsService;
    private SerializerInterface $serializer;
    private NormalizerInterface $normalizer;
    private ValidatorInterface $validator;

    public function __construct(ClanStatsService $clanStatsService, LoggerInterface $logger, SerializerInterface $serializer, NormalizerInterface $normalizer, ValidatorInterface $validator)
    {
        $this->logger = $logger;
        $this->clanStatsService = $clanStatsService;
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
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
        $data = json_decode($request->getContent(), true);
        $this->logger->info("class 'ClanStatsController' function 'searchClanName' .");

        try {
            $dto = $this->serializer->deserialize($request->getContent(), ClanSearchForm::class, "json");
            $errors = $this->validator->validate($dto);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }
                return new JsonResponse([
                    'success' => false,
                    'errors' => $errorMessages
                ], 422);
            }
            $params = [
                "name" => $dto->getNomClan(),
                "minMembers" => $dto->getMinMembers(),
                "maxMembers" => $dto->getMaxMembers(),
                "minScore" => $dto->getMinScore()
            ];

            $this->logger->info("Recherche pour le clan: '" . $dto->getNomClan() . "'", $params);
            $clans = $this->clanStatsService->getSearchClanName($params);
            $normalizedClans = $this->normalizer->normalize($clans, null, ['groups' => 'ajaxed']);

            return new JsonResponse([
                "success" => true,
                "clans" => $normalizedClans
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                "success" => false,
                "errors" => ["general" => $e->getMessage()]
            ], 500);
        }
    }
    #[Route("/clanstats/clan", name: "app_clan_stats_clan", methods: ["POST"])]
    public function clan(Request $request): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $clan = json_decode($request->getContent(), true) ?? null;
            $this->logger->info("class 'ClanStatsController' function 'clan' pour '" . $clan["tag"] . "'.");
            $clan = $this->clanStatsService->getClan($clan["tag"]);
            $normalizedClans = $this->normalizer->normalize($clan, null, ['groups' => 'ajaxed']);
            return new JsonResponse([
                "success" => true,
                "clan" => $normalizedClans
            ]);
        }
        return new JsonResponse([
            "success" => false,
            "message" => "Une erreur s'est produite"
        ], 400);
    }
    #[Route("/clanstats/riverRaceLog", name: "app_clan_stats_riverRaceLog", methods: ["POST"])]
    public function riverRaceLog(Request $request): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $clan = json_decode($request->getContent(), true) ?? null;
            $this->logger->info("class 'ClanStatsController' function 'riverRaceLog' pour '" . $clan["tag"] . "'.");
            $riverRaceLogs = $this->clanStatsService->getRiverRaceLog($clan["tag"]);
            $normalizedRiverRaceLogs = $this->normalizer->normalize($riverRaceLogs, null, ['groups' => 'ajaxed']);
            return new JsonResponse([
                "success" => true,
                "riverRaceLogs" => $normalizedRiverRaceLogs
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
            $this->logger->info("class 'ClanStatsController' function 'historiqueClanWar'");
            $result = $this->clanStatsService->getHistoriqueClanWar($data["clanTag"], $data["warsSelected"]);

            $normalizedActiveMembers = $this->normalizer->normalize($result["activeMembers"], null, ['groups' => 'ajaxed']);
            $normalizedExMembers = $this->normalizer->normalize($result["exMembers"], null, ['groups' => 'ajaxed']);

            return new JsonResponse([
                "success" => true,
                "activeMembers" => $normalizedActiveMembers,
                "exMembers" => $normalizedExMembers
            ]);
        }
        return new JsonResponse([
            "success" => false,
            "message" => "Une erreur s'est produite"
        ], 400);
    }
}
