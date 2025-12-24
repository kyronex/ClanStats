<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Gestionnaire de stockage et traitement asynchrone des tâches d'analyse.
 */
class ClanStatsTools
{
  private LoggerInterface $logger;
  private ParameterBagInterface $parameterBag;
  private HttpClientInterface $client;
  private array $allowedRoutes;
  private array $allowedDirs;

  /**
   * Initialise le gestionnaire avec les répertoires et routes autorisés.
   *
   * @param ParameterBagInterface $parameterBag Conteneur de paramètres de configuration
   * @param HttpClientInterface $client Client HTTP pour appels asynchrones
   * @param LoggerInterface $logger Logger Symfony pour traçabilité
   * @param array<string, string> $allowedRoutes Routes HTTP autorisées pour traitement
   * @param array<string, string> $allowedDirs Répertoires autorisés par type de tâche
   */
  public function __construct(ParameterBagInterface $parameterBag, HttpClientInterface $client, LoggerInterface $logger, $allowedRoutes, $allowedDirs)
  {
    $this->logger = $logger;
    $this->client = $client;
    $this->parameterBag = $parameterBag;
    $this->allowedRoutes = $allowedRoutes;
    $this->allowedDirs = $allowedDirs;
    $this->logger->info("Initialisation de : class 'ClanStatsTools'.");
  }

  /**
   * Recherche le chemin complet d'un fichier de tâche dans tous les répertoires autorisés.
   *
   * Parcourt séquentiellement les répertoires configurés jusqu'à trouver le fichier correspondant au taskId fourni.
   *
   * @param string $taskId Identifiant unique de la tâche (UUID ou hash)
   *
   * @return string Chemin complet du fichier trouvé, ou chaîne vide si inexistant
   */
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

  /**
   * Crée un nouveau fichier de tâche avec statut initial "pending".
   *
   * @param string $taskId Identifiant unique de la tâche
   * @param array<string, mixed> $data Données métier à stocker
   *
   * @return bool True si l'écriture a réussi, false sinon
   */
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

  /**
   * Charge le contenu JSON d'un fichier de tâche.
   *
   * @param string $taskId Identifiant unique de la tâche
   *
   * @return array<string, mixed>|null Données de la tâche, ou null si le fichier n'existe pas
   */
  public function loadTaskData(string $taskId): ?array
  {
    $this->logger->info("Lancement de : class 'ClanStatsTools' function 'loadTaskData'.");
    $filePath = $this->getTaskFilePath($taskId);
    if (!file_exists($filePath)) {
      return null;
    }
    return json_decode(file_get_contents($filePath), true);
  }

  /**
   * Met à jour partiellement les données d'une tâche existante.
   *
   * Effectue une fusion (merge) des nouvelles valeurs avec l'existant. Opération sans effet si le fichier n'existe pas.
   *
   * @param string $taskId Identifiant unique de la tâche
   * @param array<string, mixed> $updates Données à fusionner
   *
   * @return void
   */
  public function updateTaskData(string $taskId, array $updates): void
  {
    $this->logger->info("Lancement de : class 'ClanStatsTools' function 'updateTaskData'.");
    $taskData = $this->loadTaskData($taskId);
    if ($taskData) {
      $taskData = array_merge($taskData, $updates);
      file_put_contents($this->getTaskFilePath($taskId), json_encode($taskData));
    }
  }

  /**
   * Supprime définitivement un fichier de tâche.
   *
   * @param string $taskId Identifiant unique de la tâche
   *
   * @return void
   */
  public function deleteTaskFile(string $taskId): void
  {
    $this->logger->info("Lancement de : class 'ClanStatsTools' function 'deleteTaskFile'.");
    unlink($this->getTaskFilePath($taskId));
  }

  /**
   * Déplace un fichier de tâche vers un répertoire correspondant à son nouveau statut.
   *
   * Si la route demandée n'est pas autorisée, déplace automatiquement vers taskErr.
   *
   * @param string $taskId Identifiant unique de la tâche
   * @param string $route Clé du répertoire cible (doit exister dans $allowedDirs)
   *
   * @return void
   */
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

  /**
   * Déclenche le traitement asynchrone immédiat d'une tâche via requête HTTP POST.
   *
   * Effectue un appel HTTP interne (fire-and-forget) vers une route de traitement configurée.
   * Configuration :
   * - Timeout : 5 minutes
   * - Durée max : 10 minutes
   * - Header X-Internal-Request pour sécurité
   *
   * Si la route n'est pas autorisée, déplace la tâche vers taskErr.
   *
   * @param string $taskId Identifiant unique de la tâche
   * @param string $route Clé de la route de traitement (doit exister dans $allowedRoutes)
   *
   * @return void
   */
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
