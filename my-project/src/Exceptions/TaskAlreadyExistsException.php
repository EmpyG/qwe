<?php declare(strict_types=1);

namespace App\Exceptions;

use Doctrine\ORM\EntityNotFoundException;

class TaskAlreadyExistsException extends EntityNotFoundException
{
}
