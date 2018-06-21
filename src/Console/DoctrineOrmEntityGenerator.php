<?php

declare(strict_types=1);

/*
 * This file is part of gpupo/common-sdk
 * Created by Gilmar Pupo <contact@gpupo.com>
 * For the information of copyright and license you should read the file
 * LICENSE which is distributed with this source code.
 * Para a informação dos direitos autorais e de licença você deve ler o arquivo
 * LICENSE que é distribuído com este código-fonte.
 * Para obtener la información de los derechos de autor y la licencia debe leer
 * el archivo LICENSE que se distribuye con el código fuente.
 * For more information, see <https://opensource.gpupo.com/>.
 *
 */

namespace Gpupo\CommonSdk\Console;

use Gpupo\CommonSchema\ArrayCollection\Thing\CollectionInterface;
use Gpupo\CommonSchema\ArrayCollection\Thing\EntityInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Yaml;

class DoctrineOrmEntityGenerator
{
    protected $input;
    protected $output;

    public function __construct(ArgvInput $input, ConsoleOutput $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function saveDataToSchema($key, $value, array $data)
    {
        $file = sprintf('Resources/schema/v2/%s.yaml', $key);
        $content = sprintf("# common-schema %s $ generated at %s\n", $key, date('Y-m-d')).Yaml::dump($data, 8);

        return $this->save($file, $content);
    }

    public function saveDataDoctrineMetadata($object)
    {
        $class = get_class($object);
        $explode = explode('\\', $class);

        $abstractList = [
            // 'People',
            // 'Thing',
            // 'Organization',
        ];

        $subnamespace = $explode[3];
        $lastname = end($explode);
        if (in_array($subnamespace, $abstractList, true)) {
            $this->output->writeln(sprintf('Namespace <fg=yellow> %s </> is abstract. Ignoring <bg=black> %s </>', $subnamespace, $class));

            return;
        }
        //$this->output->writeln(sprintf(' - Calculating metadata for <bg=black> %s </> ...', $class));
        $classNames = $this->processClassNames($object, $class);
        $table = $object->getTableName();
        $doctrine = [
                'type' => 'entity',
                'table' => $table,
                'repositoryClass' => $classNames['repository'],
                'id' => [
                    'id' => ['type' => 'integer', 'generator' => ['strategy' => 'AUTO']],
                ],
            ];

        foreach ($object->getSchema() as $key => $value) {
            if ('id' === $key) {
                continue;
            }
            if ('object' === $value) {
                //$this->output->writeln(sprintf('   * Calculating <fg=yellow> association mapping </> for <fg=yellow> %s </> ...', $key));
                $normalizedKey = $this->removePlural($key);

                $meta = $this->generateDoctrineObject($object, $key, $value, $lastname);

                if ('manyToMany' === $meta['associationMappingType']) {
                    $propertyKey = $normalizedKey.'s';
                } else {
                    $propertyKey = $normalizedKey;
                }

                $doctrine[$meta['associationMappingType']][$propertyKey] = $meta['spec'];
                $this->output->writeln(sprintf('     - Key is <bg=black;fg=white> %s </> and Association type is <bg=white;fg=blue> %s </>', $propertyKey, $meta['associationMappingType']));
            } else {
                //$this->output->writeln(sprintf('   * Calculating metadata field <bg=blue> %s </> ...', $key));
                $doctrine['fields'][$key] = $this->generateDoctrineField($key, $value);
            }

            $this->recursiveSaveDataDoctrineMetadata($object->get($key));
        }

        $doctrine['lifecycleCallbacks'] = [
                'prePersist' => [],
                'postPersist' => [],
            ];

        $entity = [$classNames['to'] => $doctrine];
        $file = sprintf('config/yaml/%s.dcm.yml', str_replace('\\', '.', $classNames['to']));

        $content = sprintf("# %s metadata\n", $key).Yaml::dump($entity, 8, 2);

        return $this->save($file, $content);
    }

    public function recursiveSaveDataDoctrineMetadata($object)
    {
        if (!is_object($object)) {
            //String or Array
            return;
        }
        if ($object instanceof CollectionInterface) {
            return $this->recursiveSaveDataDoctrineMetadata($object->factoryElement([]));
        }

        if (!$object instanceof EntityInterface) {
            die(sprintf('Class %s must implement %s', get_class($object), EntityInterface::class));
        }

        $this->saveDataDoctrineMetadata($object);

        foreach ($object as $prop) {
            if (is_object($prop)) {
                $this->recursiveSaveDataDoctrineMetadata($prop);
            }
        }
    }

    protected function removePlural($string)
    {
        return rtrim($string, 's');
    }

    protected function processClassNames($object, $class)
    {
        $target = $class;

        if ($object instanceof CollectionInterface) {
            $target = get_class($object->factoryElement([]));
        }

        return [
            'to' => str_replace('ArrayCollection', 'ORM\Entity', $target),
            'repository' => str_replace('ArrayCollection', 'ORM\Repository', $target).'Repository',
        ];
    }

    protected function save($file, $content)
    {
        file_put_contents($file, $content);
        $this->output->writeln(sprintf('Generated <fg=green> %s </>', $file));
    }

    protected function generateDoctrineObject($object, $key, $value, $lastname)
    {
        $lastname = strtolower($lastname);
        $normalizedKey = $this->removePlural($key);
        $targetObject = $object->get($key);
        $targetEntity = get_class($targetObject);
        $plural = $normalizedKey.'s';

        if ($targetObject instanceof CollectionInterface) {
            $associationMappingType = $targetObject->getAssociationMappingType();

        } else {
            $associationMappingType = 'oneToOne';
        }

        $classNames = $this->processClassNames($targetObject, $targetEntity);
        $spec = [
                'targetEntity' => $classNames['to'],
                'options' => [],
            ];


        if ('manyToMany' === $associationMappingType) {
            $spec = array_merge($spec, [
                    'joinTable' => [
                        'name' => sprintf('cs_pivot_%s_to_%s', $lastname, $plural),
                        'joinColumns' => [
                            sprintf('%s_id', $normalizedKey) => [
                                'referencedColumnName' => 'id',
                            ],
                        ],
                        'inverseJoinColumns' => [
                            sprintf('%s_id', $normalizedKey) => [
                                'referencedColumnName' => 'id',
                                'unique' => true,
                            ],
                        ],
                    ]
                ]);
        }

        return [
            'associationMappingType' => $associationMappingType,
            'spec' => $spec,
        ];
    }

    protected function generateDoctrineField($key, $value)
    {
        switch ($value) {
                case 'string':
                    $spec = [
                        'type' => 'string',
                        'lenght' => 50,
                        'unique' => false,
                        'options' => [],
                    ];

                    break;
                case 'datetime':
                    $spec = [
                        'type' => 'datetime',
                        'options' => [],
                    ];

                    break;
                case 'number':
                    $spec = [
                        'type' => 'decimal',
                        'precision' => 10,
                        'scale' => 2,
                        'options' => [],
                    ];

                    break;
                case 'integer':
                    $spec = [
                        'type' => 'bigint',
                        'options' => [],
                    ];

                    break;
                case 'array':
                    $spec = [
                        'type' => 'array',
                        'options' => [],
                    ];

                    break;
                case 'boolean':
                    $spec = [
                        'type' => 'boolean',
                        'options' => [],
                    ];

                    break;
                default:
                    throw new \Exception(sprintf('Type %s not found', $value));

                    break;
            }

        //$spec['options']['comment'] = '';

        return $spec;
    }
}
