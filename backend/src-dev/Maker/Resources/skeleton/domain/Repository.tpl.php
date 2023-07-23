<?php declare(strict_types=1);
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * Репозиторий <?php echo $entity_class_name."\n"; ?>
 */
#[AsService]
final readonly class <?php echo $class_name."\n"; ?>
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(<?php echo $entity_class_name; ?> $entity): void
    {
        $this->entityManager->persist($entity);
    }

    public function remove(<?php echo $entity_class_name; ?> $entity): void
    {
        $this->entityManager->remove($entity);
    }

    public function findById(Uuid $id): ?<?php echo $entity_class_name."\n"; ?>
    {
        return $this->entityManager->getRepository(<?php echo $entity_class_name; ?>::class)->find($id);
    }
}
