<?php declare(strict_types=1);

namespace App\Service;

use App\Entity\Task;
use App\Exceptions\TaskAlreadyExistsException;
use App\Exceptions\TaskNotFoundException;
use App\Repository\TaskRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Task CRUD
 */
class TaskService
{
    private $taskRepository;
    private $entityManager;

    public const TO_DO_STATUS = 1;
    public const WIP_STATUS = 2;
    public const FINISH_STATUS = 3;

    private const NOT_FOUND_MESSAGE = 'Task was not found.';
    public const ALREADY_EXISTS = 'Task with this name already exists.';


    /**
     * TaskService constructor.
     *
     * @param TaskRepository         $taskRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(TaskRepository $taskRepository, EntityManagerInterface $entityManager)
    {
        $this->taskRepository = $taskRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $name
     * @param string $description
     * @param string $deadline
     * @param int    $status
     * @return Task
     * @throws TaskAlreadyExistsException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function create(string $name, string $description, string $deadline, int $status): Task
    {
        if ($this->taskRepository->count(['name' => $name]) > 0) {
            throw new TaskAlreadyExistsException(self::ALREADY_EXISTS);
        }

        $task = new Task();
        $dateTime = new DateTime($deadline);

        $task->setName($name)
             ->setDescription($description)
             ->setStatus($status)
             ->setDeadline($dateTime);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    /**
     * @param int $id
     * @return Task
     * @throws TaskNotFoundException
     */
    public function read(int $id): Task
    {
        $task = $this->taskRepository->find($id);
        if (!$task instanceof Task) {
            throw new TaskNotFoundException(self::NOT_FOUND_MESSAGE);
        }
        return $task;
    }

    /**
     * @param $status
     * @return array|null
     * @throws TaskNotFoundException
     */
    public function readByStatus($status): ?array
    {
        $tasks = $this->taskRepository->findBy(['status' => $status]);
        if (count($tasks) < 1) {
            throw new TaskNotFoundException(self::NOT_FOUND_MESSAGE);
        }
        return $tasks;
    }

    /**
     * @param $name
     * @return array|null
     * @throws TaskNotFoundException
     */
    public function readByName($name): ?array
    {
        $task = $this->taskRepository->findBy(['name' => $name]);
        if (count($task) < 1) {
            throw new TaskNotFoundException(self::NOT_FOUND_MESSAGE);
        }
        return $task;
    }

    /**
     * @return Task[]
     * @throws TaskNotFoundException
     */
    public function readAll(): array
    {
        $tasks = $this->taskRepository->findAll();
        if (count($tasks) === 0) {
            throw new TaskNotFoundException(self::NOT_FOUND_MESSAGE);

        }
        return $tasks;
    }


    /**
     * @param int    $id
     * @param string $description
     * @param string $deadline
     * @param int    $status
     * @return Task
     * @throws TaskNotFoundException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    public function update(int $id, string $description, string $deadline, int $status): Task
    {
        $dateTime = new DateTime($deadline);

        $task = $this->read($id);

        $task->setDescription($description)
             ->setStatus($status)
             ->setDeadline($dateTime);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    /**
     * @param int $id
     * @throws \Doctrine\ORM\ORMException
     * @throws TaskNotFoundException
     */
    public function delete(int $id): void
    {
        $task = $this->read($id);
        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }
}