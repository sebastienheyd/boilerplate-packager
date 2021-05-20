<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Doctrine\DBAL\Types\StringType;
use Illuminate\Console\Command as BaseCommand;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Command extends BaseCommand
{
    protected $storage;

    public function __construct()
    {
        parent::__construct();

        $this->storage = Storage::disk('packages');
    }

    protected function getNamespace($package)
    {
        [$vendor, $package] = explode('/', $package);
        return Str::studly($vendor).'\\'.Str::studly($package);
    }

    protected function getColumnsFromTable($table)
    {
        $indexes = collect(Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes($table));
        return collect(Schema::getColumnListing($table))->map(function ($column) use ($table, $indexes) {
            $uniqueIndex = $indexes->filter(function ($index) use ($column) {
                return in_array($column, $index->getColumns()) && ($index->isUnique() && !$index->isPrimary());
            });

            $required = boolval(Schema::getConnection()->getDoctrineColumn($table, $column)->getNotnull());

            return [
                'name'        => $column,
                'type'        => Schema::getColumnType($table, $column),
                'required'    => $required,
                'unique'      => $uniqueIndex->count() > 0,
            ];
        });
    }

    protected function getTableRelations($tableName)
    {
        $tables = Schema::getConnection()->getDoctrineSchemaManager()->listTables();

        $relations = [];

        /** @var \Doctrine\DBAL\Schema\Table $table */
        foreach ($tables as $table) {

            if (preg_match('#^([a-z]+)_([a-z]+)$#', $table->getName())) {
                $return = false;
                $foreignTable = [];

                foreach ($table->getForeignKeys() as $fk) {
                    if ($fk->getForeignTableName() === $tableName) {
                        $return = true;
                    } else {
                        $foreignTable = [
                            'method' => $fk->getForeignTableName(),
                            'model' => $this->getClassFromRelationTable($fk->getForeignTableName()),
                            'labelField' => $this->getTableLabelField($fk->getForeignTableName()),
                            'idField' => $this->getTableIdField($fk->getForeignTableName()),
                            'required' => false,
                        ];
                    }
                }

                if ($return) {
                    $relations['belongsToMany'][] = $foreignTable;
                    continue;
                }
            }

            foreach ($table->getForeignKeys() as $fk) {
                if($fk->getForeignTableName() === $tableName) {
                    $relations['hasMany'][] = [
                        'method' => $fk->getLocalTableName(),
                        'model' => $this->getClassFromRelationTable($fk->getLocalTableName()),
                        'labelField' => $this->getTableLabelField($fk->getLocalTableName()),
                        'idField' => $this->getTableIdField($fk->getLocalTableName()),
                        'required' => false,
                    ];
                }

                if($table->getName() === $tableName) {

                    $req = Schema::getConnection()->getDoctrineColumn($tableName, $fk->getColumns()[0])->getNotnull();

                    $relations['belongsTo'][] = [
                        'method' => $fk->getForeignTableName(),
                        'model' => $this->getClassFromRelationTable($fk->getForeignTableName()),
                        'labelField' => $this->getTableLabelField($fk->getForeignTableName()),
                        'idField' => $this->getTableIdField($fk->getForeignTableName()),
                        'required' => boolval($req),
                    ];
                }
            }
        }

        return $relations;
    }

    private function getTableIdField($table)
    {
        $columns = Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns($table);

        foreach ($columns as $column) {
            if($column->getAutoincrement()) {
                return $column->getName();
            }
        }

        return false;
    }

    /**
     * Get the first label field from table structure.
     *
     * @param string $table
     *
     * @return false|string
     */
    private function getTableLabelField($table)
    {
        $columns = Schema::getColumnListing($table);

        foreach (['label', 'title', 'name', 'first_name'] as $field) {
            if (in_array($field, $columns)) {
                return $field;
            }
        }

        $columns = Schema::getConnection()->getDoctrineSchemaManager()->listTableColumns($table);

        foreach ($columns as $column) {
            if(get_class($column->getType()) === StringType::class) {
                return $column->getName();
            }
        }

        return false;
    }

    /**
     * Get model class name from table name.
     *
     * @param string $table
     *
     * @return string
     */
    private function getClassFromRelationTable($table)
    {
        return Str::studly(Str::singular($table));
    }
}
