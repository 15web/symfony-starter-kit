<?php

declare(strict_types=1);

namespace App\Task\Http\Export;

use App\Infrastructure\ApiException\ApiUnauthorizedException;
use App\User\Model\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[IsGranted('ROLE_USER')]
#[Route('/export/tasks.{format}', methods: ['GET'])]
final class ExportTaskAction
{
    public function __construct(private readonly ExportTasks $exportTasks)
    {
    }

    public function __invoke(Format $format, #[CurrentUser] ?User $user): BinaryFileResponse
    {
        if ($user === null) {
            throw new ApiUnauthorizedException();
        }

        return ($this->exportTasks)($format, $user);
    }
}
