<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClanStatsTools
{
  private LoggerInterface $logger;
  private ParameterBagInterface $parameterBag;
  private HttpClientInterface $client;
  private array $allowedRoutes;
  private array $allowedDirs;


  public function __construct(ParameterBagInterface $parameterBag, HttpClientInterface $client, LoggerInterface $logger, $allowedRoutes, $allowedDirs)
  {
    $this->logger = $logger;
    $this->client = $client;
    $this->parameterBag = $parameterBag;
    $this->allowedRoutes = $allowedRoutes;
    $this->allowedDirs = $allowedDirs;
    $this->logger->info("Initialisation de : class 'ClanStatsTools'.");
  }

  public function getTaskFilePath(string $taskId): string
  {
    $this->logger->info("Lancement de : class 'ClanStatsTools' function 'getTaskFilePath'.");
    $taskDir = "";
    foreach ($this->allowedDirs as $keyDir => $dir) {
      $tempTaskDir = $dir . $taskId . ".json";
      if (is_file($tempTaskDir)) {
        $taskDir = $tempTaskDir;
      }
    }
    /*  //$taskDir = $this->parameterBag->get("clash_royale.tools.task_dir");
    $taskDir = $this->parameterBag->get("clash_royale.tools.dir")["task"];
    if (!is_dir($taskDir)) {
      mkdir($taskDir, 0755, true);
    }
     */
    return $taskDir;
  }

  public function saveTaskFile(string $taskId, array $data): bool
  {
    $this->logger->info("Lancement de : class 'ClanStatsTools' function 'saveTaskFile'.");
    $taskData = [
      "status" => "pending",
      "pending_at" => time(),
      "data" => $data,
      "result" => null
    ];
    $taskDir = $this->parameterBag->get("clash_royale.tools.dirs")["task"] . $taskId . ".json";
    return file_put_contents($taskDir, json_encode($taskData));
  }

  public function loadTaskData(string $taskId): ?array
  {
    $this->logger->info("Lancement de : class 'ClanStatsTools' function 'loadTaskData'.");
    $filePath = $this->getTaskFilePath($taskId);
    if (!file_exists($filePath)) {
      return null;
    }
    return json_decode(file_get_contents($filePath), true);
  }

  public function updateTaskData(string $taskId, array $updates): void
  {
    $this->logger->info("Lancement de : class 'ClanStatsTools' function 'updateTaskData'.");
    $taskData = $this->loadTaskData($taskId);
    if ($taskData) {
      $taskData = array_merge($taskData, $updates);
      file_put_contents($this->getTaskFilePath($taskId), json_encode($taskData));
    }
  }

  public function deleteTaskFile(string $taskId): void
  {
    $this->logger->info("Lancement de : class 'ClanStatsTools' function 'deleteTaskFile'.");
    unlink($this->getTaskFilePath($taskId));
  }

  public function mooveTaskFile(string $taskId, string $route): void
  {
    $this->logger->info("Lancement de : class 'ClanStatsTools' function 'mooveTaskFile'.");
    if (!array_key_exists($route, $this->allowedDirs)) {
      $this->logger->error("Route not allowed: {$route}");
      $this->mooveTaskFile($taskId, "taskErr");
      return;
    }
    $newTaskDir = $this->allowedDirs[$route] . $taskId . ".json";
    rename($this->getTaskFilePath($taskId), $newTaskDir);
  }

  public function launchImmediateProcessing(string $taskId, string $route): void
  {
    $this->logger->info("Lancement de : class 'ClanStatsTools' function 'launchImmediateProcessing'.");
    if (!array_key_exists($route, $this->allowedRoutes)) {
      $this->logger->error("Route not allowed: {$route}");
      $this->mooveTaskFile($taskId, "taskErr");
      return;
    }

    $promise = $this->client->request(
      "POST",
      $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["HTTP_HOST"] . $this->allowedRoutes[$route],
      [
        "json" => ["taskId" => $taskId],
        "timeout" => 300,               // ✅ 5 minutes
        "max_duration" => 600,
        "headers" => [
          "X-Internal-Request" => "true",
          "Content-Type" => "application/json"
        ]
      ]
    );
    // intelephense(ignore=P1013)
    /*  $promise->then(
      function ($response) use ($taskId) {
        $statusCode = $response->getStatusCode();
        if ($statusCode >= 200 && $statusCode < 300) {
          $this->logger->info("✅ Tâche {$taskId} acceptée - HTTP {$statusCode}");
        } else {
          $this->logger->warning("⚠️ Tâche {$taskId} - Réponse inattendue: HTTP {$statusCode}");
        }
      },
      function ($error) use ($taskId) {
        $this->logger->warning("⚠️ Tâche {$taskId} - Erreur: " . $error->getMessage());
      }
    ); */
  }
}
