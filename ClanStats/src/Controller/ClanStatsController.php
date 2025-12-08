<?php

namespace App\Controller;

use App\Dto\ClashRoyale\RiverRace\Clan;
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
use App\Service\ClanStatsTools;


// TODO fair PHPDOC GENERICS
final class ClanStatsController extends AbstractController
{
    private LoggerInterface $logger;
    private ClanStatsService $clanStatsService;
    private ClanStatsTools $clanStatsTools;
    private SerializerInterface $serializer;
    private NormalizerInterface $normalizer;
    private ValidatorInterface $validator;

    public function __construct(ClanStatsTools $clanStatsTools, ClanStatsService $clanStatsService, LoggerInterface $logger, SerializerInterface $serializer, NormalizerInterface $normalizer, ValidatorInterface $validator)
    {
        $this->logger = $logger;
        $this->clanStatsService = $clanStatsService;
        $this->clanStatsTools = $clanStatsTools;
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
        $this->validator = $validator;
    }

    #[Route("/clanstats", name: "appClanStats")]
    public function index(): Response
    {
        $form = $this->createForm(ClanNameType::class);
        return $this->render("clan_stats/index.html.twig", [
            "form" => $form->createView(),
        ]);
    }

    #[Route("/clanstats/search", name: "appClanStats_search", methods: ["POST"])]
    public function searchClanName(Request $request): JsonResponse
    {
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
            $normalizedClans = $this->normalizer->normalize($clans, null, ["groups" => "ajaxed"]);

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
    #[Route("/clanstats/clan", name: "appClanStats_clan", methods: ["POST"])]
    public function clan(Request $request): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $clan = json_decode($request->getContent(), true) ?? null;
            $this->logger->info("class 'ClanStatsController' function 'clan' pour '" . $clan["tag"] . "'.");
            $clan = $this->clanStatsService->getClan($clan["tag"]);
            $normalizedClans = $this->normalizer->normalize($clan, null, ["groups" => "ajaxed"]);
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
    #[Route("/clanstats/riverRaceLog", name: "appClanStats_riverRaceLog", methods: ["POST"])]
    public function riverRaceLog(Request $request): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $clan = json_decode($request->getContent(), true) ?? null;
            $this->logger->info("class 'ClanStatsController' function 'riverRaceLog' pour '" . $clan["tag"] . "'.");
            $riverRaceLogs = $this->clanStatsService->getRiverRaceLog($clan["tag"]);
            $normalizedRiverRaceLogs = $this->normalizer->normalize($riverRaceLogs, null, ["groups" => "ajaxed"]);
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
    #[Route("/clanstats/historiqueClanWar", name: "appClanStats_historiqueClanWar", methods: ["POST"])]
    public function historiqueClanWar(Request $request): JsonResponse
    {
        if ($request->isXmlHttpRequest()) {
            $data = json_decode($request->getContent(), true) ?? null;
            $this->logger->info("class 'ClanStatsController' function 'historiqueClanWar'");
            $result = $this->clanStatsService->getHistoriqueClanWar($data["clanTag"], $data["warsSelected"]);

            $normalizedActiveMembers = $this->normalizer->normalize($result["activeMembers"], null, ["groups" => "ajaxed"]);
            $normalizedExMembers = $this->normalizer->normalize($result["exMembers"], null, ["groups" => "ajaxed"]);

            $dataTask = ["warsSelected" => $data["warsSelected"], "activeMembers" => $normalizedActiveMembers, "exMembers" => $normalizedExMembers];
            $taskId = uniqid("dataTask_" . $data["clanTag"] . "_", true);
            $fileResp = $this->clanStatsTools->saveTaskFile($taskId, $dataTask);
            if ($fileResp) {
                register_shutdown_function(function () use ($taskId) {
                    $this->clanStatsTools->launchImmediateProcessing($taskId, "statsHistoriqueClanWar");
                });
            }
            return new JsonResponse([
                "success" => true,
                "activeMembers" => $normalizedActiveMembers,
                "exMembers" => $normalizedExMembers,
                "taskId" => $taskId
            ]);
        }
        return new JsonResponse([
            "success" => false,
            "message" => "Une erreur s'est produite"
        ], 400);
    }

    #[Route("/clanstats/statsHistoriqueClanWar", name: "appClanStats_statsHistoriqueClanWar", methods: ["GET", "POST"])]
    public function statsHistoriqueClanWar(Request $request): JsonResponse
    {
        $this->logger->info("class 'ClanStatsController' function 'statsHistoriqueClanWar'");
        if (!$request->headers->get("X-Internal-Request")) {
            return $this->json(["success" => false, "error" => "Forbidden"], 403);
        }
        $data = json_decode($request->getContent(), true);
        $taskId = $data["taskId"];
        try {
            $taskData = $this->clanStatsTools->loadTaskData($taskId);
            $normalizedResult = [];
            if ($taskData["status"] == "completed") {
                $normalizedResult = $this->normalizer->normalize($taskData["result"], null, ["groups" => "ajaxed"]);
            } elseif ($taskData["status"] == "pending") {
                $result = $this->clanStatsService->getStatsHistoriqueClanWar($taskId);
                $normalizedResult = $this->normalizer->normalize($result, null, ["groups" => "ajaxed"]);
                $this->clanStatsTools->updateTaskData($taskId, [
                    "status" => "completed",
                    "result" => $normalizedResult,
                    "completed_at" => time()
                ]);
                $this->clanStatsTools->mooveTaskFile($taskId, "taskSolved");
            }
            return $this->json(["success" => true, "taskId" => $taskId, "status" => $taskData["status"], "data" => $normalizedResult]);
        } catch (\Exception $e) {
            $this->clanStatsTools->updateTaskData($taskId, [
                "status" => "failed",
                "result" => ["error" => $e->getMessage()],
                "failed_at" => time()
            ]);
            $this->clanStatsTools->mooveTaskFile($taskId, "taskErr");
            return $this->json(["success" => false, "error" => $e->getMessage()], 500);
        }
    }
}
