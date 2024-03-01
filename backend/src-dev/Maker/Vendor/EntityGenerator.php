<?php

declare(strict_types=1);

namespace Dev\Maker\Vendor;

use Doctrine\DBAL\Types\Type;
use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Doctrine\EntityRelation;
use Symfony\Bundle\MakerBundle\Exception\RuntimeCommandException;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\ClassDetails;
use Symfony\Bundle\MakerBundle\Util\ClassSourceManipulator;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Запрашивает информацию о генерируемой сущности, здесь фиксится захардкоденный код из MakeEntity.
 * Скопировано из MakeEntity(MakerBundle), создает сущность и репозиторий в нужном модуле.
 * Содержит информацию о генерируемых полях сущности (имя, тип, nullable).
 * Используется для генерации Domain слоя.
 */
final readonly class EntityGenerator
{
    public function __construct(
        private FileManager $fileManager,
        private DoctrineHelper $doctrineHelper,
        private CustomGenerator $generator,
        private EntityClassGeneratorForModule $entityClassGenerator,
    ) {}

    public function generate(InputInterface $input, ConsoleStyle $io): array
    {
        $moduleName = $input->getArgument('module-name');
        $overwrite = false;
        $entityClassDetails = $this->generator->createClassNameDetails(
            $input->getArgument('name'),
            $moduleName.'\\Domain\\'
        );

        $classExists = class_exists($entityClassDetails->getFullName());
        if (!$classExists) {
            $entityPath = $this->entityClassGenerator->generateEntityClass(
                $moduleName,
                $entityClassDetails,
            );
            $this->generator->writeChanges();
        }

        // Сюда записываются поля сущности (имя, тип, nullable)
        $creatingFields = [];

        if (!$this->doesEntityUseAttributeMapping($entityClassDetails->getFullName())) {
            throw new RuntimeCommandException(sprintf('Only attribute mapping is supported by make:entity, but the <info>%s</info> class uses a different format. If you would like this command to generate the properties & getter/setter methods, add your mapping configuration, and then re-run this command with the <info>--regenerate</info> flag.', $entityClassDetails->getFullName()));
        }

        if ($classExists) {
            $entityPath = $this->getPathOfClass($entityClassDetails->getFullName());
            $io->text([
                'Your entity already exists! So let\'s add some new fields!',
            ]);
        } else {
            $io->text([
                '',
                'Entity generated! Now let\'s add some fields!',
                'You can always add more fields later manually or by re-running this command.',
            ]);
        }

        $currentFields = $this->getPropertyNames($entityClassDetails->getFullName());
        $manipulator = $this->createClassManipulator($entityPath, $io, $overwrite);

        $isFirstField = true;
        while (true) {
            $newField = $this->askForNextField($io, $currentFields, $entityClassDetails->getFullName(), $isFirstField);
            $isFirstField = false;

            if ($newField === null) {
                break;
            }

            $fileManagerOperations = [];
            $fileManagerOperations[$entityPath] = $manipulator;

            if (\is_array($newField)) {
                $annotationOptions = $newField;
                unset($annotationOptions['fieldName']);
                $manipulator->addEntityField($newField['fieldName']);

                $currentFields[] = $newField['fieldName'];
            } elseif ($newField instanceof EntityRelation) {
                // both overridden below for OneToMany
                $newFieldName = $newField->getOwningProperty();
                if ($newField->isSelfReferencing()) {
                    $otherManipulatorFilename = $entityPath;
                    $otherManipulator = $manipulator;
                } else {
                    $otherManipulatorFilename = $this->getPathOfClass($newField->getInverseClass());
                    $otherManipulator = $this->createClassManipulator($otherManipulatorFilename, $io, $overwrite);
                }

                switch ($newField->getType()) {
                    case EntityRelation::MANY_TO_ONE:
                        if ($newField->getOwningClass() === $entityClassDetails->getFullName()) {
                            // THIS class will receive the ManyToOne
                            $manipulator->addManyToOneRelation($newField->getOwningRelation());

                            if ($newField->getMapInverseRelation()) {
                                $otherManipulator->addOneToManyRelation($newField->getInverseRelation());
                            }
                        } else {
                            // the new field being added to THIS entity is the inverse
                            $newFieldName = $newField->getInverseProperty();
                            $otherManipulatorFilename = $this->getPathOfClass($newField->getOwningClass());
                            $otherManipulator = $this->createClassManipulator($otherManipulatorFilename, $io, $overwrite);

                            // The *other* class will receive the ManyToOne
                            $otherManipulator->addManyToOneRelation($newField->getOwningRelation());
                            if (!$newField->getMapInverseRelation()) {
                                throw new Exception('Somehow a OneToMany relationship is being created, but the inverse side will not be mapped?');
                            }
                            $manipulator->addOneToManyRelation($newField->getInverseRelation());
                        }

                        break;

                    case EntityRelation::MANY_TO_MANY:
                        $manipulator->addManyToManyRelation($newField->getOwningRelation());
                        if ($newField->getMapInverseRelation()) {
                            $otherManipulator->addManyToManyRelation($newField->getInverseRelation());
                        }

                        break;

                    case EntityRelation::ONE_TO_ONE:
                        $manipulator->addOneToOneRelation($newField->getOwningRelation());
                        if ($newField->getMapInverseRelation()) {
                            $otherManipulator->addOneToOneRelation($newField->getInverseRelation());
                        }

                        break;

                    default:
                        throw new Exception('Invalid relation type');
                }

                // save the inverse side if it's being mapped
                if ($newField->getMapInverseRelation()) {
                    $fileManagerOperations[$otherManipulatorFilename] = $otherManipulator;
                }
                $currentFields[] = $newFieldName;
            } else {
                throw new Exception('Invalid value');
            }

            $creatingFields[] = $newField;

            foreach ($fileManagerOperations as $path => $manipulatorOrMessage) {
                if (\is_string($manipulatorOrMessage)) {
                    $io->comment($manipulatorOrMessage);
                } else {
                    $this->fileManager->dumpFile($path, $manipulatorOrMessage->getSourceCode());
                }
            }
        }

        return [$entityClassDetails, $creatingFields];
    }

    public function createEntityClassQuestion(string $questionText): Question
    {
        $question = new Question($questionText);
        $question->setValidator(static fn (?string $value = null): string => Validator::notBlank($value));
        $question->setAutocompleterValues($this->doctrineHelper->getEntitiesForAutocomplete());

        return $question;
    }

    private function askForNextField(ConsoleStyle $io, array $fields, string $entityClass, bool $isFirstField): null|array|EntityRelation
    {
        $io->writeln('');

        if ($isFirstField) {
            $questionText = 'New property name (press <return> to stop adding fields)';
        } else {
            $questionText = 'Add another property? Enter the property name (or press <return> to stop adding fields)';
        }

        $fieldName = $io->ask($questionText, null, function ($name) use ($fields): null|string {
            // allow it to be empty
            if (!$name) {
                return $name;
            }

            if (\in_array($name, $fields, true)) {
                throw new InvalidArgumentException(sprintf('The "%s" property already exists.', $name));
            }

            return Validator::validateDoctrineFieldName($name, $this->doctrineHelper->getRegistry());
        });

        if (!$fieldName) {
            return null;
        }

        $defaultType = 'string';
        // try to guess the type by the field name prefix/suffix
        // convert to snake case for simplicity
        $snakeCasedField = Str::asSnakeCase($fieldName);

        if ('_at' === $suffix = substr($snakeCasedField, -3)) {
            $defaultType = 'datetime_immutable';
        } elseif ($suffix === '_id') {
            $defaultType = 'integer';
        } elseif (str_starts_with($snakeCasedField, 'is_')) {
            $defaultType = 'boolean';
        } elseif (str_starts_with($snakeCasedField, 'has_')) {
            $defaultType = 'boolean';
        } elseif ($snakeCasedField === 'uuid') {
            $defaultType = Type::hasType('uuid') ? 'uuid' : 'guid';
        } elseif ($snakeCasedField === 'guid') {
            $defaultType = 'guid';
        }

        $type = null;
        $types = $this->getTypesMap();

        $allValidTypes = array_merge(
            array_keys($types),
            EntityRelation::getValidRelationTypes(),
            ['relation']
        );
        while ($type === null) {
            $question = new Question('Field type (enter <comment>?</comment> to see all types)', $defaultType);
            $question->setAutocompleterValues($allValidTypes);
            $type = $io->askQuestion($question);

            if ($type === '?') {
                $this->printAvailableTypes($io);
                $io->writeln('');

                $type = null;
            } elseif (!\in_array($type, $allValidTypes, true)) {
                $this->printAvailableTypes($io);
                $io->error(sprintf('Invalid type "%s".', $type));
                $io->writeln('');

                $type = null;
            }
        }
        if ($type === 'relation') {
            return $this->askRelationDetails($io, $entityClass, $type, $fieldName);
        }
        if (\in_array($type, EntityRelation::getValidRelationTypes(), true)) {
            return $this->askRelationDetails($io, $entityClass, $type, $fieldName);
        }

        // this is a normal field
        $data = ['fieldName' => $fieldName, 'type' => $type];
        if ($type === 'string') {
            // default to 255, avoid the question
            $data['length'] = $io->ask('Field length', '255', static fn ($length) => Validator::validateLength($length));
        } elseif ($type === 'decimal') {
            // 10 is the default value given in \Doctrine\DBAL\Schema\Column::$_precision
            $data['precision'] = $io->ask('Precision (total number of digits stored: 100.00 would be 5)', '10', static fn ($precision) => Validator::validatePrecision((int) $precision));

            // 0 is the default value given in \Doctrine\DBAL\Schema\Column::$_scale
            $data['scale'] = $io->ask('Scale (number of decimals to store: 100.00 would be 2)', '0', static fn ($scale) => Validator::validateScale((int) $scale));
        }

        if ($io->confirm('Can this field be null in the database (nullable)', false)) {
            $data['nullable'] = true;
        }

        return $data;
    }

    private function printAvailableTypes(ConsoleStyle $io): void
    {
        $allTypes = $this->getTypesMap();

        if (getenv('TERM_PROGRAM') === 'Hyper') {
            $wizard = 'wizard 🧙';
        } else {
            $wizard = '\\' === \DIRECTORY_SEPARATOR ? 'wizard' : 'wizard 🧙';
        }

        $typesTable = [
            'main' => [
                'string' => [],
                'text' => [],
                'boolean' => [],
                'integer' => ['smallint', 'bigint'],
                'float' => [],
            ],
            'relation' => [
                'relation' => 'a '.$wizard.' will help you build the relation',
                EntityRelation::MANY_TO_ONE => [],
                EntityRelation::ONE_TO_MANY => [],
                EntityRelation::MANY_TO_MANY => [],
                EntityRelation::ONE_TO_ONE => [],
            ],
            'array_object' => [
                'array' => ['simple_array'],
                'json' => [],
                'object' => [],
                'binary' => [],
                'blob' => [],
            ],
            'date_time' => [
                'datetime' => ['datetime_immutable'],
                'datetimetz' => ['datetimetz_immutable'],
                'date' => ['date_immutable'],
                'time' => ['time_immutable'],
                'dateinterval' => [],
            ],
        ];

        $printSection = static function (array $sectionTypes) use ($io, &$allTypes): void {
            foreach ($sectionTypes as $mainType => $subTypes) {
                unset($allTypes[$mainType]);
                $line = sprintf('  * <comment>%s</comment>', $mainType);

                if (\is_string($subTypes) && $subTypes) {
                    $line .= sprintf(' (%s)', $subTypes);
                } elseif (\is_array($subTypes) && $subTypes !== []) {
                    $line .= sprintf(
                        ' (or %s)',
                        implode(', ', array_map(
                            static fn ($subType): string => sprintf('<comment>%s</comment>', $subType),
                            $subTypes
                        ))
                    );

                    foreach ($subTypes as $subType) {
                        unset($allTypes[$subType]);
                    }
                }

                $io->writeln($line);
            }

            $io->writeln('');
        };

        $io->writeln('<info>Main Types</info>');
        $printSection($typesTable['main']);

        $io->writeln('<info>Relationships/Associations</info>');
        $printSection($typesTable['relation']);

        $io->writeln('<info>Array/Object Types</info>');
        $printSection($typesTable['array_object']);

        $io->writeln('<info>Date/Time Types</info>');
        $printSection($typesTable['date_time']);

        $io->writeln('<info>Other Types</info>');
        // empty the values
        $allTypes = array_map(static fn (): array => [], $allTypes);
        $printSection($allTypes);
    }

    private function askRelationDetails(ConsoleStyle $io, string $generatedEntityClass, string $type, string $newFieldName): EntityRelation
    {
        // ask the targetEntity
        $targetEntityClass = null;
        while ($targetEntityClass === null) {
            $question = $this->createEntityClassQuestion('What class should this entity be related to?');

            $answeredEntityClass = $io->askQuestion($question);

            // find the correct class name - but give priority over looking
            // in the Entity namespace versus just checking the full class
            // name to avoid issues with classes like "Directory" that exist
            // in PHP's core.
            if (class_exists($this->getEntityNamespace().'\\'.$answeredEntityClass)) {
                $targetEntityClass = $this->getEntityNamespace().'\\'.$answeredEntityClass;
            } elseif (class_exists($answeredEntityClass)) {
                $targetEntityClass = $answeredEntityClass;
            } else {
                $io->error(sprintf('Unknown class "%s"', $answeredEntityClass));

                continue;
            }
        }

        // help the user select the type
        if ($type === 'relation') {
            $type = $this->askRelationType($io, $generatedEntityClass, $targetEntityClass);
        }

        $askFieldName = fn (string $targetClass, string $defaultValue): mixed => $io->ask(
            sprintf('New field name inside %s', Str::getShortClassName($targetClass)),
            $defaultValue,
            function ($name) use ($targetClass): string {
                // it's still *possible* to create duplicate properties - by
                // trying to generate the same property 2 times during the
                // same make:entity run. property_exists() only knows about
                // properties that *originally* existed on this class.
                if (property_exists($targetClass, $name)) {
                    throw new InvalidArgumentException(sprintf('The "%s" class already has a "%s" property.', $targetClass, $name));
                }

                return Validator::validateDoctrineFieldName($name, $this->doctrineHelper->getRegistry());
            }
        );

        $askIsNullable = static fn (string $propertyName, string $targetClass): bool => $io->confirm(sprintf(
            'Is the <comment>%s</comment>.<comment>%s</comment> property allowed to be null (nullable)?',
            Str::getShortClassName($targetClass),
            $propertyName
        ));

        $askOrphanRemoval = static function (string $owningClass, string $inverseClass) use ($io): bool {
            $io->text([
                'Do you want to activate <comment>orphanRemoval</comment> on your relationship?',
                sprintf(
                    'A <comment>%s</comment> is "orphaned" when it is removed from its related <comment>%s</comment>.',
                    Str::getShortClassName($owningClass),
                    Str::getShortClassName($inverseClass)
                ),
                sprintf(
                    'e.g. <comment>$%s->remove%s($%s)</comment>',
                    Str::asLowerCamelCase(Str::getShortClassName($inverseClass)),
                    Str::asCamelCase(Str::getShortClassName($owningClass)),
                    Str::asLowerCamelCase(Str::getShortClassName($owningClass))
                ),
                '',
                sprintf(
                    'NOTE: If a <comment>%s</comment> may *change* from one <comment>%s</comment> to another, answer "no".',
                    Str::getShortClassName($owningClass),
                    Str::getShortClassName($inverseClass)
                ),
            ]);

            return $io->confirm(sprintf('Do you want to automatically delete orphaned <comment>%s</comment> objects (orphanRemoval)?', $owningClass), false);
        };

        $askInverseSide = function (EntityRelation $relation) use ($io): void {
            if ($this->isClassInVendor($relation->getInverseClass())) {
                $relation->setMapInverseRelation(false);

                return;
            }

            // recommend an inverse side, except for OneToOne, where it's inefficient
            $recommendMappingInverse = $relation->getType() !== EntityRelation::ONE_TO_ONE;

            $getterMethodName = 'get'.Str::asCamelCase(Str::getShortClassName($relation->getOwningClass()));
            if ($relation->getType() !== EntityRelation::ONE_TO_ONE) {
                // pluralize!
                $getterMethodName = Str::singularCamelCaseToPluralCamelCase($getterMethodName);
            }
            $mapInverse = $io->confirm(
                sprintf(
                    'Do you want to add a new property to <comment>%s</comment> so that you can access/update <comment>%s</comment> objects from it - e.g. <comment>$%s->%s()</comment>?',
                    Str::getShortClassName($relation->getInverseClass()),
                    Str::getShortClassName($relation->getOwningClass()),
                    Str::asLowerCamelCase(Str::getShortClassName($relation->getInverseClass())),
                    $getterMethodName
                ),
                $recommendMappingInverse
            );
            $relation->setMapInverseRelation($mapInverse);
        };

        switch ($type) {
            case EntityRelation::MANY_TO_ONE:
                $relation = new EntityRelation(
                    EntityRelation::MANY_TO_ONE,
                    $generatedEntityClass,
                    $targetEntityClass
                );
                $relation->setOwningProperty($newFieldName);

                $relation->setIsNullable($askIsNullable(
                    $relation->getOwningProperty(),
                    $relation->getOwningClass()
                ));

                $askInverseSide($relation);
                if ($relation->getMapInverseRelation()) {
                    $io->comment(sprintf(
                        'A new property will also be added to the <comment>%s</comment> class so that you can access the related <comment>%s</comment> objects from it.',
                        Str::getShortClassName($relation->getInverseClass()),
                        Str::getShortClassName($relation->getOwningClass())
                    ));
                    $relation->setInverseProperty($askFieldName(
                        $relation->getInverseClass(),
                        Str::singularCamelCaseToPluralCamelCase(Str::getShortClassName($relation->getOwningClass()))
                    ));

                    // orphan removal only applies if the inverse relation is set
                    if (!$relation->isNullable()) {
                        $relation->setOrphanRemoval($askOrphanRemoval(
                            $relation->getOwningClass(),
                            $relation->getInverseClass()
                        ));
                    }
                }

                break;

            case EntityRelation::ONE_TO_MANY:
                // we *actually* create a ManyToOne, but populate it differently
                $relation = new EntityRelation(
                    EntityRelation::MANY_TO_ONE,
                    $targetEntityClass,
                    $generatedEntityClass
                );
                $relation->setInverseProperty($newFieldName);

                $io->comment(sprintf(
                    'A new property will also be added to the <comment>%s</comment> class so that you can access and set the related <comment>%s</comment> object from it.',
                    Str::getShortClassName($relation->getOwningClass()),
                    Str::getShortClassName($relation->getInverseClass())
                ));
                $relation->setOwningProperty($askFieldName(
                    $relation->getOwningClass(),
                    Str::asLowerCamelCase(Str::getShortClassName($relation->getInverseClass()))
                ));

                $relation->setIsNullable($askIsNullable(
                    $relation->getOwningProperty(),
                    $relation->getOwningClass()
                ));

                if (!$relation->isNullable()) {
                    $relation->setOrphanRemoval($askOrphanRemoval(
                        $relation->getOwningClass(),
                        $relation->getInverseClass()
                    ));
                }

                break;

            case EntityRelation::MANY_TO_MANY:
                $relation = new EntityRelation(
                    EntityRelation::MANY_TO_MANY,
                    $generatedEntityClass,
                    $targetEntityClass
                );
                $relation->setOwningProperty($newFieldName);

                $askInverseSide($relation);
                if ($relation->getMapInverseRelation()) {
                    $io->comment(sprintf(
                        'A new property will also be added to the <comment>%s</comment> class so that you can access the related <comment>%s</comment> objects from it.',
                        Str::getShortClassName($relation->getInverseClass()),
                        Str::getShortClassName($relation->getOwningClass())
                    ));
                    $relation->setInverseProperty($askFieldName(
                        $relation->getInverseClass(),
                        Str::singularCamelCaseToPluralCamelCase(Str::getShortClassName($relation->getOwningClass()))
                    ));
                }

                break;

            case EntityRelation::ONE_TO_ONE:
                $relation = new EntityRelation(
                    EntityRelation::ONE_TO_ONE,
                    $generatedEntityClass,
                    $targetEntityClass
                );
                $relation->setOwningProperty($newFieldName);

                $relation->setIsNullable($askIsNullable(
                    $relation->getOwningProperty(),
                    $relation->getOwningClass()
                ));

                $askInverseSide($relation);
                if ($relation->getMapInverseRelation()) {
                    $io->comment(sprintf(
                        'A new property will also be added to the <comment>%s</comment> class so that you can access the related <comment>%s</comment> object from it.',
                        Str::getShortClassName($relation->getInverseClass()),
                        Str::getShortClassName($relation->getOwningClass())
                    ));
                    $relation->setInverseProperty($askFieldName(
                        $relation->getInverseClass(),
                        Str::asLowerCamelCase(Str::getShortClassName($relation->getOwningClass()))
                    ));
                }

                break;

            default:
                throw new InvalidArgumentException('Invalid type: '.$type);
        }

        return $relation;
    }

    private function askRelationType(ConsoleStyle $io, string $entityClass, ?string $targetEntityClass)
    {
        $io->writeln('What type of relationship is this?');

        $originalEntityShort = Str::getShortClassName($entityClass);
        $targetEntityShort = Str::getShortClassName($targetEntityClass);
        $rows = [];
        $rows[] = [
            EntityRelation::MANY_TO_ONE,
            sprintf("Each <comment>%s</comment> relates to (has) <info>one</info> <comment>%s</comment>.\nEach <comment>%s</comment> can relate to (can have) <info>many</info> <comment>%s</comment> objects.", $originalEntityShort, $targetEntityShort, $targetEntityShort, $originalEntityShort),
        ];
        $rows[] = ['', ''];
        $rows[] = [
            EntityRelation::ONE_TO_MANY,
            sprintf("Each <comment>%s</comment> can relate to (can have) <info>many</info> <comment>%s</comment> objects.\nEach <comment>%s</comment> relates to (has) <info>one</info> <comment>%s</comment>.", $originalEntityShort, $targetEntityShort, $targetEntityShort, $originalEntityShort),
        ];
        $rows[] = ['', ''];
        $rows[] = [
            EntityRelation::MANY_TO_MANY,
            sprintf("Each <comment>%s</comment> can relate to (can have) <info>many</info> <comment>%s</comment> objects.\nEach <comment>%s</comment> can also relate to (can also have) <info>many</info> <comment>%s</comment> objects.", $originalEntityShort, $targetEntityShort, $targetEntityShort, $originalEntityShort),
        ];
        $rows[] = ['', ''];
        $rows[] = [
            EntityRelation::ONE_TO_ONE,
            sprintf("Each <comment>%s</comment> relates to (has) exactly <info>one</info> <comment>%s</comment>.\nEach <comment>%s</comment> also relates to (has) exactly <info>one</info> <comment>%s</comment>.", $originalEntityShort, $targetEntityShort, $targetEntityShort, $originalEntityShort),
        ];

        $io->table([
            'Type',
            'Description',
        ], $rows);

        $question = new Question(sprintf(
            'Relation type? [%s]',
            implode(', ', EntityRelation::getValidRelationTypes())
        ));
        $question->setAutocompleterValues(EntityRelation::getValidRelationTypes());
        $question->setValidator(static function ($type) {
            if (!\in_array($type, EntityRelation::getValidRelationTypes(), true)) {
                throw new InvalidArgumentException(sprintf('Invalid type: use one of: %s', implode(', ', EntityRelation::getValidRelationTypes())));
            }

            return $type;
        });

        return $io->askQuestion($question);
    }

    private function createClassManipulator(string $path, ConsoleStyle $io, bool $overwrite): ClassSourceManipulator
    {
        $manipulator = new ClassSourceManipulator(
            sourceCode: $this->fileManager->getFileContents($path),
            overwrite: $overwrite,
        );

        $manipulator->setIo($io);

        return $manipulator;
    }

    private function getPathOfClass(string $class): string
    {
        return (new ClassDetails($class))->getPath();
    }

    private function isClassInVendor(string $class): bool
    {
        $path = $this->getPathOfClass($class);

        return $this->fileManager->isPathInVendor($path);
    }

    private function getPropertyNames(string $class): array
    {
        if (!class_exists($class)) {
            return [];
        }

        $reflClass = new ReflectionClass($class);

        return array_map(static fn (ReflectionProperty $prop): string => $prop->getName(), $reflClass->getProperties());
    }

    /**
     * @legacy Drop when Annotations are no longer supported
     */
    private function doesEntityUseAttributeMapping(string $className): bool
    {
        if (!class_exists($className)) {
            $otherClassMetadatas = $this->doctrineHelper->getMetadata(Str::getNamespace($className).'\\', true);

            // if we have no metadata, we should assume this is the first class being mapped
            if (empty($otherClassMetadatas)) {
                return false;
            }

            $className = reset($otherClassMetadatas)->getName();
        }

        return $this->doctrineHelper->doesClassUsesAttributes($className);
    }

    private function getEntityNamespace(): string
    {
        return $this->doctrineHelper->getEntityNamespace();
    }

    private function getTypesMap(): array
    {
        return Type::getTypesMap();
    }
}
