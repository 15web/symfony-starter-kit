<?php declare(strict_types=1);

/**
 * @var non-empty-string $namespace
 * @var non-empty-string $use_statements
 * @var non-empty-string $class_name
 */
echo "<?php\n"; ?>

declare(strict_types=1);

namespace <?php echo $namespace; ?>;

<?php echo $use_statements; ?>

#[ORM\Entity]
/** @final */
class <?php echo $class_name."\n"; ?>
{
    #[ORM\Id, ORM\Column(type: 'uuid', unique: true)]
    private readonly Uuid $id;

    public function __construct()
    {
        $this->id = new UuidV7();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }
}
