<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $class_name
 * @var non-empty-string $entity_title
 */
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

/**
 * @final
 *
 * <?php echo $entity_title.PHP_EOL; ?>
 */
#[ORM\Entity]
class <?php echo $class_name.PHP_EOL; ?>
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    #[ORM\Column]
    private readonly DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $updatedAt;

    public function __construct(
        Uuid $id,
#CONSTRUCTOR_ARGUMENTS#
    ) {
        $this->id = $id;
#CONSTRUCTOR_BINDINGS#
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = null;
    }

    public function update(
#CONSTRUCTOR_ARGUMENTS#
    ): void {
#CONSTRUCTOR_BINDINGS#
        $this->updatedAt = new DateTimeImmutable();
    }
}
