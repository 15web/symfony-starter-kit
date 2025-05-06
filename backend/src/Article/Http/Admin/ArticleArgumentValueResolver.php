<?php

declare(strict_types=1);

namespace App\Article\Http\Admin;

use App\Article\Domain\Article;
use App\Article\Domain\ArticleRepository;
use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiException\ApiNotFoundException;
use InvalidArgumentException;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Uid\Uuid;
use Webmozart\Assert\Assert;

/**
 * Резолвер для сущности 'статья'
 */
final readonly class ArticleArgumentValueResolver implements ValueResolverInterface
{
    public function __construct(private ArticleRepository $articleRepository) {}

    /**
     * @return iterable<Article>
     */
    #[Override]
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== Article::class) {
            return [];
        }

        /** @var string|null $id */
        $id = $request->attributes->get('id');
        if ($id === null) {
            throw new ApiBadRequestException(['Укажите id']);
        }

        try {
            Assert::uuid($id, 'id: значение должно быть в формате Uuid');

            $article = $this->articleRepository->findById(Uuid::fromString($id));

            if ($article === null) {
                throw new ApiNotFoundException(['Статья не найдена']);
            }
        } catch (InvalidArgumentException) {
            throw new ApiBadRequestException(['Укажите валидный id']);
        }

        return [$article];
    }
}
