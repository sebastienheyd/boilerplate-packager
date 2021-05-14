<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

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

            $deletedAt = $uniqueIndex->filter(function ($index) {
                return $index->hasOption('where') && $index->getOption('where') == '(deleted_at IS NULL)';
            });

            $required = boolval(Schema::getConnection()->getDoctrineColumn($table, $column)->getNotnull());

            return [
                'name'                        => $column,
                'type'                        => Schema::getColumnType($table, $column),
                'required'                    => $required,
                'unique'                      => $uniqueIndex->count() > 0,
                'unique_deleted_at_condition' => $deletedAt->count() > 0,
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
                    ];
                }

                if($table->getName() === $tableName) {
                    $relations['belongsTo'][] = [
                        'method' => $fk->getForeignTableName(),
                        'model' => $this->getClassFromRelationTable($fk->getForeignTableName()),
                    ];
                }
            }
        }

        return $relations;
    }

    private function getClassFromRelationTable($table)
    {
        return Str::studly(Str::singular($table));
    }
}
