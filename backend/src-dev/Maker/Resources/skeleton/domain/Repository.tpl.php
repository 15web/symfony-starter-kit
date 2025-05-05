<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $entity_class_name
 * @var non-empty-string $entity_variable_name
 * @var non-empty-string $class_name
 */
echo '<?php'.PHP_EOL; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * Репозиторий <?php echo $entity_class_name; ?> todo комментарий
 */

final readonly class <?php echo $class_name.PHP_EOL; ?>
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function add(<?php echo $entity_class_name; ?> $<?php echo $entity_variable_name; ?>): void
    {
        $this->entityManager->persist($<?php echo $entity_variable_name; ?>);
    }

    public function remove(<?php echo $entity_class_name; ?> $<?php echo $entity_variable_name; ?>): void
    {
        $this->entityManager->remove($<?php echo $entity_variable_name; ?>);
    }

    public function findById(Uuid $id): ?<?php echo $entity_class_name.PHP_EOL; ?>
    {
        return $this->entityManager
            ->getRepository(<?php echo $entity_class_name; ?>::class)
            ->find($id);
    }

    /**
     * @return list<<?php echo $entity_class_name; ?>>
     */
    public function getAll(): array
    {
        return $this->entityManager
            ->getRepository(<?php echo $entity_class_name; ?>::class)
            ->findAll();
    }
}
