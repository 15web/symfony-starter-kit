<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

use App\User\SignUp\Domain\UserId;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_USER')]
#[Route('/export/tasks.{format}', methods: ['GET'])]
#[AsController]
final class ExportTaskAction
{
    public function __construct(private readonly ExportTasks $exportTasks)
    {
    }

    public function __invoke(Format $format, UserId $userId): BinaryFileResponse
    {
        return ($this->exportTasks)($format, $userId);
    }
}
