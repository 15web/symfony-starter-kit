<?php

declare(strict_types=1);

namespace App\Infrastructure\Response\Pagination;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\BuildMappingErrorMessages;
use App\Infrastructure\AsService;
use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Резолвер для запроса на пагинацию
 */
#[AsService]
final readonly class PaginationRequestArgumentResolver implements ValueResolverInterface
{
    private const string QUERY_LIMIT_NAME = 'limit';
    private const string QUERY_OFFSET_NAME = 'offset';

    public function __construct(private BuildMappingErrorMessages $buildMappingErrorMessages) {}

    /**
     * @return iterable<PaginationRequest>
     *
     * @throws ApiBadRequestException
     */
    #[Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== PaginationRequest::class) {
            return [];
        }

        try {
            $paginationRequest = (new MapperBuilder())->mapper()->map(
                PaginationRequest::class,
                [
                    self::QUERY_OFFSET_NAME => $request->query->getInt(self::QUERY_OFFSET_NAME),
                    self::QUERY_LIMIT_NAME => $request->query->getInt(self::QUERY_LIMIT_NAME, 10),
                ]
            );
        } catch (MappingError $e) {
            $errorMessages = ($this->buildMappingErrorMessages)($e);

            throw new ApiBadRequestException($errorMessages, $e);
        }

        return [$paginationRequest];
    }
}
