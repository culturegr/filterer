<?php

namespace CultureGr\Filterer\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeCustomFilterCommand extends GeneratorCommand {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:custom-filter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new custom filter class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'CustomFilter';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return dirname(__DIR__) . '/stubs/custom-filter.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\CustomFilters';
    }
}
